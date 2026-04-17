@extends('layouts.layout')
@section('title', 'GeekZone - Catálogo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/catalogo.css') }}">
@endpush

@section('content')
<form method="GET" action="{{ route('catalog') }}" id="catalog-form">

    <div class="catalog-layout">

        {{-- ══ SIDEBAR FILTERS ══ --}}
        <aside class="filters-panel">

            <div class="filters-header">
                <h3>Filtros</h3>
                <a href="{{ route('catalog') }}" class="filters-reset">Limpiar todo</a>
            </div>

            {{-- Active filter chips --}}
            @php
            $hasFilters = count($activeCategories) || request()->filled('min_price') || request()->filled('max_price') || request()->filled('search') || request()->boolean('featured') || request()->boolean('in_stock');
            @endphp
            @if($hasFilters)
            <div class="active-filters">
                @foreach($categories->whereIn('id', $activeCategories) as $cat)
                <span class="active-chip">{{ $cat->name }} <span class="x" onclick="removeFilter('categories[]', '{{ $cat->id }}')">✕</span></span>
                @endforeach
                @if(request()->boolean('featured'))
                <span class="active-chip">Destacados <span class="x" onclick="removeCheckFilter('featured')">✕</span></span>
                @endif
                @if(request()->boolean('in_stock'))
                <span class="active-chip">En stock <span class="x" onclick="removeCheckFilter('in_stock')">✕</span></span>
                @endif
                @if(request()->filled('min_price') || request()->filled('max_price'))
                <span class="active-chip">
                    {{ request('min_price', '0') }}€ — {{ request('max_price', '∞') }}€
                    <span class="x" onclick="removePriceFilter()">✕</span>
                </span>
                @endif
            </div>
            @endif

            {{-- Categoría --}}
            <div class="filter-group">
                <p class="filter-group-title">Categoría</p>
                @foreach($categories as $category)
                <label class="filter-check">
                    <div class="filter-check-left">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                            {{ in_array($category->id, $activeCategories) ? 'checked' : '' }} />
                        {{ $category->name }}
                    </div>
                    <span class="filter-check-count">{{ $category->products_count }}</span>
                </label>
                @endforeach
            </div>

            {{-- Precio --}}
            <div class="filter-group">
                <p class="filter-group-title">Precio (€)</p>
                <div class="price-range-row">
                    <input type="number" name="min_price" class="price-input"
                        placeholder="0" min="0" value="{{ request('min_price') }}" />
                    <span class="price-sep">—</span>
                    <input type="number" name="max_price" class="price-input"
                        placeholder="200" min="0" value="{{ request('max_price') }}" />
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:.8rem;gap:.4rem;flex-wrap:wrap">
                    <button type="button" class="filter-pill price-preset" style="font-size:.68rem;padding:.3rem .7rem"
                        onclick="setPriceRange(0,25)">-25€</button>
                    <button type="button" class="filter-pill price-preset" style="font-size:.68rem;padding:.3rem .7rem"
                        onclick="setPriceRange(25,50)">25-50€</button>
                    <button type="button" class="filter-pill price-preset" style="font-size:.68rem;padding:.3rem .7rem"
                        onclick="setPriceRange(50,100)">50-100€</button>
                    <button type="button" class="filter-pill price-preset" style="font-size:.68rem;padding:.3rem .7rem"
                        onclick="setPriceRange(100,9999)">+100€</button>
                </div>
            </div>

            {{-- Estado --}}
            <div class="filter-group">
                <p class="filter-group-title">Estado</p>
                <label class="filter-check">
                    <div class="filter-check-left">
                        <input type="checkbox" name="featured" value="1"
                            {{ request()->boolean('featured') ? 'checked' : '' }} />
                        ⭐ Destacados
                    </div>
                </label>
                <label class="filter-check">
                    <div class="filter-check-left">
                        <input type="checkbox" name="in_stock" value="1"
                            {{ request()->boolean('in_stock') ? 'checked' : '' }} />
                        ✅ En stock
                    </div>
                </label>
            </div>

            <div class="filter-group" style="padding-bottom:1.2rem">
                <button type="submit" class="btn btn-primary btn-block" style="justify-content:center">
                    Aplicar filtros
                </button>
            </div>

        </aside>

        {{-- ══ MAIN CATALOG ══ --}}
        <div class="catalog-main">

            {{-- Search --}}
            <div style="margin-bottom:1.2rem">
                <div class="search-wrap" style="width:100%">
                    <input type="text" name="search" class="form-control" id="searchInput"
                        placeholder="Buscar producto, marca, personaje…"
                        style="width:100%;padding:.6rem 1rem .6rem 2.4rem;font-size:.95rem"
                        value="{{ request('search') }}"
                        oninput="clearTimeout(this.delay); this.delay = setTimeout(() => { document.getElementById('catalog-form').submit(); }, 700);" />
                </div>
            </div>

            <div class="catalog-topbar">
                <p class="catalog-count">
                    <strong>{{ $products->total() }} producto{{ $products->total() !== 1 ? 's' : '' }}</strong> encontrado{{ $products->total() !== 1 ? 's' : '' }}
                </p>
            </div>

            {{-- Product grid --}}
            <div class="catalog-grid" id="catalog-grid">
                @forelse($products as $product)
                <a href="{{ route('product.show', $product->id) }}" class="prod-card{{ $product->stock <= 0 ? ' out-of-stock' : '' }}" style="text-decoration:none">
                    <div class="prod-img">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async"
                            style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
                        @if($product->featured)
                        <div class="prod-badge new">Destacado</div>
                        @endif
                        @if($product->stock <= 0)
                            <div class="prod-badge out-of-stock" style="top:.7rem;left:auto;right:.7rem">Sin stock
                    </div>
                    @endif
            </div>
            <div class="prod-info">
                <p class="prod-cat">{{ $product->category->name }}</p>
                <p class="prod-name">{{ $product->name }}</p>
                <div class="prod-footer">
                    <p class="prod-price">{{ $product->price }}€</p>
                    <button type="button" class="add-btn" data-product-id="{{ $product->id }}"
                        onclick="event.preventDefault()"
                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                        title="{{ $product->stock <= 0 ? 'Sin stock' : 'Añadir al carrito' }}">+</button>
                </div>
            </div>
            </a>
            @empty
            <div style="grid-column:1/-1;text-align:center;padding:4rem 0;color:var(--grey)">
                <p style="font-size:1.1rem">No se encontraron productos con los filtros seleccionados.</p>
                <a href="{{ route('catalog') }}" style="color:var(--cobalt-light);margin-top:.5rem;display:inline-block">
                    Limpiar filtros →
                </a>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="catalog-pagination-bar">
            <p style="font-size:.85rem;color:var(--grey)">
                @if($products->total() > 0)
                Mostrando
                <strong style="color:var(--white)">{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
                de <strong style="color:var(--white)">{{ $products->total() }} productos</strong>
                @endif
            </p>
            {{ $products->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>

    </div>
    </div>

</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) {
            const input = document.getElementById('searchInput');
            if (input) {
                input.focus();
                // Pon el cursor al final del texto
                const val = input.value;
                input.value = '';
                input.value = val;
            }
        }
    });

    function setPriceRange(min, max) {
        document.querySelector('input[name="min_price"]').value = min === 0 ? '' : min;
        document.querySelector('input[name="max_price"]').value = max === 9999 ? '' : max;
        // Highlight active preset pill
        document.querySelectorAll('.price-preset').forEach(b => b.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }

    function removeFilter(name, value) {
        const inputs = document.querySelectorAll(`input[name="${name}"][value="${value}"]`);
        inputs.forEach(i => i.checked = false);
        document.getElementById('catalog-form').submit();
    }

    function removeCheckFilter(name) {
        const input = document.querySelector(`input[name="${name}"]`);
        if (input) input.checked = false;
        document.getElementById('catalog-form').submit();
    }

    function removePriceFilter() {
        document.querySelector('input[name="min_price"]').value = '';
        document.querySelector('input[name="max_price"]').value = '';
        document.getElementById('catalog-form').submit();
    }

    // ——— Añadir al carrito ———
    function showToast(msg, color = '#0047AB') {
        let t = document.getElementById('catalog-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'catalog-toast';
            t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;padding:.8rem 1.5rem;border-radius:6px;font-family:"Barlow Condensed",sans-serif;letter-spacing:1px;font-size:.9rem;text-transform:uppercase;color:#fff;opacity:0;transition:opacity .3s;pointer-events:none;z-index:9999;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.background = color;
        t.style.opacity = '1';
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.style.opacity = '0', 2500);
    }

    document.getElementById('catalog-grid').addEventListener('click', async (e) => {
        const btn = e.target.closest('.add-btn');
        if (!btn || btn.disabled) return;

        const token = Auth.getToken();
        if (!token) {
            showToast('Inicia sesión para añadir productos', '#c0392b');
            return;
        }

        btn.disabled = true;
        const productId = btn.dataset.productId;

        const res = await fetch('/api/carrito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                product_id: parseInt(productId),
                quantity: 1
            })
        });

        btn.disabled = false;

        if (res.ok) {
            showToast('Añadido al carrito ✓');
            const countEl = document.getElementById('cartCount');
            if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + 1;
        } else {
            const err = await res.json();
            showToast(err.message || 'Error al añadir', '#c0392b');
        }
    });
</script>
@endsection