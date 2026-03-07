<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Kardex.php';


final class KardexController
{
  public static function handle(array $get): array
  {
    $filters = [
      'q' => $get['q'] ?? '',
      'tipo' => $get['tipo'] ?? 'TODOS',
      'from' => $get['from'] ?? '',
      'to' => $get['to'] ?? '',
      'id_producto' => $get['id_producto'] ?? 0,
    ];

    return [
      'filters' => $filters,
      'products' => Kardex::productsForFilter(),
      'resumen' => Kardex::resumen($filters),
      'movs' => Kardex::list($filters, 200),
    ];
  }
}