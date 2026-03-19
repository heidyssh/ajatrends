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
    $pass = (string) ($post['password'] ?? '');

    if ($email === '' || $pass === '') {
      return ['error' => 'Completá correo y contraseña.'];
    }

    $user = User::findByEmail($email);
    if (!$user) {
      return ['error' => 'Credenciales inválidas.'];
    }

    if ((int) $user['estado'] !== 1) {
      return ['error' => 'Tu cuenta aún no ha sido aprobada por el administrador.'];
    }

    if (!password_verify($pass, $user['pass_hash'])) {
      return ['error' => 'Credenciales inválidas.'];
    }

    $perfil = Profile::get((int) $user['id_usuario']);
    $avatar = ($perfil['foto_archivo'] !== '')
      ? $perfil['foto_archivo']
      : ($perfil['avatar_archivo'] ?? 'assets/img/avatars/avatar1.jpg');

    $_SESSION['user'] = [
      'id' => (int) $user['id_usuario'],
      'nombre' => $user['nombre'],
      'rol' => (int) $user['id_rol'],
      'rol_nombre' => ((int) $user['id_rol'] === 1 ? 'ADMIN' : 'LOGISTICA'),
      'email' => $user['email'],
      'avatar' => $avatar
    ];
    session_regenerate_id(true);
    if ((int) $user['id_rol'] === 1) {
      Notifier::notifyUser(
        (int) $user['id_usuario'],
        'login',
        'Seguridad',
        'Inicio de sesión',
        'El usuario ' . $user['nombre'] . ' inició sesión en el sistema.',
        'usuarios',
        (int) $user['id_usuario']
      );
    }

    header('Location: /ajatrends/public/index.php?page=dashboard');
    exit;
  }

  public static function register(array $post): array
  {
    start_session();

    $nombre = trim($post['nombre'] ?? '');
    $email = trim($post['email'] ?? '');
    $pass = (string) ($post['password'] ?? '');
    $pass2 = (string) ($post['password2'] ?? '');

    if ($nombre === '' || $email === '' || $pass === '') {
      return ['error' => 'Completá todos los campos.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ['error' => 'Correo no válido.'];
    }

    if ($pass !== $pass2) {
      return ['error' => 'Las contraseñas no coinciden.'];
    }

    if (
      strlen($pass) < 8 ||
      !preg_match('/[A-Z]/', $pass) ||
      !preg_match('/[a-z]/', $pass) ||
      !preg_match('/[0-9]/', $pass)
    ) {
      return ['error' => 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y un número.'];
    }

    if (User::findByEmail($email)) {
      return ['error' => 'Ese correo ya está registrado.'];
    }

    $idRol = 2; // por defecto colaborador / logística
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    User::create($idRol, $nombre, $email, $hash, 0);
    Notifier::notifyAdmins(
      null,
      'user_register',
      'Usuarios',
      'Nuevo usuario registrado',
      'Se registró un nuevo usuario: ' . $nombre,
      'usuarios',
      0,
      ['email' => $email]
    );

    $_SESSION['flash_success'] = 'Tu cuenta fue creada correctamente. Espera la aprobación del administrador para poder iniciar sesión.';
    header('Location: /ajatrends/public/index.php?page=login');
    exit;
  }


  public static function logout(): void
  {
    start_session();
    $idUser = $_SESSION['user']['id'] ?? null;
    $nombre = $_SESSION['user']['nombre'] ?? '';

    $rol = (int) ($_SESSION['user']['rol'] ?? 0);

    if ($rol === 1) {
      Notifier::notifyUser(
        (int) $idUser,
        'logout',
        'Seguridad',
        'Cierre de sesión',
        'El usuario ' . $nombre . ' cerró sesión.',
        'usuarios',
        (int) $idUser
      );
    }
    $_SESSION = [];
    session_unset();

    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }

    session_destroy();

    header('Location: /ajatrends/public/index.php?page=login&logout=1');
    exit;
  }
}