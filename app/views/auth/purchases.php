<?php
$filters = $viewData['filters'] ?? [];
$purchases = $viewData['purchases'] ?? [];
$purchase = $viewData['purchase'] ?? null;
$items = $viewData['items'] ?? [];
$products = $viewData['products'] ?? [];
$proveedores = $viewData['proveedores'] ?? [];

function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$q = $filters['q'] ?? '';
$estado = $filters['estado'] ?? 'TODOS';
?>
<div class="products-page page-fade purchases-page">

  <div class="cardx mb-4">
    <div class="hd purchases-toolbar">
      <div class="toolbar-left">
        <div class="fw-bold title">Compras · Inventario AJA</div>
        <div class="subtitle">Registrá compras ordenadas y actualizá el stock automáticamente.</div>
      </div>

      <div class="toolbar-right">
        <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
          <i class="bi bi-plus-lg me-1"></i> Registrar compra
        </button>
      </div>
    </div>

    <div class="bd">
      <form class="purchases-filters filters-glass" method="get" action="index.php">
        <input type="hidden" name="page" value="purchases">

        <div class="filter">
          <label class="form-label">Buscar</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input class="form-control form-control-sm" name="q" value="<?= h($q) ?>"
              placeholder="ID, nota, proveedor...">
          </div>
        </div>

        <div class="filter">
          <label class="form-label">Estado</label>
          <select class="form-select form-select-sm" name="estado">
            <?php foreach (['TODOS', 'REGISTRADA', 'ANULADA'] as $op): ?>
              <option value="<?= h($op) ?>" <?= $estado === $op ? 'selected' : '' ?>><?= h($op) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter actions">
          <div class="actions">
            <button class="btn btn-brand btn-sm">
              <i class="bi bi-funnel me-1"></i> Aplicar
            </button>
            <a class="btn btn-light btn-sm" href="index.php?page=purchases">
              <i class="bi bi-x-lg me-1"></i> Limpiar
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="cardx">
    <div class="hd d-flex align-items-center justify-content-between">
      <div>
        <div class="fw-bold">Compras</div>
        <div class="small text-muted">Vista limpia tipo sistema pro.</div>
      </div>
    </div>

    <div class="bd">
      <?php if (!$purchases): ?>
        <div class="text-center text-muted py-4">Sin registros.</div>
      <?php else: ?>
        <div class="sales-list">
          <?php foreach ($purchases as $c): ?>
            <?php
            $st = strtoupper(trim((string) ($c['estado'] ?? '')));
            $cls = 'status-pill';
            if ($st === 'ANULADA')
              $cls .= ' st-danger';
            else if ($st === 'REGISTRADA')
              $cls .= ' st-ok';
            else
              $cls .= ' st-neutral';
            ?>

            <div class="sale-row">
              <div class="sale-main">
                <div class="sale-top">
                  <div class="sale-id">#<?= (int) $c['id_compra'] ?></div>
                  <div class="sale-date"><?= h($c['fecha']) ?></div>
                </div>

                <div class="sale-mid">
                  <div class="sale-client">
                    <i class="bi bi-person me-1"></i><?= h($c['usuario'] ?? '') ?>
                  </div>
                  <div class="sale-note">
                    <strong><?= h($c['proveedor'] ?? 'Sin proveedor') ?></strong>
                    <?= !empty($c['nota']) ? (' · ' . h($c['nota'])) : '' ?>
                  </div>
                </div>
              </div>

              <div class="sale-metrics">
                <div class="metric">
                  <div class="lbl">Items</div>
                  <div class="val"><?= (int) $c['items'] ?></div>
                </div>
                <div class="metric">
                  <div class="lbl">Total</div>
                  <div class="val">L. <?= number_format((float) $c['total'], 2) ?></div>
                </div>
              </div>

              <div class="sale-status">
                <span class="<?= $cls ?>"><?= h($st) ?></span>
              </div>

              <div class="sale-actions text-end">
                <div class="dropdown">
                  <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Acciones
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <button class="dropdown-item" type="button" onclick="openViewCompra(<?= (int) $c['id_compra'] ?>)">
                        <i class="bi bi-eye me-2"></i> Ver
                      </button>
                    </li>

                    <?php if ($st !== 'ANULADA'): ?>
                      <li>
                        <button class="dropdown-item text-danger" type="button"
                          onclick="openDeleteCompra(<?= (int) $c['id_compra'] ?>)">
                          <i class="bi bi-trash me-2"></i> Eliminar
                        </button>
                      </li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- MODAL CREAR COMPRA -->
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content product-modal sale-modal">
      <div class="modal-header">
        <h5 class="modal-title">Registrar compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form method="post" action="index.php?page=purchases" id="frmCompra">
        <input type="hidden" name="action" value="create">
        <div class="modal-body">

          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <label class="form-label small text-muted">Proveedor</label>
              <select class="form-select" name="id_proveedor">
                <option value="">Seleccionar proveedor</option>
                <?php foreach (($proveedores ?? []) as $pr): ?>
                  <option value="<?= (int) $pr['id_proveedor'] ?>"><?= h($pr['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small text-muted">Nota / referencia</label>
              <input class="form-control" name="nota" placeholder="Ej: factura #..., detalle...">
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 align-items-end mb-2">
            <div style="min-width:260px; flex:1;">
              <label class="form-label small text-muted">Producto</label>
              <select class="form-select" id="selProducto">
                <option value="">-- Elegí un producto --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= (int) $p['id_producto'] ?>" data-precio="<?= h($p['precio']) ?>">
                    <?= h($p['nombre']) ?> (<?= h($p['sku']) ?>) · L. <?= number_format((float) $p['precio'], 2) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div style="width:140px;">
              <label class="form-label small text-muted">Cantidad</label>
              <input type="number" min="1" class="form-control" id="inpCantidad" value="1">
            </div>

            <div class="ms-auto">
              <button type="button" class="btn btn-light" id="btnAdd">
                <i class="bi bi-plus-lg me-1"></i> Agregar
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm align-middle purchases-items-table sale-table" id="tblItems">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th style="width:110px;" class="text-end">Cant.</th>
                  <th style="width:140px;" class="text-end">Precio</th>
                  <th style="width:140px;" class="text-end">Subtotal</th>
                  <th style="width:70px;"></th>
                </tr>
              </thead>
              <tbody>
                <tr class="text-muted" id="rowEmpty">
                  <td colspan="5" class="text-center py-3">Agregá productos a la compra.</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">TOTAL</th>
                  <th class="text-end" id="lblTotal">L. 0.00</th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="small text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Si algo falla, revisá que los productos tengan precio y que la compra tenga items.
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-brand" type="submit">
            <i class="bi bi-check2-circle me-1"></i> Guardar compra
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<!-- MODAL VER COMPRA -->
<div class="modal fade" id="modalViewCompra" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content product-modal sale-modal">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Detalle de compra</h5>
          <div class="small text-muted" id="viewMeta">Cargando...</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <div class="fw-semibold">Nota</div>
          <div class="text-muted" id="viewNota">-</div>
        </div>
        <div class="mb-2">
          <div class="fw-semibold">Proveedor</div>
          <div class="text-muted" id="viewProveedor">-</div>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0 sale-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th class="text-end">Cant.</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody id="viewItems">
              <tr>
                <td colspan="5" class="text-center text-muted py-3">Cargando...</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="4" class="text-end">TOTAL</th>
                <th class="text-end" id="viewTotal">L. 0.00</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- MODAL ELIMINAR COMPRA -->
<div class="modal fade" id="modalDeleteCompra" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content product-modal sale-modal">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        ¿Seguro que querés eliminar esta compra?
        <div class="small text-muted mt-2">
          Esta acción elimina definitivamente el registro.
        </div>
      </div>

      <div class="modal-footer">
        <form method="post" action="index.php?page=purchases">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id_compra" id="deleteCompraId">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
  (function () {
    const sel = document.getElementById('selProducto');
    const qty = document.getElementById('inpCantidad');
    const btn = document.getElementById('btnAdd');
    const tbody = document.querySelector('#tblItems tbody');
    const lblTotal = document.getElementById('lblTotal');

    function money(n) {
      return 'L. ' + (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
    }

    function recomputeTotal() {
      let total = 0;
      tbody.querySelectorAll('tr[data-subtotal]').forEach(tr => {
        total += parseFloat(tr.getAttribute('data-subtotal') || '0');
      });
      lblTotal.textContent = money(total);
    }

    btn?.addEventListener('click', () => {
      const id = parseInt(sel.value || '0', 10);
      if (!id) return;

      const opt = sel.options[sel.selectedIndex];
      const name = opt.textContent.trim();
      const precio = parseFloat(opt.getAttribute('data-precio') || '0');
      const cantidad = parseInt(qty.value || '1', 10);
      if (cantidad <= 0) return;

      const rowEmpty = document.getElementById('rowEmpty');
      if (rowEmpty) rowEmpty.remove();

      const subtotal = precio * cantidad;

      const tr = document.createElement('tr');
      tr.setAttribute('data-subtotal', String(subtotal));
      tr.innerHTML = `
      <td>
        <div class="fw-semibold">${name}</div>
        <input type="hidden" name="id_producto[]" value="${id}">
      </td>
      <td class="text-end">
        <input type="number" min="1" class="form-control form-control-sm text-end"
               name="cantidad[]" value="${cantidad}" style="max-width:110px; margin-left:auto;">
      </td>
      <td class="text-end">
        <input type="text" class="form-control form-control-sm text-end"
               name="costo_unit[]" value="${precio.toFixed(2)}" style="max-width:140px; margin-left:auto;">
      </td>
      <td class="text-end"><span class="text-muted">${money(subtotal)}</span></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-dark btn-sm btnDel">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
      tbody.appendChild(tr);
      recomputeTotal();
    });

    tbody?.addEventListener('input', (e) => {
      const tr = e.target.closest('tr');
      if (!tr) return;
      const cant = tr.querySelector('input[name="cantidad[]"]');
      const cu = tr.querySelector('input[name="costo_unit[]"]');
      if (!cant || !cu) return;

      const cantidad = parseInt(cant.value || '0', 10);
      const precio = parseFloat(cu.value || '0');
      const subtotal = Math.max(0, cantidad) * Math.max(0, precio);

      tr.setAttribute('data-subtotal', String(subtotal));
      const cell = tr.querySelector('td:nth-child(4) span');
      if (cell) cell.textContent = money(subtotal);
      recomputeTotal();
    });

    tbody?.addEventListener('click', (e) => {
      const btnDel = e.target.closest('.btnDel');
      if (!btnDel) return;
      const tr = btnDel.closest('tr');
      tr.remove();

      if (!tbody.querySelector('tr[data-subtotal]')) {
        const empty = document.createElement('tr');
        empty.className = 'text-muted';
        empty.id = 'rowEmpty';
        empty.innerHTML = '<td colspan="5" class="text-center py-3">Agregá productos a la compra.</td>';
        tbody.appendChild(empty);
      }
      recomputeTotal();
    });
  })();
</script>

<script>
  function openDeleteCompra(id) {
    document.getElementById('deleteCompraId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('modalDeleteCompra'));
    modal.show();
  }
</script>
<script>
  async function openViewCompra(id) {
    const modalEl = document.getElementById('modalViewCompra');
    const modal = new bootstrap.Modal(modalEl);

    // UI loading
    document.getElementById('viewMeta').textContent = 'Cargando...';
    document.getElementById('viewNota').textContent = '-';
    document.getElementById('viewItems').innerHTML =
      '<tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>';
    document.getElementById('viewTotal').textContent = 'L. 0.00';

    modal.show();

    try {
      const res = await fetch(`index.php?page=purchases&action=view_json&id=${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      // meta
      document.getElementById('viewMeta').textContent =
        `Compra #${data.id_compra} · ${data.fecha} · Usuario: ${data.usuario} · Estado: ${data.estado}`;

      document.getElementById('viewNota').textContent = data.nota || '-';
      document.getElementById('viewProveedor').textContent = (data.proveedor || '-');
      // items
      let total = 0;
      const rows = (data.items || []).map(it => {
        total += parseFloat(it.subtotal || 0);
        return `
        <tr>
          <td class="text-muted">${escapeHtml(it.sku || '')}</td>
          <td>${escapeHtml(it.nombre || '')}</td>
          <td class="text-end">${parseInt(it.cantidad || 0, 10)}</td>
          <td class="text-end">L. ${Number(it.costo_unit || 0).toFixed(2)}</td>
          <td class="text-end">L. ${Number(it.subtotal || 0).toFixed(2)}</td>
        </tr>
      `;
      }).join('');

      document.getElementById('viewItems').innerHTML =
        rows || '<tr><td colspan="5" class="text-center text-muted py-3">Sin items.</td></tr>';

      document.getElementById('viewTotal').textContent = 'L. ' + total.toFixed(2);

    } catch (err) {
      document.getElementById('viewMeta').textContent = 'Error cargando detalle';
      document.getElementById('viewItems').innerHTML =
        '<tr><td colspan="5" class="text-center text-danger py-3">No se pudo cargar el detalle.</td></tr>';
    }
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[s]));
  }
</script>