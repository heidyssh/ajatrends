<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Category {

  public static function allActive(): array {
    try {
      $pdo = db();
      $sql = "SELECT id_categoria, nombre
              FROM categorias
              WHERE estado = 1
              ORDER BY nombre ASC";
      $st = $pdo->query($sql);
      return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
      return [];
    }
  }
}