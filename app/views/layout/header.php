<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$page = $_GET['page'] ?? 'login';
$isAuthPage = in_array($page, ['login','register'], true);
$isLogged = isset($_SESSION['user']);
$userName = $_SESSION['user']['nombre'] ?? 'Admin';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AJA TRENDS | Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>

<?php if ($isAuthPage || !$isLogged): ?>
  <!-- Auth topbar: ultra minimal -->
  <nav class="auth-top glass">
    <div class="auth-top-inner">
      <a class="auth-top-logo" href="index.php?page=login" title="Inicio">
        <img src="assets/img/logo.jpeg" alt="Logo">
      </a>

      <div class="d-flex align-items-center gap-2">
        <?php if ($page === 'login'): ?>
          <a class="auth-top-link" href="index.php?page=register">Crear cuenta</a>
        <?php else: ?>
          <a class="auth-top-link" href="index.php?page=login">Volver</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="container py-5">

<?php else: ?>

  <div class="app-shell">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
      <div class="brand">
        <img src="assets/img/logo.jpeg" alt="Logo">
        <div class="txt">
          <div class="t1">AJA TRENDS</div>
          <div class="t2">Inventario Admin</div>
        </div>
      </div>

      <div class="side-sec small text-white-50 mt-2 mb-2">MENÚ</div>

      <nav class="navs">
        <a class="nav-item <?= $page==='dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
          <div class="ic"><i class="bi bi-grid-1x2"></i></div>
          <span class="lbl">Dashboard</span>
        </a>

        <div class="side-sep"></div>

        <a class="nav-item disabled-link" href="#">
          <div class="ic"><i class="bi bi-bag-heart"></i></div>
          <span class="lbl">Productos</span>
        </a>
        <a class="nav-item disabled-link" href="#">
          <div class="ic"><i class="bi bi-truck"></i></div>
          <span class="lbl">Compras</span>
        </a>
        <a class="nav-item disabled-link" href="#">
          <div class="ic"><i class="bi bi-receipt"></i></div>
          <span class="lbl">Ventas</span>
        </a>
        <a class="nav-item disabled-link" href="#">
          <div class="ic"><i class="bi bi-box-seam"></i></div>
          <span class="lbl">Kardex</span>
        </a>
        <a class="nav-item disabled-link" href="#">
          <div class="ic"><i class="bi bi-graph-up"></i></div>
          <span class="lbl">Reportes</span>
        </a>
      </nav>

      <div class="sidebar-bottom mt-auto">
        <div class="mini-note">
          <div class="small text-white-50">Tip:</div>
          <div class="text-white-50 small">Revisá “Stock bajo” para saber qué pedir al proveedor.</div>
        </div>
      </div>
    </aside>

    <!-- Content -->
    <div class="content">
      <header class="topbar topbar-pro">
        <div class="left">
          <button id="sidebarToggle" class="icon-btn" type="button" title="Colapsar sidebar">
            <i class="bi bi-list"></i>
          </button>

          <!-- Search (solo UI por ahora, no rompe nada) -->
          <div class="search d-none d-md-flex">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Buscar (demo)" aria-label="Buscar">
          </div>
        </div>

        <div class="right d-flex align-items-center gap-2 gap-sm-3">
          <span class="pill d-none d-lg-inline-flex">
            <i class="bi bi-shield-lock"></i> Admin
          </span>

          <span class="pill d-none d-lg-inline-flex" id="clock">
            <i class="bi bi-clock"></i> --
          </span>

          <div class="userchip">
            <img src="assets/img/dueña.jpeg" alt="Dueña">
            <div class="d-none d-sm-block">
              <div class="fw-bold" style="line-height:1;">
                <?= htmlspecialchars($userName) ?>
              </div>
              <small>Sesión activa</small>
            </div>
          </div>

          <a class="btn btn-outline-dark btn-sm rounded-pill px-3" href="index.php?page=logout">
            <i class="bi bi-box-arrow-right me-1"></i> Salir
          </a>
        </div>
      </header>

      <main class="pt-4">
<?php endif; ?>
