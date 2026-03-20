<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Notifier.php';

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

    $action = $post['action'] ?? '';
    $idUsuario = (int) ($_SESSION['user']['id'] ?? 0);

    if ($idUsuario <= 0) {
      return ['error' => 'Sesión inválida.'];
    }


    if ($action === 'change_email') {
      $new = trim($post['new_email'] ?? '');

      if ($new === '' || !filter_var($new, FILTER_VALIDATE_EMAIL)) {
        return [
          'error' => 'Ingresá un correo válido.',
          'perfil' => Profile::get($idUsuario),
          'avatars' => Profile::listAvatars()
        ];
      }

      Profile::updateEmail($idUsuario, $new);
      Notifier::notifyUser(
  $idUsuario,
  'profile_email',
  'Perfil',
  'Correo actualizado',
  'Se cambió el correo de acceso del usuario.',
  'usuarios',
  $idUsuario,
  ['nuevo_correo' => $new]
);
      $_SESSION['user']['email'] = $new;

      return [
        'success' => 'Correo actualizado.',
        'perfil' => Profile::get($idUsuario),
        'avatars' => Profile::listAvatars()
      ];
    }


if ($action === 'change_password') {
  Notifier::notifyUser(
  $idUsuario,
  'profile_password',
  'Perfil',
  'Contraseña actualizada',
  'Se actualizó la contraseña de la cuenta.',
  'usuarios',
  $idUsuario
);
  $p1 = trim((string) ($post['new_password'] ?? ''));
  $p2 = trim((string) ($post['new_password2'] ?? ''));

  if ($p1 === '' || $p2 === '') {
    return [
      'error' => 'Completá ambos campos de contraseña.',
      'perfil' => Profile::get($idUsuario),
      'avatars' => Profile::listAvatars()
    ];
  }

  if (strlen($p1) < 6) {
    return [
      'error' => 'La contraseña debe tener al menos 6 caracteres.',
      'perfil' => Profile::get($idUsuario),
      'avatars' => Profile::listAvatars()
    ];
  }

  if ($p1 !== $p2) {
    return [
      'error' => 'Las contraseñas no coinciden.',
      'perfil' => Profile::get($idUsuario),
      'avatars' => Profile::listAvatars()
    ];
  }

  $hash = password_hash($p1, PASSWORD_BCRYPT);

  User::updatePassword($idUsuario, $hash);

  return [
    'success' => 'Contraseña actualizada',
    'perfil' => Profile::get($idUsuario),
    'avatars' => Profile::listAvatars()
  ];
}


    $id = $idUsuario;
    $perfilActual = Profile::get($id);

    $idAvatar = (int) ($post['id_avatar'] ?? 0);
    $telefono = trim($post['telefono'] ?? '');
    $bio = trim($post['bio'] ?? '');

    if ($idAvatar <= 0) {
      $idAvatar = (int) ($perfilActual['id_avatar'] ?? 1);
    }

  
    Profile::updateBasic($id, $idAvatar, $telefono, $bio);
    Notifier::notifyUser(
  $idUsuario,
  'profile_avatar',
  'Perfil',
  'Perfil actualizado',
  'Se actualizó la foto/avatar del perfil.',
  'usuarios',
  $idUsuario
);

    $subioFoto = false;

   
    if (isset($files['foto']) && isset($files['foto']['error']) && $files['foto']['error'] === UPLOAD_ERR_OK) {
      $tmp = $files['foto']['tmp_name'];
      $name = $files['foto']['name'];

      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      $allowed = ['jpg', 'jpeg', 'png', 'webp'];

      if (!in_array($ext, $allowed, true)) {
        return ['error' => 'Formato inválido. Usá JPG, PNG o WEBP.'] + self::show();
      }

      $dir = __DIR__ . '/../../public/uploads/avatars/';
      if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
      }

      $fileName = 'u' . $id . '_' . time() . '.' . $ext;
      $dest = $dir . $fileName;

      if (!move_uploaded_file($tmp, $dest)) {
        return ['error' => 'No se pudo subir la imagen.'] + self::show();
      }

      Profile::setPhoto($id, 'uploads/avatars/' . $fileName);
      $subioFoto = true;
    }

    
    if (!$subioFoto) {
      Profile::setAvatar($id, $idAvatar);
    }

    
    $perfil = Profile::get($id);
    $_SESSION['user']['avatar'] = ($perfil['foto_archivo'] !== '')
      ? $perfil['foto_archivo']
      : ($perfil['avatar_archivo'] ?? 'assets/img/avatars/avatar1.jpg');

    return ['success' => 'Perfil actualizado'] + self::show();
  }
}