<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Sale.php';

final class SaleController {

  private static function redirectToSales(array $filters = []): void {
    $qs = [
      'page' => 'sales',
      'q' => $filters['q'] ?? '',
      'estado' => $filters['estado'] ?? 'TODOS',
      'from' => $filters['from'] ?? '',
      'to' => $filters['to'] ?? '',
    ];
    header('Location: index.php?' . http_build_query($qs));
    exit;
  }

  public static function handle(array $post, array $get): array {
    require_auth();
    require_admin(); // ventas solo admin en tu sistema

    $filters = [
      'q' => trim((string)($get['q'] ?? '')),
      'estado' => (string)($get['estado'] ?? 'TODOS'),
      'from' => (string)($get['from'] ?? ''),
      'to' => (string)($get['to'] ?? ''),
    ];

    $data = [
      'filters' => $filters,
      'sales' => [],
      'products' => Sale::productosActivosConStock(),
      'clientes' => Sale::clientes(),
      'direcciones' => [],
      'stats' => [
        'kpis' => Sale::kpis($filters),
        'serie' => Sale::seriesDiaria($filters, 14),
        'top' => Sale::topProductos($filters, 8),
        'cats' => Sale::ventasPorCategoria($filters, 8),
      ],
      'isAdmin' => is_admin(),
    ];

    // ===== AJAX: direcciones por cliente =====
    $actionGet = (string)($get['action'] ?? '');
    if ($actionGet === 'direcciones_json') {
      $idCliente = (int)($get['id_cliente'] ?? 0);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'ok' => true,
        'direcciones' => Sale::direccionesByCliente($idCliente),
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // ===== AJAX: ver detalle venta =====
    if ($actionGet === 'view_json') {
      $id = (int)($get['id'] ?? 0);
      header('Content-Type: application/json; charset=utf-8');

      if ($id <= 0) {
        echo json_encode(['ok' => false, 'message' => 'ID inválido']);
        exit;
      }

      $venta = Sale::find($id);
      if (!$venta) {
        echo json_encode(['ok' => false, 'message' => 'Venta no encontrada']);
        exit;
      }

      $items = Sale::items($id);

      echo json_encode([
        'ok' => true,
        'id_venta' => (int)$venta['id_venta'],
        'fecha' => (string)($venta['fecha'] ?? ''),
        'usuario' => (string)($venta['id_usuario'] ?? ''),
        'estado' => (string)($venta['estado'] ?? ''),
        'cliente' => (string)($venta['cliente'] ?? ''),
        'direccion' => (string)($venta['direccion_linea'] ?? ''),
        'ciudad' => (string)($venta['ciudad'] ?? ''),
        'referencia' => (string)($venta['referencia'] ?? ''),
        'nota' => (string)($venta['nota'] ?? ''),
        'subtotal' => (float)($venta['subtotal'] ?? 0),
        'descuento' => (float)($venta['descuento'] ?? 0),
        'total' => (float)($venta['total'] ?? 0),
        'items' => array_map(static function($it){
          return [
            'sku' => (string)($it['sku'] ?? ''),
            'nombre' => (string)($it['nombre'] ?? ''),
            'cantidad' => (int)($it['cantidad'] ?? 0),
            'precio_unit' => (float)($it['precio_unit'] ?? 0),
            'subtotal' => (float)($it['subtotal'] ?? 0),
          ];
        }, is_array($items) ? $items : []),
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // ===== POST actions =====
    if (!empty($post)) {
      $action = (string)($post['action'] ?? '');

      try {
        // sacar id_usuario desde sesión (tu misma lógica)
        $idUser = (int)($_SESSION['user']['id_usuario'] ?? 0);
        if ($idUser <= 0) $idUser = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
        if ($idUser <= 0) $idUser = (int)($_SESSION['auth']['id_usuario'] ?? 0);
        if ($idUser <= 0) $idUser = (int)($_SESSION['user']['id'] ?? 0);

        if ($action === 'create') {
          // ✅ Ventas modo libreta: NO se selecciona cliente/dirección
$idCliente   = 1; // CONSUMIDOR FINAL (de tu BD)
$idDireccion = 1; // SIN DIRECCION (de tu BD)

$descuento = (float)($post['descuento'] ?? 0);

// Texto libre escrito por ella
$clienteTxt   = trim((string)($post['cliente_txt'] ?? ''));
$direccionTxt = trim((string)($post['direccion_txt'] ?? ''));
$notaBase     = trim((string)($post['nota'] ?? ''));

if ($clienteTxt === '')   $clienteTxt = 'CONSUMIDOR FINAL';
if ($direccionTxt === '') $direccionTxt = 'SIN DIRECCION';

// ✅ Guardamos todo como “libreta” en la nota
$nota = "Cliente: $clienteTxt | Dirección: $direccionTxt";
if ($notaBase !== '') $nota .= " | Nota: $notaBase";

          $ids = $post['id_producto'] ?? [];
          $cants = $post['cantidad'] ?? [];
          $pus = $post['precio_unit'] ?? [];

          $items = [];
          if (is_array($ids) && is_array($cants)) {
            $n = min(count($ids), count($cants));
            for ($i = 0; $i < $n; $i++) {
              $items[] = [
                'id_producto' => (int)$ids[$i],
                'cantidad' => (int)$cants[$i],
                'precio_unit' => isset($pus[$i]) ? (float)$pus[$i] : 0,
              ];
            }
          }

          $idVenta = Sale::createVenta($idUser, $idCliente, $idDireccion, $descuento, $nota, $items);
          $_SESSION['flash_success'] = "Venta #$idVenta registrada. Stock actualizado.";
          self::redirectToSales($filters);
        }

        if ($action === 'cancel') {
          $idVenta = (int)($post['id_venta'] ?? 0);
          if ($idVenta <= 0) throw new Exception('ID inválido.');
          Sale::cancel($idVenta, $idUser, 'Venta anulada y stock devuelto');
          $_SESSION['flash_success'] = "Venta #$idVenta anulada. Stock devuelto.";
          self::redirectToSales($filters);
        }

      } catch (Throwable $e) {
        $_SESSION['flash_error'] = $e->getMessage();
        self::redirectToSales($filters);
      }
    }

    // ===== data list =====
    $data['sales'] = Sale::list($filters);
    return $data;
  }
}