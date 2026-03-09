<?php
$page = $_GET['page'] ?? 'login';
$isAuthPage = in_array($page, ['login','register'], true);
$isLogged = isset($_SESSION['user']);
?>
<?php if ($isAuthPage || !$isLogged): ?>
  </main>
<?php else: ?>
        <footer class="app-footer">
          <div class="app-footer-inner">
            <span>© <?= date('Y') ?> AJA Trends. Todos los derechos reservados.</span>
            <span class="app-footer-dot">•</span>
            <span>Sistema de inventario y ventas</span>
          </div>
        </footer>
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
<script>
document.addEventListener("click", async function(e){
  const btn = e.target.closest(".notif-delete");
  if(!btn) return;

  e.preventDefault();
  e.stopPropagation();

  const id = btn.dataset.id;
  const item = btn.closest(".notif-item");
  const badge = document.getElementById("notifBadge");
  const dropdown = document.getElementById("notifDropdown");

  try {
    const res = await fetch("index.php?page=delete_notification", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "id=" + encodeURIComponent(id)
    });

    const data = await res.json();

    if (data.ok) {
      if (item) item.remove();

      if (badge) {
        let count = parseInt((badge.textContent || "0").trim(), 10);
        count = Math.max(0, count - 1);

        if (count <= 0) {
          badge.textContent = "0";
          badge.classList.add("d-none");
        } else {
          badge.textContent = String(count);
          badge.classList.remove("d-none");
        }
      }

      const remaining = dropdown ? dropdown.querySelectorAll(".notif-item").length : 0;
      let emptyMsg = document.getElementById("notifEmpty");

      if (remaining === 0 && dropdown) {
        if (!emptyMsg) {
          emptyMsg = document.createElement("div");
          emptyMsg.id = "notifEmpty";
          emptyMsg.className = "px-2 py-2 text-muted small";
          emptyMsg.textContent = "No hay notificaciones.";
          dropdown.appendChild(emptyMsg);
        }
      }

      showToast('success', 'Notificación eliminada');
    } else {
      showToast('error', 'No se pudo eliminar la notificación');
    }
  } catch (err) {
    showToast('error', 'Error al eliminar la notificación');
  }
});
</script>
</body>
</html>
