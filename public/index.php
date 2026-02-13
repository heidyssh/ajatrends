<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

start_session();

$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
  AuthController::logout();
}

$viewData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($page === 'login')   $viewData = AuthController::login($_POST);
  if ($page === 'register') $viewData = AuthController::register($_POST);
}

$allowed = ['login', 'register', 'dashboard'];
if (!in_array($page, $allowed, true)) $page = 'login';

if ($page === 'dashboard') require_auth();

require __DIR__ . '/../app/views/layout/header.php';
require __DIR__ . "/../app/views/auth/$page.php";
require __DIR__ . '/../app/views/layout/footer.php';
