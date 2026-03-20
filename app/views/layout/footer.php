<?php
$page = $_GET['page'] ?? 'login';
$isAuthPage = in_array($page, ['login', 'register'], true);
$isLogged = isset($_SESSION['user']);
?>
<?php if ($isAuthPage || !$isLogged): ?>
  </main>
<?php else: ?>
  <footer class="app-footer">
    <div class="app-footer-inner d-flex align-items-center justify-content-center flex-wrap gap-2">
      <span>© <?= date('Y') ?> AJA Trends. Todos los derechos reservados.</span>
      <span class="app-footer-dot">•</span>
      <span>Sistema de inventario, ventas y gestión administrativa</span>
    </div>
  </footer>
  </main>
  </div>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-toggle-pw]');
    if (!btn) return;

    const sel = btn.getAttribute('data-toggle-pw');
    const inp = document.querySelector(sel);
    if (!inp) return;

    const isPw = inp.type === 'password';
    inp.type = isPw ? 'text' : 'password';

    const icon = btn.querySelector('i');
    if (icon) {
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    }
  });

  document.addEventListener('change', function (e) {
    const r = e.target;
    if (!r.matches('.avatar-item input[type="radio"][name="id_avatar"]')) return;

    document.querySelectorAll('.avatar-item').forEach(el => el.classList.remove('active'));
    const label = r.closest('.avatar-item');
    if (label) label.classList.add('active');
  });
</script>

<div class="toast-stack" id="toastStack"></div>

<?php
$fs = $_SESSION['flash_success'] ?? null;
$fe = $_SESSION['flash_error'] ?? null;
$fw = $_SESSION['flash_warn'] ?? null;
$fi = $_SESSION['flash_info'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_warn'], $_SESSION['flash_info']);
?>

<script>
  window.showToast = function (type, message, ms = 3500) {
    const stack = document.getElementById('toastStack');
    if (!stack) return;

    const icons = { success: 'bi-check2', error: 'bi-x-lg', warn: 'bi-exclamation-triangle', info: 'bi-info-lg' };

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
  <?php if ($fe): ?> showToast('error', <?= json_encode($fe) ?>); <?php endif; ?>
  <?php if ($fw): ?> showToast('warn', <?= json_encode($fw) ?>); <?php endif; ?>
  <?php if ($fi): ?> showToast('info', <?= json_encode($fi) ?>); <?php endif; ?>
  });
</script>

<script>
  document.addEventListener("click", async function (e) {
    const btnDelete = e.target.closest(".notif-delete");
    const btnClearAll = e.target.closest("#btnClearAllNotifications");

    if (btnDelete) {
      e.preventDefault();
      e.stopPropagation();

      const id = btnDelete.dataset.id;
      const item = btnDelete.closest(".notif-item");
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
          const clearBtn = document.getElementById("btnClearAllNotifications");

          if (remaining === 0 && dropdown) {
            if (clearBtn) clearBtn.remove();

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

      return;
    }

    if (btnClearAll) {
      e.preventDefault();
      e.stopPropagation();

      const dropdown = document.getElementById("notifDropdown");
      const badge = document.getElementById("notifBadge");

      try {
        const res = await fetch("index.php?page=clear_all_notifications", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: ""
        });

        const data = await res.json();

        if (data.ok) {
          if (dropdown) {
            dropdown.querySelectorAll(".notif-item").forEach(el => el.remove());
          }

          btnClearAll.remove();

          let emptyMsg = document.getElementById("notifEmpty");
          if (!emptyMsg && dropdown) {
            emptyMsg = document.createElement("div");
            emptyMsg.id = "notifEmpty";
            emptyMsg.className = "px-2 py-2 text-muted small";
            emptyMsg.textContent = "No hay notificaciones.";
            dropdown.appendChild(emptyMsg);
          }

          if (badge) {
            badge.textContent = "0";
            badge.classList.add("d-none");
          }

          showToast('success', 'Se limpiaron todas las notificaciones');
        } else {
          showToast('error', 'No se pudieron limpiar las notificaciones');
        }
      } catch (err) {
        showToast('error', 'Error al limpiar las notificaciones');
      }
    }
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('appConfirmModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const titleEl = document.getElementById('appConfirmTitle');
    const textEl = document.getElementById('appConfirmText');
    const okBtn = document.getElementById('appConfirmOk');

    let currentForm = null;

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.js-open-confirm');
      if (!btn) return;

      currentForm = btn.closest('.js-confirm-form');
      if (!currentForm) return;

      titleEl.textContent = btn.dataset.confirmTitle || 'Confirmar acción';
      textEl.textContent = btn.dataset.confirmText || '¿Deseás continuar?';
      okBtn.textContent = btn.dataset.confirmBtn || 'Aceptar';

      modal.show();
    });

    okBtn.addEventListener('click', () => {
  if (!currentForm) return;

  const submitBtn = currentForm.querySelector('button[type="submit"], input[type="submit"]');
  if (submitBtn) {
    submitBtn.click();
  } else {
    currentForm.submit();
  }
});

    modalEl.addEventListener('hidden.bs.modal', () => {
      currentForm = null;
    });
  });
</script>
<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content app-confirm-modal">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title" id="appConfirmTitle">Confirmar acción</h5>
        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body pt-0">
        <div class="app-confirm-icon mb-3">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <p class="mb-0 text-light-emphasis" id="appConfirmText">¿Deseás continuar?</p>
      </div>

      <div class="modal-footer border-0 pt-2">
        <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger btn-sm px-3" id="appConfirmOk">Aceptar</button>
      </div>
    </div>
  </div>
</div>

</body>

</html>