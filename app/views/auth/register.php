<?php
$err = $viewData['error'] ?? '';
$ok  = $viewData['success'] ?? '';
?>

<div class="auth-center">
  <div class="auth-panel">
    <div class="auth-head text-center">
      <img class="auth-logo" src="assets/img/logo.jpeg" alt="Logo">
      <h2 class="auth-title">Crear cuenta</h2>
      <p class="auth-sub">Solo usuarios autorizados</p>
    </div>

    <?php if ($err): ?><div class="alert alert-danger mb-3"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="alert alert-success mb-3"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

    <form method="post" action="index.php?page=register" class="auth-form2">
      <div class="field">
        <label class="form-label">Nombre</label>
        <div class="input-ic">
          <i class="bi bi-person"></i>
          <input name="nombre" class="form-control" placeholder="Nombre completo" required>
        </div>
      </div>

      <div class="field">
        <label class="form-label">Correo</label>
        <div class="input-ic">
          <i class="bi bi-envelope"></i>
          <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
        </div>
      </div>

      <div class="field">
        <label class="form-label">Contraseña</label>
        <div class="input-ic">
          <i class="bi bi-lock"></i>
          <input id="regPass1" type="password" name="password" class="form-control" placeholder="mínimo 8 caracteres" required>
          <button class="pw-toggle" type="button" data-toggle-pass="#regPass1" aria-label="Mostrar contraseña">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <div class="field">
        <label class="form-label">Confirmar contraseña</label>
        <div class="input-ic">
          <i class="bi bi-shield-lock"></i>
          <input id="regPass2" type="password" name="password2" class="form-control" placeholder="repetir contraseña" required>
          <button class="pw-toggle" type="button" data-toggle-pass="#regPass2" aria-label="Mostrar contraseña">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button class="btn btn-brand w-100 mt-2">Crear cuenta</button>

      <div class="text-center mt-3">
        <a class="link-soft" href="index.php?page=login">Volver</a>
      </div>
    </form>
  </div>
</div>
