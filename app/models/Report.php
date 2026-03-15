<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Report
{
  private static function buildPeriodWhere(string $alias, array $filters, array &$params): string
  {
    $where = [];
    $year = (int) ($filters['year'] ?? date('Y'));
    $month = (int) ($filters['month'] ?? 0);

    $where[] = "YEAR($alias) = :year";
    $params[':year'] = $year;

    if ($month > 0) {
      $where[] = "MONTH($alias) = :month";
      $params[':month'] = $month;
    }

    return implode(' AND ', $where);
  }

  public static function filters(array $get): array
  {
    $year = (int) ($get['year'] ?? date('Y'));
    $month = (int) ($get['month'] ?? 0);
    $module = trim((string) ($get['module'] ?? 'TODOS'));

    if ($year < 2020 || $year > 2100) {
      $year = (int) date('Y');
    }

    if ($month < 0 || $month > 12) {
      $month = 0;
    }

    $validModules = ['TODOS', 'VENTAS', 'COMPRAS', 'INVENTARIO'];
    if (!in_array($module, $validModules, true)) {
      $module = 'TODOS';
    }

    return [
      'year' => $year,
      'month' => $month,
      'module' => $module,
    ];
  }

  public static function years(): array
  {
    $current = (int) date('Y');
    $years = [];
    for ($i = $current + 1; $i >= $current - 5; $i--) {
      $years[] = $i;
    }
    return $years;
  }

  public static function kpis(array $filters): array
  {
    $pdo = db();

    $paramsVentas = [];
    $whereVentas = self::buildPeriodWhere('v.fecha', $filters, $paramsVentas);

    $sqlVentas = "
      SELECT
        COUNT(*) AS ventas,
        COALESCE(SUM(v.total), 0) AS total_vendido,
        COALESCE(AVG(v.total), 0) AS ticket_promedio,
        COALESCE(SUM(vd.cantidad), 0) AS unidades,
        COALESCE(SUM((vd.precio_unit - p.costo) * vd.cantidad), 0) AS utilidad_estimada
      FROM ventas v
      INNER JOIN ventas_detalle vd ON vd.id_venta = v.id_venta
      INNER JOIN productos p ON p.id_producto = vd.id_producto
      WHERE $whereVentas
        AND v.estado <> 'ANULADA'
    ";
    $st = $pdo->prepare($sqlVentas);
    $st->execute($paramsVentas);
    $ventas = $st->fetch() ?: [];

    $paramsCompras = [];
    $whereCompras = self::buildPeriodWhere('c.fecha', $filters, $paramsCompras);

    $sqlCompras = "
      SELECT
        COUNT(*) AS compras,
        COALESCE(SUM(cd.cantidad * cd.costo_unit), 0) AS total_comprado,
        COALESCE(SUM(cd.cantidad), 0) AS unidades_compradas
      FROM compras c
      INNER JOIN compras_detalle cd ON cd.id_compra = c.id_compra
      WHERE $whereCompras
    ";
    $st = $pdo->prepare($sqlCompras);
    $st->execute($paramsCompras);
    $compras = $st->fetch() ?: [];

    $paramsInv = [];
    $whereInv = self::buildPeriodWhere('im.fecha', $filters, $paramsInv);

    $sqlInv = "
      SELECT
        COUNT(*) AS movimientos,
        COALESCE(SUM(CASE WHEN imd.cantidad > 0 THEN imd.cantidad ELSE 0 END), 0) AS entradas,
        COALESCE(SUM(CASE WHEN imd.cantidad < 0 THEN ABS(imd.cantidad) ELSE 0 END), 0) AS salidas
      FROM inventario_movimientos im
      INNER JOIN inventario_mov_detalle imd ON imd.id_mov = im.id_mov
      WHERE $whereInv
    ";
    $st = $pdo->prepare($sqlInv);
    $st->execute($paramsInv);
    $inventario = $st->fetch() ?: [];

    return [
      'ventas' => (int) ($ventas['ventas'] ?? 0),
      'total_vendido' => (float) ($ventas['total_vendido'] ?? 0),
      'ticket_promedio' => (float) ($ventas['ticket_promedio'] ?? 0),
      'unidades' => (int) ($ventas['unidades'] ?? 0),
      'utilidad_estimada' => (float) ($ventas['utilidad_estimada'] ?? 0),

      'compras' => (int) ($compras['compras'] ?? 0),
      'total_comprado' => (float) ($compras['total_comprado'] ?? 0),
      'unidades_compradas' => (int) ($compras['unidades_compradas'] ?? 0),

      'movimientos' => (int) ($inventario['movimientos'] ?? 0),
      'entradas' => (int) ($inventario['entradas'] ?? 0),
      'salidas' => (int) ($inventario['salidas'] ?? 0),
    ];
  }

  public static function monthlySalesVsPurchases(int $year): array
  {
    $pdo = db();

    $months = [];
    for ($m = 1; $m <= 12; $m++) {
      $months[$m] = [
        'mes' => $m,
        'ventas' => 0.0,
        'compras' => 0.0,
      ];
    }

    $sqlVentas = "
      SELECT MONTH(v.fecha) AS mes, COALESCE(SUM(v.total), 0) AS total
      FROM ventas v
      WHERE YEAR(v.fecha) = :year
        AND v.estado <> 'ANULADA'
      GROUP BY MONTH(v.fecha)
    ";
    $st = $pdo->prepare($sqlVentas);
    $st->execute([':year' => $year]);
    foreach ($st->fetchAll() as $r) {
      $m = (int) $r['mes'];
      $months[$m]['ventas'] = (float) $r['total'];
    }

    $sqlCompras = "
      SELECT MONTH(c.fecha) AS mes, COALESCE(SUM(cd.cantidad * cd.costo_unit), 0) AS total
      FROM compras c
      INNER JOIN compras_detalle cd ON cd.id_compra = c.id_compra
      WHERE YEAR(c.fecha) = :year
      GROUP BY MONTH(c.fecha)
    ";
    $st = $pdo->prepare($sqlCompras);
    $st->execute([':year' => $year]);
    foreach ($st->fetchAll() as $r) {
      $m = (int) $r['mes'];
      $months[$m]['compras'] = (float) $r['total'];
    }

    return array_values($months);
  }

  public static function topProducts(array $filters, int $limit = 8): array
  {
    $pdo = db();
    $params = [];
    $where = self::buildPeriodWhere('v.fecha', $filters, $params);

    $sql = "
      SELECT
        p.nombre,
        p.sku,
        SUM(vd.cantidad) AS unidades,
        SUM(vd.subtotal) AS ingreso
      FROM ventas v
      INNER JOIN ventas_detalle vd ON vd.id_venta = v.id_venta
      INNER JOIN productos p ON p.id_producto = vd.id_producto
      WHERE $where
        AND v.estado <> 'ANULADA'
      GROUP BY p.id_producto, p.nombre, p.sku
      ORDER BY ingreso DESC, unidades DESC
      LIMIT " . (int) $limit;

    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll() ?: [];
  }

  public static function salesByCategory(array $filters): array
  {
    $pdo = db();
    $params = [];
    $where = self::buildPeriodWhere('v.fecha', $filters, $params);

    $sql = "
      SELECT
        c.nombre AS categoria,
        COALESCE(SUM(vd.subtotal), 0) AS total
      FROM ventas v
      INNER JOIN ventas_detalle vd ON vd.id_venta = v.id_venta
      INNER JOIN productos p ON p.id_producto = vd.id_producto
      INNER JOIN categorias c ON c.id_categoria = p.id_categoria
      WHERE $where
        AND v.estado <> 'ANULADA'
      GROUP BY c.id_categoria, c.nombre
      ORDER BY total DESC
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll() ?: [];
  }

  public static function inventoryByType(array $filters): array
  {
    $pdo = db();
    $params = [];
    $where = self::buildPeriodWhere('im.fecha', $filters, $params);

    $sql = "
  SELECT
    CASE im.tipo
      WHEN 'ENTRADA_COMPRA' THEN 'Entradas por compra'
      WHEN 'SALIDA_VENTA' THEN 'Salidas por venta'
      WHEN 'AJUSTE_POSITIVO' THEN 'Ajustes positivos'
      WHEN 'AJUSTE_NEGATIVO' THEN 'Ajustes negativos'
      WHEN 'AJUSTE_STOCK' THEN 'Correcciones de stock'
      WHEN 'SALIDA_ANULACION_COMPRA' THEN 'Reversión de compra'
      WHEN 'ENTRADA_ANULACION_VENTA' THEN 'Reversión de venta'
      ELSE im.tipo
    END AS tipo,
    COUNT(*) AS movimientos,
    COALESCE(SUM(ABS(imd.cantidad)), 0) AS unidades
  FROM inventario_movimientos im
  INNER JOIN inventario_mov_detalle imd ON imd.id_mov = im.id_mov
  WHERE $where
  GROUP BY im.tipo
  ORDER BY movimientos DESC, unidades DESC
";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll() ?: [];
  }

  public static function salesTable(array $filters): array
  {
    $pdo = db();
    $params = [];
    $where = self::buildPeriodWhere('v.fecha', $filters, $params);

    $sql = "
      SELECT
        v.id_venta,
        DATE(v.fecha) AS fecha,
        v.cliente_txt AS cliente,
        v.estado,
        v.total,
        v.nota
      FROM ventas v
        WHERE $where
          AND v.estado <> 'ANULADA'
        ORDER BY v.fecha DESC
        LIMIT 100
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll() ?: [];
  }

  public static function purchasesTable(array $filters): array
  {
    $pdo = db();
    $params = [];
    $where = self::buildPeriodWhere('c.fecha', $filters, $params);

    $sql = "
      SELECT
        c.id_compra,
        DATE(c.fecha) AS fecha,
        COALESCE(pr.nombre, 'Sin proveedor') AS proveedor,
        c.estado,
        COALESCE(SUM(cd.cantidad * cd.costo_unit), 0) AS total,
        c.nota
      FROM compras c
      LEFT JOIN proveedores pr ON pr.id_proveedor = c.id_proveedor
      LEFT JOIN compras_detalle cd ON cd.id_compra = c.id_compra
      WHERE $where
      GROUP BY c.id_compra, c.fecha, proveedor, c.estado, c.nota
      ORDER BY c.fecha DESC
      LIMIT 100
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll() ?: [];
  }

  public static function inventoryTable(array $filters): array
  {
    $pdo = db();
    $params = [];
    $where = self::buildPeriodWhere('im.fecha', $filters, $params);

    $sql = "
      SELECT
        im.id_mov,
        DATE(im.fecha) AS fecha,
        im.tipo,
        p.nombre AS producto,
        imd.cantidad,
        imd.stock_antes,
        imd.stock_despues,
        im.nota
      FROM inventario_movimientos im
      INNER JOIN inventario_mov_detalle imd ON imd.id_mov = im.id_mov
      INNER JOIN productos p ON p.id_producto = imd.id_producto
      WHERE $where
      ORDER BY im.fecha DESC
      LIMIT 150
    ";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll() ?: [];
  }

  public static function reportPack(array $filters): array
{
    return [
      'filters' => $filters,
      'years' => self::years(),
      'kpis' => self::kpis($filters),
      'monthly' => self::monthlySalesVsPurchases((int) $filters['year']),
      'topProducts' => self::topProducts($filters),
      'byCategory' => self::salesByCategory($filters),
      'inventoryByType' => self::inventoryByType($filters),
      'salesRows' => self::salesTable($filters),
      'purchaseRows' => self::purchasesTable($filters),
      'inventoryRows' => self::inventoryTable($filters),
      'lowStockRows' => self::lowStockProducts(),
    ];
}
  public static function lowStockProducts(): array
{
  $pdo = db();

  $sql = "
    SELECT
      p.id_producto,
      p.sku,
      p.nombre,
      c.nombre AS categoria,
      COALESCE(SUM(imd.stock_despues), 0) AS stock_actual,
      p.stock_min
    FROM productos p
    INNER JOIN categorias c ON c.id_categoria = p.id_categoria
    LEFT JOIN inventario_mov_detalle imd ON imd.id_producto = p.id_producto
    WHERE p.estado = 1
    GROUP BY p.id_producto, p.sku, p.nombre, c.nombre, p.stock_min
    HAVING stock_actual <= p.stock_min
    ORDER BY stock_actual ASC, p.nombre ASC
    LIMIT 20
  ";

  $st = $pdo->query($sql);
  return $st->fetchAll() ?: [];
}
}