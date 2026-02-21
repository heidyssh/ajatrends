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
  // 1) Intentar solo activos
  try {
    $sql = "SELECT id_avatar, codigo, archivo
            FROM avatars
            WHERE estado = 1
            ORDER BY id_avatar ASC";
    $rows = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    if (!empty($rows)) return $rows;
  } catch (Throwable $e) {
    // seguimos con fallback
  }

  // 2) Fallback: traer todos (por si 'estado' está en 0 o no se usa)
  try {
    $sql2 = "SELECT id_avatar, codigo, archivo
             FROM avatars
             ORDER BY id_avatar ASC";
    $rows2 = db()->query($sql2)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    if (!empty($rows2)) return $rows2;
  } catch (Throwable $e) {
    // seguimos con fallback
  }

  // 3) Último fallback: leer la carpeta de avatares del proyecto
  $dir = __DIR__ . '/../../public/assets/img/avatars/';
  $out = [];
  if (is_dir($dir)) {
    $files = scandir($dir) ?: [];
    $id = 1;
    foreach ($files as $f) {
      if ($f === '.' || $f === '..') continue;
      $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) continue;
      $out[] = [
        'id_avatar' => $id++,
        'codigo'    => pathinfo($f, PATHINFO_FILENAME),
        'archivo'   => 'assets/img/avatars/' . $f
      ];
    }
  }
  return $out;
}

  public static function updateBasic(int $idUsuario, int $idAvatar, string $telefono, string $bio): void
  {
    $sql = "UPDATE usuarios_perfil
            SET id_avatar=?, telefono=?, bio=?, actualizado_en=CURRENT_TIMESTAMP
            WHERE id_usuario=?";
    $st = db()->prepare($sql);
    $st->execute([$idAvatar, $telefono, $bio, $idUsuario]);
  }

  public static function setAvatar(int $idUsuario, int $idAvatar): void {
  $pdo = db();
  // importante: si selecciona avatar, quitamos foto_archivo para que se vea el avatar
  $sql = "UPDATE usuarios_perfil
          SET id_avatar = :a, foto_archivo = '', actualizado_en = NOW()
          WHERE id_usuario = :u";
  $st = $pdo->prepare($sql);
  $st->execute([':a' => $idAvatar, ':u' => $idUsuario]);
}

public static function setPhoto(int $idUsuario, string $archivo): void {
  $pdo = db();
  $sql = "UPDATE usuarios_perfil
          SET foto_archivo = :f, actualizado_en = NOW()
          WHERE id_usuario = :u";
  $st = $pdo->prepare($sql);
  $st->execute([':f' => $archivo, ':u' => $idUsuario]);
}

public static function updateEmail(int $idUsuario, string $email): void {
  $pdo = db();
  $st = $pdo->prepare("UPDATE usuarios SET email = :e WHERE id_usuario = :u");
  $st->execute([':e' => $email, ':u' => $idUsuario]);
}

public static function updatePasswordHash(int $idUsuario, string $hash): void {
  $pdo = db();
  $st = $pdo->prepare("UPDATE usuarios SET pass_hash = :h WHERE id_usuario = :u");
  $st->execute([':h' => $hash, ':u' => $idUsuario]);
}

}