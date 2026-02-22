<?php
$filters = $viewData['filters'] ?? [];
$purchases = $viewData['purchases'] ?? [];
$purchase = $viewData['purchase'] ?? null;
$items = $viewData['items'] ?? [];
$products = $viewData['products'] ?? [];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$q = $filters['q'] ?? '';
$estado = $filters['estado'] ?? 'TODOS';
?>
<div class="products-page page-fade purchases-page">

  <div class="cardx mb-4">
    <div class="hd d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <div class="fw-bold" style="font-size:1.15rem;">Compras · Inventario AJA 🧾</div>
        <small class="text-muted">Registrá compras ordenadas y actualizá el stock automáticamente.</small>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
          <i class="bi bi-plus-lg me-1"></i> Registrar compra
        </button>
      </div>
    </div>

    <div class="bd">
      <form class="row g-2 align-items-end" method="get" action="index.php">
        <input type="hidden" name="page" value="purchases">

        <div class="col-12 col-md-6">
          <label class="form-label small text-muted">Buscar (ID o nota)</label>
          <input class="form-control form-control-sm" name="q" value="<?= h($q) ?>" placeholder="Ej: 12 o 'proveedor'">
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label small text-muted">Estado</label>
          <select class="form-select form-select-sm" name="estado">
            <?php foreach (['TODOS','REGISTRADA','ANULADA'] as $op): ?>
              <option value="<?= h($op) ?>" <?= $estado === $op ? 'selected' : '' ?>><?= h($op) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-3">
          <button class="btn btn-brand btn-sm w-100">
            <i class="bi bi-search me-1"></i> Filtrar
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="cardx">
    <div class="bd p-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0 purchases-table">
          <thead>
            <tr>
              <th style="width:90px;">#</th>
              <th>Fecha</th>
              <th>Usuario</th>
              <th>Nota</th>
              <th class="text-end">Items</th>
              <th class="text-end">Total</th>
              <th style="width:160px;">Estado</th>
              <th style="width:180px;" class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$purchases): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">Sin registros.</td></tr>
            <?php endif; ?>

            <?php foreach ($purchases as $c): ?>
              <tr>
                <td class="fw-semibold">#<?= (int)$c['id_compra'] ?></td>
                <td class="text-muted"><?= h($c['fecha']) ?></td>
                <td><?= h($c['usuario'] ?? '') ?></td>
                <td class="text-muted"><?= h($c['nota']) ?></td>
                <td class="text-end"><?= (int)$c['items'] ?></td>
                <td class="text-end">L. <?= number_format((float)$c['total'], 2) ?></td>
                <td>
                  <?php $st = (string)$c['estado']; $badge = ($st === 'ANULADA') ? 'bg-danger' : 'bg-success'; ?>
                  <span class="badge <?= $badge ?>"><?= h($st) ?></span>
                </td>
                <td class="text-end">
                  <a class="btn btn-light btn-sm shadow-sm"
                     href="index.php?page=purchases&view=<?= (int)$c['id_compra'] ?>">
                    <i class="bi bi-eye me-1"></i> Ver
                  </a>

                  <?php if ((string)$c['estado'] !== 'ANULADA'): ?>
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="openDeleteCompra(<?= (int)$c['id_compra'] ?>)">
                      <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if ($purchase): ?>
    <div class="cardx mt-4">
      <div class="hd d-flex justify-content-between flex-wrap gap-2">
        <div>
          <div class="fw-bold">Detalle · Compra #<?= (int)$purchase['id_compra'] ?></div>
          <small class="text-muted">
            <?= h($purchase['fecha']) ?> · <?= h($purchase['usuario'] ?? '') ?> ·
            <span class="badge <?= ((string)$purchase['estado']==='ANULADA')?'bg-danger':'bg-success' ?>"><?= h($purchase['estado']) ?></span>
          </small>
        </div>
        <a class="btn btn-light btn-sm shadow-sm" href="index.php?page=purchases">
          <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
      </div>

      <div class="bd">
        <div class="mb-3 text-muted"><i class="bi bi-chat-quote me-1"></i> <?= h($purchase['nota']) ?></div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th class="text-end">Cant.</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php $total = 0.0; foreach ($items as $it): $total += (float)$it['subtotal']; ?>
                <tr>
                  <td class="text-muted"><?= h($it['sku']) ?></td>
                  <td><?= h($it['nombre']) ?></td>
                  <td class="text-end"><?= (int)$it['cantidad'] ?></td>
                  <td class="text-end">L. <?= number_format((float)$it['costo_unit'], 2) ?></td>
                  <td class="text-end">L. <?= number_format((float)$it['subtotal'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="4" class="text-end">TOTAL</th>
                <th class="text-end">L. <?= number_format($total, 2) ?></th>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>
    </div>
  <?php endif; ?>

</div>

<!-- MODAL CREAR COMPRA -->
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content product-modal">
      <div class="modal-header">
        <h5 class="modal-title">Registrar compra</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form method="post" action="index.php?page=purchases" id="frmCompra">
        <input type="hidden" name="action" value="create">

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small text-muted">Nota / referencia</label>
            <input class="form-control" name="nota" placeholder="Ej: Compra proveedor X, factura #...">
          </div>

          <div class="d-flex flex-wrap gap-2 align-items-end mb-2">
            <div style="min-width:260px; flex:1;">
              <label class="form-label small text-muted">Producto</label>
              <select class="form-select" id="selProducto">
                <option value="">-- Elegí un producto --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= (int)$p['id_producto'] ?>" data-precio="<?= h($p['precio']) ?>">
                    <?= h($p['nombre']) ?> (<?= h($p['sku']) ?>) · L. <?= number_format((float)$p['precio'],2) ?>
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
            <table class="table table-sm align-middle purchases-items-table" id="tblItems">
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

<!-- MODAL ELIMINAR COMPRA -->
<div class="modal fade" id="modalDeleteCompra" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content product-modal">
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
(function(){
  const sel = document.getElementById('selProducto');
  const qty = document.getElementById('inpCantidad');
  const btn = document.getElementById('btnAdd');
  const tbody = document.querySelector('#tblItems tbody');
  const lblTotal = document.getElementById('lblTotal');

  function money(n){
    return 'L. ' + (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
  }

  function recomputeTotal(){
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