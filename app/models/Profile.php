<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

final class Profile
{


  public static function get(int $idUsuario): array
  {
    // 1) Asegurar que exista perfil (sin NULL)
    $check = db()->prepare("SELECT 1 FROM usuarios_perfil WHERE id_usuario=?");
    $check->execute([$idUsuario]);
    $exists = (bool) $check->fetchColumn();

    if (!$exists) {
      // avatar por defecto: el primero disponible
      $idAv = (int) db()->query("SELECT id_avatar FROM avatars WHERE estado=1 ORDER BY id_avatar ASC LIMIT 1")->fetchColumn();
      if ($idAv <= 0)
        $idAv = 1;

      $ins = db()->prepare("INSERT INTO usuarios_perfil (id_usuario, id_avatar, foto_archivo, telefono, bio)
                          VALUES (?, ?, '', '', '')");
      $ins->execute([$idUsuario, $idAv]);
    }

    // 2) Traer perfil + archivo de avatar
    $sql = "SELECT p.id_usuario, p.id_avatar, p.foto_archivo, p.telefono, p.bio,
                 a.archivo AS avatar_archivo
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
    $sql = "SELECT id_avatar, codigo, archivo
            FROM avatars
            WHERE estado = 1
            ORDER BY id_avatar ASC";
    return db()->query($sql)->fetchAll();
  }

  public static function updateBasic(int $idUsuario, int $idAvatar, string $telefono, string $bio): void
  {
    $sql = "UPDATE usuarios_perfil
            SET id_avatar=?, telefono=?, bio=?, actualizado_en=CURRENT_TIMESTAMP
            WHERE id_usuario=?";
    $st = db()->prepare($sql);
    $st->execute([$idAvatar, $telefono, $bio, $idUsuario]);
  }

  public static function setPhoto(int $idUsuario, string $fotoArchivo): void
  {
    $sql = "UPDATE usuarios_perfil
            SET foto_archivo=?, actualizado_en=CURRENT_TIMESTAMP
            WHERE id_usuario=?";
    $st = db()->prepare($sql);
    $st->execute([$fotoArchivo, $idUsuario]);
  }
}