<?php
$err = $viewData['error'] ?? '';
$ok  = $viewData['success'] ?? '';
?>
<div class="auth-wrap">
  <div class="card auth-card">
    <div class="row g-0">
      <div class="col-lg-5 auth-side">
        <span class="badge rounded-pill">Setup</span>
        <h2 class="mt-3 fw-bold">Crear cuenta admin</h2>
        <p class="mt-3 text-white-50">Usá esto solo para la dueña o usuarios autorizados.</p>
      </div>
      <div class="col-lg-7 bg-white auth-form">
        <h3 class="fw-bold mb-1">Registro</h3>
        <p class="text-muted mb-4">Creá tu usuario para administrar el sistema.</p>

        <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
        <?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

        <form method="post" action="index.php?page=register">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="nombre" class="form-control" placeholder="Dueña" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="email" class="form-control" placeholder="admin@aja.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="mínimo 8 caracteres" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password2" class="form-control" required>
          </div>
          <button class="btn btn-brand w-100 text-white">Crear cuenta ✨</button>
          <div class="text-center mt-3">
            <a class="text-decoration-none" href="index.php?page=login">Volver a login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
