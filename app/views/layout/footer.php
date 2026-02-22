<?php
$page = $_GET['page'] ?? 'login';
$isAuthPage = in_array($page, ['login','register'], true);
$isLogged = isset($_SESSION['user']);
?>
<?php if ($isAuthPage || !$isLogged): ?>
  </main>
<?php else: ?>
      </main>
    </div>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('[data-toggle-pw]');
  if(!btn) return;

  const sel = btn.getAttribute('data-toggle-pw');
  const inp = document.querySelector(sel);
  if(!inp) return;

  const isPw = inp.type === 'password';
  inp.type = isPw ? 'text' : 'password';

  const icon = btn.querySelector('i');
  if(icon){
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
  }
});
document.addEventListener('change', function(e){
  const r = e.target;
  if(!r.matches('.avatar-item input[type="radio"][name="id_avatar"]')) return;

  document.querySelectorAll('.avatar-item').forEach(el => el.classList.remove('active'));
  const label = r.closest('.avatar-item');
  if(label) label.classList.add('active');
});
</script>
<!-- Contenedor global de toasts -->
<div class="toast-stack" id="toastStack"></div>

<?php
$fs = $_SESSION['flash_success'] ?? null;
$fe = $_SESSION['flash_error'] ?? null;
$fw = $_SESSION['flash_warn'] ?? null;
$fi = $_SESSION['flash_info'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_warn'], $_SESSION['flash_info']);
?>

<script>
window.showToast = function(type, message, ms = 3500){
  const stack = document.getElementById('toastStack');
  if (!stack) return;

  const icons = { success:'bi-check2', error:'bi-x-lg', warn:'bi-exclamation-triangle', info:'bi-info-lg' };

  const el = document.createElement('div');
  el.className = `toastx ${type}`;
  el.innerHTML = `
    <div class="ic"><i class="bi ${icons[type] || icons.info}"></i></div>
    <div class="msg"></div>
    <button class="close" type="button">×</button>
  `;
  el.querySelector('.msg').textContent = String(message || '');
  stack.appendChild(el);

  const kill = () => el.remove();
  el.querySelector('.close').addEventListener('click', kill);
  setTimeout(kill, ms);
};

document.addEventListener('DOMContentLoaded', () => {
  <?php if ($fs): ?> showToast('success', <?= json_encode($fs) ?>); <?php endif; ?>
  <?php if ($fe): ?> showToast('error',   <?= json_encode($fe) ?>); <?php endif; ?>
  <?php if ($fw): ?> showToast('warn',    <?= json_encode($fw) ?>); <?php endif; ?>
  <?php if ($fi): ?> showToast('info',    <?= json_encode($fi) ?>); <?php endif; ?>
});
</script>
</body>
</html>
