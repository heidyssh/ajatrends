<?php
// $viewData viene de ProductController
$filters = $viewData['filters'] ?? [];
$categories = $viewData['categories'] ?? [];
$products = $viewData['products'] ?? [];
$isAdmin = (bool)($viewData['isAdmin'] ?? false);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$q = $filters['q'] ?? '';
$cat = (int)($filters['cat'] ?? 0);
$min = $filters['min'] ?? '';
$max = $filters['max'] ?? '';
$estado = $filters['estado'] ?? '';
?>
<div class="products-page page-fade">
<div class="cardx mb-4">
<div class="hd d-flex align-items-start justify-content-between flex-wrap gap-3">
<div>
<div class="fw-bold" style="font-size:1.15rem;">Productos · Catálogo AJA ✨</div>
<small>Catálogo visual (móvil) + filtros + CRUD admin · Imágenes/Precio/Categoría desde tu BD</small>
</div>

<div class="d-flex align-items-center gap-2">
<a class="btn btn-sm btn-outline-dark" href="index.php?page=products">
<i class="bi bi-arrow-clockwise me-1"></i> Limpiar
</a>
<button class="btn btn-sm btn-outline-dark d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersCanvas">
<i class="bi bi-funnel me-1"></i> Filtros
</button>
<?php if ($isAdmin): ?>
<button class="btn btn-sm btn-brand" type="button" id="btnNewProduct">
<i class="bi bi-plus-lg me-1"></i> Nuevo
</button>
<?php endif; ?>
</div>
</div>

<div class="bd">
<!-- Filtros desktop -->
<form class="row g-2 align-items-end d-none d-md-flex" method="get" action="index.php">
<input type="hidden" name="page" value="products">

<div class="col-lg-4">
<label class="form-label small mb-1">Buscar</label>
<input class="form-control" name="q" value="<?= h($q) ?>" placeholder="SKU o nombre (ej: serum, labial, AJA-001)">
</div>

<div class="col-lg-3">
<label class="form-label small mb-1">Categoría</label>
<select class="form-select" name="cat">
<option value="0">Todas</option>
<?php foreach($categories as $c): ?>
<option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)$c['id_categoria'] === $cat) ? 'selected' : '' ?>>
<?= h($c['nombre']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-lg-2">
<label class="form-label small mb-1">Precio min</label>
<input class="form-control" type="number" step="0.01" name="min" value="<?= h($min) ?>" placeholder="0.00">
</div>

<div class="col-lg-2">
<label class="form-label small mb-1">Precio max</label>
<input class="form-control" type="number" step="0.01" name="max" value="<?= h($max) ?>" placeholder="999.99">
</div>

<div class="col-lg-1">
<label class="form-label small mb-1">Estado</label>
<select class="form-select" name="estado">
<option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos</option>
<option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Act</option>
<option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Off</option>
</select>
</div>

<div class="col-12 d-flex gap-2 mt-2">
<button class="btn btn-brand" type="submit"><i class="bi bi-search me-1"></i> Aplicar</button>
<div class="ms-auto d-flex align-items-center gap-2">
<span class="chip chip-muted"><i class="bi bi-box-seam me-1"></i><?= count($products) ?> productos</span>
</div>
</div>
</form>

<!-- Chips categorías (scroll tipo “slides”) -->
<div class="chips mt-3">
<a class="chip <?= $cat === 0 ? 'active' : '' ?>" href="index.php?page=products<?= $q!==''?('&q='.urlencode($q)):'' ?>">Todas</a>
<?php foreach($categories as $c): ?>
<?php $idc = (int)$c['id_categoria']; ?>
<a class="chip <?= $idc === $cat ? 'active' : '' ?>" href="index.php?page=products&cat=<?= $idc ?><?= $q!==''?('&q='.urlencode($q)):'' ?>">
<?= h($c['nombre']) ?>
</a>
<?php endforeach; ?>
</div>

<?php if (!empty($viewData['error'])): ?>
<div class="alert alert-danger mt-3 mb-0"><?= h($viewData['error']) ?></div>
<?php endif; ?>
<?php if (!empty($viewData['success'])): ?>
<div class="alert alert-success mt-3 mb-0"><?= h($viewData['success']) ?></div>
<?php endif; ?>

<div class="divider my-4"></div>

<!-- Grid productos -->
<div class="row g-3">
<?php if (!$products): ?>
<div class="col-12">
<div class="empty-state">
<div class="ic"><i class="bi bi-bag-x"></i></div>
<div class="fw-bold">No hay productos con esos filtros</div>
<small>Probá limpiar filtros o crear tu primer producto.</small>
</div>
</div>
<?php endif; ?>

<?php foreach($products as $p): ?>
<?php
$id = (int)$p['id_producto'];
$isOff = ((int)$p['estado'] === 0);
?>
<div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
<div class="product-card <?= $isOff ? 'off' : '' ?>" role="button"
data-product-id="<?= $id ?>">

<div class="img">
<img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>">
<span class="badge-cat"><i class="bi bi-tag"></i> <?= h($p['categoria']) ?></span>
<?php if ($isOff): ?><span class="badge-off">Inactivo</span><?php endif; ?>
</div>

<div class="info">
<div class="name"><?= h($p['nombre']) ?></div>
<div class="meta">
<span class="sku">SKU: <?= h($p['sku']) ?></span>
<span class="dot">•</span>
<span class="imgs"><i class="bi bi-images"></i> <?= (int)$p['total_imagenes'] ?></span>
</div>

<div class="price-row">
<div class="price">L <?= number_format((float)$p['precio'], 2) ?></div>
<div class="ms-auto">
<?php if ($isAdmin): ?>
<button class="btn btn-sm btn-light btn-mini" type="button" data-edit-id="<?= $id ?>" title="Editar">
<i class="bi bi-pencil"></i>
</button>
<button class="btn btn-sm btn-light btn-mini" type="button" data-delete-id="<?= $id ?>" title="Eliminar">
<i class="bi bi-trash"></i>
</button>
<?php endif; ?>
</div>
</div>

<div class="desc"><?= h(mb_strimwidth((string)$p['descripcion'], 0, 110, '…', 'UTF-8')) ?></div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>

<?php if ($isAdmin): ?>
<!-- FAB mobile -->
<button class="fab d-md-none" id="fabNew" type="button" title="Nuevo producto">
<i class="bi bi-plus-lg"></i>
</button>
<?php endif; ?>

</div>
</div>

<!-- Offcanvas filtros móvil -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filtersCanvas" aria-labelledby="filtersCanvasLabel">
<div class="offcanvas-header">
<h5 class="offcanvas-title" id="filtersCanvasLabel"><i class="bi bi-funnel me-1"></i> Filtros</h5>
<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
</div>
<div class="offcanvas-body">
<form method="get" action="index.php" class="vstack gap-2">
<input type="hidden" name="page" value="products">

<label class="form-label small mb-0">Buscar</label>
<input class="form-control" name="q" value="<?= h($q) ?>" placeholder="SKU o nombre">

<label class="form-label small mb-0 mt-2">Categoría</label>
<select class="form-select" name="cat">
<option value="0">Todas</option>
<?php foreach($categories as $c): ?>
<option value="<?= (int)$c['id_categoria'] ?>" <?= ((int)$c['id_categoria'] === $cat) ? 'selected' : '' ?>>
<?= h($c['nombre']) ?>
</option>
<?php endforeach; ?>
</select>

<div class="row g-2 mt-1">
<div class="col-6">
<label class="form-label small mb-0">Min</label>
<input class="form-control" type="number" step="0.01" name="min" value="<?= h($min) ?>">
</div>
<div class="col-6">
<label class="form-label small mb-0">Max</label>
<input class="form-control" type="number" step="0.01" name="max" value="<?= h($max) ?>">
</div>
</div>

<label class="form-label small mb-0 mt-2">Estado</label>
<select class="form-select" name="estado">
<option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos</option>
<option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
<option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
</select>

<button class="btn btn-brand mt-3" type="submit"><i class="bi bi-check2 me-1"></i> Aplicar</button>
<a class="btn btn-outline-dark" href="index.php?page=products"><i class="bi bi-eraser me-1"></i> Limpiar</a>
</form>
</div>
</div>

<!-- Modal detalle producto (dinámico con AJAX) -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content product-modal">
<div class="modal-header">
<div>
<div class="modal-title fw-bold" id="pmTitle">Producto</div>
<small id="pmMeta">—</small>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>
<div class="modal-body">

<div class="row g-3">
<div class="col-lg-6">
<div id="pmCarousel" class="carousel slide" data-bs-ride="carousel">
<div class="carousel-inner" id="pmCarouselInner">
<!-- JS inject -->
</div>
<button class="carousel-control-prev" type="button" data-bs-target="#pmCarousel" data-bs-slide="prev">
<span class="carousel-control-prev-icon" aria-hidden="true"></span>
<span class="visually-hidden">Anterior</span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#pmCarousel" data-bs-slide="next">
<span class="carousel-control-next-icon" aria-hidden="true"></span>
<span class="visually-hidden">Siguiente</span>
</button>
</div>
</div>

<div class="col-lg-6">
<div class="pm-price" id="pmPrice">L 0.00</div>
<div class="d-flex flex-wrap gap-2 mt-2">
<span class="pm-pill" id="pmCat"><i class="bi bi-tag"></i> Categoría</span>
<span class="pm-pill" id="pmSku"><i class="bi bi-upc-scan"></i> SKU</span>
<span class="pm-pill" id="pmState"><i class="bi bi-toggle-on"></i> Estado</span>
</div>

<div class="mt-3">
<div class="fw-bold">Descripción</div>
<div class="pm-desc" id="pmDesc">—</div>
</div>

<?php if ($isAdmin): ?>
<div class="mt-3 d-flex gap-2">
<button class="btn btn-outline-dark w-50" type="button" id="pmEditBtn">
<i class="bi bi-pencil me-1"></i> Editar
</button>
<button class="btn btn-outline-danger w-50" type="button" id="pmDeleteBtn">
<i class="bi bi-trash me-1"></i> Eliminar
</button>
</div>
<?php endif; ?>
</div>
</div>

</div>
</div>
</div>
</div>

<?php if ($isAdmin): ?>
<!-- Modal crear/editar -->
<div class="modal fade" id="productUpsertModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">
<form method="post" enctype="multipart/form-data" id="productForm" class="needs-validation" novalidate>
<input type="hidden" name="action" id="pfAction" value="create">
<input type="hidden" name="id_producto" id="pfId" value="">

<div class="modal-header">
<div>
<div class="modal-title fw-bold" id="pfTitle">Nuevo producto</div>
<small>Imágenes · precio · categoría · todo se guarda en tu BD</small>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<div class="modal-body">
<div class="row g-2">
<div class="col-md-4">
<label class="form-label small">SKU</label>
<input class="form-control" name="sku" id="pfSku" required>
<div class="invalid-feedback">SKU requerido.</div>
</div>
<div class="col-md-8">
<label class="form-label small">Nombre</label>
<input class="form-control" name="nombre" id="pfNombre" required>
<div class="invalid-feedback">Nombre requerido.</div>
</div>
<div class="col-md-5">
<label class="form-label small">Categoría</label>
<select class="form-select" name="id_categoria" id="pfCat" required>
<option value="">Seleccionar…</option>
<?php foreach($categories as $c): ?>
<option value="<?= (int)$c['id_categoria'] ?>"><?= h($c['nombre']) ?></option>
<?php endforeach; ?>
</select>
<div class="invalid-feedback">Elegí una categoría.</div>
</div>

<div class="col-md-3">
<label class="form-label small">Costo</label>
<input class="form-control" type="number" step="0.01" name="costo" id="pfCosto" value="0.00" required>
</div>
<div class="col-md-3">
<label class="form-label small">Precio</label>
<input class="form-control" type="number" step="0.01" name="precio" id="pfPrecio" value="0.00" required>
</div>
<div class="col-md-1">
<label class="form-label small">Min</label>
<input class="form-control" type="number" name="stock_min" id="pfMin" value="0" required>
</div>

<div class="col-md-3">
<label class="form-label small">Estado</label>
<select class="form-select" name="estado" id="pfEstado">
<option value="1">Activo</option>
<option value="0">Inactivo</option>
</select>
</div>

<div class="col-12">
<label class="form-label small">Descripción</label>
<textarea class="form-control" name="descripcion" id="pfDesc" rows="3" placeholder="Detalles del producto…"></textarea>
</div>

<div class="col-12">
<label class="form-label small">Imágenes (se pueden subir varias)</label>
<input class="form-control" type="file" name="imagenes[]" id="pfImgs" accept="image/*" multiple>
<div class="form-text">Tip: la primera imagen subida queda como principal (luego se puede cambiar).</div>
</div>

<div class="col-12" id="pfExistingImagesWrap" style="display:none;">
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
<div class="fw-bold">Imágenes actuales</div>
<small class="text-muted">Marcar como principal o eliminar</small>
</div>
<div class="img-grid" id="pfExistingImages"></div>
</div>

</div>
</div>

<div class="modal-footer">
<button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
<button class="btn btn-brand" type="submit"><i class="bi bi-save me-1"></i> Guardar</button>
</div>
</form>
</div>
</div>
</div>
</div>

<!-- Form oculto para borrar producto -->
<form method="post" id="deleteForm" style="display:none;">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id_producto" id="delId" value="">
</form>

<!-- Form oculto borrar imagen -->
<form method="post" id="deleteImgForm" style="display:none;">
<input type="hidden" name="action" value="delete_image">
<input type="hidden" name="id_imagen" id="delImgId" value="">
</form>

<!-- Form oculto set principal -->
<form method="post" id="principalImgForm" style="display:none;">
<input type="hidden" name="action" value="set_principal">
<input type="hidden" name="id_producto" id="priProdId" value="">
<input type="hidden" name="id_imagen" id="priImgId" value="">
</form>
<?php endif; ?>
<!-- Modal confirmación (reemplaza confirm() del navegador) -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="fw-bold" id="confirmTitle">Confirmar</div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="confirmText">¿Seguro?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmOk">Sí, eliminar</button>
      </div>
    </div>
  </div>
</div>
<script>
(function(){
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
let lastOpenedId = 0;

// Abrir modal detalle + cargar JSON
const productModal = document.getElementById('productModal');
// --- FIX stacking context: llevar overlays al <body> ---
const upsertEl = document.getElementById('productUpsertModal');
const filtersEl = document.getElementById('filtersCanvas');

if (productModal && productModal.parentElement !== document.body) document.body.appendChild(productModal);
if (upsertEl && upsertEl.parentElement !== document.body) document.body.appendChild(upsertEl);
if (filtersEl && filtersEl.parentElement !== document.body) document.body.appendChild(filtersEl);
const titleEl = document.getElementById('pmTitle');
const metaEl = document.getElementById('pmMeta');
const priceEl = document.getElementById('pmPrice');
const catEl = document.getElementById('pmCat');
const skuEl = document.getElementById('pmSku');
const stateEl = document.getElementById('pmState');
const descEl = document.getElementById('pmDesc');
const carouselInner = document.getElementById('pmCarouselInner');

function moneyL(v){
const n = Number(v || 0);
return 'L ' + n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}

document.addEventListener('click', async (e) => {
const card = e.target.closest('.product-card');
if (!card) return;
// Si el modal de crear/editar está abierto, cerrarlo antes de abrir el detalle
const upEl = document.getElementById('productUpsertModal');
const up = bootstrap.Modal.getInstance(upEl);
if (upEl && upEl.classList.contains('show') && up){
  up.hide();
  await waitHidden(upEl);
  cleanupModals();
}

// Evitar que botones mini abran el detalle
if (e.target.closest('[data-edit-id]') || e.target.closest('[data-delete-id]')) return;

const id = Number(card.getAttribute('data-product-id') || 0);
if (!id) return;
lastOpenedId = id;

// Cargar detalle
titleEl.textContent = 'Cargando…';
metaEl.textContent = '';
carouselInner.innerHTML = '<div class="carousel-item active"><div class="pm-skel"></div></div>';
bootstrap.Modal.getOrCreateInstance(productModal).show();
try {
const res = await fetch(`index.php?page=products&ajax=1&view=${id}`);
const json = await res.json();
if (!json.ok) throw new Error(json.error || 'No se pudo cargar');

const p = json.product;
const imgs = json.images || [];

titleEl.textContent = p.nombre;
metaEl.textContent = `${p.categoria} · ID ${p.id_producto}`;
priceEl.textContent = moneyL(p.precio);
catEl.innerHTML = `<i class="bi bi-tag"></i> ${p.categoria}`;
skuEl.innerHTML = `<i class="bi bi-upc-scan"></i> ${p.sku}`;
stateEl.innerHTML = `<i class="bi bi-toggle-${Number(p.estado)===1?'on':'off'}"></i> ${Number(p.estado)===1?'Activo':'Inactivo'}`;
descEl.textContent = p.descripcion || '—';

// Carousel
carouselInner.innerHTML = '';
if (imgs.length === 0) imgs.push({url:'assets/img/logo.jpeg'});

imgs.forEach((im, idx) => {
const div = document.createElement('div');
div.className = 'carousel-item' + (idx===0?' active':'');
div.innerHTML = `<img class="d-block w-100 pm-img" src="${im.url}" alt="Imagen">`;
carouselInner.appendChild(div);
});

// Admin buttons en modal
if (isAdmin){
const editBtn = document.getElementById('pmEditBtn');
const delBtn = document.getElementById('pmDeleteBtn');
if (editBtn) editBtn.onclick = () => openEdit(id);
if (delBtn) delBtn.onclick = () => askDelete(id);
}
} catch(err){
titleEl.textContent = 'Error';
metaEl.textContent = '';
descEl.textContent = err.message || 'No se pudo cargar.';
}
});
function cleanupModals(){
  // backdrops de modales
  document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
  // backdrops de offcanvas (filtros móvil)
  document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());

  document.body.classList.remove('modal-open');
  document.body.classList.remove('offcanvas-backdrop'); // por si quedó algo raro
  document.body.style.removeProperty('padding-right');
  document.body.style.removeProperty('overflow');
}
function hookCleanup(el){
  if (!el) return;
  el.addEventListener('hidden.bs.modal', cleanupModals);
  el.addEventListener('hidden.bs.offcanvas', cleanupModals);
}

hookCleanup(productModal);
hookCleanup(document.getElementById('productUpsertModal'));
hookCleanup(document.getElementById('filtersCanvas'));
function waitHidden(modalEl){
  return new Promise(resolve => {
    if (!modalEl || !modalEl.classList.contains('show')) return resolve();
    modalEl.addEventListener('hidden.bs.modal', () => resolve(), { once:true });
  });
}
// Admin: nuevo
async function openNew(){

  const upsertEl = document.getElementById('productUpsertModal');
  const detailEl = document.getElementById('productModal');

  // Si el modal de detalle está abierto, cerrarlo primero
  const detailModal = bootstrap.Modal.getInstance(detailEl);
  if (detailEl && detailEl.classList.contains('show') && detailModal){
    detailModal.hide();
    await new Promise(resolve => {
      detailEl.addEventListener('hidden.bs.modal', resolve, { once:true });
    });

    // limpiar posibles backdrops pegados
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());   
  }
    cleanupModals();
  const m = new bootstrap.Modal(upsertEl);

  document.getElementById('pfTitle').textContent = 'Nuevo producto';
  document.getElementById('pfAction').value = 'create';
  document.getElementById('pfId').value = '';
  document.getElementById('productForm').reset();
  document.getElementById('pfEstado').value = '1';
  document.getElementById('pfExistingImagesWrap').style.display = 'none';
  document.getElementById('pfExistingImages').innerHTML = '';

  m.show();
}

// Admin: editar
async function openEdit(id){
const upsertEl = document.getElementById('productUpsertModal');
cleanupModals();
const m = bootstrap.Modal.getOrCreateInstance(upsertEl);

// cerrar modal detalle si está abierto
const pm = bootstrap.Modal.getInstance(productModal);
if (pm){
  pm.hide();
  await waitHidden(productModal);
  cleanupModals();
}

document.getElementById('pfTitle').textContent = 'Editar producto';
document.getElementById('pfAction').value = 'update';
document.getElementById('pfId').value = String(id);
document.getElementById('pfExistingImagesWrap').style.display = 'none';
document.getElementById('pfExistingImages').innerHTML = '';

try{
const res = await fetch(`index.php?page=products&ajax=1&view=${id}`);
const json = await res.json();
if (!json.ok) throw new Error(json.error || 'No se pudo cargar');

const p = json.product;
const imgs = json.images || [];

document.getElementById('pfSku').value = p.sku || '';
document.getElementById('pfNombre').value = p.nombre || '';
document.getElementById('pfCat').value = p.id_categoria || '';
document.getElementById('pfCosto').value = p.costo || 0;
document.getElementById('pfPrecio').value = p.precio || 0;
document.getElementById('pfMin').value = p.stock_min || 0;
document.getElementById('pfEstado').value = String(p.estado ?? 1);
document.getElementById('pfDesc').value = p.descripcion || '';

// mini grid imágenes
if (imgs.length > 0){
document.getElementById('pfExistingImagesWrap').style.display = 'block';
const wrap = document.getElementById('pfExistingImages');
wrap.innerHTML = '';

imgs.forEach(im => {
const isP = Number(im.es_principal) === 1;
const item = document.createElement('div');
item.className = 'img-item' + (isP ? ' principal' : '');
item.innerHTML = `
<img src="${im.url}" alt="img">
<div class="img-actions">
<button class="btn btn-sm btn-light" type="button" data-set-principal="${im.id_imagen}" title="Principal">
<i class="bi bi-star${isP?'-fill':''}"></i>
</button>
<button class="btn btn-sm btn-light" type="button" data-del-img="${im.id_imagen}" title="Eliminar">
<i class="bi bi-x-lg"></i>
</button>
</div>
`;
wrap.appendChild(item);
});
}

m.show();

}catch(err){
alert(err.message || 'Error');
}
}

function askDelete(id){
  const modalEl = document.getElementById('confirmModal');
  const titleEl = document.getElementById('confirmTitle');
  const textEl  = document.getElementById('confirmText');
  const okBtn   = document.getElementById('confirmOk');

  titleEl.textContent = 'Eliminar producto';
  textEl.textContent  = '¿Eliminar este producto? Esto borrará también sus imágenes.';

  const m = bootstrap.Modal.getOrCreateInstance(modalEl);

  // IMPORTANTE: evitar que se acumulen onclick
  okBtn.onclick = null;
  okBtn.onclick = () => {
    document.getElementById('delId').value = String(id);
    document.getElementById('deleteForm').submit();
    m.hide();
  };

  m.show();
}

// Botones mini en cards
document.addEventListener('click', (e) => {
const btnEdit = e.target.closest('[data-edit-id]');
if (btnEdit){
  e.preventDefault();
  e.stopImmediatePropagation();
  openEdit(Number(btnEdit.getAttribute('data-edit-id')));
  return;
}

const btnDel = e.target.closest('[data-delete-id]');
if (btnDel){
  e.preventDefault();
  e.stopImmediatePropagation();
  const id = Number(btnDel.getAttribute('data-delete-id'));
  askDelete(id);
  return;
}

const btnDelImg = e.target.closest('[data-del-img]');
if (btnDelImg){
  e.preventDefault();
  e.stopImmediatePropagation();

  const idImg = Number(btnDelImg.getAttribute('data-del-img'));
  const modalEl = document.getElementById('confirmModal');
  const titleEl = document.getElementById('confirmTitle');
  const textEl  = document.getElementById('confirmText');
  const okBtn   = document.getElementById('confirmOk');

  titleEl.textContent = 'Eliminar imagen';
  textEl.textContent  = '¿Eliminar esta imagen del producto?';

  const m = bootstrap.Modal.getOrCreateInstance(modalEl);

  okBtn.onclick = null;
  okBtn.onclick = () => {
    document.getElementById('delImgId').value = String(idImg);
    document.getElementById('deleteImgForm').submit();
    m.hide();
  };

  m.show();
  return;
}

const btnPri = e.target.closest('[data-set-principal]');
if (btnPri){
const idImg = Number(btnPri.getAttribute('data-set-principal'));
const idP = Number(document.getElementById('pfId').value || 0);
if (!idP) return;
document.getElementById('priProdId').value = String(idP);
document.getElementById('priImgId').value = String(idImg);
document.getElementById('principalImgForm').submit();
return;
}
});

// botones nuevo
const btnNew = document.getElementById('btnNewProduct');
if (btnNew) btnNew.addEventListener('click', openNew);
const fab = document.getElementById('fabNew');
if (fab) fab.addEventListener('click', openNew);

// Validación bootstrap
const form = document.getElementById('productForm');
if (form){
form.addEventListener('submit', (event) => {
if (!form.checkValidity()) {
event.preventDefault();
event.stopPropagation();
}
form.classList.add('was-validated');
}, false);
}
})();

</script>