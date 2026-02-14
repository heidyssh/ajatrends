<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nombre = $_SESSION['user']['nombre'] ?? 'Admin';
?>

<style>
.hero-card{
  border:1px solid rgba(20,19,26,.10);
  border-radius:22px;
  box-shadow: 0 30px 80px rgba(20,19,26,.10);
  overflow:hidden;
  background:#fff;
}
.hero-left{
  padding:32px;
}
.hero-right{
  padding:32px;
  background:
    radial-gradient(600px 400px at 20% 20%, rgba(255,79,167,.35), transparent 60%),
    radial-gradient(700px 500px at 80% 30%, rgba(124,58,237,.30), transparent 55%),
    linear-gradient(135deg, #111018 0%, #1b1730 55%, #171627 100%);
  color:#fff;
}
.owner-img{
  width:150px;
  height:150px;
  border-radius:28px;
  object-fit:cover;
  border:3px solid rgba(255,255,255,.45);
  box-shadow: 0 18px 40px rgba(0,0,0,.35);
}
.kpi{
  border:1px solid rgba(20,19,26,.10);
  border-radius:18px;
  box-shadow: 0 20px 50px rgba(0,0,0,.06);
}
.quick{
  border:0;border-radius:18px;
  box-shadow: 0 20px 50px rgba(0,0,0,.06);
  transition: transform .15s ease, box-shadow .15s ease;
}
.quick:hover{ transform: translateY(-3px); box-shadow: 0 26px 70px rgba(0,0,0,.08); }
.pill{
  display:inline-flex; align-items:center; gap:8px;
  border:1px solid rgba(255,255,255,.20);
  background: rgba(255,255,255,.10);
  color:#fff; padding:10px 14px; border-radius:999px;
  font-size:.85rem;
}
</style>

<div class="hero-card mb-4">
  <div class="row g-0">
    <div class="col-lg-7 hero-left">
      <div class="d-flex align-items-center gap-3">
        <img src="assets/img/logo.jpeg" alt="Logo" style="width:56px;height:56px;border-radius:16px;object-fit:cover;border:1px solid rgba(0,0,0,.08);">
        <div>
          <h3 class="fw-bold mb-0">Dashboard</h3>
          <div class="text-muted">Control administrativo del inventario AJA TRENDS</div>
        </div>
      </div>

      <div class="mt-4">
        <h4 class="fw-bold mb-1">Hola, <?= htmlspecialchars($nombre) ?> ✨</h4>
        <p class="text-muted mb-0">
          Desde aquí gestionás productos, compras, ventas, kardex, facturación y reportes (solo administración).
        </p>
      </div>

      <div class="row g-3 mt-4">
        <div class="col-md-4">
          <div class="p-3 kpi">
            <div class="text-muted small">Ventas del día</div>
            <div class="fs-4 fw-bold">L 0.00</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 kpi">
            <div class="text-muted small">Stock bajo</div>
            <div class="fs-4 fw-bold">0</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 kpi">
            <div class="text-muted small">Ganancia estimada</div>
            <div class="fs-4 fw-bold">L 0.00</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5 hero-right d-flex flex-column justify-content-between">
      <div>
        <span class="pill">💗 Inventario • Ventas • Reportes</span>

        <div class="d-flex align-items-center gap-3 mt-4">
          <img class="owner-img" src="assets/img/dueña.jpeg" alt="Dueña AJA TRENDS">
          <div>
            <div class="text-white-50 small">Propietaria</div>
            <div class="fs-5 fw-bold">AJA TRENDS</div>
            <div class="text-white-50 small">Administración interna</div>
          </div>
        </div>

        <div class="mt-4 text-white-50">
          Tip: revisá “Stock bajo” para saber qué pedirle al proveedor.
        </div>
      </div>

      <div class="text-white-50 small">
        BD II · PHP · MySQL · Bootstrap 5
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-3">
    <a class="text-decoration-none text-dark" href="#">
      <div class="card quick text-center">
        <div class="card-body">
          <div class="fs-2 mb-2">🛍️</div>
          <h6 class="fw-bold mb-1">Productos</h6>
          <div class="text-muted small">Catálogo + precios + variantes</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-3">
    <a class="text-decoration-none text-dark" href="#">
      <div class="card quick text-center">
        <div class="card-body">
          <div class="fs-2 mb-2">🚚</div>
          <h6 class="fw-bold mb-1">Compras</h6>
          <div class="text-muted small">Órdenes a proveedor</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-3">
    <a class="text-decoration-none text-dark" href="#">
      <div class="card quick text-center">
        <div class="card-body">
          <div class="fs-2 mb-2">🧾</div>
          <h6 class="fw-bold mb-1">Ventas</h6>
          <div class="text-muted small">Facturas + pagos</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-3">
    <a class="text-decoration-none text-dark" href="#">
      <div class="card quick text-center">
        <div class="card-body">
          <div class="fs-2 mb-2">📦</div>
          <h6 class="fw-bold mb-1">Kardex</h6>
          <div class="text-muted small">Movimientos de inventario</div>
        </div>
      </div>
    </a>
  </div>
</div>
