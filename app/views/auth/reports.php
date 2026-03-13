<?php
$filters = $viewData['filters'] ?? [];
$years = $viewData['years'] ?? [];
$kpis = $viewData['kpis'] ?? [];
$monthly = $viewData['monthly'] ?? [];
$topProducts = $viewData['topProducts'] ?? [];
$byCategory = $viewData['byCategory'] ?? [];
$inventoryByType = $viewData['inventoryByType'] ?? [];
$salesRows = $viewData['salesRows'] ?? [];
$purchaseRows = $viewData['purchaseRows'] ?? [];
$inventoryRows = $viewData['inventoryRows'] ?? [];

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
function money($n)
{
    return 'L. ' . number_format((float) $n, 2);
}

$monthNames = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];
?>

<link rel="preconnect" href="https://cdn.jsdelivr.net">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

<div class="reports-page page-fade">
    <div class="cardx mb-4 report-hero">
        <div class="hd reports-toolbar">
            <div>
                <div class="fw-bold title">Centro de análisis comercial · AJA Trends</div>
                <div class="subtitle">Visualiza el rendimiento del negocio, controla inventario y exporta reportes
                    ejecutivos con una vista más clara, estética y profesional.</div>
            </div>

            <div class="reports-actions">
                <button type="button" class="btn btn-light btn-sm" id="btnExportPdf">
                    <i class="bi bi-filetype-pdf me-1"></i> PDF
                </button>
                <button type="button" class="btn btn-brand btn-sm" id="btnExportExcel">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
            </div>
        </div>

        <div class="bd">
            <form class="reports-filters filters-glass" method="get" action="index.php">
                <input type="hidden" name="page" value="reports">

                <div class="filter">
                    <label class="form-label">Año</label>
                    <select class="form-select form-select-sm" name="year">
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y ?>" <?= (int) $filters['year'] === (int) $y ? 'selected' : '' ?>>
                                <?= (int) $y ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter">
                    <label class="form-label">Mes</label>
                    <select class="form-select form-select-sm" name="month">
                        <option value="0" <?= (int) $filters['month'] === 0 ? 'selected' : '' ?>>Todos</option>
                        <?php foreach ($monthNames as $num => $name): ?>
                            <option value="<?= $num ?>" <?= (int) $filters['month'] === $num ? 'selected' : '' ?>>
                                <?= h($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter">
                    <label class="form-label">Módulo</label>
                    <select class="form-select form-select-sm" name="module">
                        <?php foreach (['TODOS', 'VENTAS', 'COMPRAS', 'INVENTARIO'] as $mod): ?>
                            <option value="<?= h($mod) ?>" <?= ($filters['module'] ?? 'TODOS') === $mod ? 'selected' : '' ?>>
                                <?= h($mod) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter actions">
                    <button class="btn btn-brand btn-sm">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a class="btn btn-light btn-sm" href="index.php?page=reports">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reiniciar
                    </a>
                </div>
            </form>

            <div class="cardx mt-3 report-panel">
                <div class="bd d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="fw-bold" style="color:#18352e;">Resumen ejecutivo del período</div>
                        <div class="text-muted small">
                            <?= ($filters['month'] ?? 0) == 0 ? 'Vista anual consolidada' : 'Vista mensual detallada' ?>
                            · Módulo:
                            <strong><?= h($filters['module'] ?? 'TODOS') ?></strong>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-soft px-3 py-2">Año <?= (int) ($filters['year'] ?? date('Y')) ?></span>
                        <span class="badge badge-soft px-3 py-2">
                            <?= (int) ($filters['month'] ?? 0) === 0 ? 'Todos los meses' : h($monthNames[(int) $filters['month']]) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-kpi-strip mb-4">
        <div class="row g-3 flex-nowrap flex-lg-wrap">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="cardx report-kpi kpi-glow">
                    <div class="bd">
                        <div class="kpi-top">
                            <span class="kpi-label">Ingresos del período</span>
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="kpi-value"><?= money($kpis['total_vendido'] ?? 0) ?></div>
                        <div class="kpi-meta">
                            <?= (int) ($kpis['ventas'] ?? 0) ?> pedidos procesados · Ticket promedio:
                            <?= money($kpis['ticket_promedio'] ?? 0) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="cardx report-kpi">
                    <div class="bd">
                        <div class="kpi-top">
                            <span class="kpi-label">Inversión en reposición</span>
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div class="kpi-value"><?= money($kpis['total_comprado'] ?? 0) ?></div>
                        <div class="kpi-meta">
                            <?= (int) ($kpis['compras'] ?? 0) ?> reposiciones ·
                            <?= (int) ($kpis['unidades_compradas'] ?? 0) ?> unidades abastecidas
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="cardx report-kpi">
                    <div class="bd">
                        <div class="kpi-top">
                            <span class="kpi-label">Margen estimado</span>
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="kpi-value"><?= money($kpis['utilidad_estimada'] ?? 0) ?></div>
                        <div class="kpi-meta">
                            <?= (int) ($kpis['unidades'] ?? 0) ?> unidades desplazadas en ventas
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="cardx report-kpi">
                    <div class="bd">
                        <div class="kpi-top">
                            <span class="kpi-label">Actividad de stock</span>
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="kpi-value"><?= (int) ($kpis['movimientos'] ?? 0) ?></div>
                        <div class="kpi-meta">
                            Entradas registradas: <?= (int) ($kpis['entradas'] ?? 0) ?> · Salidas registradas:
                            <?= (int) ($kpis['salidas'] ?? 0) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="cardx report-panel">
                <div class="hd">
                    <div class="fw-bold">Rendimiento mensual del negocio</div>
                    <small>Comparativa estratégica entre ventas y compras del año seleccionado</small>
                </div>
                <div class="bd">
                    <canvas id="chartMonthly" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="cardx report-panel">
                <div class="hd">
                    <div class="fw-bold">Composición de ingresos por categoría</div>
                    <small>Qué línea de producto aporta más al negocio</small>
                </div>
                <div class="bd">
                    <canvas id="chartCategory" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="cardx mb-4 report-slider-card">
        <div class="hd d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="fw-bold">Visualización analítica</div>
                <small>Explora los indicadores clave del negocio sin saturar la vista</small>
            </div>

            <div class="report-carousel-controls">
                <button class="btn btn-light btn-sm" type="button" data-bs-target="#reportsCarousel"
                    data-bs-slide="prev">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="btn btn-brand btn-sm" type="button" data-bs-target="#reportsCarousel"
                    data-bs-slide="next">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="bd">
            <div id="reportsCarousel" class="carousel slide" data-bs-ride="false">
                <div class="carousel-inner">

                    <div class="carousel-item active">
                        <div class="row g-3">
                            <div class="col-12 col-xl-8">
                                <div class="cardx report-panel h-100">
                                    <div class="hd">
                                        <div class="fw-bold">Rendimiento mensual del negocio</div>
                                        <small>Comparativa estratégica entre ventas y compras del año
                                            seleccionado</small>
                                    </div>
                                    <div class="bd">
                                        <canvas id="chartMonthly" height="115"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-4">
                                <div class="cardx report-panel h-100">
                                    <div class="hd">
                                        <div class="fw-bold">Composición de ingresos por categoría</div>
                                        <small>Qué línea de producto aporta más al negocio</small>
                                    </div>
                                    <div class="bd">
                                        <div id="categoryChartWrap">
                                            <canvas id="chartCategory" height="220"></canvas>
                                        </div>
                                        <div id="categoryEmpty" class="report-empty d-none">
                                            <i class="bi bi-pie-chart"></i>
                                            <div class="fw-bold">Sin datos de categorías</div>
                                            <small>No hay ventas categorizadas para este período</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="row g-3">
                            <div class="col-12 col-xl-6">
                                <div class="cardx report-panel h-100">
                                    <div class="hd">
                                        <div class="fw-bold">Productos con mejor desempeño</div>
                                        <small>Los productos que más sostienen el ingreso del período</small>
                                    </div>
                                    <div class="bd">
                                        <div id="topProductsWrap">
                                            <canvas id="chartTopProducts" height="180"></canvas>
                                        </div>
                                        <div id="topProductsEmpty" class="report-empty d-none">
                                            <i class="bi bi-bar-chart"></i>
                                            <div class="fw-bold">Sin productos destacados</div>
                                            <small>No hay registros suficientes para este período</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="cardx report-panel h-100">
                                    <div class="hd">
                                        <div class="fw-bold">Trazabilidad del inventario</div>
                                        <small>Entradas, salidas y ajustes para entender el flujo de stock</small>
                                    </div>
                                    <div class="bd">
                                        <div id="inventoryWrap">
                                            <canvas id="chartInventoryType" height="180"></canvas>
                                        </div>
                                        <div id="inventoryEmpty" class="report-empty d-none">
                                            <i class="bi bi-box-seam"></i>
                                            <div class="fw-bold">Sin movimientos de inventario</div>
                                            <small>No hay movimientos registrados en este período</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cardx mb-4 report-table-card">
        <div class="hd d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="fw-bold">Detalle del período</div>
                <small>Tablas listas para exportación</small>
            </div>
            <?php $activeModule = $filters['module'] ?? 'TODOS'; ?>
            <ul class="nav nav-pills report-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $activeModule === 'COMPRAS' || $activeModule === 'INVENTARIO' ? '' : 'active' ?>"
                        data-bs-toggle="pill" data-bs-target="#tabVentas" type="button">Ventas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeModule === 'COMPRAS' ? 'active' : '' ?>" data-bs-toggle="pill"
                        data-bs-target="#tabCompras" type="button">Compras</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeModule === 'INVENTARIO' ? 'active' : '' ?>" data-bs-toggle="pill"
                        data-bs-target="#tabInventario" type="button">Inventario</button>
                </li>
            </ul>
        </div>

        <div class="bd">
            <div class="tab-content">
                <div class="tab-pane fade <?= $activeModule === 'TODOS' || $activeModule === 'VENTAS' ? 'show active' : '' ?>"
                    id="tabVentas">
                    <div class="table-responsive">
                        <table class="table align-middle report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$salesRows): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Sin ventas en este período.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($salesRows as $r): ?>
                                        <tr>
                                            <td>#<?= (int) $r['id_venta'] ?></td>
                                            <td><?= h($r['fecha']) ?></td>
                                            <td><?= h($r['cliente']) ?></td>
                                            <td><span class="badge badge-soft"><?= h($r['estado']) ?></span></td>
                                            <td><?= money($r['total']) ?></td>
                                            <td><?= h($r['nota']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeModule === 'COMPRAS' ? 'show active' : '' ?>" id="tabCompras">
                    <div class="table-responsive">
                        <table class="table align-middle report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$purchaseRows): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Sin compras en este período.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($purchaseRows as $r): ?>
                                        <tr>
                                            <td>#<?= (int) $r['id_compra'] ?></td>
                                            <td><?= h($r['fecha']) ?></td>
                                            <td><?= h($r['proveedor']) ?></td>
                                            <td><span class="badge badge-soft"><?= h($r['estado']) ?></span></td>
                                            <td><?= money($r['total']) ?></td>
                                            <td><?= h($r['nota']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeModule === 'INVENTARIO' ? 'show active' : '' ?>"
                    id="tabInventario">
                    <div class="table-responsive">
                        <table class="table align-middle report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Antes</th>
                                    <th>Después</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$inventoryRows): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Sin movimientos en este período.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inventoryRows as $r): ?>
                                        <tr>
                                            <td>#<?= (int) $r['id_mov'] ?></td>
                                            <td><?= h($r['fecha']) ?></td>
                                            <td><span class="badge badge-soft"><?= h($r['tipo']) ?></span></td>
                                            <td><?= h($r['producto']) ?></td>
                                            <td><?= (int) $r['cantidad'] ?></td>
                                            <td><?= (int) $r['stock_antes'] ?></td>
                                            <td><?= (int) $r['stock_despues'] ?></td>
                                            <td><?= h($r['nota']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const monthly = <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>;
        const byCategory = <?= json_encode($byCategory, JSON_UNESCAPED_UNICODE) ?>;
        const topProducts = <?= json_encode($topProducts, JSON_UNESCAPED_UNICODE) ?>;
        const inventoryByType = <?= json_encode($inventoryByType, JSON_UNESCAPED_UNICODE) ?>;

        const salesRows = <?= json_encode($salesRows, JSON_UNESCAPED_UNICODE) ?>;
        const purchaseRows = <?= json_encode($purchaseRows, JSON_UNESCAPED_UNICODE) ?>;
        const inventoryRows = <?= json_encode($inventoryRows, JSON_UNESCAPED_UNICODE) ?>;
        const kpis = <?= json_encode($kpis, JSON_UNESCAPED_UNICODE) ?>;
        const filters = <?= json_encode($filters, JSON_UNESCAPED_UNICODE) ?>;

        const monthLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const monthNamesFull = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        const currentMonthNumber = Number(filters.month || 0);
        const currentMonthLabel = currentMonthNumber === 0
            ? 'Todos los meses'
            : monthNamesFull[currentMonthNumber - 1];

        const currentModuleLabel = filters.module || 'TODOS';
        const root = getComputedStyle(document.documentElement);

        const AJA = {
            primary: (root.getPropertyValue('--aja-green') || '#1f6f5c').trim(),
            primarySoft: 'rgba(31, 111, 92, 0.16)',
            secondary: (root.getPropertyValue('--aja-green-soft') || '#2e8b73').trim(),
            secondarySoft: 'rgba(46, 139, 115, 0.16)',
            accent: '#8fb9ae',
            accentSoft: 'rgba(143, 185, 174, 0.18)',
            warm: '#d8c3a5',
            warmSoft: 'rgba(216, 195, 165, 0.20)',
            text: '#5f6b73',
            dark: '#18352e',
            grid: 'rgba(31, 111, 92, 0.10)',
            line2: '#7aa79c'
        };
        new Chart(document.getElementById('chartMonthly'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Ventas',
                        data: monthly.map(x => Number(x.ventas || 0)),
                        tension: 0.4,
                        fill: true,
                        borderColor: AJA.primary,
                        backgroundColor: AJA.primarySoft,
                        pointBackgroundColor: AJA.primary,
                        pointBorderColor: '#fff',
                        pointRadius: 4
                    },
                    {
                        label: 'Compras',
                        data: monthly.map(x => Number(x.compras || 0)),
                        tension: 0.4,
                        fill: true,
                        borderColor: AJA.warm,
                        backgroundColor: AJA.warmSoft,
                        pointBackgroundColor: AJA.warm,
                        pointBorderColor: '#fff',
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: AJA.text,
                            usePointStyle: true
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: AJA.text },
                        grid: { color: AJA.grid }
                    },
                    y: {
                        ticks: { color: AJA.text },
                        grid: { color: AJA.grid }
                    }
                }
            }
        });

        const categoryCanvas = document.getElementById('chartCategory');
        if (categoryCanvas && byCategory.length) {
            new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: byCategory.map(x => x.categoria),
                    datasets: [{
                        data: byCategory.map(x => Number(x.total || 0)),
                        backgroundColor: [
                            AJA.primary,
                            AJA.secondary,
                            AJA.line2,
                            AJA.accent,
                            AJA.warm,
                            '#b7cec7'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: AJA.text,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        } else {
            document.getElementById('categoryChartWrap')?.classList.add('d-none');
            document.getElementById('categoryEmpty')?.classList.remove('d-none');
        }

        const topProductsCanvas = document.getElementById('chartTopProducts');
        if (topProductsCanvas && topProducts.length) {
            new Chart(topProductsCanvas, {
                type: 'bar',
                data: {
                    labels: topProducts.map(x => x.nombre),
                    datasets: [{
                        label: 'Ingreso generado',
                        data: topProducts.map(x => Number(x.ingreso || 0)),
                        backgroundColor: 'rgba(31, 111, 92, 0.18)',
                        borderColor: AJA.primary,
                        borderWidth: 2,
                        borderRadius: 12,
                        hoverBackgroundColor: 'rgba(31, 111, 92, 0.28)',
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: { color: AJA.text },
                            grid: { color: AJA.grid }
                        },
                        y: {
                            ticks: { color: AJA.text },
                            grid: { display: false }
                        }
                    }
                }
            });
        } else {
            document.getElementById('topProductsWrap')?.classList.add('d-none');
            document.getElementById('topProductsEmpty')?.classList.remove('d-none');
        }

        const inventoryCanvas = document.getElementById('chartInventoryType');
        if (inventoryCanvas && inventoryByType.length) {
            new Chart(inventoryCanvas, {
                type: 'bar',
                data: {
                    labels: inventoryByType.map(x => x.tipo),
                    datasets: [{
                        label: 'Cantidad de movimientos',
                        data: inventoryByType.map(x => Number(x.movimientos || 0)),
                        backgroundColor: [
                            AJA.primary,
                            AJA.secondary,
                            AJA.accent,
                            AJA.warm,
                            '#b7cec7',
                            '#99a8a3'
                        ],
                        borderRadius: 10,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: AJA.text,
                                maxRotation: 15,
                                minRotation: 0
                            },
                            grid: { display: false }
                        },
                        y: {
                            ticks: { color: AJA.text },
                            grid: { color: AJA.grid }
                        }
                    }
                }
            });
        } else {
            document.getElementById('inventoryWrap')?.classList.add('d-none');
            document.getElementById('inventoryEmpty')?.classList.remove('d-none');
        }

        document.getElementById('btnExportExcel')?.addEventListener('click', () => {
            if (typeof XLSX === 'undefined') {
                alert('No se pudo cargar la librería de Excel. Revisa el script de xlsx.');
                return;
            }

            const wb = XLSX.utils.book_new();

            const moneyFmt = '"L." #,##0.00';
            const borderColor = 'D9E5E1';

            const styles = {
                title: {
                    font: { bold: true, sz: 16, color: { rgb: '18352E' } },
                    fill: { fgColor: { rgb: 'EEF6F3' } },
                    alignment: { horizontal: 'center', vertical: 'center' }
                },
                subtitle: {
                    font: { italic: true, sz: 10, color: { rgb: '5F6B73' } },
                    alignment: { horizontal: 'center' }
                },
                section: {
                    font: { bold: true, sz: 12, color: { rgb: '18352E' } },
                    fill: { fgColor: { rgb: 'F6FAF8' } },
                    alignment: { horizontal: 'left', vertical: 'center' },
                    border: {
                        top: { style: 'thin', color: { rgb: borderColor } },
                        bottom: { style: 'thin', color: { rgb: borderColor } }
                    }
                },
                head: {
                    font: { bold: true, color: { rgb: 'FFFFFF' } },
                    fill: { fgColor: { rgb: '1F6F5C' } },
                    alignment: { horizontal: 'center', vertical: 'center' },
                    border: {
                        top: { style: 'thin', color: { rgb: '1F6F5C' } },
                        bottom: { style: 'thin', color: { rgb: '1F6F5C' } },
                        left: { style: 'thin', color: { rgb: '1F6F5C' } },
                        right: { style: 'thin', color: { rgb: '1F6F5C' } }
                    }
                },
                cell: {
                    alignment: { vertical: 'center' },
                    border: {
                        top: { style: 'thin', color: { rgb: borderColor } },
                        bottom: { style: 'thin', color: { rgb: borderColor } },
                        left: { style: 'thin', color: { rgb: borderColor } },
                        right: { style: 'thin', color: { rgb: borderColor } }
                    }
                },
                money: {
                    numFmt: moneyFmt,
                    alignment: { vertical: 'center', horizontal: 'right' },
                    border: {
                        top: { style: 'thin', color: { rgb: borderColor } },
                        bottom: { style: 'thin', color: { rgb: borderColor } },
                        left: { style: 'thin', color: { rgb: borderColor } },
                        right: { style: 'thin', color: { rgb: borderColor } }
                    }
                },
                totalLabel: {
                    font: { bold: true, color: { rgb: '18352E' } },
                    fill: { fgColor: { rgb: 'EEF6F3' } },
                    alignment: { horizontal: 'right' },
                    border: {
                        top: { style: 'thin', color: { rgb: borderColor } },
                        bottom: { style: 'thin', color: { rgb: borderColor } },
                        left: { style: 'thin', color: { rgb: borderColor } },
                        right: { style: 'thin', color: { rgb: borderColor } }
                    }
                },
                totalMoney: {
                    font: { bold: true, color: { rgb: '18352E' } },
                    fill: { fgColor: { rgb: 'EEF6F3' } },
                    numFmt: moneyFmt,
                    alignment: { horizontal: 'right' },
                    border: {
                        top: { style: 'thin', color: { rgb: borderColor } },
                        bottom: { style: 'thin', color: { rgb: borderColor } },
                        left: { style: 'thin', color: { rgb: borderColor } },
                        right: { style: 'thin', color: { rgb: borderColor } }
                    }
                }
            };

            const ws = XLSX.utils.aoa_to_sheet([]);

            ws['!cols'] = [
                { wch: 22 },
                { wch: 22 },
                { wch: 18 },
                { wch: 18 },
                { wch: 18 },
                { wch: 18 }
            ];

            ws['!merges'] = [
                XLSX.utils.decode_range('A1:F1'),
                XLSX.utils.decode_range('A2:F2'),
                XLSX.utils.decode_range('A4:F4'),
                XLSX.utils.decode_range('A11:F11'),
                XLSX.utils.decode_range('A17:F17'),
                XLSX.utils.decode_range('A33:F33'),
                XLSX.utils.decode_range('A50:H50'),
                XLSX.utils.decode_range('A67:H67')
            ];

            XLSX.utils.sheet_add_aoa(ws, [
                ['AJA Trends · Reporte Ejecutivo'],
                [`Período: ${filters.year} · ${currentMonthLabel} · Módulo: ${currentModuleLabel}`],
                [],
                ['Resumen ejecutivo'],
                ['Indicador', 'Valor'],
                ['Ingresos del período', Number(kpis.total_vendido || 0)],
                ['Inversión en reposición', Number(kpis.total_comprado || 0)],
                ['Margen estimado', Number(kpis.utilidad_estimada || 0)],
                ['Actividad de stock', Number(kpis.movimientos || 0)],
                ['Ticket promedio', Number(kpis.ticket_promedio || 0)],
                ['Serie mensual'],
                ['Mes', 'Ventas', 'Compras', 'Balance'],
                ...monthly.map((x, idx) => [
                    monthNamesFull[idx],
                    Number(x.ventas || 0),
                    Number(x.compras || 0),
                    null
                ]),
                ['Totales', null, null, null],
                [],
                ['Detalle de ventas'],
                ['# Venta', 'Fecha', 'Cliente', 'Estado', 'Total', 'Nota'],
                ...salesRows.map(x => [
                    Number(x.id_venta || 0),
                    x.fecha || '',
                    x.cliente || '',
                    x.estado || '',
                    Number(x.total || 0),
                    x.nota || ''
                ]),
                ['Total ventas', '', '', '', null, ''],
                [],
                ['Detalle de compras'],
                ['# Compra', 'Fecha', 'Proveedor', 'Estado', 'Total', 'Nota'],
                ...purchaseRows.map(x => [
                    Number(x.id_compra || 0),
                    x.fecha || '',
                    x.proveedor || '',
                    x.estado || '',
                    Number(x.total || 0),
                    x.nota || ''
                ]),
                ['Total compras', '', '', '', null, ''],
                [],
                ['Detalle de inventario'],
                ['# Movimiento', 'Fecha', 'Tipo', 'Producto', 'Cantidad', 'Stock antes', 'Stock después', 'Nota'],
                ...inventoryRows.map(x => [
                    Number(x.id_mov || 0),
                    x.fecha || '',
                    x.tipo || '',
                    x.producto || '',
                    Number(x.cantidad || 0),
                    Number(x.stock_antes || 0),
                    Number(x.stock_despues || 0),
                    x.nota || ''
                ])
            ], { origin: 'A1' });

            const setStyle = (cell, style) => {
                if (ws[cell]) ws[cell].s = style;
            };

            const range = XLSX.utils.decode_range(ws['!ref']);

            for (let r = range.s.r; r <= range.e.r; r++) {
                for (let c = range.s.c; c <= range.e.c; c++) {
                    const cellRef = XLSX.utils.encode_cell({ r, c });
                    if (ws[cellRef] && !ws[cellRef].s) ws[cellRef].s = styles.cell;
                }
            }

            setStyle('A1', styles.title);
            setStyle('A2', styles.subtitle);
            setStyle('A4', styles.section);
            setStyle('A11', styles.section);
            setStyle('A17', styles.section);
            setStyle('A33', styles.section);
            setStyle('A50', styles.section);
            setStyle('A67', styles.section);

            ['A5', 'B5'].forEach(c => setStyle(c, styles.head));
            ['A12', 'B12', 'C12', 'D12'].forEach(c => setStyle(c, styles.head));
            ['A18', 'B18', 'C18', 'D18', 'E18', 'F18'].forEach(c => setStyle(c, styles.head));
            ['A34', 'B34', 'C34', 'D34', 'E34', 'F34'].forEach(c => setStyle(c, styles.head));
            ['A51', 'B51', 'C51', 'D51', 'E51', 'F51', 'G51', 'H51'].forEach(c => setStyle(c, styles.head));

            ['B6', 'B7', 'B8', 'B9', 'B10'].forEach(c => setStyle(c, styles.money));

            const monthlyStart = 13;
            monthly.forEach((_, idx) => {
                const row = monthlyStart + idx;
                setStyle(`B${row}`, styles.money);
                setStyle(`C${row}`, styles.money);
                ws[`D${row}`] = {
                    t: 'n',
                    f: `B${row}-C${row}`,
                    s: styles.money
                };
            });

            const monthlyTotalRow = monthlyStart + monthly.length;
            ws[`B${monthlyTotalRow}`] = { t: 'n', f: `SUM(B${monthlyStart}:B${monthlyTotalRow - 1})`, s: styles.totalMoney };
            ws[`C${monthlyTotalRow}`] = { t: 'n', f: `SUM(C${monthlyStart}:C${monthlyTotalRow - 1})`, s: styles.totalMoney };
            ws[`D${monthlyTotalRow}`] = { t: 'n', f: `B${monthlyTotalRow}-C${monthlyTotalRow}`, s: styles.totalMoney };
            setStyle(`A${monthlyTotalRow}`, styles.totalLabel);

            const salesStart = 19;
            salesRows.forEach((_, idx) => setStyle(`E${salesStart + idx}`, styles.money));
            const salesTotalRow = salesStart + salesRows.length;
            ws[`E${salesTotalRow}`] = { t: 'n', f: salesRows.length ? `SUM(E${salesStart}:E${salesTotalRow - 1})` : '0', s: styles.totalMoney };
            setStyle(`A${salesTotalRow}`, styles.totalLabel);

            const purchasesStart = 35;
            purchaseRows.forEach((_, idx) => setStyle(`E${purchasesStart + idx}`, styles.money));
            const purchasesTotalRow = purchasesStart + purchaseRows.length;
            ws[`E${purchasesTotalRow}`] = { t: 'n', f: purchaseRows.length ? `SUM(E${purchasesStart}:E${purchasesTotalRow - 1})` : '0', s: styles.totalMoney };
            setStyle(`A${purchasesTotalRow}`, styles.totalLabel);

            XLSX.utils.book_append_sheet(wb, ws, 'Reporte');

            const wsCat = XLSX.utils.json_to_sheet(
                byCategory.map(x => ({
                    Categoria: x.categoria || '',
                    Total: Number(x.total || 0)
                }))
            );
            wsCat['!cols'] = [{ wch: 26 }, { wch: 16 }];
            XLSX.utils.book_append_sheet(wb, wsCat, 'Categorias');

            const wsTop = XLSX.utils.json_to_sheet(
                topProducts.map(x => ({
                    Producto: x.nombre || '',
                    Ingreso: Number(x.ingreso || 0)
                }))
            );
            wsTop['!cols'] = [{ wch: 32 }, { wch: 16 }];
            XLSX.utils.book_append_sheet(wb, wsTop, 'TopProductos');

            XLSX.writeFile(wb, `AJA_Reportes_${filters.year}_${currentMonthNumber || 'ALL'}.xlsx`);
        });

        document.getElementById('btnExportPdf')?.addEventListener('click', () => {
            if (!window.jspdf) {
                alert('No se pudo cargar la librería de PDF.');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
            const pageWidth = doc.internal.pageSize.getWidth();

            const colors = {
                dark: [24, 53, 46],
                green: [31, 111, 92],
                soft: [238, 246, 243],
                text: [95, 107, 115],
                line: [217, 229, 225]
            };

            doc.setFillColor(...colors.soft);
            doc.roundedRect(36, 30, pageWidth - 72, 72, 14, 14, 'F');

            doc.setTextColor(...colors.dark);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(18);
            doc.text('AJA Trends · Reporte Ejecutivo', 52, 58);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.setTextColor(...colors.text);
            doc.text(`Período: ${filters.year} · ${currentMonthLabel} · Módulo: ${currentModuleLabel}`, 52, 78);

            doc.autoTable({
                startY: 120,
                head: [['Indicador', 'Valor']],
                body: [
                    ['Ingresos del período', `L. ${Number(kpis.total_vendido || 0).toFixed(2)}`],
                    ['Inversión en reposición', `L. ${Number(kpis.total_comprado || 0).toFixed(2)}`],
                    ['Margen estimado', `L. ${Number(kpis.utilidad_estimada || 0).toFixed(2)}`],
                    ['Actividad de stock', String(kpis.movimientos || 0)],
                    ['Ticket promedio', `L. ${Number(kpis.ticket_promedio || 0).toFixed(2)}`]
                ],
                theme: 'grid',
                styles: {
                    fontSize: 9,
                    cellPadding: 7,
                    textColor: colors.dark,
                    lineColor: colors.line,
                    lineWidth: 0.6
                },
                headStyles: {
                    fillColor: colors.green,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                    halign: 'center'
                },
                alternateRowStyles: {
                    fillColor: [250, 252, 251]
                }
            });

            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 18,
                head: [['Mes', 'Ventas', 'Compras', 'Balance']],
                body: monthly.map((x, i) => {
                    const ventas = Number(x.ventas || 0);
                    const compras = Number(x.compras || 0);
                    return [
                        monthNamesFull[i],
                        `L. ${ventas.toFixed(2)}`,
                        `L. ${compras.toFixed(2)}`,
                        `L. ${(ventas - compras).toFixed(2)}`
                    ];
                }),
                theme: 'grid',
                styles: {
                    fontSize: 8.7,
                    cellPadding: 6,
                    textColor: colors.dark,
                    lineColor: colors.line,
                    lineWidth: 0.5
                },
                headStyles: {
                    fillColor: colors.green,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [250, 252, 251]
                }
            });

            doc.addPage();

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(15);
            doc.setTextColor(...colors.dark);
            doc.text('Detalle de ventas del período', 40, 46);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9.5);
            doc.setTextColor(...colors.text);
            doc.text('Resumen operacional de ventas registradas.', 40, 62);

            doc.autoTable({
                startY: 80,
                head: [['#', 'Fecha', 'Cliente', 'Estado', 'Total']],
                body: salesRows.length
                    ? salesRows.slice(0, 30).map(x => [
                        x.id_venta,
                        x.fecha,
                        x.cliente,
                        x.estado,
                        `L. ${Number(x.total || 0).toFixed(2)}`
                    ])
                    : [['-', '-', 'Sin registros', '-', 'L. 0.00']],
                theme: 'grid',
                styles: {
                    fontSize: 8.5,
                    cellPadding: 6,
                    textColor: colors.dark,
                    lineColor: colors.line,
                    lineWidth: 0.5
                },
                headStyles: {
                    fillColor: colors.green,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [250, 252, 251]
                }
            });

            doc.save(`AJA_Reportes_${filters.year}_${currentMonthNumber || 'ALL'}.pdf`);
        });
    })();
</script>