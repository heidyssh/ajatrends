<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Sale {

  /* ===========================
     LISTADO / DETALLE
  =========================== */

  public static function list(array $filters = []): array {
    $q = trim((string)($filters['q'] ?? ''));
    $estado = trim((string)($filters['estado'] ?? 'TODOS'));
    $from = trim((string)($filters['from'] ?? ''));
    $to   = trim((string)($filters['to'] ?? ''));

    $where = [];
    $params = [];

    if ($q !== '') {
      // Buscar por id_venta o por nota o por cliente
      $where[] = '(v.id_venta = :idq OR v.nota LIKE :nota OR cl.nombre LIKE :cliente)';
      $params[':idq'] = ctype_digit($q) ? (int)$q : 0;
      $params[':nota'] = "%$q%";
      $params[':cliente'] = "%$q%";
    }

    if ($estado !== '' && $estado !== 'TODOS') {
      $where[] = 'v.estado = :estado';
      $params[':estado'] = $estado;
    }

    if ($from !== '') {
      $where[] = 'DATE(v.fecha) >= :from';
      $params[':from'] = $from;
    }
    if ($to !== '') {
      $where[] = 'DATE(v.fecha) <= :to';
      $params[':to'] = $to;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
      SELECT
        v.id_venta,
        v.fecha,
        v.estado,
        v.subtotal,
        v.descuento,
        v.total,
        v.nota,
        u.nombre AS usuario,
        cl.nombre AS cliente,
        COALESCE(SUM(vd.cantidad),0) AS items
      FROM ventas v
      INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
      INNER JOIN clientes cl ON cl.id_cliente = v.id_cliente
      LEFT JOIN ventas_detalle vd ON vd.id_venta = v.id_venta
      $whereSql
      GROUP BY v.id_venta
      ORDER BY v.id_venta DESC
    ";

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }

  public static function find(int $idVenta): ?array {
    $st = db()->prepare("
      SELECT
        v.*,
        u.nombre AS usuario,
        cl.nombre AS cliente,
        d.direccion_linea,
        d.ciudad,
        d.referencia
      FROM ventas v
      INNER JOIN usuarios u ON u.id_usuario = v.id_usuario
      INNER JOIN clientes cl ON cl.id_cliente = v.id_cliente
      INNER JOIN cliente_direcciones d ON d.id_direccion = v.id_direccion
      WHERE v.id_venta = :id
      LIMIT 1
    ");
    $st->execute([':id' => $idVenta]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function items(int $idVenta): array {
    $st = db()->prepare("
      SELECT
        vd.id_venta_det,
        vd.id_producto,
        p.sku,
        p.nombre,
        vd.cantidad,
        vd.precio_unit,
        vd.subtotal
      FROM ventas_detalle vd
      INNER JOIN productos p ON p.id_producto = vd.id_producto
      WHERE vd.id_venta = :id
      ORDER BY vd.id_venta_det ASC
    ");
    $st->execute([':id' => $idVenta]);
    return $st->fetchAll();
  }

  /* ===========================
     CATALOGOS
  =========================== */

  public static function clientes(): array {
    return db()->query("
      SELECT id_cliente, nombre, telefono, email
      FROM clientes
      ORDER BY nombre ASC
    ")->fetchAll();
  }

  public static function direccionesByCliente(int $idCliente): array {
    $st = db()->prepare("
      SELECT id_direccion, direccion_linea, ciudad, referencia, es_principal
      FROM cliente_direcciones
      WHERE id_cliente = :c
      ORDER BY es_principal DESC, id_direccion ASC
    ");
    $st->execute([':c' => $idCliente]);
    return $st->fetchAll();
  }

  public static function productosActivosConStock(): array {
    // stock actual = ultimo stock_despues en inventario_mov_detalle
    $sql = "
      SELECT
        p.id_producto, p.sku, p.nombre, p.precio,
        p.id_categoria,
        c.nombre AS categoria,
        COALESCE((
          SELECT imd.stock_despues
          FROM inventario_mov_detalle imd
          WHERE imd.id_producto = p.id_producto
          ORDER BY imd.id_mov_det DESC
          LIMIT 1
        ), 0) AS stock
      FROM productos p
      LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
      WHERE p.estado = 1
      ORDER BY p.nombre ASC
    ";
    return db()->query($sql)->fetchAll();
  }

  public static function stockActual(int $idProducto): int {
    $st = db()->prepare("
      SELECT imd.stock_despues
      FROM inventario_mov_detalle imd
      WHERE imd.id_producto = :p
      ORDER BY imd.id_mov_det DESC
      LIMIT 1
    ");
    $st->execute([':p' => $idProducto]);
    $r = $st->fetch();
    return $r ? (int)$r['stock_despues'] : 0;
  }

  /**
   * Costo estimado actual: último costo_unit de ENTRADA_COMPRA para ese producto.
   * (Esto sirve para estadística de utilidad: ganancia ≈ ventas - costo)
   */
  public static function costoEstimado(int $idProducto): float {
    $st = db()->prepare("
      SELECT imd.costo_unit
      FROM inventario_mov_detalle imd
      INNER JOIN inventario_movimientos im ON im.id_mov = imd.id_mov
      WHERE imd.id_producto = :p
        AND im.tipo = 'ENTRADA_COMPRA'
      ORDER BY imd.id_mov_det DESC
      LIMIT 1
    ");
    $st->execute([':p' => $idProducto]);
    $r = $st->fetch();
    return $r ? (float)$r['costo_unit'] : 0.0;
  }

  /* ===========================
     CREAR VENTA (SALIDA STOCK)
  =========================== */

  private static function resolveUserId(PDO $pdo, int $preferred): int {
    if ($preferred > 0) {
      $st = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario=:u LIMIT 1");
      $st->execute([':u' => $preferred]);
      if ($st->fetch()) return $preferred;
    }
    $st = $pdo->query("SELECT id_usuario FROM usuarios ORDER BY id_usuario ASC LIMIT 1");
    $r = $st->fetch();
    if ($r) return (int)$r['id_usuario'];
    throw new Exception("No existe ningún usuario en la tabla usuarios. Creá al menos 1 usuario admin.");
  }

  public static function createVenta(
    int $idUsuario,
    int $idCliente,
    int $idDireccion,
    float $descuento,
    string $nota,
    array $items
  ): int {

    if ($idCliente <= 0) throw new Exception('Elegí un cliente.');
    if ($idDireccion <= 0) throw new Exception('Elegí una dirección del cliente.');
    if (!$items) throw new Exception('Agregá al menos 1 producto.');
    if ($descuento < 0) $descuento = 0;

    $pdo = db();
    $pdo->beginTransaction();

    try {
      $uid = self::resolveUserId($pdo, $idUsuario);

      // validar cliente/direccion
      $st = $pdo->prepare("SELECT id_cliente FROM clientes WHERE id_cliente=:c LIMIT 1");
      $st->execute([':c' => $idCliente]);
      if (!$st->fetch()) throw new Exception('Cliente inválido.');

      $st = $pdo->prepare("SELECT id_direccion FROM cliente_direcciones WHERE id_direccion=:d AND id_cliente=:c LIMIT 1");
      $st->execute([':d' => $idDireccion, ':c' => $idCliente]);
      if (!$st->fetch()) throw new Exception('Dirección inválida para ese cliente.');

      // calcular subtotal
      $subtotal = 0.0;
      foreach ($items as $it) {
        $cant = (int)($it['cantidad'] ?? 0);
        $pu = (float)($it['precio_unit'] ?? 0);
        if ($cant > 0 && $pu >= 0) $subtotal += ($cant * $pu);
      }

      $total = max(0, $subtotal - $descuento);
      if (trim($nota) === '') $nota = 'Venta registrada';

      // 1) insertar venta
      $st = $pdo->prepare("
        INSERT INTO ventas
          (id_usuario, id_cliente, id_direccion, fecha, estado, subtotal, descuento, total, nota)
        VALUES
          (:u, :c, :d, NOW(), 'PENDIENTE', :sub, :des, :tot, :nota)
      ");
      $st->execute([
        ':u' => $uid,
        ':c' => $idCliente,
        ':d' => $idDireccion,
        ':sub' => $subtotal,
        ':des' => $descuento,
        ':tot' => $total,
        ':nota' => $nota,
      ]);
      $idVenta = (int)$pdo->lastInsertId();

      // 2) movimiento inventario SALIDA_VENTA
      $st = $pdo->prepare("
        INSERT INTO inventario_movimientos (fecha, tipo, ref_tabla, ref_id, id_usuario, nota)
        VALUES (NOW(), 'SALIDA_VENTA', 'ventas', :rid, :u, :nota)
      ");
      $st->execute([':rid' => $idVenta, ':u' => $uid, ':nota' => $nota]);
      $idMov = (int)$pdo->lastInsertId();

      $stDet = $pdo->prepare("
        INSERT INTO ventas_detalle (id_venta, id_producto, cantidad, precio_unit, subtotal)
        VALUES (:v, :p, :cant, :pu, :sub)
      ");

      $stMovDet = $pdo->prepare("
        INSERT INTO inventario_mov_detalle
          (id_mov, id_producto, cantidad, costo_unit, stock_antes, stock_despues)
        VALUES
          (:m, :p, :cant, :cu, :antes, :despues)
      ");

      foreach ($items as $it) {
        $idProducto = (int)($it['id_producto'] ?? 0);
        $cantidad   = (int)($it['cantidad'] ?? 0);
        $pu         = (float)($it['precio_unit'] ?? 0);

        if ($idProducto <= 0 || $cantidad <= 0) continue;

        $stockAntes = self::stockActual($idProducto);
        if ($stockAntes < $cantidad) {
          throw new Exception("Stock insuficiente para el producto #$idProducto. Stock: $stockAntes, solicitado: $cantidad.");
        }

        $sub = $cantidad * $pu;

        $stDet->execute([
          ':v' => $idVenta,
          ':p' => $idProducto,
          ':cant' => $cantidad,
          ':pu' => $pu,
          ':sub' => $sub,
        ]);

        // cantidad negativa para reflejar salida
        $cantMov = -abs($cantidad);
        $stockDespues = $stockAntes - $cantidad;

        $cu = self::costoEstimado($idProducto); // estimado para tu estadística

        $stMovDet->execute([
          ':m' => $idMov,
          ':p' => $idProducto,
          ':cant' => $cantMov,
          ':cu' => $cu,
          ':antes' => $stockAntes,
          ':despues' => $stockDespues,
        ]);
      }

      // validar al menos 1 item guardado
      $st = $pdo->prepare("SELECT COUNT(*) n FROM ventas_detalle WHERE id_venta=:v");
      $st->execute([':v' => $idVenta]);
      if ((int)($st->fetch()['n'] ?? 0) <= 0) {
        throw new Exception("No se guardó detalle de venta.");
      }

      $pdo->commit();
      return $idVenta;

    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  /* ===========================
     ANULAR VENTA (DEVUELVE STOCK)
  =========================== */

  public static function cancel(int $idVenta, int $idUsuario, string $nota = 'Venta anulada'): void {
    $venta = self::find($idVenta);
    if (!$venta) throw new Exception('La venta no existe.');
    if ((string)$venta['estado'] === 'ANULADA') throw new Exception('Esta venta ya está ANULADA.');

    $items = self::items($idVenta);
    if (!$items) throw new Exception('Esta venta no tiene detalle.');

    $pdo = db();
    $pdo->beginTransaction();

    try {
      $uid = self::resolveUserId($pdo, $idUsuario);

      // 1) marcar venta anulada
      $st = $pdo->prepare("UPDATE ventas SET estado='ANULADA' WHERE id_venta=:v");
      $st->execute([':v' => $idVenta]);

      // 2) movimiento inventario ENTRADA_ANULACION_VENTA (devuelve stock)
      $st = $pdo->prepare("
        INSERT INTO inventario_movimientos (fecha, tipo, ref_tabla, ref_id, id_usuario, nota)
        VALUES (NOW(), 'ENTRADA_ANULACION_VENTA', 'ventas', :rid, :u, :nota)
      ");
      $st->execute([':rid' => $idVenta, ':u' => $uid, ':nota' => $nota]);
      $idMov = (int)$pdo->lastInsertId();

      $stMovDet = $pdo->prepare("
        INSERT INTO inventario_mov_detalle
          (id_mov, id_producto, cantidad, costo_unit, stock_antes, stock_despues)
        VALUES
          (:m, :p, :cant, :cu, :antes, :despues)
      ");

      foreach ($items as $it) {
        $idProducto = (int)$it['id_producto'];
        $cantidad = (int)$it['cantidad'];

        $stockAntes = self::stockActual($idProducto);
        $stockDespues = $stockAntes + $cantidad; // devuelve

        $cu = self::costoEstimado($idProducto);

        $stMovDet->execute([
          ':m' => $idMov,
          ':p' => $idProducto,
          ':cant' => abs($cantidad),
          ':cu' => $cu,
          ':antes' => $stockAntes,
          ':despues' => $stockDespues,
        ]);
      }

      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  /* ===========================
     ESTADÍSTICA (KPIs + Top + Series)
  =========================== */

  private static function dateWhere(array $filters, array &$params): string {
    $from = trim((string)($filters['from'] ?? ''));
    $to   = trim((string)($filters['to'] ?? ''));

    $w = ["v.estado <> 'ANULADA'"];

    if ($from !== '') { $w[] = "DATE(v.fecha) >= :from"; $params[':from'] = $from; }
    if ($to !== '')   { $w[] = "DATE(v.fecha) <= :to";   $params[':to']   = $to; }

    return 'WHERE ' . implode(' AND ', $w);
  }

  public static function kpis(array $filters = []): array {
    $params = [];
    $where = self::dateWhere($filters, $params);

    $st = db()->prepare("
      SELECT
        COUNT(*) AS ventas,
        COALESCE(SUM(v.total),0) AS total,
        COALESCE(SUM(v.subtotal),0) AS subtotal,
        COALESCE(SUM(v.descuento),0) AS descuento,
        COALESCE(AVG(v.total),0) AS ticket_prom,
        COALESCE(SUM( (SELECT COALESCE(SUM(vd.cantidad),0) FROM ventas_detalle vd WHERE vd.id_venta=v.id_venta) ),0) AS unidades,
        COALESCE(COUNT(DISTINCT v.id_cliente),0) AS clientes_unicos
      FROM ventas v
      $where
    ");
    $st->execute($params);
    $r = $st->fetch() ?: [];

    // estimación utilidad: suma (ingreso - costo_est*qty)
    $st = db()->prepare("
      SELECT
        COALESCE(SUM(vd.subtotal),0) AS ingreso_items,
        COALESCE(SUM(vd.cantidad * COALESCE((
          SELECT imd.costo_unit
          FROM inventario_mov_detalle imd
          INNER JOIN inventario_movimientos im ON im.id_mov = imd.id_mov
          WHERE imd.id_producto = vd.id_producto AND im.tipo='ENTRADA_COMPRA'
          ORDER BY imd.id_mov_det DESC
          LIMIT 1
        ),0)),0) AS costo_est
      FROM ventas_detalle vd
      INNER JOIN ventas v ON v.id_venta = vd.id_venta
      $where
    ");
    $st->execute($params);
    $x = $st->fetch() ?: [];

    $ingreso = (float)($x['ingreso_items'] ?? 0);
    $costo = (float)($x['costo_est'] ?? 0);
    $util = $ingreso - $costo;

    return [
      'ventas' => (int)($r['ventas'] ?? 0),
      'total' => (float)($r['total'] ?? 0),
      'subtotal' => (float)($r['subtotal'] ?? 0),
      'descuento' => (float)($r['descuento'] ?? 0),
      'ticket_prom' => (float)($r['ticket_prom'] ?? 0),
      'unidades' => (int)($r['unidades'] ?? 0),
      'clientes_unicos' => (int)($r['clientes_unicos'] ?? 0),
      'costo_est' => $costo,
      'util_est' => $util,
      'margen_est' => ($ingreso > 0) ? ($util / $ingreso) : 0,
    ];
  }

  public static function seriesDiaria(array $filters = [], int $days = 14): array {
    // Si no mandan from/to, damos últimos N días
    $params = [];
    $from = trim((string)($filters['from'] ?? ''));
    $to   = trim((string)($filters['to'] ?? ''));

    if ($from === '' && $to === '') {
      $params[':from'] = date('Y-m-d', strtotime("-$days days"));
      $params[':to']   = date('Y-m-d');
      $filters = ['from' => $params[':from'], 'to' => $params[':to']];
    }

    $params = [];
    $where = self::dateWhere($filters, $params);

    $st = db()->prepare("
      SELECT DATE(v.fecha) AS dia, COALESCE(SUM(v.total),0) AS total
      FROM ventas v
      $where
      GROUP BY DATE(v.fecha)
      ORDER BY dia ASC
    ");
    $st->execute($params);
    return $st->fetchAll();
  }

  public static function topProductos(array $filters = [], int $limit = 8): array {
    $params = [];
    $where = self::dateWhere($filters, $params);

    $limit = max(1, min(20, $limit));

    $sql = "
      SELECT
        p.id_producto,
        p.nombre,
        p.sku,
        COALESCE(SUM(vd.cantidad),0) AS unidades,
        COALESCE(SUM(vd.subtotal),0) AS ingreso,
        COALESCE(SUM(vd.cantidad * COALESCE((
          SELECT imd.costo_unit
          FROM inventario_mov_detalle imd
          INNER JOIN inventario_movimientos im ON im.id_mov = imd.id_mov
          WHERE imd.id_producto = vd.id_producto AND im.tipo='ENTRADA_COMPRA'
          ORDER BY imd.id_mov_det DESC
          LIMIT 1
        ),0)),0) AS costo_est
      FROM ventas_detalle vd
      INNER JOIN ventas v ON v.id_venta = vd.id_venta
      INNER JOIN productos p ON p.id_producto = vd.id_producto
      $where
      GROUP BY p.id_producto
      ORDER BY ingreso DESC
      LIMIT $limit
    ";

    $st = db()->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();

    // agregar utilidad estimada
    foreach ($rows as &$r) {
      $ing = (float)$r['ingreso'];
      $cos = (float)$r['costo_est'];
      $r['util_est'] = $ing - $cos;
      $r['margen_est'] = ($ing > 0) ? (($ing - $cos) / $ing) : 0;
    }
    return $rows;
  }

  public static function ventasPorCategoria(array $filters = [], int $limit = 8): array {
    $params = [];
    $where = self::dateWhere($filters, $params);
    $limit = max(1, min(20, $limit));

    $sql = "
      SELECT
        c.nombre AS categoria,
        COALESCE(SUM(vd.cantidad),0) AS unidades,
        COALESCE(SUM(vd.subtotal),0) AS ingreso
      FROM ventas_detalle vd
      INNER JOIN ventas v ON v.id_venta = vd.id_venta
      INNER JOIN productos p ON p.id_producto = vd.id_producto
      LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
      $where
      GROUP BY c.id_categoria
      ORDER BY ingreso DESC
      LIMIT $limit
    ";

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }
}