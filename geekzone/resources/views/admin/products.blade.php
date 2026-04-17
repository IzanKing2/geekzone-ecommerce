@extends('layouts.layout')
@section('title', 'GeekZone — Admin · Productos')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/crud.css') }}">
<style>
    .img-picker {
        margin-top: .5rem
    }

    .img-picker-area {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .8rem;
        background: var(--mid2);
        border: 2px dashed var(--border);
        border-radius: 6px;
        cursor: pointer;
        transition: border-color .2s;
    }

    .img-picker-area:hover {
        border-color: var(--cobalt)
    }

    .img-picker-area.has-image {
        border-style: solid;
        border-color: var(--cobalt)
    }

    .img-picker-area.uploading {
        border-color: var(--gold);
        cursor: wait
    }

    .img-preview {
        width: 64px;
        height: 64px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
        background: rgba(0, 71, 171, .1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }

    .img-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px
    }

    .img-picker-info {
        flex: 1;
        min-width: 0
    }

    .img-picker-name {
        font-size: .82rem;
        color: var(--white);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: .2rem;
    }

    .img-picker-hint {
        font-size: .75rem;
        color: var(--grey)
    }

    .img-picker-btns {
        display: flex;
        gap: .5rem;
        flex-shrink: 0
    }

    .img-picker-error {
        font-size: .78rem;
        color: #f87171;
        margin-top: .4rem;
        display: none;
        padding: .3rem .5rem;
        background: rgba(185, 28, 28, .12);
        border: 1px solid rgba(185, 28, 28, .25);
        border-radius: 4px;
    }

    .img-picker-area.error {
        border-color: rgba(185, 28, 28, .5)
    }
</style>
@endpush

@section('content')
<div class="layout-sidebar">

    {{-- ══ SIDEBAR ══ --}}
    <aside class="sidebar">
        <div style="padding:1rem 1.5rem 1.2rem;border-bottom:1px solid var(--border);margin-bottom:.5rem">
            <p style="font-family:'Barlow Condensed',sans-serif;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.45);margin-bottom:.2rem">Panel de control</p>
            <p style="font-weight:600;font-size:.95rem">GeekZone Admin</p>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Principal</p>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
            <a href="{{ route('admin.products') }}" class="sidebar-link active"><span class="icon">📦</span> Productos</a>
        </div>
        <hr class="sidebar-divider" />
        <div class="sidebar-section">
            <p class="sidebar-label">Contenido</p>
            <a href="{{ route('admin.categories') }}" class="sidebar-link"><span class="icon">🏷️</span> Categorías</a>
        </div>
        <hr class="sidebar-divider" />
        <div class="sidebar-section">
            <a href="{{ route('shop') }}" class="sidebar-link"><span class="icon">🏠</span> Ver tienda</a>
            <a href="#" class="sidebar-link" id="admin-logout" style="color:var(--red)"><span class="icon">↩</span> Salir</a>
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    <main class="main-content">

        <div class="crud-header-wrap">
            <div>
                <p class="page-eyebrow" style="padding-top:0;margin-bottom:0">Gestión de inventario</p>
                <h2 class="panel-title" style="font-family:'Bebas Neue',sans-serif;font-size:2.5rem;letter-spacing:2px;margin:0">Productos <span style="color:var(--cobalt-light)">CRUD</span></h2>
            </div>
            <button class="btn btn-primary" id="btn-add-product">+ Añadir producto</button>
        </div>

        {{-- Alerta feedback --}}
        <div id="alert-box" style="display:none;margin-bottom:1.5rem"></div>

        {{-- ══ FILTER BAR ══ --}}
        <form method="GET" action="{{ route('admin.products') }}" id="filter-form">
            <div class="filter-bar">

                {{-- Filtros por categoría y estado --}}
                <a href="{{ route('admin.products', array_merge(request()->except(['cat','sin_stock','destacados','page']), [])) }}"
                    class="filter-pill {{ !request('cat') && !request()->boolean('sin_stock') && !request()->boolean('destacados') ? 'active' : '' }}">
                    Todos ({{ $products->total() }})
                </a>

                @foreach($categories as $cat)
                <a href="{{ route('admin.products', array_merge(request()->except(['cat','sin_stock','destacados','page']), ['cat' => $cat->id])) }}"
                    class="filter-pill {{ request('cat') == $cat->id ? 'active' : '' }}">
                    {{ $cat->name }} ({{ $cat->products_count ?? $cat->products()->count() }})
                </a>
                @endforeach

                <a href="{{ route('admin.products', array_merge(request()->except(['cat','sin_stock','destacados','page']), ['sin_stock' => 1])) }}"
                    class="filter-pill {{ request()->boolean('sin_stock') ? 'active' : '' }}"
                    style="{{ $totalSinStock > 0 ? 'color:var(--red);border-color:rgba(224,16,32,.3)' : '' }}">
                    ⚠️ Sin stock ({{ $totalSinStock }})
                </a>

                {{-- Búsqueda y orden --}}
                <div class="filter-right">
                    <div class="search-wrap">
                        <input type="text" name="q" class="form-control" id="searchInputAdmin"
                            placeholder="Buscar producto…"
                            style="width:200px;padding:.45rem 1rem .45rem 2.2rem"
                            value="{{ request('q') }}"
                            oninput="clearTimeout(this.delay); this.delay = setTimeout(() => { this.form.submit(); }, 700);" />
                    </div>
                    <select name="sort" class="filter-select" onchange="this.form.submit()">
                        <option value="recent" {{ request('sort','recent') === 'recent'     ? 'selected' : '' }}>Recientes</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc'           ? 'selected' : '' }}>Precio ↑</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc'          ? 'selected' : '' }}>Precio ↓</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc'            ? 'selected' : '' }}>Nombre A-Z</option>
                    </select>
                    {{-- Preserva filtros activos en el form --}}
                    @foreach(request()->except(['q','sort','page']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}" />
                    @endforeach
                </div>
            </div>
        </form>

        {{-- ══ TABLE ══ --}}
        <div class="card" id="admin-results-container">
            <div id="admin-results">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:.8rem">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                            style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid var(--border);flex-shrink:0"
                                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <div class="product-thumb-cell" style="display:none;background:rgba(0,71,171,.15)">📦</div>
                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            @if($product->featured)
                                            <span class="badge badge-gold" style="margin-left:.4rem;font-size:.6rem">⭐ Destacado</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-cobalt" style="background:rgba(0,71,171,.2);border:1px solid rgba(0,71,171,.35);color:var(--cobalt-light)">
                                        {{ $product->category->name ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:var(--gold);font-family:'Bebas Neue',sans-serif;font-size:1.2rem">
                                        {{ number_format($product->price, 2, ',', '.') }}€
                                    </strong>
                                </td>
                                <td>
                                    @if($product->stock === 0)
                                    <strong style="color:var(--red)">0</strong>
                                    @elseif($product->stock <= 5)
                                        <strong style="color:#d4af37">{{ $product->stock }}</strong>
                                        @else
                                        <strong>{{ $product->stock }}</strong>
                                        @endif
                                </td>
                                <td>
                                    @if($product->stock === 0)
                                    <span class="badge badge-grey">Sin stock</span>
                                    @elseif($product->featured)
                                    <span class="badge badge-gold">Destacado</span>
                                    @else
                                    <span class="badge badge-green">Activo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="td-actions">
                                        <button class="btn btn-secondary btn-sm"
                                            onclick="openEditModal(
                                                {{ $product->id }},
                                                '{{ addslashes($product->name) }}',
                                                {{ $product->category_id }},
                                                '{{ $product->price }}',
                                                {{ $product->stock }},
                                                {{ $product->featured ? 'true' : 'false' }},
                                                '{{ addslashes($product->image_url ?? '') }}',
                                                '{{ addslashes($product->description ?? '') }}'
                                            )"
                                            title="Editar">✏️</button>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                            title="Eliminar">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:3rem;color:var(--grey)">
                                    No se encontraron productos con los filtros aplicados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-top:1px solid var(--border);flex-wrap:wrap;gap:.8rem">
                    <p style="font-size:.85rem;color:var(--grey)">
                        @if($products->total() > 0)
                        Mostrando <strong style="color:var(--white)">{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
                        de <strong style="color:var(--white)">{{ $products->total() }}</strong> productos
                        @else
                        Sin resultados
                        @endif
                    </p>
                    @if($products->hasPages())
                    <div class="pagination" style="margin-top:0">
                        @if($products->onFirstPage())
                        <button class="page-btn" disabled style="opacity:.4">‹</button>
                        @else
                        <a href="{{ $products->previousPageUrl() }}" class="page-btn">‹</a>
                        @endif

                        @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if($page == $products->currentPage())
                        <button class="page-btn active">{{ $page }}</button>
                        @elseif($page == 1 || $page == $products->lastPage() || abs($page - $products->currentPage()) <= 1)
                            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                            @elseif(abs($page - $products->currentPage()) == 2)
                            <button class="page-btn" disabled style="opacity:.4;cursor:default">…</button>
                            @endif
                            @endforeach

                            @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="page-btn">›</a>
                            @else
                            <button class="page-btn" disabled style="opacity:.4">›</button>
                            @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </main>
</div>

{{-- ══ MODAL: NUEVO PRODUCTO ══ --}}
<div class="modal-overlay" id="modal-add" style="display:none" onclick="if(event.target===this)closeModal('modal-add')">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Nuevo Producto</h3>
            <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
        </div>
        <div class="modal-body" style="max-height:70vh;overflow-y:auto">
            <div class="form-group">
                <label class="form-label">Nombre del producto *</label>
                <input type="text" id="add-name" class="form-control" placeholder="Ej: Figura Thor Premium 25cm" />
                <p class="form-error" id="add-name-error" style="display:none;color:var(--red);font-size:.82rem;margin-top:.3rem"></p>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select id="add-cat" class="form-control">
                        <option value="">Selecciona…</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-error" id="add-cat-error" style="display:none;color:var(--red);font-size:.82rem;margin-top:.3rem"></p>
                </div>
                <div class="form-group">
                    <label class="form-label">Precio (€) *</label>
                    <input type="number" id="add-price" class="form-control" placeholder="29.99" step="0.01" min="0.01" />
                    <p class="form-error" id="add-price-error" style="display:none;color:var(--red);font-size:.82rem;margin-top:.3rem"></p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Stock *</label>
                    <input type="number" id="add-stock" class="form-control" placeholder="0" min="0" value="0" />
                </div>
                <div class="form-group" style="display:flex;flex-direction:column;justify-content:flex-end">
                    <label class="form-label" style="display:flex;align-items:center;gap:.6rem;cursor:pointer;margin-bottom:.75rem">
                        <input type="checkbox" id="add-featured" style="accent-color:var(--gold);width:16px;height:16px" />
                        ⭐ Marcar como Destacado
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Imagen del producto</label>
                <input type="hidden" id="add-image" />
                <input type="file" id="add-image-file" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" style="display:none" onchange="handleImageSelect('add')" />
                <div class="img-picker">
                    <div class="img-picker-area" id="add-picker-area" onclick="document.getElementById('add-image-file').click()">
                        <div class="img-preview" id="add-img-preview">📦</div>
                        <div class="img-picker-info">
                            <p class="img-picker-name" id="add-img-name">Sin imagen seleccionada</p>
                            <p class="img-picker-hint" id="add-img-hint">Haz clic para seleccionar · JPG, PNG, GIF, SVG · Máx. 2MB</p>
                        </div>
                        <div class="img-picker-btns" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('add-image-file').click()">
                                📁 Seleccionar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="add-img-remove" style="display:none" onclick="removeImage('add')">
                                ✕
                            </button>
                        </div>
                    </div>
                    <p class="img-picker-error" id="add-img-error"></p>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Descripción</label>
                <textarea id="add-desc" class="form-control" rows="3" placeholder="Descripción del producto…" style="resize:vertical"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-add')">Cancelar</button>
            <button class="btn btn-primary" id="btn-create-prod" onclick="createProduct()">Crear producto</button>
        </div>
    </div>
</div>

{{-- ══ MODAL: EDITAR PRODUCTO ══ --}}
<div class="modal-overlay" id="modal-edit" style="display:none" onclick="if(event.target===this)closeModal('modal-edit')">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ Editar Producto</h3>
            <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
        </div>
        <div class="modal-body" style="max-height:70vh;overflow-y:auto">
            <input type="hidden" id="edit-id" />
            <div class="alert alert-info" style="margin-bottom:1.2rem">
                <span>📝</span> Editando: <strong id="edit-title-label">—</strong>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre del producto *</label>
                <input type="text" id="edit-name" class="form-control" />
                <p class="form-error" id="edit-name-error" style="display:none;color:var(--red);font-size:.82rem;margin-top:.3rem"></p>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select id="edit-cat" class="form-control">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Precio (€) *</label>
                    <input type="number" id="edit-price" class="form-control" step="0.01" min="0.01" />
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Stock *</label>
                    <input type="number" id="edit-stock" class="form-control" min="0" />
                </div>
                <div class="form-group" style="display:flex;flex-direction:column;justify-content:flex-end">
                    <label class="form-label" style="display:flex;align-items:center;gap:.6rem;cursor:pointer;margin-bottom:.75rem">
                        <input type="checkbox" id="edit-featured" style="accent-color:var(--gold);width:16px;height:16px" />
                        ⭐ Destacado
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Imagen del producto</label>
                <input type="hidden" id="edit-image" />
                <input type="file" id="edit-image-file" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" style="display:none" onchange="handleImageSelect('edit')" />
                <div class="img-picker">
                    <div class="img-picker-area" id="edit-picker-area" onclick="document.getElementById('edit-image-file').click()">
                        <div class="img-preview" id="edit-img-preview">📦</div>
                        <div class="img-picker-info">
                            <p class="img-picker-name" id="edit-img-name">Sin imagen</p>
                            <p class="img-picker-hint" id="edit-img-hint">Haz clic para cambiar · JPG, PNG, GIF, SVG · Máx. 2MB</p>
                        </div>
                        <div class="img-picker-btns" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('edit-image-file').click()">
                                📁 Cambiar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="edit-img-remove" style="display:none" onclick="removeImage('edit')">
                                ✕
                            </button>
                        </div>
                    </div>
                    <p class="img-picker-error" id="edit-img-error"></p>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Descripción</label>
                <textarea id="edit-desc" class="form-control" rows="3" style="resize:vertical"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger btn-sm" id="btn-delete-prod" style="margin-right:auto"
                onclick="deleteProductFromModal()">🗑️ Eliminar</button>
            <button class="btn btn-secondary" onclick="closeModal('modal-edit')">Cancelar</button>
            <button class="btn btn-primary" id="btn-save-prod" onclick="saveProduct()">Guardar cambios</button>
        </div>
    </div>
</div>

<script>
    let searchDebounceTimeout;

    function doSearchAdmin() {
        const form = document.getElementById('filter-form');
        const url = form.action + '?' + new URLSearchParams(new FormData(form)).toString();

        const resultsContainer = document.getElementById('admin-results-container');
        if (resultsContainer) resultsContainer.style.opacity = '0.5';

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newResults = doc.getElementById('admin-results');
                if (newResults && resultsContainer) {
                    resultsContainer.querySelector('#admin-results').innerHTML = newResults.innerHTML;
                    resultsContainer.style.opacity = '1';
                }
                window.history.pushState({}, '', url);
            })
            .catch(() => {
                form.submit();
            });
    }

    // Prevenimos el submit normal para que se use AJAX
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        doSearchAdmin();
    });

    const API_PROD = '/api/productos';

    function getToken() {
        return Auth.getToken();
    }

    // ── Modales ─────────────────────────────────────────────────────
    document.getElementById('btn-add-product').addEventListener('click', () => {
        ['add-name', 'add-desc'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('add-cat').value = '';
        document.getElementById('add-price').value = '';
        document.getElementById('add-stock').value = '0';
        document.getElementById('add-featured').checked = false;
        ['add-name-error', 'add-cat-error', 'add-price-error'].forEach(id => {
            document.getElementById(id).style.display = 'none';
        });
        resetPicker('add');
        document.getElementById('modal-add').style.display = 'flex';
    });

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openEditModal(id, name, catId, price, stock, featured, image, desc) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-cat').value = catId;
        document.getElementById('edit-price').value = price;
        document.getElementById('edit-stock').value = stock;
        document.getElementById('edit-featured').checked = featured;
        document.getElementById('edit-desc').value = desc;
        document.getElementById('edit-title-label').textContent = name;
        document.getElementById('edit-name-error').style.display = 'none';
        // Carga la imagen actual en el picker
        if (image) {
            setPickerImage('edit', image, image.split('/').pop());
        } else {
            resetPicker('edit');
        }
        document.getElementById('modal-edit').style.display = 'flex';
    }

    // ── Feedback ─────────────────────────────────────────────────────
    function showAlert(msg, type = 'success') {
        const box = document.getElementById('alert-box');
        const colors = {
            success: 'rgba(26,127,55,.15);border:1px solid rgba(26,127,55,.3);color:#2da44e',
            error: 'rgba(185,28,28,.15);border:1px solid rgba(185,28,28,.3);color:#f87171',
        };
        box.style.cssText = `display:flex;align-items:center;gap:.7rem;padding:.9rem 1.2rem;border-radius:4px;font-size:.9rem;background:${colors[type]}`;
        box.textContent = msg;
        clearTimeout(box._t);
        box._t = setTimeout(() => {
            box.style.display = 'none';
        }, 4000);
    }

    function setLoading(btnId, loading) {
        const btn = document.getElementById(btnId);
        btn.disabled = loading;
        btn.style.opacity = loading ? '.6' : '1';
    }

    function firstError(json) {
        if (json.errors) {
            const first = Object.values(json.errors)[0];
            return Array.isArray(first) ? first[0] : first;
        }
        return json.message || 'Error inesperado.';
    }

    // ── Crear ────────────────────────────────────────────────────────
    async function createProduct() {
        const name = document.getElementById('add-name').value.trim();
        const catId = document.getElementById('add-cat').value;
        const price = document.getElementById('add-price').value;
        const stock = document.getElementById('add-stock').value;
        const featured = document.getElementById('add-featured').checked;
        const image = document.getElementById('add-image').value;
        const desc = document.getElementById('add-desc').value.trim();

        let valid = true;
        ['add-name-error', 'add-cat-error', 'add-price-error'].forEach(id =>
            document.getElementById(id).style.display = 'none');

        if (!name) {
            document.getElementById('add-name-error').textContent = 'El nombre es obligatorio.';
            document.getElementById('add-name-error').style.display = 'block';
            valid = false;
        }
        if (!catId) {
            document.getElementById('add-cat-error').textContent = 'Selecciona una categoría.';
            document.getElementById('add-cat-error').style.display = 'block';
            valid = false;
        }
        if (!price) {
            document.getElementById('add-price-error').textContent = 'El precio es obligatorio.';
            document.getElementById('add-price-error').style.display = 'block';
            valid = false;
        }
        if (!valid) return;

        setLoading('btn-create-prod', true);
        const res = await fetch(API_PROD, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify({
                name,
                category_id: parseInt(catId),
                price: parseFloat(price),
                stock: parseInt(stock) || 0,
                featured,
                image_url: image || null,
                description: desc || null,
            }),
        });
        const json = await res.json();
        setLoading('btn-create-prod', false);

        if (!res.ok) {
            showAlert(firstError(json), 'error');
            return;
        }
        closeModal('modal-add');
        showAlert('Producto creado correctamente. Recargando…');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Guardar edición ──────────────────────────────────────────────
    async function saveProduct() {
        const id = document.getElementById('edit-id').value;
        const name = document.getElementById('edit-name').value.trim();
        const catId = document.getElementById('edit-cat').value;
        const price = document.getElementById('edit-price').value;
        const stock = document.getElementById('edit-stock').value;
        const featured = document.getElementById('edit-featured').checked;
        const image = document.getElementById('edit-image').value;
        const desc = document.getElementById('edit-desc').value.trim();

        document.getElementById('edit-name-error').style.display = 'none';
        if (!name) {
            document.getElementById('edit-name-error').textContent = 'El nombre es obligatorio.';
            document.getElementById('edit-name-error').style.display = 'block';
            return;
        }

        setLoading('btn-save-prod', true);
        const res = await fetch(`${API_PROD}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
            body: JSON.stringify({
                name,
                category_id: parseInt(catId),
                price: parseFloat(price),
                stock: parseInt(stock) || 0,
                featured,
                image_url: image || null,
                description: desc || null,
            }),
        });
        const json = await res.json();
        setLoading('btn-save-prod', false);

        if (!res.ok) {
            showAlert(firstError(json), 'error');
            return;
        }
        closeModal('modal-edit');
        showAlert('Producto actualizado correctamente. Recargando…');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Eliminar (desde botón de la tabla) ────────────────────────────
    async function deleteProduct(id, name) {
        if (!confirm(`¿Eliminar "${name}"? Esta acción no se puede deshacer.`)) return;
        const res = await fetch(`${API_PROD}/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${getToken()}`
            },
        });
        const json = await res.json();
        if (!res.ok) {
            showAlert(json.message || 'No se pudo eliminar.', 'error');
            return;
        }
        showAlert('Producto eliminado. Recargando…');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Eliminar (desde modal de edición) ─────────────────────────────
    async function deleteProductFromModal() {
        const id = document.getElementById('edit-id').value;
        const name = document.getElementById('edit-title-label').textContent;
        closeModal('modal-edit');
        await deleteProduct(id, name);
    }

    // ── Image picker ─────────────────────────────────────────────────

    function pickerError(prefix, msg) {
        resetPicker(prefix);
        const el = document.getElementById(`${prefix}-img-error`);
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
        document.getElementById(`${prefix}-picker-area`).classList.add('error');
    }

    function setPickerImage(prefix, url, filename) {
        document.getElementById(`${prefix}-image`).value = url;
        const preview = document.getElementById(`${prefix}-img-preview`);
        preview.innerHTML = `<img src="${url}" onerror="this.parentElement.textContent='📦'">`;
        document.getElementById(`${prefix}-img-name`).textContent = filename || url.split('/').pop();
        document.getElementById(`${prefix}-img-hint`).textContent = 'Imagen cargada · Haz clic para cambiar';
        const area = document.getElementById(`${prefix}-picker-area`);
        area.classList.add('has-image');
        area.classList.remove('error');
        document.getElementById(`${prefix}-img-remove`).style.display = 'inline-flex';
        const errEl = document.getElementById(`${prefix}-img-error`);
        if (errEl) errEl.style.display = 'none';
    }

    function resetPicker(prefix) {
        document.getElementById(`${prefix}-image`).value = '';
        document.getElementById(`${prefix}-image-file`).value = '';
        document.getElementById(`${prefix}-img-preview`).innerHTML = '📦';
        document.getElementById(`${prefix}-img-name`).textContent = prefix === 'add' ? 'Sin imagen seleccionada' : 'Sin imagen';
        document.getElementById(`${prefix}-img-hint`).textContent = 'Haz clic para seleccionar · JPG, PNG, GIF, SVG · Máx. 2MB';
        const area = document.getElementById(`${prefix}-picker-area`);
        area.classList.remove('has-image', 'uploading', 'error');
        document.getElementById(`${prefix}-img-remove`).style.display = 'none';
        const errEl = document.getElementById(`${prefix}-img-error`);
        if (errEl) errEl.style.display = 'none';
    }

    function removeImage(prefix) {
        resetPicker(prefix);
    }

    async function handleImageSelect(prefix) {
        const fileInput = document.getElementById(`${prefix}-image-file`);
        const file = fileInput.files[0];
        if (!file) return;

        // Limpiar error previo
        const errEl = document.getElementById(`${prefix}-img-error`);
        if (errEl) errEl.style.display = 'none';

        // Validación local
        const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
        if (!allowed.includes(file.type)) {
            pickerError(prefix, 'Tipo no válido. Usa JPG, PNG, GIF o SVG.');
            fileInput.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            pickerError(prefix, 'La imagen supera el límite de 2MB.');
            fileInput.value = '';
            return;
        }

        // Estado de carga
        const area = document.getElementById(`${prefix}-picker-area`);
        area.classList.add('uploading');
        area.classList.remove('error');
        document.getElementById(`${prefix}-img-name`).textContent = 'Subiendo imagen…';
        document.getElementById(`${prefix}-img-hint`).textContent = file.name;

        const form = new FormData();
        form.append('image', file);

        try {
            const res = await fetch('/api/imagenes', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${getToken()}`
                },
                body: form,
            });
            const json = await res.json();
            area.classList.remove('uploading');

            if (!res.ok) {
                const msg = json.errors?.image?.[0] || json.message || 'Error al subir la imagen.';
                pickerError(prefix, `Error ${res.status}: ${msg}`);
                return;
            }

            setPickerImage(prefix, json.image, file.name);
        } catch (e) {
            area.classList.remove('uploading');
            pickerError(prefix, 'Error de red. Comprueba la conexión e inténtalo de nuevo.');
        }
    }

    // ── Logout ───────────────────────────────────────────────────────
    document.getElementById('admin-logout')?.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${getToken()}`
                }
            });
        } catch {}
        Auth.clear();
        window.location.href = "{{ route('shop') }}";
    });
</script>
@endsection