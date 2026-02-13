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
