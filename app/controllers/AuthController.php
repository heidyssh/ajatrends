<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/Notifier.php';

final class AuthController
{
  public static function login(array $post): array
  {
    start_session();

    $email = trim($post['email'] ?? '');
    $pass  = (string)($post['password'] ?? '');

    if ($email === '' || $pass === '') {
      return ['error' => 'Completá correo y contraseña.'];
    }

    $user = User::findByEmail($email);
    if (!$user || (int)$user['estado'] !== 1) {
      return ['error' => 'Credenciales inválidas.'];
    }

    if (!password_verify($pass, $user['pass_hash'])) {
      return ['error' => 'Credenciales inválidas.'];
    }

    $perfil = Profile::get((int)$user['id_usuario']);
    $avatar = ($perfil['foto_archivo'] !== '')
      ? $perfil['foto_archivo']
      : ($perfil['avatar_archivo'] ?? 'assets/img/avatars/avatar1.jpg');

    $_SESSION['user'] = [
      'id' => (int)$user['id_usuario'],
      'nombre' => $user['nombre'],
      'rol' => (int)$user['id_rol'],
      'email' => $user['email'],
      'avatar' => $avatar
    ];
    Notifier::notify(
  (int)$user['id_usuario'],
  'login',
  'Seguridad',
  'Inicio de sesión',
  'El usuario ' . $user['nombre'] . ' inició sesión en el sistema.',
  'usuarios',
  (int)$user['id_usuario']
);

    header('Location: /ajatrends/public/index.php?page=dashboard');
    exit;
  }

  public static function register(array $post): array
  {
    start_session();

    $nombre = trim($post['nombre'] ?? '');
    $email  = trim($post['email'] ?? '');
    $pass   = (string)($post['password'] ?? '');
    $pass2  = (string)($post['password2'] ?? '');

    if ($nombre === '' || $email === '' || $pass === '') {
      return ['error' => 'Completá todos los campos.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ['error' => 'Correo no válido.'];
    }

    if ($pass !== $pass2) {
      return ['error' => 'Las contraseñas no coinciden.'];
    }

    if (strlen($pass) < 8) {
      return ['error' => 'Usá mínimo 8 caracteres.'];
    }

    if (User::findByEmail($email)) {
      return ['error' => 'Ese correo ya está registrado.'];
    }

    $idRol = 1;
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    User::create($idRol, $nombre, $email, $hash);
    Notifier::notify(
  null,
  'user_register',
  'Usuarios',
  'Nuevo usuario registrado',
  'Se registró un nuevo usuario: ' . $nombre,
  'usuarios',
  0,
  ['email'=>$email]
);

    $_SESSION['flash_success'] = 'Usuario creado. Ya podés iniciar sesión.';
    header('Location: /ajatrends/public/index.php?page=login');
    exit;
  }
  

  public static function logout(): void
  {
    start_session();
    $idUser = $_SESSION['user']['id'] ?? null;
$nombre = $_SESSION['user']['nombre'] ?? '';

Notifier::notify(
  $idUser,
  'logout',
  'Seguridad',
  'Cierre de sesión',
  'El usuario ' . $nombre . ' cerró sesión.',
  'usuarios',
  (int)$idUser
);
    session_destroy();
    header('Location: /ajatrends/public/index.php?page=login');
    exit;
  }
}