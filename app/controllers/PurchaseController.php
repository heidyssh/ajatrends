<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Purchase.php';
require_once __DIR__ . '/../helpers/Notifier.php';

final class PurchaseController {

  private static function redirectToPurchases(array $filters = []): void {
    $qs = [
      'page' => 'purchases',
      'q' => $filters['q'] ?? '',
      'estado' => $filters['estado'] ?? 'TODOS',
    ];
    header('Location: index.php?' . http_build_query($qs));
    exit;
  }

  public static function handle(array $post, array $get): array {
    require_auth();
    require_admin(); // compras/pedidos solo admin

    $data = [
      'filters' => [
        'q' => trim((string)($get['q'] ?? '')),
        'estado' => (string)($get['estado'] ?? 'TODOS'),
      ],
      'purchases' => [],
      'purchase' => null,
      'items' => [],
      'products' => Purchase::productosActivos(),
      'isAdmin' => is_admin(),
      'proveedores' => Purchase::proveedores(),
    ];
     // ✅ AJAX: devolver detalle en JSON para modal "Ver"
    $actionGet = (string)($get['action'] ?? '');
    if ($actionGet === 'view_json') {
      $id = (int)($get['id'] ?? 0);
      if ($id <= 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'ID inválido']);
        exit;
      }

      $compra = Purchase::find($id);
      Notifier::notify(
  $idUser,
  'purchase_create',
  'Compras',
  'Compra registrada',
  'Se registró la compra #' . $idCompra . '.',
  'compras',
  $idCompra,
  [
    'proveedor_id' => $idProveedor,
    'nota' => $nota
  ]
);
      if (!$compra) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Compra no encontrada']);
        exit;
      }

      $items = Purchase::items($id);

      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'ok' => true,
        'id_compra' => (int)$compra['id_compra'],
        'fecha' => (string)($compra['fecha'] ?? ''),
        'usuario' => (string)($compra['usuario'] ?? ''),
        'proveedor' => (string)($compra['proveedor'] ?? ''),
        'nota' => (string)($compra['nota'] ?? ''),
        'estado' => (string)($compra['estado'] ?? ''),
        'items' => array_map(static function($it){
          return [
            'sku' => (string)($it['sku'] ?? ''),
            'nombre' => (string)($it['nombre'] ?? ''),
            'cantidad' => (int)($it['cantidad'] ?? 0),
            'costo_unit' => (float)($it['costo_unit'] ?? 0),
            'subtotal' => (float)($it['subtotal'] ?? 0),
          ];
        }, is_array($items) ? $items : []),
      ], JSON_UNESCAPED_UNICODE);
      exit;
    } // ✅ AJAX: devolver detalle en JSON para modal "Ver"
    $actionGet = (string)($get['action'] ?? '');
    if ($actionGet === 'view_json') {
      $id = (int)($get['id'] ?? 0);
      if ($id <= 0) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'ID inválido']);
        exit;
      }

      $compra = Purchase::find($id);
      if (!$compra) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Compra no encontrada']);
        exit;
      }

      $items = Purchase::items($id);

      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'ok' => true,
        'id_compra' => (int)$compra['id_compra'],
        'fecha' => (string)($compra['fecha'] ?? ''),
        'usuario' => (string)($compra['usuario'] ?? ''),
        'nota' => (string)($compra['nota'] ?? ''),
        'estado' => (string)($compra['estado'] ?? ''),
        'items' => array_map(static function($it){
          return [
            'sku' => (string)($it['sku'] ?? ''),
            'nombre' => (string)($it['nombre'] ?? ''),
            'cantidad' => (int)($it['cantidad'] ?? 0),
            'costo_unit' => (float)($it['costo_unit'] ?? 0),
            'subtotal' => (float)($it['subtotal'] ?? 0),
          ];
        }, is_array($items) ? $items : []),
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
    if (!empty($post)) {
      $action = (string)($post['action'] ?? '');

      try {
        if ($action === 'create') {
          $nota = trim((string)($post['nota'] ?? ''));

          $ids = $post['id_producto'] ?? [];
          $cants = $post['cantidad'] ?? [];
          $cus = $post['costo_unit'] ?? [];
          $idProveedor = (int)($post['id_proveedor'] ?? 0);

          $items = [];
          if (is_array($ids) && is_array($cants)) {
            $n = min(count($ids), count($cants));
            for ($i = 0; $i < $n; $i++) {
              $items[] = [
                'id_producto' => (int)$ids[$i],
                'cantidad' => (int)$cants[$i],
                'costo_unit' => isset($cus[$i]) ? (float)$cus[$i] : 0,
              ];
            }
          }

          // sacar id_usuario desde sesión (ajustado a varias estructuras)
$idUser = (int)($_SESSION['user']['id_usuario'] ?? 0);
if ($idUser <= 0) $idUser = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
if ($idUser <= 0) $idUser = (int)($_SESSION['auth']['id_usuario'] ?? 0);
if ($idUser <= 0) $idUser = (int)($_SESSION['user']['id'] ?? 0);

$idCompra = Purchase::createCompra($idUser, $idProveedor, $nota, $items);
          $_SESSION['flash_success'] = "Pedido #$idCompra registrado y stock actualizado.";
          self::redirectToPurchases($data['filters']);
        }

if ($action === 'delete') {
  $idCompra = (int)($post['id_compra'] ?? 0);
  if ($idCompra <= 0) throw new Exception('ID inválido.');

  Purchase::deleteHard($idCompra);
Notifier::notify(
  $idUser,
  'purchase_delete',
  'Compras',
  'Compra eliminada',
  'Se eliminó la compra #' . $idCompra . '.',
  'compras',
  $idCompra
);
  $_SESSION['flash_success'] = 'Compra eliminada.';
  self::redirectToPurchases($data['filters']);
}

      } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        self::redirectToPurchases($data['filters']);
      }
    }

    $data['purchases'] = Purchase::list($data['filters']);


    return $data;
  }
}