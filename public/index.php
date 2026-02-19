<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ProfileController.php';

start_session();

$page = $_GET['page'] ?? 'login';

if ($page === 'logout') {
  AuthController::logout();
}
if ($page === 'profile')   $viewData = ProfileController::update($_POST, $_FILES);
$viewData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($page === 'login')   $viewData = AuthController::login($_POST);
  if ($page === 'register') $viewData = AuthController::register($_POST);
  if ($page === 'profile')   $viewData = ProfileController::update($_POST, $_FILES);
  }

$allowed = ['login', 'register', 'dashboard','profile','change_password'];
if (!in_array($page, $allowed, true)) $page = 'login';

if ($page === 'dashboard') require_auth();
if ($page === 'change_password') {
    $viewData = ProfileController::changePassword($_POST);
}

require __DIR__ . '/../app/views/layout/header.php';
require __DIR__ . "/../app/views/auth/$page.php";
require __DIR__ . '/../app/views/layout/footer.php';
