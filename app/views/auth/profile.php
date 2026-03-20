<?php
$perfil  = $viewData['perfil'] ?? [];
$avatars = $viewData['avatars'] ?? [];
$err = $viewData['error'] ?? '';
$ok  = $viewData['success'] ?? '';

$fotoArchivo   = $perfil['foto_archivo'] ?? '';
$avatarArchivo = $perfil['avatar_archivo'] ?? 'assets/img/avatars/avatar1.jpg';
$img = ($fotoArchivo !== '') ? $fotoArchivo : $avatarArchivo;

$telefono = $perfil['telefono'] ?? '';
$bio = $perfil['bio'] ?? '';
$idAvatar = (int)($perfil['id_avatar'] ?? 0);
?>

<div class="container profile-page page-fade">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0 fw-bold text-white">Configuración</h3>
    </div>
    <a href="index.php?page=dashboard" class="btn btn-soft-back btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
  </div>

  <div class="row g-4">
 
    <div class="col-lg-4">
      <div class="cardx mb-4 module-hero">
        <div class="hd">
          <div class="fw-bold">Vista previa</div>
        </div>
        <div class="bd text-center">
          <img src="<?= htmlspecialchars($img) ?>" class="profile-pic mb-3" alt="Perfil">
          <div class="fw-bold"><?= htmlspecialchars($_SESSION['user']['nombre'] ?? 'Admin') ?></div>
          <small class="text-muted"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></small>

          <div class="mt-3 d-flex justify-content-center gap-2">
            <span class="pill"><i class="bi bi-person-check"></i> Activo</span>
            <span class="pill"><i class="bi bi-shield-lock"></i> Admin</span>
          </div>
        </div>
      </div>
    </div>


    <div class="col-lg-8">
      <div class="cardx">
        <div class="hd">
          <ul class="nav nav-pills gap-2 profile-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-cuenta" type="button" role="tab">
                <i class="bi bi-person-gear me-1"></i> Cuenta
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-avatar" type="button" role="tab">
                <i class="bi bi-stars me-1"></i> Avatar
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-seguridad" type="button" role="tab">
                <i class="bi bi-shield-lock me-1"></i> Seguridad
              </button>
            </li>
          </ul>
        </div>

        <div class="bd">
          <div class="tab-content">

            <div class="tab-pane fade show active" id="tab-cuenta" role="tabpanel">
              <form method="post" action="index.php?page=profile">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input class="form-control" name="telefono" value="<?= htmlspecialchars($telefono) ?>" placeholder="Opcional">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Biografía</label>
                    <input class="form-control" name="bio" maxlength="140" value="<?= htmlspecialchars($bio) ?>" placeholder="Opcional">
                  </div>
                </div>

         
                <input type="hidden" name="id_avatar" value="<?= (int)$idAvatar ?>">

                <button class="btn btn-brand w-100 mt-3">Guardar cambios</button>
              </form>
            </div>

          
            <div class="tab-pane fade" id="tab-avatar" role="tabpanel">
              <form method="post" action="index.php?page=profile" enctype="multipart/form-data">

                <label class="form-label">Elegí un avatar</label>
                <div class="avatar-grid">
                  <?php if (!$avatars): ?>
                    <div class="text-muted">No hay avatares cargados.</div>
                  <?php else: ?>
                    <?php foreach ($avatars as $a): ?>
                      <?php $active = ((int)$idAvatar === (int)$a['id_avatar']); ?>
                      <label class="avatar-item <?= $active ? 'active' : '' ?>">
                        <input type="radio" name="id_avatar" value="<?= (int)$a['id_avatar'] ?>" <?= $active ? 'checked' : '' ?>>
                        <img src="<?= htmlspecialchars($a['archivo']) ?>" alt="<?= htmlspecialchars($a['codigo'] ?? 'avatar') ?>">
                        <span class="check"><i class="bi bi-check2"></i></span>
                      </label>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <div class="mt-3">
                  <label class="form-label">Cargar imagen</label>
                  <input class="form-control" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <button class="btn btn-brand w-100 mt-3">Guardar avatar</button>
              </form>
            </div>

            
<div class="tab-pane fade" id="tab-seguridad" role="tabpanel">


  <form method="post" action="index.php?page=profile" class="mb-3">
    <input type="hidden" name="action" value="change_email">

    <div class="cardx" style="border-radius:18px;">
      <div class="hd">
        <div class="fw-bold"><i class="bi bi-envelope me-1"></i> Cambiar correo</div>
        <small>Mantenga su cuenta actualizada.</small>
      </div>
      <div class="bd">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Correo actual</label>
            <input class="form-control" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" disabled>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nuevo correo</label>
            <input class="form-control" type="email" name="new_email" placeholder="nuevo@correo.com" required>
          </div>
        </div>

        <div class="mt-3">
          <button class="btn btn-brand w-100">Guardar nuevo correo</button>
        </div>
      </div>
    </div>
  </form>

 
  <form method="post" action="index.php?page=profile">
    <input type="hidden" name="action" value="change_password">

    <div class="cardx" style="border-radius:18px;">
      <div class="hd">
        <div class="fw-bold"><i class="bi bi-shield-lock me-1"></i> Cambiar contraseña</div>
        <small>Ingrese una contraseña nueva.</small>
      </div>
      <div class="bd">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nueva contraseña</label>
            <div class="input-group">
              <input class="form-control" type="password" id="pw_new" name="new_password" minlength="6" required>
              <button class="btn pw-eye" type="button" data-toggle-pass="#pw_new" aria-label="Mostrar contraseña">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Confirmar contraseña</label>
            <div class="input-group">
              <input class="form-control" type="password" id="pw_new2" name="new_password2" minlength="6" required>
              <button class="btn pw-eye" type="button" data-toggle-pass="#pw_new2" aria-label="Mostrar contraseña">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button class="btn btn-brand w-100">Guardar nueva contraseña</button>
          <small class="text-muted d-block mt-2">
            Use al menos 8 caracteres (letras y números).
          </small>
        </div>

      </div>
    </div>
  </form>

</div>

          </div>
        </div>
      </div>
    </div>

  </div>
  <script>
document.addEventListener('DOMContentLoaded', function () {
  const toast = document.querySelector('.app-toast');
  if (!toast) return;

  const closeBtn = toast.querySelector('.app-toast-close');

  const hideToast = () => {
    toast.classList.remove('is-visible');
    setTimeout(() => {
      if (toast) toast.remove();
    }, 250);
  };

  if (closeBtn) {
    closeBtn.addEventListener('click', hideToast);
  }

  setTimeout(hideToast, 3200);
});
</script>
</div>