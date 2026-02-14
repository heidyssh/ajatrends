<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/auth.php';

final class AuthController {

  public static function login(array $post): array {
    start_session();
    $email = trim($post['email'] ?? '');
    $pass  = (string)($post['password'] ?? '');

    if ($email === '' || $pass === '') return ['error' => 'Completá correo y contraseña.'];

    $user = User::findByEmail($email);
    if (!$user || (int)$user['estado'] !== 1) return ['error' => 'Credenciales inválidas.'];

    if (!password_verify($pass, $user['pass_hash'])) return ['error' => 'Credenciales inválidas.'];

    $_SESSION['user'] = [
      'id' => (int)$user['id_usuario'],
      'nombre' => $user['nombre'],
      'rol' => (int)$user['id_rol'],
      'email' => $user['email']
    ];

    header('Location: /ajatrends/public/index.php?page=dashboard');
      exit;
  }

  public static function register(array $post): array {
    start_session();
    $nombre = trim($post['nombre'] ?? '');
    $email  = trim($post['email'] ?? '');
    $pass   = (string)($post['password'] ?? '');
    $pass2  = (string)($post['password2'] ?? '');

    if ($nombre === '' || $email === '' || $pass === '') return ['error' => 'Completá todos los campos.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['error' => 'Correo no válido.'];
    if ($pass !== $pass2) return ['error' => 'Las contraseñas no coinciden.'];
    if (strlen($pass) < 8) return ['error' => 'Usá mínimo 8 caracteres.'];

    if (User::findByEmail($email)) return ['error' => 'Ese correo ya está registrado.'];

    // Por defecto: rol ADMIN (asumiendo que ADMIN quedó como id_rol = 1 en tu insert)
    $idRol = 1;

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    User::create($idRol, $nombre, $email, $hash);

    return ['success' => 'Usuario creado. Ya podés iniciar sesión.'];
  }

  public static function logout(): void {
    start_session();
    session_destroy();
    header('Location: /ajatrends/public/index.php?page=login');
      exit;
  }
}
