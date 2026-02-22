<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Purchase {

  public static function list(array $filters = []): array {
    $q = trim((string)($filters['q'] ?? ''));
    $estado = trim((string)($filters['estado'] ?? ''));

    $where = [];
    $params = [];

    if ($q !== '') {
      $where[] = '(c.id_compra = :idq OR c.nota LIKE :nota)';
      $params[':idq'] = ctype_digit($q) ? (int)$q : 0;
      $params[':nota'] = "%$q%";
    }
    if ($estado !== '' && $estado !== 'TODOS') {
      $where[] = 'c.estado = :estado';
      $params[':estado'] = $estado;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
      SELECT
        c.id_compra,
        c.id_oc,
        c.id_usuario,
        u.nombre AS usuario,
        c.fecha,
        c.estado,
        c.nota,
        COALESCE(SUM(cd.cantidad), 0) AS items,
        COALESCE(SUM(cd.cantidad * cd.costo_unit), 0) AS total
      FROM compras c
      INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
      LEFT JOIN compras_detalle cd ON cd.id_compra = c.id_compra
      $whereSql
      GROUP BY c.id_compra
      ORDER BY c.id_compra DESC
    ";

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
  }

  public static function find(int $idCompra): ?array {
    $st = db()->prepare("
      SELECT c.*, u.nombre AS usuario
      FROM compras c
      INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
      WHERE c.id_compra = :id
      LIMIT 1
    ");
    $st->execute([':id' => $idCompra]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function items(int $idCompra): array {
    $st = db()->prepare("
      SELECT
        cd.id_compra_det,
        cd.id_producto,
        p.sku,
        p.nombre,
        cd.cantidad,
        cd.costo_unit,
        (cd.cantidad * cd.costo_unit) AS subtotal
      FROM compras_detalle cd
      INNER JOIN productos p ON p.id_producto = cd.id_producto
      WHERE cd.id_compra = :id
      ORDER BY cd.id_compra_det ASC
    ");
    $st->execute([':id' => $idCompra]);
    return $st->fetchAll();
  }

  /** Stock actual por producto: último stock_despues en inventario_mov_detalle */
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

  /** precio del producto (para guardarlo como costo_unit en tu tabla compras_detalle) */
  public static function productoPrecio(int $idProducto): float {
    $st = db()->prepare("SELECT precio FROM productos WHERE id_producto=:p LIMIT 1");
    $st->execute([':p' => $idProducto]);
    $r = $st->fetch();
    return $r ? (float)$r['precio'] : 0.0;
  }

  public static function productosActivos(): array {
    $st = db()->query("
      SELECT id_producto, sku, nombre, precio
      FROM productos
      WHERE estado = 1
      ORDER BY nombre ASC
    ");
    return $st->fetchAll();
  }

  /**
   * Registra un pedido/compra y BAJA stock (SALIDA).
   * Usa: compras, compras_detalle, inventario_movimientos, inventario_mov_detalle.
   */
public static function createCompra(int $idUsuario, string $nota, array $items): int {

  if (trim($nota) === '') $nota = 'Compra registrada';
  if (!$items) throw new Exception('Agregá al menos 1 producto.');

  $pdo = db();
  $pdo->beginTransaction();

  try {

    // ✅ Asegurar usuario válido (FK compras.id_usuario)
    if ($idUsuario <= 0) {
      // fallback: tomar el primer usuario (idealmente admin)
      $st = $pdo->query("SELECT id_usuario FROM usuarios ORDER BY id_usuario ASC LIMIT 1");
      $r = $st->fetch();
      $idUsuario = $r ? (int)$r['id_usuario'] : 0;
    }

    if ($idUsuario <= 0) {
      throw new Exception("No hay usuarios en la tabla usuarios. Creá al menos un usuario admin.");
    }

    $st = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario=:u LIMIT 1");
    $st->execute([':u' => $idUsuario]);
    if (!$st->fetch()) {
      throw new Exception("El usuario $idUsuario no existe en usuarios. Cerrá sesión y volvé a entrar.");
    }

    // 1) Crear compra (✅ con id_usuario)
    $st = $pdo->prepare("
      INSERT INTO compras (id_oc, id_usuario, fecha, estado, nota)
      VALUES (NULL, :u, NOW(), 'REGISTRADA', :nota)
    ");
    $st->execute([':u' => $idUsuario, ':nota' => $nota]);
    $idCompra = (int)$pdo->lastInsertId();

    // 2) Movimiento inventario ENTRADA (compra suma stock)
    $st = $pdo->prepare("
      INSERT INTO inventario_movimientos (fecha, tipo, ref_tabla, ref_id, id_usuario, nota)
      VALUES (NOW(), 'ENTRADA_COMPRA', 'compras', :rid, :u, :nota)
    ");
    $st->execute([':rid' => $idCompra, ':u' => $idUsuario, ':nota' => $nota]);
    $idMov = (int)$pdo->lastInsertId();

    $stDet = $pdo->prepare("
      INSERT INTO compras_detalle (id_compra, id_producto, cantidad, costo_unit)
      VALUES (:c, :p, :cant, :cu)
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
      if ($idProducto <= 0 || $cantidad <= 0) continue;

      $cu = (float)($it['costo_unit'] ?? 0);
      if ($cu <= 0) $cu = self::productoPrecio($idProducto);

      $stockAntes = self::stockActual($idProducto);
      $stockDespues = $stockAntes + $cantidad; // ✅ SUMA stock

      $stDet->execute([
        ':c' => $idCompra,
        ':p' => $idProducto,
        ':cant' => $cantidad,
        ':cu' => $cu,
      ]);

      $stMovDet->execute([
        ':m' => $idMov,
        ':p' => $idProducto,
        ':cant' => $cantidad,
        ':cu' => $cu,
        ':antes' => $stockAntes,
        ':despues' => $stockDespues,
      ]);
    }

    // validar al menos 1 item
    $st = $pdo->prepare("SELECT COUNT(*) n FROM compras_detalle WHERE id_compra=:c");
    $st->execute([':c' => $idCompra]);
    if ((int)($st->fetch()['n'] ?? 0) <= 0) {
      throw new Exception("No se guardó detalle de compra.");
    }

    $pdo->commit();
    return $idCompra;

  } catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
  }
}
private static function resolveUserId(PDO $pdo, int $preferred, int $fallbackFromCompra = 0): int {
  // 1) preferido y existe
  if ($preferred > 0) {
    $st = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario=:u LIMIT 1");
    $st->execute([':u' => $preferred]);
    if ($st->fetch()) return $preferred;
  }

  // 2) fallback (ej: id_usuario guardado en la compra) y existe
  if ($fallbackFromCompra > 0) {
    $st = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario=:u LIMIT 1");
    $st->execute([':u' => $fallbackFromCompra]);
    if ($st->fetch()) return $fallbackFromCompra;
  }

  // 3) último recurso: primer usuario del sistema
  $st = $pdo->query("SELECT id_usuario FROM usuarios ORDER BY id_usuario ASC LIMIT 1");
  $r = $st->fetch();
  if ($r) return (int)$r['id_usuario'];

  throw new Exception("No existe ningún usuario en la tabla usuarios. Creá al menos 1 usuario admin.");
}

  /** Anula y devuelve stock creando movimiento espejo */
  public static function cancel(int $idCompra, int $idUsuario, string $nota = 'Compra eliminada'): void {
  $compra = self::find($idCompra);
  if (!$compra) throw new Exception('La compra no existe.');
  if ((string)$compra['estado'] === 'ANULADA') throw new Exception('Esta compra ya está ANULADA.');

  $items = self::items($idCompra);
  if (!$items) throw new Exception('Esta compra no tiene detalle.');

  $pdo = db();
  $pdo->beginTransaction();

  try {
    // ✅ usuario válido para cumplir FK inventario_movimientos.id_usuario
    $uid = self::resolveUserId($pdo, $idUsuario, (int)($compra['id_usuario'] ?? 0));

    // Marcar anulada (tu “eliminar” interno)
    $st = $pdo->prepare("UPDATE compras SET estado='ANULADA' WHERE id_compra=:c");
    $st->execute([':c' => $idCompra]);

    // Movimiento espejo: si la compra fue ENTRADA, al anular debe ser SALIDA (restar stock)
    $st = $pdo->prepare("
      INSERT INTO inventario_movimientos (fecha, tipo, ref_tabla, ref_id, id_usuario, nota)
      VALUES (NOW(), 'SALIDA_ANULA_COMPRA', 'compras', :rid, :u, :nota)
    ");
    $st->execute([':rid' => $idCompra, ':u' => $uid, ':nota' => $nota]);
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
      $cu = (float)$it['costo_unit'];

      $stockAntes = self::stockActual($idProducto);
      $stockDespues = $stockAntes - $cantidad;

      if ($stockDespues < 0) {
        throw new Exception("No se puede anular: stock insuficiente para revertir producto ID $idProducto.");
      }

      $stMovDet->execute([
        ':m' => $idMov,
        ':p' => $idProducto,
        ':cant' => $cantidad,
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
public static function deleteHard(int $idCompra): void {
  $pdo = db();
  $pdo->beginTransaction();

  try {
    // 1) verificar existencia
    $st = $pdo->prepare("SELECT id_compra FROM compras WHERE id_compra = :id");
    $st->execute([':id' => $idCompra]);
    if (!$st->fetch()) {
      throw new Exception('La compra no existe.');
    }

    // 2) obtener movimientos ligados
    $st = $pdo->prepare("
      SELECT id_mov
      FROM inventario_movimientos
      WHERE ref_tabla = 'compras' AND ref_id = :id
    ");
    $st->execute([':id' => $idCompra]);
    $movs = $st->fetchAll(PDO::FETCH_COLUMN);

    if ($movs) {

      // generar placeholders solo con ?
      $placeholders = implode(',', array_fill(0, count($movs), '?'));

      // borrar detalle inventario
      $st = $pdo->prepare("
        DELETE FROM inventario_mov_detalle
        WHERE id_mov IN ($placeholders)
      ");
      $st->execute($movs);

      // borrar movimientos
      $st = $pdo->prepare("
        DELETE FROM inventario_movimientos
        WHERE id_mov IN ($placeholders)
      ");
      $st->execute($movs);
    }

    // borrar detalle compra
    $st = $pdo->prepare("
      DELETE FROM compras_detalle
      WHERE id_compra = :id
    ");
    $st->execute([':id' => $idCompra]);

    // borrar compra
    $st = $pdo->prepare("
      DELETE FROM compras
      WHERE id_compra = :id
    ");
    $st->execute([':id' => $idCompra]);

    $pdo->commit();

  } catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
  }
}
}