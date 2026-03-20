<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/Notifier.php';

final class ProductController
{
  public static function ajax(array $get): array
  {
    $id = (int) ($get["view"] ?? 0);
    if ($id <= 0)
      return ["ok" => false, "error" => "ID inválido"];

    $p = Product::find($id);
    if (!$p)
      return ["ok" => false, "error" => "Producto no encontrado"];

    $imgs = Product::images($id);
    if (!$imgs)
      $imgs = [["id_imagen" => 0, "url" => "assets/img/logo.jpeg", "es_principal" => 1]];

    return ["ok" => true, "product" => $p, "images" => $imgs];
  }


  private static function redirectToProducts(array $filters): void
  {
    $qs = http_build_query([
      'page' => 'products',
      'q' => $filters['q'] ?? '',
      'cat' => (int) ($filters['cat'] ?? 0),
      'min' => $filters['min'] ?? '',
      'max' => $filters['max'] ?? '',
      'estado' => $filters['estado'] ?? '',
    ]);

    header('Location: index.php?' . $qs);
    exit;
  }
  public static function handle(array $post, array $files, array $get): array
  {
    $data = [
      'error' => '',
      'success' => '',
      'filters' => [
        'q' => trim((string) ($get['q'] ?? '')),
        'cat' => (int) ($get['cat'] ?? 0),
        'min' => (string) ($get['min'] ?? ''),
        'max' => (string) ($get['max'] ?? ''),
        'estado' => (string) ($get['estado'] ?? ''),
      ],
      'categories' => Category::allActive(),
      'products' => [],
      'product' => null,
      'images' => [],
      'isAdmin' => is_admin_or_logistica(),
    ];

    if (!empty($post)) {
      $action = (string) ($post['action'] ?? '');

      $mutating = in_array($action, ['create', 'update', 'delete', 'delete_image', 'set_principal', 'adjust_stock'], true);
      if ($mutating)
        require_admin_or_logistica('Productos');

      try {
        if ($action === 'create') {
          $payload = self::sanitizeProductPayload($post);
          $id = Product::create($payload);

          $idUser = (int) ($_SESSION['user']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['auth']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['user']['id'] ?? 0);

          $stockTarget = (int) ($post['stock'] ?? 0);
          if ($stockTarget > 0) {
            Product::setStock($id, $stockTarget, $idUser, 'Stock inicial desde Productos');
          }

          self::handleUploads($id, $files);
          Notifier::notifyShared(
            'product_create',
            'Productos',
            'Producto agregado',
            'Se agregó el producto "' . ($payload['nombre'] ?? '') . '".',
            'productos',
            $id,
            [
              'sku' => $payload['sku'] ?? '',
              'precio' => $payload['precio'] ?? 0,
              'stock_inicial' => (int) ($post['stock'] ?? 0)
            ]
          );
          $_SESSION['flash_success'] = 'Producto creado con éxito.';
          self::redirectToProducts($data['filters']);
        }

        if ($action === 'update') {
          $id = (int) ($post['id_producto'] ?? 0);
          if ($id <= 0)
            throw new Exception('ID de producto inválido.');
          $payload = self::sanitizeProductPayload($post);
          Product::update($id, $payload);
          $idUser = (int) ($_SESSION['user']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['auth']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['user']['id'] ?? 0);

          self::handleUploads($id, $files);
          Notifier::notifyShared(
            'product_update',
            'Productos',
            'Producto actualizado',
            'Se actualizó el producto "' . ($payload['nombre'] ?? '') . '".',
            'productos',
            $id
          );
          $_SESSION['flash_success'] = 'Producto actualizado con éxito.';
          self::redirectToProducts($data['filters']);
        }

        if ($action === 'adjust_stock') {
          $id = (int) ($post['id_producto'] ?? 0);
          if ($id <= 0)
            throw new Exception('ID de producto inválido.');

          $stockTarget = (int) ($post['stock_target'] ?? -1);
          if ($stockTarget < 0)
            throw new Exception('Stock objetivo inválido.');

          $motivo = trim((string) ($post['motivo'] ?? ''));
          if ($motivo === '')
            throw new Exception('Motivo requerido para ajustar inventario.');

          $idUser = (int) ($_SESSION['user']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['auth']['id_usuario'] ?? 0);
          if ($idUser <= 0)
            $idUser = (int) ($_SESSION['user']['id'] ?? 0);

          $producto = Product::find($id);
          Product::setStock($id, $stockTarget, $idUser, $motivo);

          Notifier::notifyShared(
            'stock_adjust',
            'Productos',
            'Inventario ajustado',
            'Se ajustó el inventario del producto "' . ($producto['nombre'] ?? '') . '" a ' . $stockTarget . ' unidades.',
            'productos',
            $id,
            [
              'producto' => $producto['nombre'] ?? '',
              'sku' => $producto['sku'] ?? '',
              'nuevo_stock' => $stockTarget,
              'motivo' => $motivo
            ]
          );

          $_SESSION['flash_success'] = 'Inventario ajustado y registrado en Kardex.';
          self::redirectToProducts($data['filters']);
        }
        if ($action === 'delete') {
          $id = (int) ($post['id_producto'] ?? 0);
          if ($id <= 0)
            throw new Exception('ID de producto inválido.');
          Product::delete($id);
          $_SESSION['flash_success'] = 'Producto eliminado.';
          self::redirectToProducts($data['filters']);
        }

        if ($action === 'delete_image') {
          $idImagen = (int) ($post['id_imagen'] ?? 0);
          if ($idImagen <= 0)
            throw new Exception('ID de imagen inválido.');
          Product::deleteImage($idImagen);
          $id = (int) ($post['id_producto'] ?? 0);

          Notifier::notifyShared(
            'product_image_delete',
            'Productos',
            'Imagen eliminada',
            'Se eliminó una imagen del producto.',
            'productos',
            $id
          );
          $_SESSION['flash_success'] = 'Imagen eliminada.';
          self::redirectToProducts($data['filters']);
        }

        if ($action === 'set_principal') {
          $id = (int) ($post['id_producto'] ?? 0);
          $idImagen = (int) ($post['id_imagen'] ?? 0);
          if ($id <= 0 || $idImagen <= 0)
            throw new Exception('Datos inválidos.');
          Product::setPrincipalImage($id, $idImagen);
          $_SESSION['flash_success'] = 'Imagen principal actualizada.';
          self::redirectToProducts($data['filters']);
        }

      } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        self::redirectToProducts($data['filters']);
      }
    }

    
    $data['products'] = Product::list($data['filters']);

    $idView = (int) ($get['view'] ?? 0);
    if ($idView > 0) {
      $data['product'] = Product::find($idView);
      $data['images'] = Product::images($idView);
    }

    return $data;
  }

  private static function sanitizeProductPayload(array $post): array
  {
    $sku = trim((string) ($post['sku'] ?? ''));
    $nombre = trim((string) ($post['nombre'] ?? ''));
    $idCategoria = (int) ($post['id_categoria'] ?? 0);

    if ($sku === '' || $nombre === '' || $idCategoria <= 0) {
      throw new Exception('Completá SKU, nombre y categoría.');
    }

    $costo = (float) ($post['costo'] ?? 0);
    $precio = (float) ($post['precio'] ?? 0);
    $stockMin = (int) ($post['stock_min'] ?? 0);
    $estado = (int) ($post['estado'] ?? 1);

    return [
      'sku' => $sku,
      'nombre' => $nombre,
      'id_categoria' => $idCategoria,
      'costo' => $costo,
      'precio' => $precio,
      'stock_min' => $stockMin,
      'estado' => $estado ? 1 : 0,
      'descripcion' => (string) ($post['descripcion'] ?? ''),
    ];
  }

  private static function handleUploads(int $idProducto, array $files): void
  {
    if (!isset($files['imagenes']))
      return;

    $destDir = __DIR__ . '/../../public/assets/img/products';
    if (!is_dir($destDir)) {
      @mkdir($destDir, 0777, true);
    }

  
    $imgs = $files['imagenes'];

    $names = $imgs['name'] ?? [];
    $tmp = $imgs['tmp_name'] ?? [];
    $err = $imgs['error'] ?? [];

 
    if (!is_array($names)) {
      $names = [$names];
      $tmp = [$tmp];
      $err = [$err];
    }

   
    $existing = Product::images($idProducto);
    $hasPrincipal = false;
    foreach ($existing as $e) {
      if ((int) $e['es_principal'] === 1) {
        $hasPrincipal = true;
        break;
      }
    }

    for ($i = 0; $i < count($names); $i++) {
      if (($err[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)
        continue;

      $original = (string) $names[$i];
      $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true))
        continue;

      $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME));
      $filename = $idProducto . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '_' . $safe . '.' . $ext;
      $dest = $destDir . '/' . $filename;

      if (!move_uploaded_file($tmp[$i], $dest))
        continue;

      $publicUrl = 'assets/img/products/' . $filename;
      $makePrincipal = !$hasPrincipal && $i === 0; 
      Product::addImage($idProducto, $publicUrl, $makePrincipal);
      if ($makePrincipal)
        $hasPrincipal = true;
    }
  }
  public static function stockActual(int $idProducto): int
  {
    $st = db()->prepare("
    SELECT imd.stock_despues
    FROM inventario_mov_detalle imd
    WHERE imd.id_producto = :p
    ORDER BY imd.id_mov_det DESC
    LIMIT 1
  ");
    $st->execute([':p' => $idProducto]);
    $r = $st->fetch();
    return $r ? (int) $r['stock_despues'] : 0;
  }

  public static function setStock(int $idProducto, int $stockTarget, int $idUsuario, string $nota = ''): void
  {
    if ($idProducto <= 0)
      throw new Exception('Producto inválido.');
    if ($stockTarget < 0)
      throw new Exception('Stock inválido.');

    $pdo = db();
    $pdo->beginTransaction();
    try {
      $stockAntes = self::stockActual($idProducto);
      $diff = $stockTarget - $stockAntes;

      if ($diff === 0) {
        $pdo->commit();
        return;
      }

      if (trim($nota) === '')
        $nota = 'Ajuste de stock';
      Notifier::notifyShared(
        'stock_adjust',
        'Productos',
        'Stock ajustado',
        'Se realizó un ajuste manual de stock.',
        'productos',
        $idProducto,
        [
          'nuevo_stock' => $stockTarget,
          'nota' => $nota
        ]
      );

      $st = $pdo->prepare("
      INSERT INTO inventario_movimientos (fecha, tipo, ref_tabla, ref_id, id_usuario, nota)
      VALUES (NOW(), 'AJUSTE_STOCK', 'productos', :rid, :u, :nota)
    ");
      $st->execute([
        ':rid' => $idProducto,
        ':u' => $idUsuario,
        ':nota' => $nota
      ]);
      $idMov = (int) $pdo->lastInsertId();

 
      $stC = $pdo->prepare("SELECT costo FROM productos WHERE id_producto=:p LIMIT 1");
      $stC->execute([':p' => $idProducto]);
      $costoUnit = (float) ($stC->fetch()['costo'] ?? 0);

      $stDet = $pdo->prepare("
      INSERT INTO inventario_mov_detalle (id_mov, id_producto, cantidad, costo_unit, stock_antes, stock_despues)
      VALUES (:m, :p, :cant, :cu, :antes, :despues)
    ");
      $stDet->execute([
        ':m' => $idMov,
        ':p' => $idProducto,
        ':cant' => $diff,              
        ':cu' => $costoUnit,
        ':antes' => $stockAntes,
        ':despues' => $stockTarget,
      ]);

      $pdo->commit();
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
