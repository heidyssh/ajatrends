<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Product {

  public static function list(array $filters = []): array {
    $q = trim((string)($filters['q'] ?? ''));
    $cat = (int)($filters['cat'] ?? 0);
    $min = $filters['min'] ?? '';
    $max = $filters['max'] ?? '';
    $estado = $filters['estado'] ?? '';

    $where = [];
    $params = [];

    if ($q !== '') {
      $where[] = '(p.nombre LIKE :q OR p.sku LIKE :q)';
      $params[':q'] = "%$q%";
    }
    if ($cat > 0) {
      $where[] = 'p.id_categoria = :cat';
      $params[':cat'] = $cat;
    }
    if ($min !== '' && is_numeric($min)) {
      $where[] = 'p.precio >= :min';
      $params[':min'] = (float)$min;
    }
    if ($max !== '' && is_numeric($max)) {
      $where[] = 'p.precio <= :max';
      $params[':max'] = (float)$max;
    }
    if ($estado !== '' && ($estado === '0' || $estado === '1')) {
      $where[] = 'p.estado = :estado';
      $params[':estado'] = (int)$estado;
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Imagen principal: prioridad es_principal=1, sino la primera por id
    $sql = "
      SELECT
        p.id_producto,
        p.sku,
        p.nombre,
        p.id_categoria,
        c.nombre AS categoria,
        p.costo,
        p.precio,
        p.stock_min,
        p.estado,
        COALESCE(img_principal.url, img_first.url, 'assets/img/logo.jpeg') AS imagen,
        COALESCE(d.descripcion, '') AS descripcion,
        (SELECT COUNT(*) FROM producto_imagenes pi2 WHERE pi2.id_producto = p.id_producto) AS total_imagenes
      FROM productos p
      INNER JOIN categorias c ON c.id_categoria = p.id_categoria
      LEFT JOIN producto_descripcion d ON d.id_producto = p.id_producto
      LEFT JOIN producto_imagenes img_principal
        ON img_principal.id_producto = p.id_producto AND img_principal.es_principal = 1
      LEFT JOIN (
        SELECT pi.id_producto, MIN(pi.id_imagen) AS id_first
        FROM producto_imagenes pi
        GROUP BY pi.id_producto
      ) x ON x.id_producto = p.id_producto
      LEFT JOIN producto_imagenes img_first
        ON img_first.id_producto = x.id_producto AND img_first.id_imagen = x.id_first
      $whereSql
      ORDER BY p.creado_en DESC, p.id_producto DESC
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public static function find(int $id): ?array {
    $sql = "
      SELECT p.*, c.nombre AS categoria, COALESCE(d.descripcion,'') AS descripcion
      FROM productos p
      INNER JOIN categorias c ON c.id_categoria = p.id_categoria
      LEFT JOIN producto_descripcion d ON d.id_producto = p.id_producto
      WHERE p.id_producto = :id
      LIMIT 1
    ";
    $st = db()->prepare($sql);
    $st->execute([':id' => $id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function images(int $idProducto): array {
    $sql = "SELECT id_imagen, url, es_principal FROM producto_imagenes WHERE id_producto=:id ORDER BY es_principal DESC, id_imagen ASC";
    $st = db()->prepare($sql);
    $st->execute([':id' => $idProducto]);
    return $st->fetchAll();
  }

  public static function create(array $data): int {
    $sql = "INSERT INTO productos (sku, nombre, id_categoria, costo, precio, stock_min, estado)
            VALUES (:sku,:nombre,:cat,:costo,:precio,:stock_min,:estado)";
    $st = db()->prepare($sql);
    $st->execute([
      ':sku' => $data['sku'],
      ':nombre' => $data['nombre'],
      ':cat' => (int)$data['id_categoria'],
      ':costo' => (float)$data['costo'],
      ':precio' => (float)$data['precio'],
      ':stock_min' => (int)$data['stock_min'],
      ':estado' => (int)$data['estado'],
    ]);
    $id = (int)db()->lastInsertId();

    $desc = trim((string)($data['descripcion'] ?? ''));
    if ($desc !== '') {
      $st2 = db()->prepare("INSERT INTO producto_descripcion (id_producto, descripcion) VALUES (:id,:d)");
      $st2->execute([':id' => $id, ':d' => $desc]);
    }

    return $id;
  }

  public static function update(int $id, array $data): void {
    $sql = "UPDATE productos SET sku=:sku, nombre=:nombre, id_categoria=:cat, costo=:costo, precio=:precio, stock_min=:stock_min, estado=:estado
            WHERE id_producto=:id";
    $st = db()->prepare($sql);
    $st->execute([
      ':id' => $id,
      ':sku' => $data['sku'],
      ':nombre' => $data['nombre'],
      ':cat' => (int)$data['id_categoria'],
      ':costo' => (float)$data['costo'],
      ':precio' => (float)$data['precio'],
      ':stock_min' => (int)$data['stock_min'],
      ':estado' => (int)$data['estado'],
    ]);

    $desc = trim((string)($data['descripcion'] ?? ''));
    // UPSERT de descripción
    $st2 = db()->prepare("INSERT INTO producto_descripcion (id_producto, descripcion)
                          VALUES (:id,:d)
                          ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion)");
    $st2->execute([':id' => $id, ':d' => $desc]);
  }

  public static function delete(int $id): void {
    $st = db()->prepare("DELETE FROM productos WHERE id_producto=:id");
    $st->execute([':id' => $id]);
  }

  public static function addImage(int $idProducto, string $url, bool $principal = false): void {
    if ($principal) {
      db()->prepare("UPDATE producto_imagenes SET es_principal=0 WHERE id_producto=:id")
        ->execute([':id' => $idProducto]);
    }
    $st = db()->prepare("INSERT INTO producto_imagenes (id_producto, url, es_principal) VALUES (:id,:url,:p)");
    $st->execute([':id' => $idProducto, ':url' => $url, ':p' => $principal ? 1 : 0]);
  }

  public static function deleteImage(int $idImagen): void {
    $st = db()->prepare("DELETE FROM producto_imagenes WHERE id_imagen=:id");
    $st->execute([':id' => $idImagen]);
  }

  public static function setPrincipalImage(int $idProducto, int $idImagen): void {
    db()->prepare("UPDATE producto_imagenes SET es_principal=0 WHERE id_producto=:id")
      ->execute([':id' => $idProducto]);
    db()->prepare("UPDATE producto_imagenes SET es_principal=1 WHERE id_imagen=:img AND id_producto=:id")
      ->execute([':img' => $idImagen, ':id' => $idProducto]);
  }
}
