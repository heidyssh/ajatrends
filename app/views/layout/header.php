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
    <a class="navbar-brand fw-bold" href="index.php?page=dashboard">
      <span class="brand-dot"></span>AJA TRENDS
      <span class="text-muted fw-semibold ms-1" style="font-size:.9rem;">Admin</span>
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
