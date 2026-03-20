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
$lowStockRows = $viewData['lowStockRows'] ?? [];
$selectedModule = $filters['module'] ?? 'TODOS';
$showSales = $selectedModule === 'TODOS' || $selectedModule === 'VENTAS';
$showPurchases = $selectedModule === 'TODOS' || $selectedModule === 'COMPRAS';
$showInventory = $selectedModule === 'TODOS' || $selectedModule === 'INVENTARIO';
function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
function money($n)
{
    return 'L. ' . number_format((float) $n, 2);
}
function prettyType($type)
{
    $map = [
        'ENTRADA_COMPRA' => 'Entrada por compra',
        'SALIDA_VENTA' => 'Salida por venta',
        'SALIDA_ANULA_COMPRA' => 'Salida por anulación de compra',
        'ENTRADA_ANULACION_VE' => 'Entrada por anulación de venta',
        'AJUSTE_STOCK' => 'Ajuste de stock',
        'AJUSTE_POSITIVO' => 'Ajuste positivo',
        'AJUSTE_NEGATIVO' => 'Ajuste negativo'
    ];

    return $map[$type] ?? ucwords(strtolower(str_replace('_', ' ', $type)));
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
<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script>
    const REPORT_LOGO_URL = 'assets/img/logo.jpeg';
</script>
<script>
    async function loadImageAsDataURL(url) {
        const res = await fetch(url);
        if (!res.ok) {
            throw new Error(`No se pudo cargar la imagen: ${url}`);
        }

        const blob = await res.blob();

        const dataUrl = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });

        const extension = blob.type.includes('png') ? 'png' : 'jpeg';

        return {
            dataUrl,
            extension
        };
    }
</script>
<div class="reports-page page-fade">
    <div class="cardx mb-4 module-hero">
        <div class="hd reports-toolbar">
            <div>
                <div class="fw-bold title">Centro de análisis comercial</div>

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

    <div class="cardx mb-4 report-slider-card">
        <div class="hd d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="fw-bold">Visualización analítica</div>
                <small>Explora los indicadores clave del negocio</small>
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
        <div class="hd d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="fw-bold">Detalle del período</div>
                <small>Tablas listas para exportación</small>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2 reports-actions reports-actions-detail">
                <?php $activeModule = $filters['module'] ?? 'TODOS'; ?>
                <ul class="nav nav-pills report-tabs me-2" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link <?= $activeModule === 'COMPRAS' || $activeModule === 'INVENTARIO' ? '' : 'active' ?>"
                            data-bs-toggle="pill" data-bs-target="#tabVentas" type="button">Ventas</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeModule === 'COMPRAS' ? 'active' : '' ?>"
                            data-bs-toggle="pill" data-bs-target="#tabCompras" type="button">Compras</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $activeModule === 'INVENTARIO' ? 'active' : '' ?>"
                            data-bs-toggle="pill" data-bs-target="#tabInventario" type="button">Inventario</button>
                    </li>
                </ul>
                <button type="button" class="btn btn-light btn-sm" id="btnDetailPdf">
                    <i class="bi bi-filetype-pdf me-1"></i> PDF detalle
                </button>
                <button type="button" class="btn btn-brand btn-sm" id="btnDetailExcel">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel detalle
                </button>
            </div>
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
                                        <td colspan="6" class="text-center text-muted py-4">Sin ventas en este período.
                                        </td>
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
                                        <td colspan="8" class="text-center text-muted py-4">Sin movimientos en este
                                            período.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inventoryRows as $r): ?>
                                        <tr>
                                            <td>#<?= (int) $r['id_mov'] ?></td>
                                            <td><?= h($r['fecha']) ?></td>
                                            <td><span class="badge badge-soft"><?= h(prettyType($r['tipo'])) ?></span></td>
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
    <div class="cardx report-table-card report-alert-card mt-4">
        <div class="hd d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-bold">Productos con stock crítico</div>
                <small>Inventario que necesita reposición prioritaria</small>
            </div>
            <span class="badge badge-soft"><?= count($lowStockRows) ?> alerta(s)</span>
        </div>

        <div class="bd">
            <div class="table-responsive">
                <table class="table report-table align-middle">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock actual</th>
                            <th>Stock mínimo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$lowStockRows): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay productos en stock
                                    crítico.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lowStockRows as $r): ?>
                                <tr>
                                    <td><?= h($r['sku']) ?></td>
                                    <td><?= h($r['nombre']) ?></td>
                                    <td><?= h($r['categoria']) ?></td>
                                    <td><span class="badge bg-danger-subtle text-danger"><?= (int) $r['stock_actual'] ?></span>
                                    </td>
                                    <td><?= (int) $r['stock_min'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
        const lowStockRows = <?= json_encode($lowStockRows, JSON_UNESCAPED_UNICODE) ?>;
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
        const prettyInventoryType = (type) => {
            const map = {
                ENTRADA_COMPRA: 'Entrada por compra',
                SALIDA_VENTA: 'Salida por venta',
                SALIDA_ANULA_COMPRA: 'Salida por anulación de compra',
                ENTRADA_ANULACION_VE: 'Entrada por anulación de venta',
                AJUSTE_STOCK: 'Ajuste de stock',
                AJUSTE_POSITIVO: 'Ajuste positivo',
                AJUSTE_NEGATIVO: 'Ajuste negativo'
            };

            return map[type] || String(type || '')
                .replaceAll('_', ' ')
                .toLowerCase()
                .replace(/\b\w/g, l => l.toUpperCase());
        };
        const getActiveDetailTab = () => {
            const activePane = document.querySelector('.tab-content .tab-pane.show.active');
            return activePane ? activePane.id : 'tabVentas';
        };
        const getActiveDetailData = () => {
            const activeTab = getActiveDetailTab();

            if (activeTab === 'tabCompras') {
                return {
                    title: 'Detalle de Compras',
                    headers: ['#', 'Fecha', 'Proveedor', 'Estado', 'Total', 'Nota'],
                    rows: purchaseRows.map(x => [
                        x.id_compra,
                        x.fecha,
                        x.proveedor,
                        x.estado,
                        Number(x.total || 0),
                        x.nota || ''
                    ])
                };
            }

            if (activeTab === 'tabInventario') {
                return {
                    title: 'Detalle de Inventario',
                    headers: ['#', 'Fecha', 'Tipo', 'Producto', 'Cantidad', 'Antes', 'Después', 'Nota'],
                    rows: inventoryRows.map(x => [
                        x.id_mov,
                        x.fecha,
                        prettyInventoryType(x.tipo),
                        x.producto,
                        Number(x.cantidad || 0),
                        Number(x.stock_antes || 0),
                        Number(x.stock_despues || 0),
                        x.nota || ''
                    ])
                };
            }

            return {
                title: 'Detalle de Ventas',
                headers: ['#', 'Fecha', 'Cliente', 'Estado', 'Total', 'Nota'],
                rows: salesRows.map(x => [
                    x.id_venta,
                    x.fecha,
                    x.cliente,
                    x.estado,
                    Number(x.total || 0),
                    x.nota || ''
                ])
            };
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
                    labels: inventoryByType.map(x => prettyInventoryType(x.tipo)),
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

        document.getElementById('btnExportExcel')?.addEventListener('click', async () => {
            try {
                const workbook = new ExcelJS.Workbook();
                workbook.creator = 'AJA Trends';
                workbook.created = new Date();

                let logoImage = null;

                try {
                    logoImage = await loadImageAsDataURL(REPORT_LOGO_URL);
                } catch (e) {
                    console.warn('No se pudo cargar el logo para Excel', e);
                }

                const addHeader = (sheet, title, subtitle = '') => {
                    sheet.properties.defaultRowHeight = 20;
                    sheet.views = [{ state: 'frozen', ySplit: 6 }];

                    if (logoImage?.dataUrl) {
                        const imageId = workbook.addImage({
                            base64: logoImage.dataUrl,
                            extension: logoImage.extension
                        });

                        sheet.addImage(imageId, {
                            tl: { col: 0.2, row: 0.2 },
                            ext: { width: 90, height: 90 }
                        });
                    }

                    sheet.mergeCells('C1:H1');
                    sheet.getCell('C1').value = 'AJA Trends';
                    sheet.getCell('C1').font = { bold: true, size: 20, color: { argb: '18352E' } };

                    sheet.mergeCells('C2:H2');
                    sheet.getCell('C2').value = 'Sistema de inventario, ventas y análisis comercial';
                    sheet.getCell('C2').font = { size: 11, color: { argb: '5F6B73' } };

                    sheet.mergeCells('C3:H3');
                    sheet.getCell('C3').value = title;
                    sheet.getCell('C3').font = { bold: true, size: 14, color: { argb: '1F6F5C' } };

                    sheet.mergeCells('C4:H4');
                    sheet.getCell('C4').value = subtitle || `Período: ${filters.year} · ${currentMonthLabel} · Módulo: ${currentModuleLabel}`;

                    sheet.mergeCells('C5:H5');
                    sheet.getCell('C5').value = `Generado: ${new Date().toLocaleString()}`;
                };

                const styleHeaderRow = (row) => {
                    row.font = { bold: true, color: { argb: 'FFFFFF' } };
                    row.alignment = { vertical: 'middle', horizontal: 'center' };
                    row.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: '1F6F5C' }
                    };
                    row.height = 22;
                };

                const styleMoneyColumn = (sheet, col, start, end) => {
                    for (let i = start; i <= end; i++) {
                        sheet.getCell(`${col}${i}`).numFmt = '"L." #,##0.00';
                    }
                };

                const autoFit = (sheet, widths = {}) => {
                    Object.entries(widths).forEach(([col, width]) => {
                        const colRef = /^\d+$/.test(col) ? Number(col) : col;
                        sheet.getColumn(colRef).width = width;
                    });
                };

                
                const ws1 = workbook.addWorksheet('Resumen Ejecutivo');
                addHeader(ws1, 'Resumen Ejecutivo');

                ws1.getRow(7).values = ['Indicador', 'Valor'];
                styleHeaderRow(ws1.getRow(7));

                ws1.addRow(['Ingresos del período', Number(kpis.total_vendido || 0)]);
                ws1.addRow(['Inversión en reposición', Number(kpis.total_comprado || 0)]);
                ws1.addRow(['Margen estimado', Number(kpis.utilidad_estimada || 0)]);
                ws1.addRow(['Actividad de stock', Number(kpis.movimientos || 0)]);
                ws1.addRow(['Ticket promedio', Number(kpis.ticket_promedio || 0)]);

                autoFit(ws1, { 1: 34, 2: 20 });
                styleMoneyColumn(ws1, 'B', 8, 12);

                
                const ws2 = workbook.addWorksheet('Ventas');
                addHeader(ws2, 'Detalle de Ventas');

                ws2.getRow(7).values = ['#', 'Fecha', 'Cliente', 'Estado', 'Total', 'Nota'];
                styleHeaderRow(ws2.getRow(7));

                if (salesRows.length) {
                    salesRows.forEach(x => ws2.addRow([
                        x.id_venta,
                        x.fecha,
                        x.cliente,
                        x.estado,
                        Number(x.total || 0),
                        x.nota
                    ]));
                    styleMoneyColumn(ws2, 'E', 8, 7 + salesRows.length);
                }

                autoFit(ws2, { 1: 10, 2: 16, 3: 28, 4: 18, 5: 16, 6: 40 });

              
                const ws3 = workbook.addWorksheet('Compras');
                addHeader(ws3, 'Detalle de Compras');

                ws3.getRow(7).values = ['#', 'Fecha', 'Proveedor', 'Estado', 'Total', 'Nota'];
                styleHeaderRow(ws3.getRow(7));

                if (purchaseRows.length) {
                    purchaseRows.forEach(x => ws3.addRow([
                        x.id_compra,
                        x.fecha,
                        x.proveedor,
                        x.estado,
                        Number(x.total || 0),
                        x.nota
                    ]));
                    styleMoneyColumn(ws3, 'E', 8, 7 + purchaseRows.length);
                }

                autoFit(ws3, { 1: 10, 2: 16, 3: 28, 4: 18, 5: 16, 6: 40 });

            
                const ws4 = workbook.addWorksheet('Inventario');
                addHeader(ws4, 'Movimientos de Inventario');

                ws4.getRow(7).values = ['#', 'Fecha', 'Tipo', 'Producto', 'Cantidad', 'Antes', 'Después', 'Nota'];
                styleHeaderRow(ws4.getRow(7));

                if (inventoryRows.length) {
                    inventoryRows.forEach(x => ws4.addRow([
                        x.id_mov,
                        x.fecha,
                        prettyInventoryType(x.tipo),
                        x.producto,
                        Number(x.cantidad || 0),
                        Number(x.stock_antes || 0),
                        Number(x.stock_despues || 0),
                        x.nota
                    ]));
                }

                autoFit(ws4, { 1: 10, 2: 16, 3: 24, 4: 28, 5: 12, 6: 12, 7: 12, 8: 34 });

                const ws5 = workbook.addWorksheet('Stock Crítico');
                addHeader(ws5, 'Productos con Stock Crítico');

                ws5.getRow(7).values = ['SKU', 'Producto', 'Categoría', 'Stock actual', 'Stock mínimo'];
                styleHeaderRow(ws5.getRow(7));

                if (lowStockRows.length) {
                    lowStockRows.forEach(x => ws5.addRow([
                        x.sku,
                        x.nombre,
                        x.categoria,
                        Number(x.stock_actual || 0),
                        Number(x.stock_min || 0)
                    ]));
                }

                autoFit(ws5, { 1: 18, 2: 34, 3: 24, 4: 16, 5: 16 });

                const buffer = await workbook.xlsx.writeBuffer();
                const excelBlob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });

                if (window.saveAs) {
                    window.saveAs(excelBlob, `AJA_Reportes_${filters.year}_${currentMonthNumber || 'ALL'}.xlsx`);
                } else {
                    const url = window.URL.createObjectURL(excelBlob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `AJA_Reportes_${filters.year}_${currentMonthNumber || 'ALL'}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                }
                if (typeof window.showToast === 'function') {
                    window.showToast('success', 'Excel general descargado correctamente.');
                }
            } catch (error) {
                console.error('Error exportando Excel general:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('error', 'No se pudo generar el Excel general.');
                }
            }

        });
        document.getElementById('btnDetailExcel')?.addEventListener('click', async () => {
            const detail = getActiveDetailData();
            const workbook = new ExcelJS.Workbook();
            workbook.creator = 'AJA Trends';
            workbook.created = new Date();

            const sheet = workbook.addWorksheet('Detalle');
            sheet.properties.defaultRowHeight = 20;
            sheet.views = [{ state: 'frozen', ySplit: 7 }];

            let logoImage = null;
            try {
                logoImage = await loadImageAsDataURL(REPORT_LOGO_URL);
            } catch (e) {
                console.warn('No se pudo cargar el logo para Excel detalle', e);
            }

            if (logoImage?.dataUrl) {
                const imageId = workbook.addImage({
                    base64: logoImage.dataUrl,
                    extension: logoImage.extension
                });

                sheet.addImage(imageId, {
                    tl: { col: 0.2, row: 0.2 },
                    ext: { width: 90, height: 90 }
                });
            }

            sheet.mergeCells('C1:H1');
            sheet.getCell('C1').value = 'AJA Trends';
            sheet.getCell('C1').font = { bold: true, size: 18, color: { argb: '18352E' } };

            sheet.mergeCells('C2:H2');
            sheet.getCell('C2').value = detail.title;
            sheet.getCell('C2').font = { bold: true, size: 13, color: { argb: '1F6F5C' } };

            sheet.mergeCells('C3:H3');
            sheet.getCell('C3').value = `Período: ${filters.year} · ${currentMonthLabel} · Módulo: ${currentModuleLabel}`;

            sheet.getRow(7).values = detail.headers;
            sheet.getRow(7).font = { bold: true, color: { argb: 'FFFFFF' } };
            sheet.getRow(7).fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '1F6F5C' }
            };

            detail.rows.forEach(r => sheet.addRow(r));
            if (detail.title === 'Detalle de Ventas' || detail.title === 'Detalle de Compras') {
                for (let i = 8; i < 8 + detail.rows.length; i++) {
                    sheet.getCell(`E${i}`).numFmt = '"L." #,##0.00';
                }
            }
            sheet.columns.forEach(col => {
                col.width = 18;
            });

            const buffer = await workbook.xlsx.writeBuffer();
            const excelBlob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });

            if (window.saveAs) {
                window.saveAs(excelBlob, `${detail.title.replaceAll(' ', '_')}.xlsx`);
            } else {
                const url = window.URL.createObjectURL(excelBlob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${detail.title.replaceAll(' ', '_')}.xlsx`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }
            if (typeof window.showToast === 'function') {
                window.showToast('success', `${detail.title} en Excel descargado correctamente.`);
            }
        });

        document.getElementById('btnExportPdf')?.addEventListener('click', async () => {
            if (!window.jspdf) {
                alert('No se pudo cargar la librería de PDF.');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
            const pageWidth = doc.internal.pageSize.getWidth();
            let logoImage = null;

            try {
                logoImage = await loadImageAsDataURL(REPORT_LOGO_URL);
            } catch (e) {
                console.warn('No se pudo cargar el logo para PDF', e);
            }

            const colors = {
                dark: [24, 53, 46],
                green: [31, 111, 92],
                soft: [238, 246, 243],
                text: [95, 107, 115],
                line: [217, 229, 225]
            };

            doc.setFillColor(...colors.soft);
            doc.roundedRect(36, 24, pageWidth - 72, 92, 16, 16, 'F');

            if (logoImage?.dataUrl) {
                doc.addImage(
                    logoImage.dataUrl,
                    logoImage.extension.toUpperCase(),
                    50,
                    38,
                    52,
                    52
                );
            }

            doc.setTextColor(...colors.dark);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(18);
            doc.text('AJA Trends', 114, 54);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.setTextColor(...colors.text);
            doc.text('Sistema de inventario, ventas y análisis comercial', 114, 72);
            doc.text(`Período: ${filters.year} · ${currentMonthLabel} · Módulo: ${currentModuleLabel}`, 114, 88);
            doc.text(`Generado: ${new Date().toLocaleString()}`, 114, 102);

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
            const pageCount = doc.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setDrawColor(...colors.line);
                doc.line(40, 810, pageWidth - 40, 810);

                doc.setFontSize(9);
                doc.setTextColor(...colors.text);
                doc.text('AJA Trends · Reporte ejecutivo', 40, 826);
                doc.text(`Página ${i} de ${pageCount}`, pageWidth - 95, 826);
            }
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
            if (typeof window.showToast === 'function') {
                window.showToast('success', 'PDF general descargado correctamente.');
            }
        });
        document.getElementById('btnDetailPdf')?.addEventListener('click', async () => {
            const detail = getActiveDetailData();
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
            const pageWidth = doc.internal.pageSize.getWidth();

            let logoImage = null;
            try {
                logoImage = await loadImageAsDataURL(REPORT_LOGO_URL);
            } catch (e) {
                console.warn('No se pudo cargar el logo para PDF detalle', e);
            }

            const colors = {
                dark: [24, 53, 46],
                green: [31, 111, 92],
                soft: [238, 246, 243],
                text: [95, 107, 115],
                line: [217, 229, 225]
            };

            doc.setFillColor(...colors.soft);
            doc.roundedRect(36, 24, pageWidth - 72, 86, 16, 16, 'F');

            if (logoImage?.dataUrl) {
                doc.addImage(logoImage.dataUrl, logoImage.extension.toUpperCase(), 46, 36, 48, 48);
            }

            doc.setTextColor(...colors.dark);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(17);
            doc.text('AJA Trends', 106, 52);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.setTextColor(...colors.text);
            doc.text(detail.title, 106, 70);
            doc.text(`Período: ${filters.year} · ${currentMonthLabel} · Módulo: ${currentModuleLabel}`, 106, 87);

            doc.autoTable({
                startY: 128,
                head: [detail.headers],
                body: detail.rows.length ? detail.rows : [['Sin registros']],
                styles: {
                    fontSize: 8,
                    cellPadding: 6
                },
                headStyles: {
                    fillColor: colors.green,
                    textColor: [255, 255, 255]
                }
            });

            doc.save(`${detail.title.replaceAll(' ', '_')}.pdf`);
            if (typeof window.showToast === 'function') {
                window.showToast('success', `${detail.title} en PDF descargado correctamente.`);
            }
        });

    })();
</script>