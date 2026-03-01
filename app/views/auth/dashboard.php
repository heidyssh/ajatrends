<?php
if (session_status() === PHP_SESSION_NONE)
  session_start();
$nombre = $_SESSION['user']['nombre'] ?? 'Admin';
require_once __DIR__ . '/../../models/Agenda.php'; // si tu dashboard.php está en app/views/auth/
$idUser = (int) ($_SESSION['user']['id_usuario'] ?? 0);
$events = Agenda::upcoming($idUser, 8);
?>
<div class="dash-layout">
  <div class="dash-main">
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
      </div>
    </div>


    <!-- IZQUIERDA -->
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
  <a href="index.php?page=sales" class="quick-link">
    <div class="quick-card">
      <div class="ic"><i class="bi bi-receipt"></i></div>
      <div>
        <div class="fw-bold">Ventas</div>
        <small>Facturación · salidas · estadísticas</small>
      </div>
    </div>
  </a>
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

  <!-- DERECHA: AGENDA -->
  <aside class="dash-right">
    <div class="cardx agenda-card">
      <div class="hd d-flex align-items-center justify-content-between">
        <div>
          <div class="fw-bold">Agenda AJA</div>
          <small class="text-muted">Calendario · recordatorios</small>
        </div>
        <span class="badge bg-success">HOY</span>
      </div>

      <div class="bd">

        <!-- Calendario -->
        <div class="agenda-mini-cal mb-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold" id="calTitle"></div>
            <div class="d-flex gap-2">
              <button class="btn btn-light btn-sm" type="button" id="calPrev"><i
                  class="bi bi-chevron-left"></i></button>
              <button class="btn btn-light btn-sm" type="button" id="calNext"><i
                  class="bi bi-chevron-right"></i></button>
            </div>
          </div>
          <div class="agenda-cal-grid" id="calGrid"></div>
        </div>

        <!-- Próximos -->
        <div class="fw-bold mb-2"><i class="bi bi-list-check me-2"></i> Próximos</div>

        <div class="agenda-list">
          <?php if (!$events): ?>
            <div class="text-muted small">Sin eventos próximos.</div>
          <?php else: ?>
            <?php foreach ($events as $e): ?>
              <div class="agenda-item">
                <div class="dot"></div>
                <div class="content">
                  <div class="title"><?= htmlspecialchars($e['titulo']) ?></div>
                  <div class="meta">
                    <?= htmlspecialchars($e['fecha']) ?>
                    <?php if (!empty($e['hora'])): ?> ·
                      <?= htmlspecialchars(substr((string) $e['hora'], 0, 5)) ?>     <?php endif; ?>
                  </div>
                </div>
                <span class="pill"><?= htmlspecialchars($e['modulo']) ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="mt-3 d-grid gap-2">
          <a class="btn btn-brand btn-sm" href="index.php?page=agenda">
            <i class="bi bi-calendar-event me-1"></i> Ver agenda
          </a>
        </div>
      </div>
    </div>
  </aside>
</div>
<script>
  (function () {
    const title = document.getElementById('calTitle');
    const grid = document.getElementById('calGrid');
    const prev = document.getElementById('calPrev');
    const next = document.getElementById('calNext');
    if (!title || !grid || !prev || !next) return;

    const eventDates = new Set(<?= json_encode(array_values(array_unique(array_map(fn($e) => $e['fecha'], $events))), JSON_UNESCAPED_UNICODE) ?>);

    let cur = new Date(); cur.setDate(1);
    const dows = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do'];

    function render() {
      const y = cur.getFullYear();
      const m = cur.getMonth();

      title.textContent = cur.toLocaleString('es-HN', { month: 'long', year: 'numeric' });
      grid.innerHTML = '';

      dows.forEach(d => {
        const el = document.createElement('div');
        el.className = 'd dow';
        el.textContent = d;
        grid.appendChild(el);
      });

      const firstDay = new Date(y, m, 1);
      let start = firstDay.getDay();
      start = (start === 0) ? 6 : start - 1;

      const daysInMonth = new Date(y, m + 1, 0).getDate();
      const prevDays = new Date(y, m, 0).getDate();

      const today = new Date();
      const isCurMonth = today.getFullYear() === y && today.getMonth() === m;

      for (let i = 0; i < start; i++) {
        const el = document.createElement('div');
        el.className = 'd muted';

        const d = (prevDays - start + 1 + i);
        el.textContent = d;

        const pm = new Date(y, m, 0);
        const key = `${pm.getFullYear()}-${String(pm.getMonth() + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        el.dataset.date = key;

        grid.appendChild(el);
      }

      for (let d = 1; d <= daysInMonth; d++) {
        const el = document.createElement('div');
        const mm = String(m + 1).padStart(2, '0');
        const dd = String(d).padStart(2, '0');
        const key = `${y}-${mm}-${dd}`;

        let cls = 'd';
        if (isCurMonth && d === today.getDate()) cls += ' today';
        if (eventDates.has(key)) cls += ' has';

        el.className = cls;
        el.textContent = d;
        grid.appendChild(el);
        el.dataset.date = key;
      }
    }

    prev.addEventListener('click', () => { cur.setMonth(cur.getMonth() - 1); render(); });
    next.addEventListener('click', () => { cur.setMonth(cur.getMonth() + 1); render(); });

    render();
    grid.addEventListener('click', (ev) => {
      const cell = ev.target.closest('.d');
      if (!cell) return;
      if (cell.classList.contains('dow')) return;
      const dt = cell.dataset.date;
      if (!dt) return;

      window.location.href = `index.php?page=agenda&date=${encodeURIComponent(dt)}`;
    });
  })();
</script>