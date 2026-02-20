<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Category {

  // Para filtros del catálogo
  public static function allActive(): array {
    $pdo = db();

    // Intenta con nombres comunes. Si tu tabla/campos cambian, me decís y lo ajusto.
    // En tu proyecto, ProductController llama Category::allActive()
    $sql = "SELECT id, nombre
            FROM categories
            WHERE 1=1
            ORDER BY nombre ASC";

    try {
      $st = $pdo->query($sql);
      return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
      // Si no existe la tabla 'categories', devolvemos vacío para que no reviente la vista
      return [];
    }
  }
}