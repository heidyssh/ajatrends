<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nombre = $_SESSION['user']['nombre'] ?? 'Admin';
?>

<div class="cardx mb-4">
  <div class="hd d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="fw-bold" style="font-size:1.15rem;">Bienvenida, <?= htmlspecialchars($nombre) ?> ✨</div>
      <small>Dashboard administrativo · Inventario · Compras · Ventas · Reportes</small>
    </div>

    <div class="d-flex align-items-center gap-2">
      <span class="badge badge-soft">Paleta AJA</span>
      <span class="badge badge-soft">Admin-only</span>
    </div>
  </div>

  <div class="bd">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="kpi">
          <div class="t">Ventas del día</div>
          <div class="v">L 0.00</div>
          <small>Hoy</small>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="t">Stock bajo</div>
          <div class="v">0</div>
          <small>Productos por reponer</small>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="t">Ganancia estimada</div>
          <div class="v">L 0.00</div>
          <small>Margen</small>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-2">
      <div class="col-lg-8">
        <div class="cardx">
          <div class="hd">
            <div class="fw-bold">Accesos rápidos</div>
            <small>Listos para cuando agregués módulos</small>
          </div>
          <div class="bd">
            <div class="row g-3">
              <div class="col-md-6">
                <a href="index.php?page=products" class="quick-link">
                  <div class="quick-card">
                    <div class="ic"><i class="bi bi-bag-heart"></i></div>
                    <div>
                      <div class="fw-bold">Productos</div>
                      <small>Catálogo · precios · variantes</small>
                    </div>
                  </div>
                </a>
              </div>

             <div class="col-md-6">
  <a href="index.php?page=purchases" class="quick-link">
    <div class="quick-card">
      <div class="ic"><i class="bi bi-truck"></i></div>
      <div>
        <div class="fw-bold">Compras</div>
        <small>Pedidos · stock automático</small>
      </div>
    </div>
  </a>
</div>

              <div class="col-md-6">
                <div class="quick-card">
                  <div class="ic"><i class="bi bi-receipt"></i></div>
                  <div>
                    <div class="fw-bold">Ventas</div>
                    <small>Factura · pagos</small>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="quick-card">
                  <div class="ic"><i class="bi bi-box-seam"></i></div>
                  <div>
                    <div class="fw-bold">Kardex</div>
                    <small>Movimientos inventario</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-3">
              <small class="text-muted">Tip: luego conectamos estos KPIs a tu BD (ventas, kardex, stock_min).</small>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
