<?php
$perfil = $viewData['perfil'] ?? [];
$avatars = $viewData['avatars'] ?? [];
$err = $viewData['error'] ?? '';
$ok  = $viewData['success'] ?? '';

$img = $perfil['foto_archivo'] !== '' ? $perfil['foto_archivo'] : ($perfil['avatar_archivo'] ?? 'assets/img/avatars/avatar1.jpg');
?>

<div class="container">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="cardx">
        <div class="hd"><div class="fw-bold">Mi perfil</div><small>Avatar o foto propia</small></div>
        <div class="bd text-center">
          <img src="<?= htmlspecialchars($img) ?>" class="owner-big mb-3" alt="Perfil">
          <div class="fw-bold"><?= htmlspecialchars($_SESSION['user']['nombre'] ?? 'Admin') ?></div>
          <small class="text-muted"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></small>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="cardx">
        <div class="hd d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold">Configuración</div>
            <small>Elegí un avatar o subí tu foto</small>
          </div>
          <a href="index.php?page=dashboard" class="btn btn-outline-dark btn-sm rounded-pill">Volver</a>
        </div>

        <div class="bd">
          <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
          <?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

          <form method="post" action="index.php?page=profile" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Teléfono</label>
              <input class="form-control" name="telefono" value="<?= htmlspecialchars($perfil['telefono'] ?? '') ?>" placeholder="Opcional">
            </div>

            <div class="mb-3">
              <label class="form-label">Bio</label>
              <input class="form-control" name="bio" maxlength="140" value="<?= htmlspecialchars($perfil['bio'] ?? '') ?>" placeholder="Opcional">
            </div>

            <div class="mb-3">
              <label class="form-label">Avatar</label>
              <div class="avatar-grid">
                <?php foreach ($avatars as $a): ?>
                  <?php $active = ((int)$perfil['id_avatar'] === (int)$a['id_avatar']); ?>
                  <label class="avatar-item <?= $active ? 'active' : '' ?>">
                    <input type="radio" name="id_avatar" value="<?= (int)$a['id_avatar'] ?>" <?= $active ? 'checked' : '' ?>>
                    <img src="<?= htmlspecialchars($a['archivo']) ?>" alt="<?= htmlspecialchars($a['codigo']) ?>">
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Subir foto (opcional)</label>
              <input class="form-control" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
              <small class="text-muted">Si subís foto, se usará esa en vez del avatar.</small>
            </div>

            <button class="btn btn-brand w-100">Guardar cambios</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>