<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

final class ProductController {
  public static function ajax(array $get): array {
    $id = (int)($get["view"] ?? 0);
    if ($id <= 0) return ["ok" => false, "error" => "ID inválido"];

    $p = Product::find($id);
    if (!$p) return ["ok" => false, "error" => "Producto no encontrado"];

    $imgs = Product::images($id);
    if (!$imgs) $imgs = [["id_imagen" => 0, "url" => "assets/img/logo.jpeg", "es_principal" => 1]];

    return ["ok" => true, "product" => $p, "images" => $imgs];
  }


  public static function handle(array $post, array $files, array $get): array {
    // Siempre devolvemos listas para pintar la pantalla
    $data = [
      'error' => '',
      'success' => '',
      'filters' => [
        'q' => trim((string)($get['q'] ?? '')),
        'cat' => (int)($get['cat'] ?? 0),
        'min' => (string)($get['min'] ?? ''),
        'max' => (string)($get['max'] ?? ''),
        'estado' => (string)($get['estado'] ?? ''),
      ],
      'categories' => Category::allActive(),
      'products' => [],
      'product' => null,
      'images' => [],
      'isAdmin' => is_admin(),
    ];

    // Acciones admin (CRUD)
    if (!empty($post)) {
      $action = (string)($post['action'] ?? '');

      // Acciones que modifican requieren admin
      $mutating = in_array($action, ['create', 'update', 'delete', 'delete_image', 'set_principal'], true);
      if ($mutating) require_admin();

      try {
        if ($action === 'create') {
          $payload = self::sanitizeProductPayload($post);
          $id = Product::create($payload);
          self::handleUploads($id, $files);
          $data['success'] = 'Producto creado con éxito.';
        }

        if ($action === 'update') {
          $id = (int)($post['id_producto'] ?? 0);
          if ($id <= 0) throw new Exception('ID de producto inválido.');
          $payload = self::sanitizeProductPayload($post);
          Product::update($id, $payload);
          self::handleUploads($id, $files);
          $data['success'] = 'Producto actualizado con éxito.';
        }

        if ($action === 'delete') {
          $id = (int)($post['id_producto'] ?? 0);
          if ($id <= 0) throw new Exception('ID de producto inválido.');
          Product::delete($id);
          $data['success'] = 'Producto eliminado.';
        }

        if ($action === 'delete_image') {
          $idImagen = (int)($post['id_imagen'] ?? 0);
          if ($idImagen <= 0) throw new Exception('ID de imagen inválido.');
          Product::deleteImage($idImagen);
          $data['success'] = 'Imagen eliminada.';
        }

        if ($action === 'set_principal') {
          $id = (int)($post['id_producto'] ?? 0);
          $idImagen = (int)($post['id_imagen'] ?? 0);
          if ($id <= 0 || $idImagen <= 0) throw new Exception('Datos inválidos.');
          Product::setPrincipalImage($id, $idImagen);
          $data['success'] = 'Imagen principal actualizada.';
        }

      } catch (Throwable $e) {
        $data['error'] = $e->getMessage();
      }
    }

    // Data para UI
    $data['products'] = Product::list($data['filters']);

    // Si piden detalle (para modal/offcanvas via query)
    $idView = (int)($get['view'] ?? 0);
    if ($idView > 0) {
      $data['product'] = Product::find($idView);
      $data['images'] = Product::images($idView);
    }

    return $data;
  }

  private static function sanitizeProductPayload(array $post): array {
    $sku = trim((string)($post['sku'] ?? ''));
    $nombre = trim((string)($post['nombre'] ?? ''));
    $idCategoria = (int)($post['id_categoria'] ?? 0);

    if ($sku === '' || $nombre === '' || $idCategoria <= 0) {
      throw new Exception('Completá SKU, nombre y categoría.');
    }

    $costo = (float)($post['costo'] ?? 0);
    $precio = (float)($post['precio'] ?? 0);
    $stockMin = (int)($post['stock_min'] ?? 0);
    $estado = (int)($post['estado'] ?? 1);

    return [
      'sku' => $sku,
      'nombre' => $nombre,
      'id_categoria' => $idCategoria,
      'costo' => $costo,
      'precio' => $precio,
      'stock_min' => $stockMin,
      'estado' => $estado ? 1 : 0,
      'descripcion' => (string)($post['descripcion'] ?? ''),
    ];
  }

  private static function handleUploads(int $idProducto, array $files): void {
    if (!isset($files['imagenes'])) return;

    $destDir = __DIR__ . '/../../public/assets/img/products';
    if (!is_dir($destDir)) {
      @mkdir($destDir, 0777, true);
    }

    // Soporta input multiple (name="imagenes[]") o single (name="imagenes")
    $imgs = $files['imagenes'];

    $names = $imgs['name'] ?? [];
    $tmp = $imgs['tmp_name'] ?? [];
    $err = $imgs['error'] ?? [];

    // Normalizar a arreglo
    if (!is_array($names)) {
      $names = [$names];
      $tmp = [$tmp];
      $err = [$err];
    }

    // ¿ya tiene imagen principal?
    $existing = Product::images($idProducto);
    $hasPrincipal = false;
    foreach ($existing as $e) {
      if ((int)$e['es_principal'] === 1) { $hasPrincipal = true; break; }
    }

    for ($i=0; $i<count($names); $i++) {
      if (($err[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

      $original = (string)$names[$i];
      $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) continue;

      $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME));
      $filename = $idProducto . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '_' . $safe . '.' . $ext;
      $dest = $destDir . '/' . $filename;

      if (!move_uploaded_file($tmp[$i], $dest)) continue;

      $publicUrl = 'assets/img/products/' . $filename;
      $makePrincipal = !$hasPrincipal && $i === 0; // primera subida, se vuelve principal
      Product::addImage($idProducto, $publicUrl, $makePrincipal);
      if ($makePrincipal) $hasPrincipal = true;
    }
  }
}
