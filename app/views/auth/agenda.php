<?php
// $viewData viene de AgendaController
$selectedDate = $viewData['selectedDate'] ?? date('Y-m-d');
$dayEvents = $viewData['dayEvents'] ?? [];

function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>
<div class="page-fade agenda-page">

  <div class="cardx mb-4">
    <div class="hd d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div class="fw-bold" style="font-size:1.15rem;">Agenda · AJA 🗓️</div>
        <small class="text-muted">Fecha seleccionada: <b><?= h($selectedDate) ?></b></small>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-light" href="index.php?page=dashboard">Volver</a>
        <a class="btn btn-primary" href="index.php?page=agenda">Hoy</a>
        <button class="btn btn-soft-success rounded-pill px-4 fw-semibold shadow-sm" type="button"
          data-bs-toggle="modal" data-bs-target="#modalEventCreate">
          + Nuevo evento
        </button>
      </div>
    </div>

    <div class="bd">

      <?php if (empty($dayEvents)): ?>
        <div class="alert alert-light border mb-0">
          No hay eventos para esta fecha.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th style="width:120px;">Hora</th>
                <th>Título</th>
                <th style="width:180px;">Módulo</th>
                <th style="width:140px;">Estado</th>
                <th class="text-end" style="width:220px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dayEvents as $e): ?>
                <tr>
                  <td><?= h($e['hora'] ?? '') ?></td>
                  <td>
                    <div class="fw-semibold"><?= h($e['titulo'] ?? '') ?></div>
                    <?php if (!empty($e['descripcion'])): ?>
                      <div class="text-muted small"><?= h($e['descripcion']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><?= h($e['modulo'] ?? '') ?></td>
                  <td>
                    <?php if (($e['estado'] ?? '') === 'HECHO'): ?>
                      <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background:#e6f4ef; color:#1f6f5c;">
                        ✓ HECHO
                      </span>
                    <?php else: ?>
                      <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background:#f1f3f5; color:#495057;">
                        PENDIENTE
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <!-- Hecho -->
                    <form method="post" action="index.php?page=agenda&date=<?= h($selectedDate) ?>" class="d-inline">
                      <input type="hidden" name="action" value="done">
                      <input type="hidden" name="fecha" value="<?= h($selectedDate) ?>">
                      <input type="hidden" name="id_evento" value="<?= (int) ($e['id_evento'] ?? 0) ?>">
                      <button type="submit" class="btn btn-aja-success btn-sm rounded-pill px-3">✓ Hecho</button>
                    </form>

                    <!-- Editar -->
                    <button type="button" class="btn btn-aja-outline btn-sm rounded-pill px-3" data-bs-toggle="modal"
                      data-bs-target="#modalEventEdit<?= (int) $e['id_evento'] ?>">
                      Editar
                    </button>

                    <!-- Eliminar (abre modal confirmación) -->
                    <button type="button" class="btn btn-aja-danger btn-sm rounded-pill px-3" data-bs-toggle="modal"
                      data-bs-target="#modalEventDelete<?= (int) $e['id_evento'] ?>">
                      Eliminar
                    </button>
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
<!-- MODAL CREAR EVENTO -->
<div class="modal fade" id="modalEventCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <div>
          <div class="fw-bold">Nuevo evento</div>
          <small class="text-muted">Fecha: <?= h($selectedDate) ?></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="post" action="index.php?page=agenda&date=<?= h($selectedDate) ?>">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="fecha" value="<?= h($selectedDate) ?>">

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Título</label>
            <input class="form-control" name="titulo" required maxlength="120"
              placeholder="Ej: Pago proveedor / Pedido / Reunión...">
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Hora</label>
              <input class="form-control" name="hora" type="time">
            </div>

            <div class="col-md-6">
              <label class="form-label">Módulo</label>
              <select class="form-select" name="modulo">
                <option>General</option>
                <option>Compras</option>
                <option>Ventas</option>
                <option>Inventario</option>
                <option>Kardex</option>
                <option>Pagos</option>
              </select>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="2" maxlength="255"
              placeholder="Detalles (opcional)"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php foreach ($dayEvents as $e): ?>
  <!-- MODAL EDITAR EVENTO -->
  <div class="modal fade" id="modalEventEdit<?= (int) $e['id_evento'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div class="fw-bold">Editar evento</div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form method="post" action="index.php?page=agenda&date=<?= h($selectedDate) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="fecha" value="<?= h($selectedDate) ?>">
          <input type="hidden" name="id_evento" value="<?= (int) ($e['id_evento'] ?? 0) ?>">

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Título</label>
              <input class="form-control" name="titulo" required maxlength="120" value="<?= h($e['titulo'] ?? '') ?>">
            </div>

            <?php
            $hhmm = '';
            if (!empty($e['hora']))
              $hhmm = substr((string) $e['hora'], 0, 5);
            $mod = (string) ($e['modulo'] ?? 'General');
            ?>

            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">Hora</label>
                <input class="form-control" name="hora" type="time" value="<?= h($hhmm) ?>">
              </div>

              <div class="col-md-6">
                <label class="form-label">Módulo</label>
                <select class="form-select" name="modulo">
                  <?php foreach (['General', 'Compras', 'Ventas', 'Inventario', 'Kardex', 'Pagos'] as $m): ?>
                    <option value="<?= h($m) ?>" <?= $m === $mod ? 'selected' : '' ?>><?= h($m) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" name="descripcion" rows="2"
                maxlength="255"><?= h($e['descripcion'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- MODAL ELIMINAR EVENTO -->
  <div class="modal fade" id="modalEventDelete<?= (int) $e['id_evento'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <div class="fw-bold">Eliminar evento</div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          ¿Seguro que quieres eliminar este evento?
          <div class="mt-2 small text-muted"><b><?= h($e['titulo'] ?? '') ?></b></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>

          <form method="post" action="index.php?page=agenda&date=<?= h($selectedDate) ?>" class="d-inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="fecha" value="<?= h($selectedDate) ?>">
            <input type="hidden" name="id_evento" value="<?= (int) ($e['id_evento'] ?? 0) ?>">
            <button type="submit" class="btn btn-primary">Eliminar</button>
          </form>
        </div>

      </div>
    </div>
  </div>
<?php endforeach; ?>