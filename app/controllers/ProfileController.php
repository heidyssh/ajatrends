<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Profile.php';

final class ProfileController
{

  public static function show(): array
  {
    start_session();
    require_auth();
    $avatars = Profile::listAvatars();
    $id = (int) ($_SESSION['user']['id'] ?? 0);

    return [
      'perfil' => Profile::get($id),
      'avatars' => $avatars
    ];
  }

  public static function update(array $post, array $files): array
  {
    start_session();
    require_auth();
    $action = $_POST['action'] ?? '';

    $idUsuario = (int) ($_SESSION['user']['id_usuario'] ?? 0);
    if ($idUsuario <= 0)
      return ['error' => 'Sesión inválida.'];

    /* 1) Cambiar correo */
    if ($action === 'change_email') {
      $new = trim($_POST['new_email'] ?? '');
      if ($new === '' || !filter_var($new, FILTER_VALIDATE_EMAIL)) {
        return ['error' => 'Ingresá un correo válido.', 'perfil' => Profile::get($idUsuario), 'avatars' => Profile::listAvatars()];
      }

      Profile::updateEmail($idUsuario, $new);
      $_SESSION['user']['email'] = $new;

      return ['success' => 'Correo actualizado.', 'perfil' => Profile::get($idUsuario), 'avatars' => Profile::listAvatars()];
    }

    /* 2) Cambiar contraseña */
    if ($action === 'change_password') {
      $p1 = (string) ($_POST['new_password'] ?? '');
      $p2 = (string) ($_POST['new_password2'] ?? '');

      if (strlen($p1) < 6) {
        return ['error' => 'La contraseña debe tener al menos 6 caracteres.', 'perfil' => Profile::get($idUsuario), 'avatars' => Profile::listAvatars()];
      }
      if ($p1 !== $p2) {
        return ['error' => 'Las contraseñas no coinciden.', 'perfil' => Profile::get($idUsuario), 'avatars' => Profile::listAvatars()];
      }

      $hash = password_hash($p1, PASSWORD_DEFAULT);
      Profile::updatePasswordHash($idUsuario, $hash);

      return ['success' => 'Contraseña actualizada.', 'perfil' => Profile::get($idUsuario), 'avatars' => Profile::listAvatars()];
    }
    $id = (int) ($_SESSION['user']['id'] ?? 0);

    $idAvatar = (int) ($post['id_avatar'] ?? 0);
    $telefono = trim($post['telefono'] ?? '');
    $bio = trim($post['bio'] ?? '');

    if ($idAvatar <= 0) {
      $idAvatar = (int) Profile::get($id)['id_avatar'];
    }

    Profile::updateBasic($id, $idAvatar, $telefono, $bio);

    // Subir foto (opcional)
    if (isset($files['foto']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
      $tmp = $files['foto']['tmp_name'];
      $name = $files['foto']['name'];

      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $allowed = ['jpg', 'jpeg', 'png', 'webp'];

      if (!in_array($ext, $allowed, true)) {
        return ['error' => 'Formato inválido. Usá JPG, PNG o WEBP.'] + self::show();
      }

      $dir = __DIR__ . '/../../public/uploads/avatars/';
      if (!is_dir($dir))
        mkdir($dir, 0777, true);

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