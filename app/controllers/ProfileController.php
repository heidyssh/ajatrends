<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Profile.php';

final class ProfileController {

  public static function show(): array {
    start_session();
    require_auth();
     $avatars = Profile::listAvatars();
    $id = (int)($_SESSION['user']['id'] ?? 0);

    return [
      'perfil' => Profile::get($id),
      'avatars' => $avatars
    ];
  }

  public static function update(array $post, array $files): array {
    start_session();
    require_auth();

    $id = (int)($_SESSION['user']['id'] ?? 0);

    $idAvatar = (int)($post['id_avatar'] ?? 0);
    $telefono = trim($post['telefono'] ?? '');
    $bio      = trim($post['bio'] ?? '');

    if ($idAvatar <= 0) {
      $idAvatar = (int)Profile::get($id)['id_avatar'];
    }

    Profile::updateBasic($id, $idAvatar, $telefono, $bio);

    // Subir foto (opcional)
    if (isset($files['foto']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
      $tmp  = $files['foto']['tmp_name'];
      $name = $files['foto']['name'];

      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','webp'];

      if (!in_array($ext, $allowed, true)) {
        return ['error' => 'Formato inválido. Usá JPG, PNG o WEBP.'] + self::show();
      }

      $dir = __DIR__ . '/../../public/uploads/avatars/';
      if (!is_dir($dir)) mkdir($dir, 0777, true);

      $fileName = 'u' . $id . '_' . time() . '.' . $ext;
      $dest = $dir . $fileName;

      if (!move_uploaded_file($tmp, $dest)) {
        return ['error' => 'No se pudo subir la imagen.'] + self::show();
      }

      Profile::setPhoto($id, 'uploads/avatars/' . $fileName);
    }

    // Refrescar avatar en sesión para el header
    $perfil = Profile::get($id);
    $_SESSION['user']['avatar'] = ($perfil['foto_archivo'] !== '')
      ? $perfil['foto_archivo']
      : ($perfil['avatar_archivo'] ?? 'assets/img/avatars/avatar1.jpg');

    return ['success' => 'Perfil actualizado ✅'] + self::show();
  }
}