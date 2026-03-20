<?php
$filters = $viewData['filters'] ?? [];
$sales = $viewData['sales'] ?? [];
$products = $viewData['products'] ?? [];
$clientes = $viewData['clientes'] ?? [];
$stats = $viewData['stats'] ?? [];
$sale_form_token = $viewData['sale_form_token'] ?? '';

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
$maxTopIngreso = 0;
if (!empty($top)) {
  foreach ($top as $t)
    $maxTopIngreso = max($maxTopIngreso, (float) ($t['ingreso'] ?? 0));
}

$maxCatIngreso = 0;
if (!empty($cats)) {
  foreach ($cats as $c)
    $maxCatIngreso = max($maxCatIngreso, (float) ($c['ingreso'] ?? 0));
}

$maxSerieTotal = 0;
if (!empty($serie)) {
  foreach ($serie as $s)
    $maxSerieTotal = max($maxSerieTotal, (float) ($s['total'] ?? 0));
}
function money($n)
{
  return 'L. ' . number_format((float) $n, 2);
}
?>
<div class="products-page page-fade sales-page">

  
  <div class="cardx mb-4 module-hero">
    <div class="hd purchases-toolbar">
      <div class="toolbar-left">
        <div class="fw-bold title">Ventas · Inventario</div>
        <br>
      </div>

      <div class="toolbar-right">
        <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateVenta">
          <i class="bi bi-plus-lg me-1"></i> Registrar venta
        </button>
      </div>
    </div>

    <div class="bd">
      <form class="purchases-filters filters-glass" method="get" action="index.php">
        <input type="hidden" name="page" value="sales">

        <div class="filter">
          <label class="form-label">Buscar</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input class="form-control form-control-sm" name="q" value="<?= h($q) ?>"
              placeholder="ID, cliente, nota...">
          </div>
        </div>

        <div class="filter">
          <label class="form-label">Estado</label>
          <select class="form-select form-select-sm" name="estado">
            <?php foreach (['TODOS', 'PENDIENTE', 'PAGADA', 'ENTREGADA', 'ANULADA'] as $op): ?>
              <option value="<?= h($op) ?>" <?= $estado === $op ? 'selected' : '' ?>><?= h($op) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter actions">
          <div class="actions">
            <button class="btn btn-brand btn-sm">
              <i class="bi bi-funnel me-1"></i> Aplicar
            </button>

            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#salesMore"
              aria-expanded="false" aria-controls="salesMore">
              <i class="bi bi-sliders me-1"></i> Más
            </button>

            <a class="btn btn-light btn-sm" href="index.php?page=sales">
              <i class="bi bi-x-lg me-1"></i> Limpiar
            </a>
          </div>
        </div>

        <div class="collapse filters-more" id="salesMore">
          <div class="more-grid">
            <div>
              <label class="form-label">Desde</label>
              <input type="date" class="form-control form-control-sm" name="from" value="<?= h($from) ?>">
            </div>
            <div>
              <label class="form-label">Hasta</label>
              <input type="date" class="form-control form-control-sm" name="to" value="<?= h($to) ?>">
            </div>
            <div></div>
          </div>
        </div>
      </form>
    </div>
  </div>


  <div class="row g-3 mb-4">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="cardx sales-kpi report-kpi">
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
      <div class="cardx sales-kpi report-kpi">
        <div class="bd">
          <div class="small text-muted">Unidades vendidas</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= (int) ($k['unidades'] ?? 0) ?></div>
          <div class="small text-muted">Clientes únicos: <?= (int) ($k['clientes_unicos'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="cardx sales-kpi report-kpi">
        <div class="bd">
          <div class="small text-muted">Descuento total</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= money($k['descuento'] ?? 0) ?></div>
          <div class="small text-muted">Subtotal bruto: <?= money($k['subtotal'] ?? 0) ?></div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
      <div class="cardx sales-kpi report-kpi">
        <div class="bd">
          <div class="small text-muted">Utilidad estimada</div>
          <div class="fw-bold" style="font-size:1.35rem;"><?= money($k['util_est'] ?? 0) ?></div>
          <div class="small text-muted">Margen est: <?= number_format(((float) ($k['margen_est'] ?? 0)) * 100, 1) ?>%
          </div>
        </div>
      </div>
    </div>

  </div>

  
  <div class="cardx mb-4 sales-analytics">
    <div class="hd d-flex align-items-center justify-content-between">
      <div>
        <div class="fw-bold">Analítica</div>
        <div class="small text-muted">Top productos, categorías y serie diaria.</div>
      </div>

      <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse"
        data-bs-target="#salesAnalyticsCollapse" aria-expanded="false" aria-controls="salesAnalyticsCollapse">
        <i class="bi bi-bar-chart-line me-1"></i> Ver / Ocultar
      </button>
    </div>

    <div class="bd pt-2 collapse show" id="salesAnalyticsCollapse">
      <div class="row g-3">

      
        <div class="col-12 col-xl-6">
          <div class="sales-panel">
            <div class="sales-panel-hd">
              <div class="fw-semibold">Top productos</div>
              <div class="small text-muted">Más ingreso según filtros.</div>
            </div>

            <div class="sales-panel-bd">
              <?php if (!$top): ?>
                <div class="text-center text-muted py-3">Sin datos.</div>
              <?php else: ?>
                <div class="stat-list">
                  <?php foreach ($top as $t):
                    $ing = (float) ($t['ingreso'] ?? 0);
                    $pct = $maxTopIngreso > 0 ? min(100, ($ing / $maxTopIngreso) * 100) : 0;
                    ?>
                    <div class="stat-item">
                      <div class="stat-left">
                        <div class="fw-semibold"><?= h($t['nombre']) ?></div>
                        <div class="small text-muted"><?= h($t['sku']) ?> · <?= (int) $t['unidades'] ?> unid.</div>
                      </div>

                      <div class="stat-right">
                        <div class="fw-bold"><?= money($ing) ?></div>
                        <div class="small text-muted">Util est: <?= money($t['util_est'] ?? 0) ?></div>
                      </div>

                      <div class="stat-bar">
                        <div class="stat-fill" style="width: <?= number_format($pct, 2) ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

       
        <div class="col-12 col-xl-6">
          <div class="sales-panel">
            <div class="sales-panel-hd">
              <div class="fw-semibold">Ventas por categoría</div>
              <div class="small text-muted">Qué área mueve más inventario.</div>
            </div>

            <div class="sales-panel-bd">
              <?php if (!$cats): ?>
                <div class="text-center text-muted py-3">Sin datos.</div>
              <?php else: ?>
                <div class="stat-list">
                  <?php foreach ($cats as $c):
                    $ing = (float) ($c['ingreso'] ?? 0);
                    $pct = $maxCatIngreso > 0 ? min(100, ($ing / $maxCatIngreso) * 100) : 0;
                    ?>
                    <div class="stat-item">
                      <div class="stat-left">
                        <div class="fw-semibold"><?= h($c['categoria'] ?? 'Sin categoría') ?></div>
                        <div class="small text-muted"><?= (int) $c['unidades'] ?> unid.</div>
                      </div>

                      <div class="stat-right">
                        <div class="fw-bold"><?= money($ing) ?></div>
                      </div>

                      <div class="stat-bar">
                        <div class="stat-fill" style="width: <?= number_format($pct, 2) ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

       
        <div class="col-12">
          <div class="sales-panel">
            <div class="sales-panel-hd d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-semibold">Serie diaria</div>
                <div class="small text-muted">Totales por día.</div>
              </div>
              <span class="badge badge-soft">Resumen</span>
            </div>

            <div class="sales-panel-bd">
              <?php if (!$serie): ?>
                <div class="text-center text-muted py-3">Sin datos.</div>
              <?php else: ?>
                <div class="serie-grid">
                  <?php foreach ($serie as $s):
                    $tot = (float) ($s['total'] ?? 0);
                    $pct = $maxSerieTotal > 0 ? min(100, ($tot / $maxSerieTotal) * 100) : 0;
                    ?>
                    <div class="serie-item">
                      <div class="serie-left">
                        <div class="small text-muted"><?= h($s['dia']) ?></div>
                        <div class="fw-bold"><?= money($tot) ?></div>
                      </div>
                      <div class="serie-bar">
                        <div class="serie-fill" style="width: <?= number_format($pct, 2) ?>%"></div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <div class="cardx">
    <div class="hd d-flex align-items-center justify-content-between">
      <div>
        <div class="fw-bold">Ventas</div>
        <div class="small text-muted">Totales por día.</div>
      </div>
    </div>

    <div class="bd">
      <?php if (!$sales): ?>
        <div class="text-center text-muted py-4">Sin registros.</div>
      <?php else: ?>
        <div class="sales-list">
          <?php foreach ($sales as $v): ?>
            <?php
            $st = strtoupper(trim((string) ($v['estado'] ?? '')));
            $cls = 'status-pill';
            if ($st === 'ANULADA')
              $cls .= ' st-danger';
            else if ($st === 'PENDIENTE')
              $cls .= ' st-blue';
            else if ($st === 'PAGADA' || $st === 'ENTREGADA')
              $cls .= ' st-ok';
            else
              $cls .= ' st-neutral';
            ?>

            <div class="sale-row">
              <div class="sale-main">
                <div class="sale-top">
                  <div class="sale-id">#<?= (int) $v['id_venta'] ?></div>
                  <div class="sale-date"><?= h($v['fecha']) ?></div>
                </div>

                <div class="sale-mid">
                  <div class="sale-client">
                    <i class="bi bi-person me-1"></i><?= h($v['cliente'] ?? '') ?>
                  </div>
                  <div class="sale-note">
                    <?= h($v['nota'] ?? '') ?>
                  </div>
                </div>
              </div>

              <div class="sale-metrics">
                <div class="metric">
                  <div class="lbl">Items</div>
                  <div class="val"><?= (int) $v['items'] ?></div>
                </div>
                <div class="metric">
                  <div class="lbl">Total</div>
                  <div class="val"><?= money($v['total'] ?? 0) ?></div>
                </div>
              </div>

              <div class="sale-status">
                <span class="<?= $cls ?>"><?= h($st) ?></span>
              </div>

              <div class="sale-actions text-end">
                <div class="dropdown">
                  <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>

                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                      <button class="dropdown-item" type="button" onclick="openViewVenta(<?= (int) $v['id_venta'] ?>)">
                        <i class="bi bi-eye me-2"></i> Ver
                      </button>
                    </li>

                    <?php if ((string) $v['estado'] !== 'ANULADA'): ?>
                      <li>
                        <button class="dropdown-item text-danger" type="button"
                          onclick="openCancelVenta(<?= (int) $v['id_venta'] ?>)">
                          <i class="bi bi-x-circle me-2"></i> Anular
                        </button>
                      </li>
                    <?php endif; ?>

                    <?php if ((string) $v['estado'] !== 'ANULADA' && (string) $v['estado'] !== 'ENTREGADA'): ?>
                      <li>
                        <button class="dropdown-item text-success" type="button"
                          onclick="openCompleteVenta(<?= (int) $v['id_venta'] ?>)">
                          <i class="bi bi-check2-circle me-2"></i> Completada
                        </button>
                      </li>
                    <?php endif; ?>

                    <?php if ((string) $v['estado'] === 'ANULADA'): ?>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li>
                        <button class="dropdown-item" type="button" onclick="openDeleteVenta(<?= (int) $v['id_venta'] ?>)">
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



<div class="modal fade" id="modalCreateVenta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content product-modal">
      <div class="modal-header">
        <h5 class="modal-title">Registrar venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

<form method="post" action="index.php?page=sales" id="frmVenta">
  <input type="hidden" name="action" value="create">
  <input type="hidden" name="sale_form_token" value="<?= h($sale_form_token ?? '') ?>">
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
          <button class="btn btn-brand" type="submit" id="btnSubmitVenta">
  <i class="bi bi-check2-circle me-1"></i> Guardar venta
</button>
        </div>
      </form>

    </div>
  </div>
</div>


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
  function openCompleteVenta(id) {
    document.getElementById('completeVentaId').value = id;
    new bootstrap.Modal(document.getElementById('modalCompleteVenta')).show();
  }

  function openDeleteVenta(id) {
    document.getElementById('deleteVentaId').value = id;
    new bootstrap.Modal(document.getElementById('modalDeleteVenta')).show();
  }

  async function openViewVenta(id) {

    document.getElementById('editIdVenta').value = '';
    document.getElementById('editCliente').value = '';
    document.getElementById('editDir').value = '';
    document.getElementById('editNota').value = '';
    setEditEnabled(false);


    const modal = new bootstrap.Modal(document.getElementById('modalViewVenta'));
    modal.show();

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
<script>
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const viewId = params.get('view');

  if (viewId) {
    openViewVenta(viewId);
  }
});
</script>
<script>
  (function () {
    const form = document.getElementById('frmVenta');
    const btn = document.getElementById('btnSubmitVenta');

    if (!form || !btn) return;

    let sending = false;

    form.addEventListener('submit', function (e) {
      if (sending) {
        e.preventDefault();
        return false;
      }

      const items = form.querySelectorAll('input[name="id_producto[]"]');
      if (!items.length) {
        e.preventDefault();
        if (typeof window.showToast === 'function') {
          window.showToast('error', 'Agregá al menos un producto antes de guardar la venta.');
        } else {
          alert('Agregá al menos un producto antes de guardar la venta.');
        }
        return false;
      }

      sending = true;
      btn.disabled = true;
      btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
    });
  })();
</script>
