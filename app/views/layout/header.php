<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$page = $_GET['page'] ?? 'login';
$isAuthPage = in_array($page, ['login', 'register'], true);
$isLogged = isset($_SESSION['user']);
$userName = $_SESSION['user']['nombre'] ?? 'Admin';

require_once __DIR__ . '/../../models/Notification.php';
$idNotifUser = (int)($_SESSION['user']['id'] ?? 0);
$notifCount = Notification::unreadCount($idNotifUser);
$notifItems = Notification::latest($idNotifUser, 8);
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
          <a class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
            <div class="ic"><i class="bi bi-grid-1x2"></i></div>
            <span class="lbl">Dashboard</span>
          </a>

          <div class="side-sep"></div>

          <a class="nav-item <?= $page === 'products' ? 'active' : '' ?>" href="index.php?page=products">
            <div class="ic"><i class="bi bi-bag-heart"></i></div>
            <span class="lbl">Productos</span>
          </a>

          <a class="nav-item <?= $page === 'purchases' ? 'active' : '' ?>" href="index.php?page=purchases">
            <div class="ic"><i class="bi bi-truck"></i></div>
            <span class="lbl">Compras</span>
          </a>

          <a class="nav-item <?= $page === 'sales' ? 'active' : '' ?>" href="index.php?page=sales">
            <div class="ic"><i class="bi bi-receipt"></i></div>
            <span class="lbl">Ventas</span>
          </a>

          <a class="nav-item <?= $page === 'kardex' ? 'active' : '' ?>" href="index.php?page=kardex">
            <div class="ic"><i class="bi bi-box-seam"></i></div>
            <span class="lbl">Kardex</span>
          </a>

          <a class="nav-item disabled-link" href="#">
            <div class="ic"><i class="bi bi-graph-up"></i></div>
            <span class="lbl">Reportes</span>
          </a>

          <?php if ((int)($_SESSION['user']['rol'] ?? 0) === 1): ?>
            <a class="nav-item <?= $page === 'users' ? 'active' : '' ?>" href="index.php?page=users">
              <div class="ic"><i class="bi bi-people"></i></div>
              <span class="lbl">Usuarios</span>
            </a>
          <?php endif; ?>
        </nav>
      </aside>

      <div class="content">
        <header class="topbar topbar-pro">
          <div class="left">
            <button id="sidebarToggle" class="icon-btn" type="button" title="Colapsar sidebar">
              <i class="bi bi-list"></i>
            </button>

            <div class="search d-none d-md-flex">
              <i class="bi bi-search"></i>
              <input type="text" placeholder="Buscar (demo)" aria-label="Buscar">
            </div>
          </div>

          <div class="right d-flex align-items-center gap-2 gap-sm-3">

            <div class="dropdown">
              <button class="icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
                <i class="bi bi-bell"></i>
                <span id="notifBadge"
                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $notifCount > 0 ? '' : 'd-none' ?>">
                  <?= (int)$notifCount ?>
                </span>
              </button>

              <div id="notifDropdown" class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width:360px; max-height:420px; overflow:auto;">
                <div class="d-flex align-items-center justify-content-between px-2 py-1">
                  <div class="fw-bold">Notificaciones</div>

                  <?php if (!empty($notifItems)): ?>
                    <button
                      type="button"
                      id="btnClearAllNotifications"
                      class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash3 me-1"></i> Limpiar todo
                    </button>
                  <?php endif; ?>
                </div>

                <hr class="my-2">

                <?php if (!$notifItems): ?>
                  <div id="notifEmpty" class="px-2 py-2 text-muted small">No hay notificaciones.</div>
                <?php else: ?>

                  <?php foreach ($notifItems as $n): ?>
                    <?php
                      $refTabla = (string)($n['ref_tabla'] ?? '');
                      $refId = (int)($n['ref_id'] ?? 0);
                      $meta = [];

                      if (!empty($n['meta_json'])) {
                        $tmp = json_decode((string)$n['meta_json'], true);
                        if (is_array($tmp)) $meta = $tmp;
                      }

                      $link = "index.php?page=dashboard";

                      switch ($refTabla) {
                        case 'agenda_eventos':
                          $fecha = (string)($meta['fecha'] ?? date('Y-m-d'));
                          $link = "index.php?page=agenda&date=" . urlencode($fecha) . "#evento-" . $refId;
                          break;

                        case 'productos':
                          $link = "index.php?page=products&highlight=" . $refId;
                          break;

                        case 'ventas':
                          $link = "index.php?page=sales&view=" . $refId;
                          break;

                        case 'compras':
                          $link = "index.php?page=purchases&view=" . $refId;
                          break;

                        case 'usuarios':
                          if (((int)($_SESSION['user']['rol'] ?? 0) === 1) && (($n['modulo'] ?? '') === 'Usuarios')) {
                            $link = "index.php?page=users";
                          } else {
                            $link = "index.php?page=profile";
                          }
                          break;

                        default:
                          if (($n['modulo'] ?? '') === 'Perfil') {
                            $link = "index.php?page=profile";
                          } elseif (($n['modulo'] ?? '') === 'Agenda') {
                            $link = "index.php?page=agenda";
                          } elseif (($n['modulo'] ?? '') === 'Ventas') {
                            $link = "index.php?page=sales";
                          } elseif (($n['modulo'] ?? '') === 'Compras') {
                            $link = "index.php?page=purchases";
                          } elseif (($n['modulo'] ?? '') === 'Productos') {
                            $link = "index.php?page=products";
                          }
                          break;
                      }
                    ?>

                    <div class="notif-item d-flex justify-content-between align-items-start px-2 py-2 border-bottom">
                      <a href="<?= htmlspecialchars($link) ?>" class="text-decoration-none flex-grow-1">
                        <div class="fw-semibold" style="font-size:.92rem;">
                          <?= htmlspecialchars($n['titulo']) ?>
                        </div>

                        <div class="small text-muted">
                          <?= htmlspecialchars($n['mensaje']) ?>
                        </div>

                        <div class="small text-secondary mt-1">
                          <?= htmlspecialchars($n['modulo']) ?> · <?= htmlspecialchars($n['created_at']) ?>
                        </div>
                      </a>

                      <button
                        class="btn btn-sm btn-light notif-delete ms-2"
                        type="button"
                        data-id="<?= (int)$n['id_notificacion'] ?>">
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  <?php endforeach; ?>

                <?php endif; ?>
              </div>
            </div>

            <span class="pill d-none d-lg-inline-flex">
              <i class="bi bi-shield-lock"></i>
              <?= ((int)($_SESSION['user']['rol'] ?? 0) === 1) ? 'Admin' : 'Logística' ?>
            </span>

            <span class="pill d-none d-lg-inline-flex" id="clock">
              <i class="bi bi-clock"></i> --
            </span>

            <div class="dropdown">
              <button class="user-dd dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="user-dd-avatar"
                  src="<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'assets/img/avatars/avatar1.jpg') ?>"
                  alt="Avatar">
                <div class="d-none d-sm-block text-start">
                  <div class="fw-bold" style="font-size:.92rem; line-height:1;">
                    <?= htmlspecialchars($_SESSION['user']['nombre'] ?? 'Admin') ?>
                  </div>
                  <small class="text-muted" style="font-size:.78rem;">Sesión activa</small>
                </div>
              </button>

              <ul class="dropdown-menu dropdown-menu-end shadow-sm user-dd-menu">
                <li>
                  <a class="dropdown-item" href="index.php?page=profile">
                    <i class="bi bi-person-gear me-2"></i> Configuración de perfil
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" href="index.php?page=logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Salir
                  </a>
                </li>
              </ul>
            </div>

          </div>
        </header>

        <main class="pt-4">
  <?php endif; ?>
