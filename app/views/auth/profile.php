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

<div class="container">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0 fw-bold">Configuración</h3>
      <small class="text-muted">Cuenta · Avatar · Seguridad</small>
    </div>
    <a href="index.php?page=dashboard" class="btn btn-outline-dark btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
  </div>

  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <div class="row g-4">
    <!-- Preview -->
    <div class="col-lg-4">
      <div class="cardx">
        <div class="hd">
          <div class="fw-bold">Vista previa</div>
          <small>Así te verás en el panel</small>
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

    <!-- Tabs -->
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

            <!-- CUENTA -->
            <div class="tab-pane fade show active" id="tab-cuenta" role="tabpanel">
              <form method="post" action="index.php?page=profile">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input class="form-control" name="telefono" value="<?= htmlspecialchars($telefono) ?>" placeholder="Opcional">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Bio</label>
                    <input class="form-control" name="bio" maxlength="140" value="<?= htmlspecialchars($bio) ?>" placeholder="Opcional">
                  </div>
                </div>

                <!-- Mantener id_avatar actual (para no perderlo al guardar cuenta) -->
                <input type="hidden" name="id_avatar" value="<?= (int)$idAvatar ?>">

                <button class="btn btn-brand w-100 mt-3">Guardar cambios</button>
              </form>
            </div>

            <!-- AVATAR -->
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
                  <label class="form-label">O subí tu foto (opcional)</label>
                  <input class="form-control" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
                  <small class="text-muted">Si subís foto, se usará esa en vez del avatar.</small>
                </div>

                <button class="btn btn-brand w-100 mt-3">Guardar avatar</button>
              </form>
            </div>

            <!-- SEGURIDAD (placeholder pro) -->
            <div class="tab-pane fade" id="tab-seguridad" role="tabpanel">
              <div class="alert alert-warning mb-0">
                <b>Seguridad</b>: aquí conectamos el cambio de contraseña (te lo dejo listo cuando me confirmés).
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>