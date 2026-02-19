<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

final class Profile {

  public static function get(int $idUsuario): array {
    $sql = "SELECT p.*, a.archivo AS avatar_archivo
            FROM usuarios_perfil p
            JOIN avatars a ON a.id_avatar = p.id_avatar
            WHERE p.id_usuario = ?";
    $st = db()->prepare($sql);
    $st->execute([$idUsuario]);
    $row = $st->fetch();
    return $row ?: [
      'id_usuario' => $idUsuario,
      'id_avatar' => 1,
      'foto_archivo' => '',
      'telefono' => '',
      'bio' => '',
      'avatar_archivo' => 'assets/img/avatars/avatar1.jpg'
    ];
  }

  public static function listAvatars(): array {
    $st = db()->query("SELECT id_avatar, codigo, archivo FROM avatars WHERE estado=1 ORDER BY id_avatar ASC");
    return $st->fetchAll();
  }

  public static function updateBasic(int $idUsuario, int $idAvatar, string $telefono, string $bio): void {
    $sql = "UPDATE usuarios_perfil
            SET id_avatar=?, telefono=?, bio=?, actualizado_en=CURRENT_TIMESTAMP
            WHERE id_usuario=?";
    $st = db()->prepare($sql);
    $st->execute([$idAvatar, $telefono, $bio, $idUsuario]);
  }

  public static function setPhoto(int $idUsuario, string $fotoArchivo): void {
    $sql = "UPDATE usuarios_perfil
            SET foto_archivo=?, actualizado_en=CURRENT_TIMESTAMP
            WHERE id_usuario=?";
    $st = db()->prepare($sql);
    $st->execute([$fotoArchivo, $idUsuario]);
  }
}