<?php $err = $viewData['error'] ?? ''; ?>

<div class="auth-center">
  <div class="auth-panel">
    <div class="auth-head text-center">
      <!-- Opcional: solo el logo (sin texto) -->
      <img class="auth-logo" src="assets/img/logo.jpeg" alt="Logo">
      <h2 class="auth-title">Iniciar sesión</h2>
      <p class="auth-sub">Acceso administrativo</p>
    </div>

    <?php if ($err): ?>
      <div class="alert alert-danger mb-3"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=login" class="auth-form2">
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
          <input id="loginPass" type="password" name="password" class="form-control" placeholder="••••••••" required>
          <button class="pw-toggle" type="button" data-toggle-pass="#loginPass" aria-label="Mostrar contraseña">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button class="btn btn-brand w-100 mt-2">Entrar</button>

      <div class="text-center mt-3">
        <a class="link-soft" href="index.php?page=register">Crear cuenta</a>
      </div>
    </form>
  </div>
</div>
