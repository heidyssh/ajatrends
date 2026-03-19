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

  // Evita volver a páginas protegidas con el botón atrás
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Cache-Control: post-check=0, pre-check=0', false);
  header('Pragma: no-cache');
  header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
}

function is_admin(): bool {
  start_session();
  $rol = (int)($_SESSION['user']['rol'] ?? 0);
  return $rol === 1;
}

function is_admin_or_logistica(): bool {
  start_session();
  $rol = (int)($_SESSION['user']['rol'] ?? 0);
  return in_array($rol, [1, 2], true);
}

function require_admin(string $modulo = 'Usuarios'): void
{
  start_session();
  require_auth();

  if ((int)($_SESSION['user']['rol'] ?? 0) !== 1) {
    $_SESSION['flash_error'] = "No tienes permiso para entrar a {$modulo}.";
    header('Location: /ajatrends/public/index.php?page=dashboard');
    exit;
  }
}

function require_admin_or_logistica(string $modulo = 'esta sección'): void
{
  start_session();
  require_auth();

  if (!is_admin_or_logistica()) {
    $_SESSION['flash_error'] = "No tienes permiso para entrar a {$modulo}.";
    header('Location: /ajatrends/public/index.php?page=dashboard');
    exit;
  }
}