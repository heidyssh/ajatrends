// AJA TRENDS Admin Pro (UI only)
(function(){
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');

  if (toggle && sidebar){
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem(
        'aja_sidebar_collapsed',
        sidebar.classList.contains('collapsed') ? '1' : '0'
      );
    });

    const saved = localStorage.getItem('aja_sidebar_collapsed');
    if (saved === '1') sidebar.classList.add('collapsed');
  }
})();

// Show/Hide password
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
// Clock pill (topbar)
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

