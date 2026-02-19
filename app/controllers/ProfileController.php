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
  public static function changePassword(array $post): array {
    start_session();
    require_auth();

    $id = (int)$_SESSION['user']['id'];
    $actual = $post['actual'] ?? '';
    $nueva  = $post['nueva'] ?? '';
    $confirm = $post['confirm'] ?? '';

    require_once __DIR__ . '/../models/User.php';
    $user = User::findById($id);

    if (!$user || !password_verify($actual, $user['pass_hash'])) {
        return ['error' => 'Contraseña actual incorrecta.'] + self::show();
    }

    if ($nueva !== $confirm) {
        return ['error' => 'Las contraseñas no coinciden.'] + self::show();
    }

    if (strlen($nueva) < 8) {
        return ['error' => 'Mínimo 8 caracteres.'] + self::show();
    }

    $hash = password_hash($nueva, PASSWORD_BCRYPT);
    User::updatePassword($id, $hash);

    return ['success' => 'Contraseña actualizada correctamente.'] + self::show();
}
}
