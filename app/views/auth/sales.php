<?php
$filters = $viewData['filters'] ?? [];
$sales = $viewData['sales'] ?? [];
$products = $viewData['products'] ?? [];
$clientes = $viewData['clientes'] ?? [];
$stats = $viewData['stats'] ?? [];

function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$q = $filters['q'] ?? '';
$estado = $filters['estado'] ?? 'TODOS';
$from = $filters['from'] ?? '';
$to = $filters['to'] ?? '';

$k = $stats['kpis'] ?? [];
$serie = $stats['serie'] ?? [];
$top = $stats['top'] ?? [];
$cats = $stats['cats'] ?? [];

function money($n)
{
  return 'L. ' . number_format((float) $n, 2);
}
?>
<div class="products-page page-fade sales-page">

  <!-- Header + filtros -->
  <div class="cardx mb-4">
    <div class="hd purchases-toolbar">
      <div class="toolbar-left">
        <div class="fw-bold title">Ventas · Inventario AJA</div>
        <div class="subtitle">Registrá ventas, bajá stock automático y mirá estadísticas del inventario.</div>
      </div>

      <div class="toolbar-right">
        <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateVenta">
          <i class="bi bi-plus-lg me-1"></i> Registrar venta
        </button>
      </div>
    </div>

    <div class="bd">
      <form class="purchases-filters" method="get" action="index.php">
        <input type="hidden" name="page" value="sales">

        <div class="filter">
          <label class="form-label">Buscar</label>
          <input class="form-control form-control-sm" name="q" value="<?= h($q) ?>" placeholder="ID, nota, cliente...">
        </div>

        <div class="filter">
          <label class="form-label">Estado</label>
          <select class="form-select form-select-sm" name="estado">
            <?php foreach (['TODOS', 'PENDIENTE', 'PAGADA', 'ENTREGADA', 'ANULADA'] as $op): ?>
              <option value="<?= h($op) ?>" <?= $estado === $op ? 'selected' : '' ?>><?= h($op) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter">
          <label class="form-label">Desde</label>
          <input type="date" class="form-control form-control-sm" name="from" value="<?= h($from) ?>">
        </div>

        <div class="filter">
          <label class="form-label">Hasta</label>
          <input type="date" class="form-control form-control-sm" name="to" value="<?= h($to) ?>">
        </div>

        <div class="filter actions">
          <button class="btn btn-brand btn-sm w-100">
            <i class="bi bi-search me-1"></i> Filtrar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- KPIs (estadística rápida) -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
      <div class="cardx">
        <div class="bd">
          <div class="small text-muted">Total vendido (neto)</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= money($k['total'] ?? 0) ?></div>
          <div class="small text-muted">Ventas: <?= (int) ($k['ventas'] ?? 0) ?> · Ticket prom:
            <?= money($k['ticket_prom'] ?? 0) ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="cardx">
        <div class="bd">
          <div class="small text-muted">Unidades vendidas</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= (int) ($k['unidades'] ?? 0) ?></div>
          <div class="small text-muted">Clientes únicos: <?= (int) ($k['clientes_unicos'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="cardx">
        <div class="bd">
          <div class="small text-muted">Descuento total</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= money($k['descuento'] ?? 0) ?></div>
          <div class="small text-muted">Subtotal bruto: <?= money($k['subtotal'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="cardx">
        <div class="bd">
          <div class="small text-muted">Utilidad estimada*</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= money($k['util_est'] ?? 0) ?></div>
          <div class="small text-muted">Margen est: <?= number_format(((float) ($k['margen_est'] ?? 0)) * 100, 1) ?>%
          </div>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="small text-muted">
        * Utilidad estimada usando el último costo de compra registrado por producto (ENTRADA_COMPRA).
      </div>
    </div>
  </div>

  <!-- Tablas estadísticas: Top productos / Categorías / Serie diaria -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-xl-6">
      <div class="cardx">
        <div class="hd d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold">Top productos</div>
            <div class="small text-muted">Los que más ingresan (según filtros).</div>
          </div>
          <span class="badge badge-soft">Estadística</span>
        </div>
        <div class="bd p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-end">Unid.</th>
                  <th class="text-end">Ingreso</th>
                  <th class="text-end">Util est</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$top): ?>
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">Sin datos.</td>
                  </tr>
                <?php endif; ?>
                <?php foreach ($top as $t): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= h($t['nombre']) ?></div>
                      <div class="small text-muted"><?= h($t['sku']) ?></div>
                    </td>
                    <td class="text-end"><?= (int) $t['unidades'] ?></td>
                    <td class="text-end"><?= money($t['ingreso']) ?></td>
                    <td class="text-end"><?= money($t['util_est']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="cardx">
        <div class="hd d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold">Ventas por categoría</div>
            <div class="small text-muted">Qué área mueve más inventario.</div>
          </div>
          <span class="badge badge-soft">Estadística</span>
        </div>
        <div class="bd p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Categoría</th>
                  <th class="text-end">Unid.</th>
                  <th class="text-end">Ingreso</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$cats): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">Sin datos.</td>
                  </tr>
                <?php endif; ?>
                <?php foreach ($cats as $c): ?>
                  <tr>
                    <td class="fw-semibold"><?= h($c['categoria'] ?? 'Sin categoría') ?></td>
                    <td class="text-end"><?= (int) $c['unidades'] ?></td>
                    <td class="text-end"><?= money($c['ingreso']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="cardx">
        <div class="hd d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-bold">Serie diaria</div>
            <div class="small text-muted">Totales por día (según filtros o últimos 14 días).</div>
          </div>
          <span class="badge badge-soft">Estadística</span>
        </div>
        <div class="bd p-0">
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Día</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$serie): ?>
                  <tr>
                    <td colspan="2" class="text-center text-muted py-4">Sin datos.</td>
                  </tr>
                <?php endif; ?>
                <?php foreach ($serie as $s): ?>
                  <tr>
                    <td class="text-muted"><?= h($s['dia']) ?></td>
                    <td class="text-end"><?= money($s['total']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Listado de ventas -->
  <div class="cardx">
    <div class="bd p-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0 purchases-table">
          <thead>
            <tr>
              <th style="width:90px;">#</th>
              <th>Fecha</th>
              <th>Cliente</th>
              <th>Nota</th>
              <th class="text-end">Items</th>
              <th class="text-end">Total</th>
              <th style="width:160px;">Estado</th>
              <th style="width:200px;" class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$sales): ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">Sin registros.</td>
              </tr>
            <?php endif; ?>

            <?php foreach ($sales as $v): ?>
              <tr>
                <td class="fw-semibold">#<?= (int) $v['id_venta'] ?></td>
                <td class="text-muted"><?= h($v['fecha']) ?></td>
                <td><?= h($v['cliente'] ?? '') ?></td>
                <td class="text-muted"><?= h($v['nota'] ?? '') ?></td>
                <td class="text-end"><?= (int) $v['items'] ?></td>
                <td class="text-end"><?= money($v['total'] ?? 0) ?></td>
                <td>
                  <?php
                  $st = strtoupper(trim((string) ($v['estado'] ?? '')));
                  $cls = 'status-pill'; // base (igual que compras)
                
                  if ($st === 'ANULADA')
                    $cls .= ' st-danger';              // ROJO
                  else if ($st === 'PENDIENTE')
                    $cls .= ' st-blue';         // AZUL
                  else if ($st === 'PAGADA' || $st === 'ENTREGADA')
                    $cls .= ' st-ok'; // VERDE
                  else
                    $cls .= ' st-neutral';                               // GRIS
                  ?>
                  <span class="<?= $cls ?>"><?= h($st) ?></span>
                </td>
                <td class="text-end">
                  <div class="actions">
                    <button type="button" class="btn btn-light btn-sm"
                      onclick="openViewVenta(<?= (int) $v['id_venta'] ?>)">
                      <i class="bi bi-eye"></i><span class="d-none d-md-inline ms-1">Ver</span>
                    </button>

                    <?php if ((string) $v['estado'] !== 'ANULADA'): ?>
                      <button type="button" class="btn btn-danger btn-sm"
                        onclick="openCancelVenta(<?= (int) $v['id_venta'] ?>)">
                        <i class="bi bi-x-circle"></i><span class="d-none d-md-inline ms-1">Anular</span>
                      </button>
                    <?php endif; ?>

                    <?php if ((string) $v['estado'] !== 'ANULADA' && (string) $v['estado'] !== 'ENTREGADA'): ?>
                      <button type="button" class="btn btn-success btn-sm"
                        onclick="openCompleteVenta(<?= (int) $v['id_venta'] ?>)">
                        <i class="bi bi-check2-circle"></i><span class="d-none d-md-inline ms-1">Completada</span>
                      </button>
                    <?php endif; ?>

                    <?php if ((string) $v['estado'] === 'ANULADA'): ?>
                      <button type="button" class="btn btn-outline-dark btn-sm"
                        onclick="openDeleteVenta(<?= (int) $v['id_venta'] ?>)">
                        <i class="bi bi-trash"></i><span class="d-none d-md-inline ms-1">Eliminar</span>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>


<!-- MODAL CREAR VENTA -->
<div class="modal fade" id="modalCreateVenta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content product-modal">
      <div class="modal-header">
        <h5 class="modal-title">Registrar venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form method="post" action="index.php?page=sales" id="frmVenta">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="_q" value="<?= h($q) ?>">
        <input type="hidden" name="_estado" value="<?= h($estado) ?>">
        <input type="hidden" name="_from" value="<?= h($from) ?>">
        <input type="hidden" name="_to" value="<?= h($to) ?>">

        <div class="modal-body">
          <div class="row g-3 mb-3">

            <div class="col-12 col-lg-4">
              <label class="form-label small text-muted">Cliente (texto)</label>
              <input type="text" class="form-control" name="cliente_txt" required
                placeholder="Ej: María López / Pedido IG / Cliente X" />
              <div class="small text-muted mt-1">Si se deja vacío, se guardará como “CONSUMIDOR FINAL”.</div>
            </div>

            <div class="col-12 col-lg-5">
              <label class="form-label small text-muted">Dirección (texto)</label>
              <input type="text" class="form-control" name="direccion_txt"
                placeholder="Ej: Col. XYZ, La Ceiba · casa #12 · referencia..." />
              <div class="small text-muted mt-1">Si se deja vacío, se guardará como “SIN DIRECCION”.</div>
            </div>

            <div class="col-12 col-lg-3">
              <label class="form-label small text-muted">Descuento</label>
              <input type="number" step="0.01" min="0" class="form-control" name="descuento" id="inpDescuento"
                value="0.00">
            </div>

          </div>

          <div class="mb-3">
            <label class="form-label small text-muted">Nota / referencia</label>
            <input class="form-control" name="nota" placeholder="Ej: Envío La Ceiba, pedido IG, etc...">
          </div>

          <div class="d-flex flex-wrap gap-2 align-items-end mb-2">
            <div style="min-width:320px; flex:1;">
              <label class="form-label small text-muted">Producto (con stock)</label>
              <select class="form-select" id="selProducto">
                <option value="">-- Elegí un producto --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= (int) $p['id_producto'] ?>" data-precio="<?= h($p['precio']) ?>"
                    data-stock="<?= (int) $p['stock'] ?>">
                    <?= h($p['nombre']) ?> (<?= h($p['sku']) ?>)
                    · Stock: <?= (int) $p['stock'] ?>
                    · L. <?= number_format((float) $p['precio'], 2) ?>
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
                  <td colspan="5" class="text-center py-3">Agregá productos a la venta.</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">SUBTOTAL</th>
                  <th class="text-end" id="lblSub">L. 0.00</th>
                  <th></th>
                </tr>
                <tr>
                  <th colspan="3" class="text-end">DESCUENTO</th>
                  <th class="text-end" id="lblDesc">L. 0.00</th>
                  <th></th>
                </tr>
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
            Si te dice “stock insuficiente”, revisá compras registradas o el stock actual.
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-brand" type="submit">
            <i class="bi bi-check2-circle me-1"></i> Guardar venta
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- MODAL VER VENTA -->
<div class="modal fade" id="modalViewVenta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content product-modal sale-modal">

      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Detalle de venta</h5>
          <div class="small text-muted" id="viewMeta">Cargando...</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body pt-3">

        <form method="post" action="index.php?page=sales" id="frmEditVenta" class="mb-3">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id_venta" id="editIdVenta" value="">

          <input type="hidden" name="_q" value="<?= h($q) ?>">
          <input type="hidden" name="_estado" value="<?= h($estado) ?>">
          <input type="hidden" name="_from" value="<?= h($from) ?>">
          <input type="hidden" name="_to" value="<?= h($to) ?>">

          <div class="row g-3">
            <div class="col-12 col-lg-5">
              <label class="form-label fw-semibold">Cliente</label>
              <input type="text" class="form-control" name="cliente_txt" id="editCliente" value="" readonly>
            </div>
            <div class="col-12 col-lg-7">
              <label class="form-label fw-semibold">Dirección</label>
              <input type="text" class="form-control" name="direccion_txt" id="editDir" value="" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Nota</label>
              <input type="text" class="form-control" name="nota" id="editNota" value="" readonly>
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-outline-dark" id="btnEditVenta">
              <i class="bi bi-pencil-square me-1"></i> Editar
            </button>
            <button type="submit" class="btn btn-brand" id="btnSaveVenta" disabled>
              <i class="bi bi-check2-circle me-1"></i> Guardar cambios
            </button>
          </div>
        </form>

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
                <th colspan="4" class="text-end">SUBTOTAL</th>
                <th class="text-end" id="viewSub">L. 0.00</th>
              </tr>
              <tr>
                <th colspan="4" class="text-end">DESCUENTO</th>
                <th class="text-end" id="viewDesc">L. 0.00</th>
              </tr>
              <tr>
                <th colspan="4" class="text-end">TOTAL</th>
                <th class="text-end" id="viewTotal">L. 0.00</th>
              </tr>
            </tfoot>
          </table>
        </div>

      </div><!-- /modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div><!-- /modal-content -->
  </div><!-- /modal-dialog -->
</div><!-- /modal -->

<!-- MODAL ANULAR -->
<div class="modal fade" id="modalCancelVenta" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content product-modal">
      <div class="modal-header">
        <h5 class="modal-title">Anular venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        ¿Seguro que querés anular esta venta?
        <div class="small text-muted mt-2">
          Esto devuelve el stock automáticamente.
        </div>
      </div>

      <div class="modal-footer">
        <form method="post" action="index.php?page=sales">
          <input type="hidden" name="action" value="cancel">
          <input type="hidden" name="id_venta" id="cancelVentaId">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Anular</button>
        </form>
      </div>

    </div>
  </div>
</div>
<!-- MODAL COMPLETAR -->
<div class="modal fade" id="modalCompleteVenta" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content product-modal">
      <div class="modal-header">
        <h5 class="modal-title">Marcar como completada</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        ¿Querés marcar esta venta como <b>ENTREGADA</b>?
        <div class="small text-muted mt-2">Esto solo cambia el estado (no toca inventario).</div>
      </div>

      <div class="modal-footer">
        <form method="post" action="index.php?page=sales">
          <input type="hidden" name="action" value="complete">
          <input type="hidden" name="id_venta" id="completeVentaId">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Marcar entregada</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalDeleteVenta" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content product-modal">
      <div class="modal-header">
        <h5 class="modal-title">Eliminar venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        Esto <b>elimina definitivamente</b> la venta del sistema.
        <div class="small text-muted mt-2">Por seguridad, solo se permite eliminar ventas ANULADAS.</div>
      </div>

      <div class="modal-footer">
        <form method="post" action="index.php?page=sales">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id_venta" id="deleteVentaId">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-outline-dark">Eliminar</button>
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
    const lblSub = document.getElementById('lblSub');
    const lblDesc = document.getElementById('lblDesc');
    const lblTotal = document.getElementById('lblTotal');
    const inpDesc = document.getElementById('inpDescuento');

    function money(n) { return 'L. ' + (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2); }

    function recompute() {
      let sub = 0;
      tbody.querySelectorAll('tr[data-subtotal]').forEach(tr => {
        sub += parseFloat(tr.getAttribute('data-subtotal') || '0');
      });
      const desc = Math.max(0, parseFloat(inpDesc?.value || '0') || 0);
      const tot = Math.max(0, sub - desc);
      lblSub.textContent = money(sub);
      lblDesc.textContent = money(desc);
      lblTotal.textContent = money(tot);
    }

    inpDesc?.addEventListener('input', recompute);

    btn?.addEventListener('click', () => {
      const id = parseInt(sel.value || '0', 10);
      if (!id) return;

      const opt = sel.options[sel.selectedIndex];
      const name = opt.textContent.trim();
      const precio = parseFloat(opt.getAttribute('data-precio') || '0');
      const stock = parseInt(opt.getAttribute('data-stock') || '0', 10);
      const cantidad = parseInt(qty.value || '1', 10);
      if (cantidad <= 0) return;

      if (stock < cantidad) {
        if (typeof window.showToast === 'function') {
          window.showToast('error', `Stock insuficiente. Disponible: ${stock}. Solicitado: ${cantidad}`);
        } else {
          alert(`Stock insuficiente. Disponible: ${stock}. Solicitado: ${cantidad}`);
        }
        return;
      }

      const rowEmpty = document.getElementById('rowEmpty');
      if (rowEmpty) rowEmpty.remove();

      const subtotal = precio * cantidad;

      const tr = document.createElement('tr');
      tr.setAttribute('data-subtotal', String(subtotal));
      tr.innerHTML = `
      <td>
        <div class="fw-semibold">${escapeHtml(name)}</div>
        <input type="hidden" name="id_producto[]" value="${id}">
      </td>
      <td class="text-end">
        <input type="number" min="1" class="form-control form-control-sm text-end"
               name="cantidad[]" value="${cantidad}" style="max-width:110px; margin-left:auto;">
      </td>
      <td class="text-end">
        <input type="text" class="form-control form-control-sm text-end"
               name="precio_unit[]" value="${precio.toFixed(2)}" style="max-width:140px; margin-left:auto;">
      </td>
      <td class="text-end"><span class="text-muted">${money(subtotal)}</span></td>
      <td class="text-end">
        <button type="button" class="btn btn-outline-dark btn-sm btnDel"><i class="bi bi-trash"></i></button>
      </td>
    `;
      tbody.appendChild(tr);
      recompute();
    });

    tbody?.addEventListener('input', (e) => {
      const tr = e.target.closest('tr');
      if (!tr) return;

      const cant = tr.querySelector('input[name="cantidad[]"]');
      const pu = tr.querySelector('input[name="precio_unit[]"]');
      if (!cant || !pu) return;

      const cantidad = parseInt(cant.value || '0', 10);
      const precio = parseFloat(pu.value || '0');
      const subtotal = Math.max(0, cantidad) * Math.max(0, precio);

      tr.setAttribute('data-subtotal', String(subtotal));
      const cell = tr.querySelector('td:nth-child(4) span');
      if (cell) cell.textContent = money(subtotal);

      recompute();
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
        empty.innerHTML = '<td colspan="5" class="text-center py-3">Agregá productos a la venta.</td>';
        tbody.appendChild(empty);
      }
      recompute();
    });

    // iniciar totales
    recompute();
  })();
</script>
<script>
  async function reloadProductsForSale() {
    const sel = document.getElementById('selProducto');
    if (!sel) return;

    const prev = sel.value;

    const res = await fetch('index.php?page=sales&action=productos_json');
    const json = await res.json();

    if (!json.ok) return;

    sel.innerHTML = '<option value="">-- Elegí un producto --</option>';

    (json.products || []).forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id_producto;
      opt.setAttribute('data-precio', String(p.precio));
      opt.setAttribute('data-stock', String(p.stock));
      opt.textContent = `${p.nombre} (${p.sku}) · Stock: ${p.stock} · L. ${Number(p.precio).toFixed(2)}`;
      sel.appendChild(opt);
    });

    if (prev) sel.value = prev;
  }

  // cada vez que se abre el modal, refrescamos lista y stock
  document.getElementById('modalCreateVenta')?.addEventListener('shown.bs.modal', reloadProductsForSale);
</script>



<script>
  function setEditEnabled(enabled) {
    const c = document.getElementById('editCliente');
    const d = document.getElementById('editDir');
    const n = document.getElementById('editNota');
    const save = document.getElementById('btnSaveVenta');
    if (!c || !d || !n || !save) return;
    c.readOnly = !enabled;
    d.readOnly = !enabled;
    n.readOnly = !enabled;
    save.disabled = !enabled;
  }

  function openCancelVenta(id) {
    document.getElementById('cancelVentaId').value = id;
    new bootstrap.Modal(document.getElementById('modalCancelVenta')).show();
  }
  function openCompleteVenta(id){
  document.getElementById('completeVentaId').value = id;
  new bootstrap.Modal(document.getElementById('modalCompleteVenta')).show();
}

function openDeleteVenta(id){
  document.getElementById('deleteVentaId').value = id;
  new bootstrap.Modal(document.getElementById('modalDeleteVenta')).show();
}

  async function openViewVenta(id) {
    // limpiar
    document.getElementById('editIdVenta').value = '';
    document.getElementById('editCliente').value = '';
    document.getElementById('editDir').value = '';
    document.getElementById('editNota').value = '';
    setEditEnabled(false);

    // abrir modal
    const modal = new bootstrap.Modal(document.getElementById('modalViewVenta'));
    modal.show();

    // pedir JSON al controller (YA EXISTE en tu SaleController.php)
    const res = await fetch(`index.php?page=sales&action=view_json&id=${encodeURIComponent(id)}`);
    const json = await res.json();

    if (!json.ok) {
      if (typeof window.showToast === 'function') window.showToast('error', json.message || 'No se pudo cargar la venta.');
      else alert(json.message || 'No se pudo cargar la venta.');
      return;
    }

    document.getElementById('editIdVenta').value = json.id_venta;
    document.getElementById('editCliente').value = json.cliente || '';
    document.getElementById('editDir').value = json.direccion || '';
    document.getElementById('editNota').value = json.nota || '';

    // items
    const tbody = document.getElementById('viewItems');
    tbody.innerHTML = '';
    (json.items || []).forEach(it => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
      <td class="text-muted">${escapeHtml(it.sku || '')}</td>
      <td>${escapeHtml(it.nombre || '')}</td>
      <td class="text-end">${Number(it.cantidad || 0)}</td>
      <td class="text-end">L. ${Number(it.precio_unit || 0).toFixed(2)}</td>
      <td class="text-end">L. ${Number(it.subtotal || 0).toFixed(2)}</td>
    `;
      tbody.appendChild(tr);
    });

    document.getElementById('viewSub').textContent = `L. ${Number(json.subtotal || 0).toFixed(2)}`;
    document.getElementById('viewDesc').textContent = `L. ${Number(json.descuento || 0).toFixed(2)}`;
    document.getElementById('viewTotal').textContent = `L. ${Number(json.total || 0).toFixed(2)}`;

    const meta = document.getElementById('viewMeta');
    if (meta) {
      meta.textContent = `Venta #${json.id_venta} · ${json.fecha} · Estado: ${json.estado}`;
    }
  }

  document.getElementById('btnEditVenta')?.addEventListener('click', () => {
    setEditEnabled(true);
    document.getElementById('editCliente')?.focus();
  });

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[s]));
  }
</script>