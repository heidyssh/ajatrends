<?php
$users = $viewData['users'] ?? [];
?>

<div class="container">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h3 class="mb-0 fw-bold text-white">Usuarios</h3>
      <small class="text-white-50">Solo visible para administrador</small>
    </div>
    <a href="index.php?page=dashboard" class="btn btn-soft-back btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
  </div>

  <div class="cardx">
    <div class="hd">
      <div class="fw-bold">Usuarios registrados</div>
      <small>Allison puede ver esto. Jaysie no.</small>
    </div>

    <div class="bd">
      <?php if (!$users): ?>
        <div class="text-muted">No hay usuarios registrados.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= (int)$u['id_usuario'] ?></td>
                  <td><?= htmlspecialchars($u['nombre']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= htmlspecialchars($u['rol_nombre']) ?></td>
                  <td><?= (int)$u['estado'] === 1 ? 'Activo' : 'Inactivo' ?></td>
                  <td><?= htmlspecialchars($u['creado_en']) ?></td>
                  <td>
                    <form method="post" action="index.php?page=users" onsubmit="return confirm('¿Eliminar este usuario?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                      <button class="btn btn-sm btn-danger rounded-pill px-3">Eliminar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>