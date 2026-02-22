<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/ProfileController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
start_session();

$page = $_GET['page'] ?? 'login';
$viewData = [];

if ($page === 'logout') {
  AuthController::logout();
}

/* GET: mostrar pantallas */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if ($page === 'profile') {
    $viewData = ProfileController::show();   // ✅ aquí carga avatars y perfil
  }
}

/* POST: acciones */
if ($page === 'logout') {
  AuthController::logout();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($page === 'login')   $viewData = AuthController::login($_POST);
  if ($page === 'register') $viewData = AuthController::register($_POST);
  if ($page === 'profile')   $viewData = ProfileController::update($_POST, $_FILES);
  }
if ($page === 'products' && isset($_GET['ajax']) && $_GET['ajax'] == '1') {
  require_auth();
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(ProductController::ajax($_GET));
  exit;
}
$allowed = ['login', 'register', 'dashboard','profile','change_password','products'];
if (!in_array($page, $allowed, true)) $page = 'login';

if ($page === 'dashboard') require_auth();
if ($page === 'profile') require_auth();
if ($page === 'products') {
  require_auth();
  $viewData = ProductController::handle($_POST, $_FILES, $_GET);
}
if ($page === 'change_password') {
    $viewData = ProfileController::changePassword($_POST);
}

require __DIR__ . '/../app/views/layout/header.php';
require __DIR__ . "/../app/views/auth/$page.php";
// ---- Puente global: viewData -> SESSION flash (para toasts)
if (!empty($viewData['success'])) {
  $_SESSION['flash_success'] = (string)$viewData['success'];
  unset($viewData['success']); // evita que te salga alert + toast al mismo tiempo
}
if (!empty($viewData['error'])) {
  $_SESSION['flash_error'] = (string)$viewData['error'];
  unset($viewData['error']);
}
require __DIR__ . '/../app/views/layout/footer.php';
