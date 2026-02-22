<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Purchase.php';

final class PurchaseController {

  private static function redirectToPurchases(array $filters = []): void {
    $qs = http_build_query([
      'page' => 'purchases',
      'q' => $filters['q'] ?? '',
      'estado' => $filters['estado'] ?? 'TODOS',
      'view' => (int)($filters['view'] ?? 0),
    ]);
    header('Location: index.php?' . $qs);
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
    ];

    if (!empty($post)) {
      $action = (string)($post['action'] ?? '');

      try {
        if ($action === 'create') {
          $nota = trim((string)($post['nota'] ?? ''));

          $ids = $post['id_producto'] ?? [];
          $cants = $post['cantidad'] ?? [];
          $cus = $post['costo_unit'] ?? [];

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

$idCompra = Purchase::createCompra($idUser, $nota, $items);
          $_SESSION['flash_success'] = "Pedido #$idCompra registrado y stock actualizado.";
          self::redirectToPurchases($data['filters']);
        }

if ($action === 'delete') {
  $idCompra = (int)($post['id_compra'] ?? 0);
  if ($idCompra <= 0) throw new Exception('ID inválido.');

  Purchase::deleteHard($idCompra);

  $_SESSION['flash_success'] = 'Compra eliminada.';
  self::redirectToPurchases($data['filters']);
}

      } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        self::redirectToPurchases($data['filters']);
      }
    }

    $data['purchases'] = Purchase::list($data['filters']);

    $idView = (int)($get['view'] ?? 0);
    if ($idView > 0) {
      $data['purchase'] = Purchase::find($idView);
      $data['items'] = Purchase::items($idView);
    }

    return $data;
  }
}