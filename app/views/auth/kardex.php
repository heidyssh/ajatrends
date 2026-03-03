<?php
$filters = $viewData['filters'] ?? [];
$products = $viewData['products'] ?? [];
$res = $viewData['resumen'] ?? [];
$movs = $viewData['movs'] ?? [];

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
function money($n)
{
    return 'L. ' . number_format((float) $n, 2);
}

$q = $filters['q'] ?? '';
$tipo = $filters['tipo'] ?? 'TODOS';
$from = $filters['from'] ?? '';
$to = $filters['to'] ?? '';
$idp = (int) ($filters['id_producto'] ?? 0);

function tipoPillClass(string $tipo): string
{
    $t = strtoupper($tipo);
    if (str_starts_with($t, 'ENTRADA'))
        return 'status-pill st-ok';
    if (str_starts_with($t, 'SALIDA'))
        return 'status-pill st-danger';
    return 'status-pill st-neutral';
}
?>

<div class="products-page page-fade kardex-page">

    <div class="cardx mb-4">
        <div class="hd purchases-toolbar">
            <div class="toolbar-left">
                <div class="fw-bold title">Kardex · Movimientos de inventario</div>
                <div class="subtitle">Auditoría por producto: compras, ventas, anulaciones y ajustes.</div>
            </div>
            <div class="toolbar-right">
                <a class="btn btn-light btn-sm" href="index.php?page=products">
                    <i class="bi bi-box-seam me-1"></i> Ver productos
                </a>
            </div>
        </div>

        <div class="bd">
            <form class="kardex-topbar" method="get" action="index.php">
                <input type="hidden" name="page" value="kardex">

                <!-- Fila compacta (siempre visible) -->
                <div class="kx-row">
                    <div class="kx-search">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input class="form-control" name="q" value="<?= h($q) ?>"
                                placeholder="Buscar: SKU, producto, nota...">
                        </div>
                    </div>

                    <div class="kx-product">
                        <select class="form-select form-select-sm" name="id_producto" aria-label="Producto">
                            <option value="0">Producto: Todos</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= (int) $p['id_producto'] ?>" <?= $idp === (int) $p['id_producto'] ? 'selected' : '' ?>>
                                    <?= h($p['nombre']) ?> (<?= h($p['sku']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="kx-actions">
                        <button class="btn btn-brand btn-sm" title="Aplicar filtros">
                            <i class="bi bi-funnel me-1"></i> Aplicar
                        </button>

                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse"
                            data-bs-target="#kxMore" aria-expanded="false" aria-controls="kxMore" title="Más filtros">
                            <i class="bi bi-sliders me-1"></i> Más
                        </button>

                        <a class="btn btn-light btn-sm" href="index.php?page=kardex" title="Limpiar">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Links rápidos -->
                <div class="kx-quick">
                    <?php
                    $base = 'index.php?page=kardex';
                    $today = date('Y-m-d');
                    $d7 = date('Y-m-d', strtotime('-7 days'));
                    $d30 = date('Y-m-d', strtotime('-30 days'));

                    // helper simple (sin función) para armar query rápido
                    $qp = $idp ? '&id_producto=' . (int) $idp : '';
                    $qq = $q !== '' ? '&q=' . urlencode($q) : '';
                    ?>

                    <a class="kx-chip" href="<?= $base . '&from=' . $today . '&to=' . $today . $qp . $qq ?>">Hoy</a>
                    <a class="kx-chip" href="<?= $base . '&from=' . $d7 . '&to=' . $today . $qp . $qq ?>">7 días</a>
                    <a class="kx-chip" href="<?= $base . '&from=' . $d30 . '&to=' . $today . $qp . $qq ?>">30 días</a>

                    <span class="kx-sep"></span>

                    <a class="kx-chip" href="<?= $base . '&tipo=ENTRADA_COMPRA' . $qp . $qq ?>">Entradas</a>
                    <a class="kx-chip" href="<?= $base . '&tipo=SALIDA_VENTA' . $qp . $qq ?>">Salidas</a>

                    <span class="kx-sep"></span>

                    <a class="kx-chip" href="index.php?page=purchases"><i class="bi bi-bag me-1"></i>Compras</a>
                    <a class="kx-chip" href="index.php?page=sales"><i class="bi bi-receipt me-1"></i>Ventas</a>
                </div>

                <!-- Más filtros (colapsable) -->
                <div class="collapse kx-more" id="kxMore">
                    <div class="kx-more-grid">
                        <div>
                            <label class="form-label">Tipo</label>
                            <select class="form-select form-select-sm" name="tipo">
                                <?php foreach (['TODOS', 'ENTRADA_COMPRA', 'SALIDA_VENTA', 'ENTRADA_ANULACION_VE', 'SALIDA_ANULA_COMPRA'] as $op): ?>
                                    <option value="<?= h($op) ?>" <?= $tipo === $op ? 'selected' : '' ?>><?= h($op) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Desde</label>
                            <input type="date" class="form-control form-control-sm" name="from" value="<?= h($from) ?>">
                        </div>

                        <div>
                            <label class="form-label">Hasta</label>
                            <input type="date" class="form-control form-control-sm" name="to" value="<?= h($to) ?>">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="cardx">
                <div class="bd">
                    <div class="small text-muted">Movimientos</div>
                    <div class="fw-bold" style="font-size:1.35rem;"><?= (int) ($res['movimientos'] ?? 0) ?></div>
                    <div class="small text-muted">Filtrados por tu rango</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="cardx">
                <div class="bd">
                    <div class="small text-muted">Entradas</div>
                    <div class="fw-bold" style="font-size:1.35rem;"><?= (int) ($res['entradas'] ?? 0) ?></div>
                    <div class="small text-muted">Unidades que subieron stock</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="cardx">
                <div class="bd">
                    <div class="small text-muted">Salidas</div>
                    <div class="fw-bold" style="font-size:1.35rem;"><?= (int) ($res['salidas'] ?? 0) ?></div>
                    <div class="small text-muted">Unidades que bajaron stock</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="cardx">
                <div class="bd">
                    <div class="small text-muted">Valor mov.*</div>
                    <div class="fw-bold" style="font-size:1.35rem;"><?= money($res['valor_mov'] ?? 0) ?></div>
                    <div class="small text-muted">* basado en costo_unit</div>
                </div>
            </div>
        </div>
    </div>
    <?php
$entr = (int)($res['entradas'] ?? 0);
$sal  = (int)($res['salidas'] ?? 0);
$mx = max(1, $entr, $sal);
$pe = min(100, ($entr / $mx) * 100);
$ps = min(100, ($sal / $mx) * 100);
?>

<div class="cardx mb-4">
  <div class="hd d-flex align-items-center justify-content-between">
    <div>
      <div class="fw-bold">Resumen visual</div>
      <div class="small text-muted">Entradas vs salidas (según filtros).</div>
    </div>
    <span class="badge badge-soft">Mini chart</span>
  </div>

  <div class="bd">
    <div class="kx-mini">
      <div class="kx-mini-row">
        <div class="kx-mini-lbl">Entradas</div>
        <div class="kx-mini-bar"><div class="kx-mini-fill" style="width: <?= number_format($pe,2) ?>%"></div></div>
        <div class="kx-mini-val"><?= $entr ?></div>
      </div>

      <div class="kx-mini-row">
        <div class="kx-mini-lbl">Salidas</div>
        <div class="kx-mini-bar danger"><div class="kx-mini-fill" style="width: <?= number_format($ps,2) ?>%"></div></div>
        <div class="kx-mini-val"><?= $sal ?></div>
      </div>
    </div>
  </div>
</div>
    <div class="cardx">
        <div class="hd d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-bold">Historial</div>
                <div class="small text-muted">Abrí un movimiento para ver el detalle por producto.</div>
            </div>
        </div>

        <div class="bd">
            <?php if (!$movs): ?>
                <div class="text-center text-muted py-4">Sin movimientos con esos filtros.</div>
            <?php else: ?>
                <div class="accordion" id="kardexAcc">
                    <?php foreach ($movs as $m):
                        $mid = (int) $m['id_mov'];
                        $tipoTxt = (string) ($m['tipo'] ?? '');
                        $pill = tipoPillClass($tipoTxt);
                        $hdrId = 'kxH' . $mid;
                        $colId = 'kxC' . $mid;

                        $ref = trim((string) ($m['ref_tabla'] ?? ''));
                        $refId = (int) ($m['ref_id'] ?? 0);
                        $refLabel = $ref !== '' ? ($ref . ' #' . $refId) : '';
                        $refLink = ($ref === 'ventas') ? 'index.php?page=sales' : (($ref === 'compras') ? 'index.php?page=purchases' : '#');
                        ?>

                        <div class="accordion-item kardex-item">
                            <h2 class="accordion-header" id="<?= h($hdrId) ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#<?= h($colId) ?>" aria-expanded="false" aria-controls="<?= h($colId) ?>">

                                    <div class="krow">
                                        <div class="kleft">
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                <span class="<?= h($pill) ?>"><?= h($tipoTxt) ?></span>
                                                <span class="kid">Mov #<?= $mid ?></span>
                                                <span class="kdate"><?= h($m['fecha']) ?></span>
                                            </div>
                                            <div class="knote text-muted small"><?= h($m['nota'] ?? '') ?></div>
                                        </div>

                                        <div class="kmeta">
                                            <?php if ($refLabel !== ''): ?>
                                                <a class="kref" href="<?= h($refLink) ?>" title="Ir a <?= h($ref) ?>">
                                                    <i class="bi bi-link-45deg me-1"></i><?= h($refLabel) ?>
                                                </a>
                                            <?php endif; ?>

                                            <div class="knums">
                                                <span class="kchip">Entradas: <b><?= (int) ($m['entradas'] ?? 0) ?></b></span>
                                                <span class="kchip">Salidas: <b><?= (int) ($m['salidas'] ?? 0) ?></b></span>
                                                <span class="kchip">Items: <b><?= (int) ($m['lineas'] ?? 0) ?></b></span>
                                            </div>
                                        </div>
                                    </div>

                                </button>
                            </h2>

                            <div id="<?= h($colId) ?>" class="accordion-collapse collapse" aria-labelledby="<?= h($hdrId) ?>"
                                data-bs-parent="#kardexAcc">
                                <div class="accordion-body">
                                    <div class="small text-muted mb-2">Usuario: <b><?= h($m['usuario'] ?? '—') ?></b></div>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle kardex-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>SKU</th>
                                                    <th>Producto</th>
                                                    <th class="text-end">Cant.</th>
                                                    <th class="text-end">Costo</th>
                                                    <th class="text-end">Antes</th>
                                                    <th class="text-end">Después</th>
                                                    <th class="text-end">Valor*</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (($m['items'] ?? []) as $it):
                                                    $cant = (int) ($it['cantidad'] ?? 0);
                                                    $isIn = $cant >= 0;
                                                    $val = abs($cant) * (float) ($it['costo_unit'] ?? 0);
                                                    ?>
                                                    <tr>
                                                        <td class="text-muted"><?= h($it['sku'] ?? '') ?></td>
                                                        <td><?= h($it['nombre'] ?? '') ?></td>
                                                        <td class="text-end <?= $isIn ? 'text-success' : 'text-danger' ?> fw-bold">
                                                            <?= $cant ?></td>
                                                        <td class="text-end"><?= money($it['costo_unit'] ?? 0) ?></td>
                                                        <td class="text-end"><?= (int) ($it['stock_antes'] ?? 0) ?></td>
                                                        <td class="text-end"><?= (int) ($it['stock_despues'] ?? 0) ?></td>
                                                        <td class="text-end"><?= money($val) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="small text-muted mt-2">* Valor aproximado: |cantidad| x costo_unit del
                                        movimiento.</div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>