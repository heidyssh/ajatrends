<?php
declare(strict_types=1);

function start_session(): void {
  if (session_status() === PHP_SESSION_NONE) session_start();
}

function is_logged_in(): bool {
  start_session();
  return isset($_SESSION['user']);
}

function require_auth(): void {
  if (!is_logged_in()) {
    header('Location: /index.php?page=login');
    exit;
  }
}
function is_admin(): bool {
  start_session();
  // En tu sesión 'rol' es id_rol (número). ADMIN normalmente = 1
  $rol = (int)($_SESSION['user']['rol'] ?? 0);
  return $rol === 1;
}

function require_admin(): void
{
  start_session();
  require_auth();

  if ((int)($_SESSION['user']['rol'] ?? 0) !== 1) {
    $_SESSION['flash_error'] = 'No tenés permiso para entrar a Usuarios.';
    header('Location: /ajatrends/public/index.php?page=dashboard');
    exit;
  }
}

