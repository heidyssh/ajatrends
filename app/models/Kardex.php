<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Kardex
{
  public static function productsForFilter(): array
  {
    $pdo = db();
    $st = $pdo->query("SELECT id_producto, nombre, sku FROM productos WHERE estado=1 ORDER BY nombre ASC");
    return $st->fetchAll();
  }

  public static function resumen(array $filters = []): array
  {
    [$whereSql, $params] = self::buildWhere($filters);
    $pdo = db();

    $sql = "
      SELECT
        COUNT(DISTINCT m.id_mov) AS movimientos,
        COALESCE(SUM(CASE WHEN d.cantidad > 0 THEN d.cantidad ELSE 0 END),0) AS entradas,
        COALESCE(SUM(CASE WHEN d.cantidad < 0 THEN -d.cantidad ELSE 0 END),0) AS salidas,
        COALESCE(SUM(d.cantidad),0) AS neto,
        COALESCE(SUM(ABS(d.cantidad) * d.costo_unit),0) AS valor_mov
      FROM inventario_movimientos m
      JOIN inventario_mov_detalle d ON d.id_mov = m.id_mov
      JOIN productos p ON p.id_producto = d.id_producto
      $whereSql
    ";

    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetch() ?: [];
  }

  public static function list(array $filters = [], int $limit = 200): array
  {
    [$whereSql, $params] = self::buildWhere($filters);
    $pdo = db();

    $sql = "
      SELECT
        m.id_mov, m.fecha, m.tipo, m.ref_tabla, m.ref_id, m.nota,
        u.nombre AS usuario,
        COUNT(d.id_mov_det) AS lineas,
        COALESCE(SUM(CASE WHEN d.cantidad > 0 THEN d.cantidad ELSE 0 END),0) AS entradas,
        COALESCE(SUM(CASE WHEN d.cantidad < 0 THEN -d.cantidad ELSE 0 END),0) AS salidas,
        COALESCE(SUM(d.cantidad),0) AS neto,
        COALESCE(SUM(ABS(d.cantidad) * d.costo_unit),0) AS valor_mov
      FROM inventario_movimientos m
      JOIN inventario_mov_detalle d ON d.id_mov = m.id_mov
      JOIN productos p ON p.id_producto = d.id_producto
      LEFT JOIN usuarios u ON u.id_usuario = m.id_usuario
      $whereSql
      GROUP BY m.id_mov
      ORDER BY m.fecha DESC
      LIMIT " . (int)$limit . "
    ";

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $movs = $st->fetchAll();

    if (!$movs) return [];

    $ids = array_map(fn($r) => (int)$r['id_mov'], $movs);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $sql2 = "
      SELECT
        d.id_mov, d.id_producto, d.cantidad, d.costo_unit, d.stock_antes, d.stock_despues,
        p.nombre, p.sku
      FROM inventario_mov_detalle d
      JOIN productos p ON p.id_producto = d.id_producto
      WHERE d.id_mov IN ($in)
      ORDER BY d.id_mov ASC, d.id_mov_det ASC
    ";
    $st2 = $pdo->prepare($sql2);
    $st2->execute($ids);
    $rows = $st2->fetchAll();

    $byMov = [];
    foreach ($rows as $r) {
      $mid = (int)$r['id_mov'];
      $byMov[$mid][] = $r;
    }

    foreach ($movs as &$m) {
      $mid = (int)$m['id_mov'];
      $m['items'] = $byMov[$mid] ?? [];
    }
    unset($m);

    return $movs;
  }

  private static function buildWhere(array $filters): array
  {
    $q = trim((string)($filters['q'] ?? ''));
    $tipo = trim((string)($filters['tipo'] ?? 'TODOS'));
    $from = trim((string)($filters['from'] ?? ''));
    $to = trim((string)($filters['to'] ?? ''));
    $pid = (int)($filters['id_producto'] ?? 0);

    $where = [];
    $params = [];

    if ($pid > 0) { $where[] = 'd.id_producto = ?'; $params[] = $pid; }

    if ($tipo !== '' && strtoupper($tipo) !== 'TODOS') {
      $where[] = 'm.tipo = ?';
      $params[] = $tipo;
    }

    if ($from !== '') { $where[] = 'm.fecha >= ?'; $params[] = $from . ' 00:00:00'; }
    if ($to !== '') { $where[] = 'm.fecha <= ?'; $params[] = $to . ' 23:59:59'; }

    if ($q !== '') {
      $where[] = '(p.nombre LIKE ? OR p.sku LIKE ? OR m.nota LIKE ? OR m.ref_tabla LIKE ?)';
      $like = '%' . $q . '%';
      array_push($params, $like, $like, $like, $like);
    }

    $sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    return [$sql, $params];
  }
}