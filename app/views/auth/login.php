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
      <?php $isPendingApproval = trim((string) $err) === 'Tu cuenta aún no ha sido aprobada por el administrador.'; ?>

      <?php if ($isPendingApproval): ?>
        <div class="login-status-card login-status-card--warning" id="loginStatusCard">
          <i class="bi bi-hourglass-split"></i>
          <span><?= htmlspecialchars($err) ?></span>
        </div>
      <?php else: ?>
        <div class="login-status-card login-status-card--error" id="loginStatusCard">
          <i class="bi bi-exclamation-circle"></i>
          <span><?= htmlspecialchars($err) ?></span>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['logout'])): ?>
      <div class="logout-card">
        <i class="bi bi-check-circle-fill"></i>
        <span>Sesión cerrada correctamente. ¡Hasta pronto!</span>
      </div>
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
<script>
  setTimeout(() => {
    const msg = document.querySelector(".logout-card");
    if (msg) {
      msg.style.opacity = "0";
      setTimeout(() => msg.remove(), 400);
    }
  }, 3500);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const msg = document.getElementById('loginStatusCard');
  if (!msg) return;

  setTimeout(() => {
    msg.classList.add('is-hiding');
    setTimeout(() => msg.remove(), 400);
  }, 3500);
});
</script>