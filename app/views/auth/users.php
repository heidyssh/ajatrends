<?php
$users = $viewData['users'] ?? [];
$totalUsers = count($users);
$activeUsers = count(array_filter($users, fn($u) => (int) $u['estado'] === 1));
?>

<div class="users-page">

  <div class="users-panel cardx users-card module-hero">
    <div class="hd users-card-head">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="users-section-title">Usuarios registrados</div>
          <small class="users-section-sub">Gestión de accesos del sistema AJA Trends.</small>
        </div>

        <div class="users-mini-stats">
          <div class="users-stat-chip">
            <span class="users-stat-label">Total</span>
            <strong id="usersTotalCount"><?= $totalUsers ?></strong>
          </div>
          <div class="users-stat-chip">
            <span class="users-stat-label">Activos</span>
            <strong><?= $activeUsers ?></strong>
          </div>
          <div class="users-stat-chip">
            <span class="users-stat-label">Visibles</span>
            <strong id="usersVisibleCount"><?= $totalUsers ?></strong>
          </div>
        </div>
      </div>
    </div>

    <div class="bd">

      <div class="users-toolbar">
        <div class="users-toolbar-search">
          <i class="bi bi-search"></i>
          <input type="text" id="usersSearch" class="form-control"
            placeholder="Buscar por ID, nombre, correo, rol o estado..." />
        </div>

        <div class="users-toolbar-select">
          <select id="usersRoleFilter" class="form-select">
            <option value="">Rol: Todos</option>
            <option value="ADMIN">ADMIN</option>
            <option value="LOGISTICA">LOGISTICA</option>
          </select>
        </div>

        <div class="users-toolbar-select">
          <select id="usersStatusFilter" class="form-select">
            <option value="">Estado: Todos</option>
            <option value="ACTIVO">Activo</option>
            <option value="INACTIVO">Inactivo</option>
          </select>
        </div>

        <div class="users-toolbar-actions">
          <button type="button" id="usersApplyFilters" class="btn btn-soft-brand btn-sm users-toolbar-btn">
            <i class="bi bi-funnel me-1"></i> Filtrar
          </button>

          <button type="button" id="usersClearFilters" class="btn btn-soft-back btn-sm users-toolbar-btn">
            <i class="bi bi-x-lg me-1"></i> Limpiar
          </button>
        </div>
      </div>

      <?php if (!$users): ?>
        <div class="users-empty">
          <div class="users-empty-icon"><i class="bi bi-people"></i></div>
          <div class="fw-semibold mb-1">No hay usuarios registrados</div>
          <div class="text-muted small">Cuando agregues usuarios, aparecerán aquí.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive users-table-wrap">
          <table class="table users-table align-middle mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Creado</th>
                <th class="text-end">Acción</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <?php foreach ($users as $u): ?>
                <?php
                $activo = (int) $u['estado'] === 1;
                $rol = strtoupper((string) $u['rol_nombre']);
                $rolClass = $rol === 'ADMIN' ? 'is-admin' : 'is-logistica';
                $estadoTexto = $activo ? 'ACTIVO' : 'INACTIVO';
                ?>
                <tr class="user-row" data-id="<?= (int) $u['id_usuario'] ?>"
                  data-name="<?= htmlspecialchars(mb_strtoupper(trim((string) $u['nombre'])), ENT_QUOTES, 'UTF-8') ?>"
                  data-email="<?= htmlspecialchars(mb_strtoupper(trim((string) $u['email'])), ENT_QUOTES, 'UTF-8') ?>"
                  data-role="<?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?>" data-status="<?= $estadoTexto ?>">
                  <td>
                    <span class="users-id-badge">#<?= (int) $u['id_usuario'] ?></span>
                  </td>

                  <td>
                    <div class="users-usercell">
                      <div class="users-avatar">
                        <?= strtoupper(mb_substr(trim((string) $u['nombre']), 0, 1)) ?>
                      </div>
                      <div>
                        <div class="users-name"><?= htmlspecialchars($u['nombre']) ?></div>
                        <div class="users-meta small">Acceso al sistema</div>
                      </div>
                    </div>
                  </td>

                  <td>
                    <div class="users-email"><?= htmlspecialchars($u['email']) ?></div>
                  </td>

                  <td>
                    <span class="users-pill <?= $rolClass ?>">
                      <i class="bi <?= $rol === 'ADMIN' ? 'bi-shield-lock' : 'bi-box-seam' ?>"></i>
                      <?= htmlspecialchars($u['rol_nombre']) ?>
                    </span>
                  </td>

                  <td>
                    <span class="users-pill <?= $activo ? 'is-active' : 'is-inactive' ?>">
                      <i class="bi <?= $activo ? 'bi-check-circle' : 'bi-dash-circle' ?>"></i>
                      <?= $activo ? 'Activo' : 'Inactivo' ?>
                    </span>
                  </td>

                  <td>
                    <div class="users-date"><?= htmlspecialchars($u['creado_en']) ?></div>
                  </td>

                  <td class="text-end">
                    <?php if ($activo): ?>
                      <form method="post" action="index.php?page=users" class="d-inline-block">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                        <input type="hidden" name="estado" value="0">
                        <button class="btn users-btn-delete btn-sm rounded-pill px-3" type="submit">
                          <i class="bi bi-person-x me-1"></i> Desactivar
                        </button>
                      </form>
                    <?php else: ?>
                      <form method="post" action="index.php?page=users" class="d-inline-block">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                        <input type="hidden" name="estado" value="1">
                        <button class="btn btn-sm rounded-pill px-3 users-btn-activate" type="submit">
                          <i class="bi bi-person-check me-1"></i> Activar
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div id="usersNoResults" class="users-no-results mt-3" hidden>
          <div class="users-empty-icon"><i class="bi bi-search"></i></div>
          <div class="fw-semibold mb-1">No se encontraron usuarios</div>
          <div class="text-muted small">Prueba con otro nombre, correo, rol o estado.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('usersSearch');
    const roleFilter = document.getElementById('usersRoleFilter');
    const statusFilter = document.getElementById('usersStatusFilter');
    const applyBtn = document.getElementById('usersApplyFilters');
    const clearBtn = document.getElementById('usersClearFilters');
    const rows = Array.from(document.querySelectorAll('.user-row'));
    const visibleCount = document.getElementById('usersVisibleCount');
    const noResults = document.getElementById('usersNoResults');
    const tableWrap = document.querySelector('.users-table-wrap');

    if (!searchInput || !roleFilter || !statusFilter || !applyBtn || !clearBtn || !rows.length) {
      return;
    }

    function normalize(value) {
      return (value || '')
        .toString()
        .trim()
        .toUpperCase();
    }

    function applyFilters() {
      const q = normalize(searchInput.value);
      const role = normalize(roleFilter.value);
      const status = normalize(statusFilter.value);

      let visible = 0;

      rows.forEach(function (row) {
        const id = normalize(row.dataset.id);
        const name = normalize(row.dataset.name);
        const email = normalize(row.dataset.email);
        const rowRole = normalize(row.dataset.role);
        const rowStatus = normalize(row.dataset.status);

        const textMatch =
          !q ||
          id.includes(q) ||
          name.includes(q) ||
          email.includes(q) ||
          rowRole.includes(q) ||
          rowStatus.includes(q);

        const roleMatch = !role || rowRole === role;
        const statusMatch = !status || rowStatus === status;

        const show = textMatch && roleMatch && statusMatch;

        row.style.display = show ? '' : 'none';

        if (show) visible++;
      });

      if (visibleCount) {
        visibleCount.textContent = visible;
      }

      if (tableWrap && noResults) {
        const hasResults = visible > 0;
        tableWrap.hidden = !hasResults;
        noResults.hidden = hasResults;
      }
    }

    function clearFilters() {
      searchInput.value = '';
      roleFilter.value = '';
      statusFilter.value = '';
      applyFilters();
      searchInput.focus();
    }

    applyBtn.addEventListener('click', applyFilters);
    clearBtn.addEventListener('click', clearFilters);

    searchInput.addEventListener('input', applyFilters);
    roleFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        applyFilters();
      }
    });
  });
</script>