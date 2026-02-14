<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AJA TRENDS | Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg border-bottom">
  <div class="container py-2">
  <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php?page=dashboard">
  <img src="assets/img/logo.jpeg" alt="AJA TRENDS" style="width:38px;height:38px;border-radius:12px;object-fit:cover;border:1px solid rgba(0,0,0,.08);">
  <div class="lh-1">
    <div class="d-flex align-items-center gap-2">
      <span>AJA TRENDS</span>
      <span class="text-muted fw-semibold" style="font-size:.9rem;">Admin</span>
    </div>
    <small class="text-muted" style="font-size:.78rem;">Inventario • Ventas • Reportes</small>
  </div>
</a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <?php if (isset($_SESSION['user'])): ?>
        <span class="text-muted small me-2">✨ <?= htmlspecialchars($_SESSION['user']['nombre']) ?></span>
        <a class="btn btn-outline-dark btn-sm rounded-pill px-3" href="index.php?page=logout">Salir</a>

      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container py-5">
