
(function () {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  const backdrop = document.getElementById('sidebarBackdrop');
  if (!sidebar || !toggle) return;

  const isMobile = () => window.innerWidth < 992;

  function applyDesktopState() {
    if (isMobile()) {
      sidebar.classList.remove('collapsed');
      document.body.classList.remove('sidebar-open');
      return;
    }

    const saved = localStorage.getItem('aja_sidebar_collapsed');
    if (saved === '1') {
      sidebar.classList.add('collapsed');
    } else {
      sidebar.classList.remove('collapsed');
    }
  }

  toggle.addEventListener('click', () => {
    if (isMobile()) {
      document.body.classList.toggle('sidebar-open');
      return;
    }

    sidebar.classList.toggle('collapsed');
    localStorage.setItem(
      'aja_sidebar_collapsed',
      sidebar.classList.contains('collapsed') ? '1' : '0'
    );
  });

  if (backdrop) {
    backdrop.addEventListener('click', () => {
      document.body.classList.remove('sidebar-open');
    });
  }

  window.addEventListener('resize', applyDesktopState);
  applyDesktopState();
})();


document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-toggle-pass]');
  if (!btn) return;

  const sel = btn.getAttribute('data-toggle-pass');
  const input = document.querySelector(sel);
  if (!input) return;

  input.type = (input.type === 'password') ? 'text' : 'password';
  const icon = btn.querySelector('i');
  if (icon) icon.className = (input.type === 'password') ? 'bi bi-eye' : 'bi bi-eye-slash';
});

(function(){
  const el = document.getElementById('clock');
  if(!el) return;

  function tick(){
    const d = new Date();
    const t = d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    el.innerHTML = `<i class="bi bi-clock"></i> ${t}`;
  }
  tick();
  setInterval(tick, 1000 * 30);
})();

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.cardx, .kpi, .quick-card, .report-kpi, .report-panel, .report-table-card').forEach((el, i) => {
    el.style.animationDelay = `${i * 0.03}s`;
    el.classList.add('ui-rise');
  });
});