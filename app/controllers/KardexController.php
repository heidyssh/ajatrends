<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Kardex.php';
require_once __DIR__ . '/../models/Sale.php';

final class KardexController
{
  private static function redirectToKardex(array $filters = []): void
  {
    $qs = [
      'page' => 'kardex',
      'q' => $filters['q'] ?? '',
      'tipo' => $filters['tipo'] ?? 'TODOS',
      'from' => $filters['from'] ?? '',
      'to' => $filters['to'] ?? '',
      'id_producto' => $filters['id_producto'] ?? 0,
    ];

    header('Location: index.php?' . http_build_query($qs));
    exit;
  }

  public static function handle(array $get, array $post = []): array
  {
    $filters = [
      'q' => $get['q'] ?? '',
      'tipo' => $get['tipo'] ?? 'TODOS',
      'from' => $get['from'] ?? '',
      'to' => $get['to'] ?? '',
      'id_producto' => $get['id_producto'] ?? 0,
    ];

    if (!empty($post)) {
      $action = (string)($post['action'] ?? '');

      try {
        $idUser = (int)($_SESSION['user']['id_usuario'] ?? 0);
        if ($idUser <= 0) {
          $idUser = (int)($_SESSION['user']['id'] ?? 0);
        }

        if ($action === 'delete_sale_from_kardex') {
          $idVenta = (int)($post['id_venta'] ?? 0);
          if ($idVenta <= 0) {
            throw new Exception('Venta inválida.');
          }

          $venta = Sale::find($idVenta);
          if (!$venta) {
            throw new Exception('La venta no existe.');
          }

          // Si no está anulada, primero la anulamos para devolver stock
          if ((string)$venta['estado'] !== 'ANULADA') {
            Sale::cancel($idVenta, $idUser, 'Venta anulada desde Kardex');
          }

          // Luego sí la eliminamos completamente
          Sale::deleteVenta($idVenta);

          $_SESSION['flash_success'] = "Venta #{$idVenta} eliminada desde Kardex.";
          self::redirectToKardex($filters);
        }

        if ($action === 'clear_kardex_sales') {
          Kardex::deleteAllSalesHistory($idUser);
          $_SESSION['flash_success'] = 'Se limpió el historial de ventas del Kardex.';
          self::redirectToKardex($filters);
        }

      } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        self::redirectToKardex($filters);
      }
    }

    return [
      'filters' => $filters,
      'products' => Kardex::productsForFilter(),
      'resumen' => Kardex::resumen($filters),
      'movs' => Kardex::list($filters, 200),
    ];
  }
}