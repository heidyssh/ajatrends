<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class User {
  public static function findByEmail(string $email): ?array {
    $stmt = db()->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  public static function create(int $idRol, string $nombre, string $email, string $passHash): int {
    $stmt = db()->prepare("INSERT INTO usuarios (id_rol, nombre, email, pass_hash, estado) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute([$idRol, $nombre, $email, $passHash]);
    return (int)db()->lastInsertId();
  }
}
