<?php $err = $viewData['error'] ?? ''; ?>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="row g-0">
      <div class="col-lg-5 auth-side d-flex flex-column justify-content-between">
        <div style="position:relative; z-index:1;">
          <span class="badge-soft">💗 Beauty • Inventory • Control</span>

          <h2 class="mt-4 fw-bold" style="line-height:1.1;">
            Admin Panel<br>
            <span style="background: linear-gradient(135deg, #ff4fa7, #7c3aed);
                         -webkit-background-clip:text; background-clip:text;
                         color: transparent;">
              AJA TRENDS
            </span>
          </h2>

          <p class="mt-3 text-white-50">
            Gestión elegante de inventario, compras, ventas y reportes.
            Todo en un solo lugar ✨
          </p>

          <div class="mt-4 d-grid gap-3">
            <div class="kpi-chip">
              <div class="t">Inventario</div>
              <div class="v">Kardex + Stock Bajo</div>
            </div>
            <div class="kpi-chip">
              <div class="t">Ventas</div>
              <div class="v">Factura + Pagos</div>
            </div>
            <div class="kpi-chip">
              <div class="t">Reportes</div>
              <div class="v">Ganancias + Gráficas</div>
            </div>
          </div>
        </div>

        <p class="mb-0 text-white-50 small" style="position:relative; z-index:1;">
          BD II • PHP • MySQL • Bootstrap 5
        </p>
      </div>

      <div class="col-lg-7 bg-white auth-form">
        <h3 class="fw-bold mb-1">Iniciar sesión</h3>
        <p class="hint mb-4">Entrá con tu correo y contraseña para administrar el sistema.</p>

        <?php if ($err): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=login" class="mt-2">
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="email" class="form-control" placeholder="admin@ajatrends.com" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>

          <button class="btn btn-brand w-100 text-white">Entrar ✨</button>

          <div class="text-center mt-3">
            <a class="link-soft" href="index.php?page=register">Crear cuenta (solo dueña)</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
