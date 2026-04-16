@extends('layouts.layout')
@section('title', 'GeekZone — ' . $product->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/producto.css') }}">
@endpush

@section('content')

{{-- ══ PRODUCT MAIN ══ --}}
<div class="product-wrap">

    {{-- GALLERY --}}
    <div class="gallery">
        <div class="gallery-main">
            <div class="gallery-badges">
                @if($product->featured)
                    <span class="badge badge-cobalt">Destacado</span>
                @endif
                @if($product->stock > 0 && $product->stock <= 10)
                    <span class="badge badge-red">Últimas unidades</span>
                @endif
            </div>
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                 style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;border-radius:12px;">
        </div>
    </div>

    {{-- INFO --}}
    <div class="product-info">

        <div class="breadcrumb">
            <a href="{{ route('shop') }}">Inicio</a>
            <span>›</span>
            <a href="{{ route('catalog', ['categories[]' => $product->category_id]) }}">{{ $product->category->name }}</a>
            <span>›</span>
            <span style="color:var(--grey)">{{ $product->name }}</span>
        </div>

        <p class="product-cat">{{ $product->category->name }}</p>
        <h1 class="product-title">{{ $product->name }}</h1>

        <div class="price-row">
            <span class="price-now">{{ number_format($product->price, 2, ',', '.') }}€</span>
        </div>

        {{-- Stock --}}
        <div class="stock-row">
            @if($product->stock <= 0)
                <span class="stock-dot" style="background:var(--red);box-shadow:0 0 6px var(--red)"></span>
                <span style="color:var(--red);font-weight:600">Sin stock</span>
            @elseif($product->stock <= 10)
                <span class="stock-dot low"></span>
                <span style="color:var(--gold);font-weight:600">Solo {{ $product->stock }} unidades</span>
            @else
                <span class="stock-dot ok"></span>
                <span style="color:var(--green);font-weight:600">En stock</span>
            @endif
        </div>

        @if($product->description)
        <p style="color:var(--grey);font-size:.92rem;line-height:1.7;margin-bottom:1.4rem;">
            {{ $product->description }}
        </p>
        @endif

        {{-- CTA --}}
        <div class="cta-block">
            <div class="qty-add-row">
                <div class="qty-ctrl">
                    <button type="button" id="qty-minus">−</button>
                    <span id="qty-value">1</span>
                    <button type="button" id="qty-plus">+</button>
                </div>
                <button class="btn btn-primary btn-lg" style="flex:1"
                    id="add-to-cart-btn"
                    data-product-id="{{ $product->id }}"
                    {{ $product->stock <= 0 ? 'disabled' : '' }}>
                    🛒 Añadir al carrito
                </button>
                <div class="btn-wish" id="wish-btn" title="Añadir a favoritos">♡</div>
            </div>
        </div>

        {{-- Guarantees --}}
        <div class="guarantees">
            <div class="g-item"><span class="gi">🚚</span><p>Envío gratis desde 50€</p></div>
            <div class="g-item"><span class="gi">↩️</span><p>Devolución 30 días</p></div>
            <div class="g-item"><span class="gi">🔒</span><p>Pago seguro</p></div>
        </div>

    </div>
</div>

{{-- ══ RELATED PRODUCTS ══ --}}
@if($related->isNotEmpty())
<div class="related-section">
    <div class="section-header">
        <div>
            <p class="section-eyebrow">Te puede interesar</p>
            <h2 class="section-title">Productos relacionados</h2>
        </div>
        <a href="{{ route('catalog', ['categories[]' => $product->category_id]) }}" class="see-all">
            Ver {{ $product->category->name }} →
        </a>
    </div>
    <div class="prod-grid" id="related-grid">
        @foreach($related as $rel)
        <a href="{{ route('product.show', $rel->id) }}" class="prod-card" style="text-decoration:none">
            <div class="prod-img">
                <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}" loading="lazy"
                     style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
                @if($rel->featured)
                    <div class="prod-badge new">Destacado</div>
                @endif
            </div>
            <div class="prod-info">
                <p class="prod-cat">{{ $rel->category->name }}</p>
                <p class="prod-name">{{ $rel->name }}</p>
                <div class="prod-footer">
                    <p class="prod-price">{{ number_format($rel->price, 2, ',', '.') }}€</p>
                    <button type="button" class="add-btn related-add-btn"
                            data-product-id="{{ $rel->id }}"
                            onclick="event.preventDefault()">+</button>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<script>
    // ——— Cantidad ———
    const qtyMinus = document.getElementById('qty-minus');
    const qtyPlus  = document.getElementById('qty-plus');
    const qtyValue = document.getElementById('qty-value');
    let qty = 1;

    qtyMinus.addEventListener('click', () => { if (qty > 1) qtyValue.textContent = --qty; });
    qtyPlus.addEventListener('click',  () => { if (qty < {{ $product->stock }}) qtyValue.textContent = ++qty; });

    // ——— Toast ———
    function showToast(msg, color = '#0047AB') {
        let t = document.getElementById('product-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'product-toast';
            t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;padding:.8rem 1.5rem;border-radius:6px;font-family:"Barlow Condensed",sans-serif;letter-spacing:1px;font-size:.9rem;text-transform:uppercase;color:#fff;opacity:0;transition:opacity .3s;pointer-events:none;z-index:9999;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.background = color;
        t.style.opacity = '1';
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.style.opacity = '0', 2500);
    }

    async function addToCart(productId, quantity = 1) {
        const token = Auth.getToken();
        if (!token) { showToast('Inicia sesión para añadir productos', '#c0392b'); return; }

        const res = await fetch('/api/carrito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ product_id: parseInt(productId), quantity })
        });

        if (res.ok) {
            showToast('Añadido al carrito ✓');
            const countEl = document.getElementById('cartCount');
            if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + quantity;
        } else {
            const err = await res.json();
            showToast(err.message || 'Error al añadir', '#c0392b');
        }
    }

    // ——— Añadir al carrito (principal) ———
    const addBtn = document.getElementById('add-to-cart-btn');
    if (addBtn) {
        addBtn.addEventListener('click', async () => {
            addBtn.disabled = true;
            await addToCart(addBtn.dataset.productId, qty);
            addBtn.disabled = false;
        });
    }

    // ——— Favorito ———
    const wishBtn    = document.getElementById('wish-btn');
    const PRODUCT_ID = {{ $product->id }};
    let   isFav      = false;

    function setWishState(active) {
        isFav = active;
        wishBtn.textContent   = active ? '♥' : '♡';
        wishBtn.style.color       = active ? 'var(--red)' : '';
        wishBtn.style.borderColor = active ? 'var(--red)' : '';
        wishBtn.title = active ? 'Quitar de favoritos' : 'Añadir a favoritos';
    }

    async function initWishBtn() {
        if (!Auth.isLoggedIn()) return;
        try {
            const res  = await fetch('/api/favoritos', {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${Auth.getToken()}` }
            });
            if (!res.ok) return;
            const data = await res.json();
            const found = (data.favorites ?? []).some(f => f.product_id === PRODUCT_ID);
            setWishState(found);
        } catch {}
    }

    if (wishBtn) {
        initWishBtn();

        wishBtn.addEventListener('click', async () => {
            if (!Auth.isLoggedIn()) {
                showToast('Inicia sesión para guardar favoritos', '#c0392b');
                return;
            }

            wishBtn.style.opacity = '.5';
            wishBtn.style.pointerEvents = 'none';

            try {
                if (isFav) {
                    const res = await fetch(`/api/favoritos/${PRODUCT_ID}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${Auth.getToken()}` }
                    });
                    if (res.ok) { setWishState(false); showToast('Eliminado de favoritos'); }
                } else {
                    const res = await fetch('/api/favoritos', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${Auth.getToken()}` },
                        body: JSON.stringify({ product_id: PRODUCT_ID })
                    });
                    if (res.ok) { setWishState(true); showToast('Añadido a favoritos ♥', '#e01020'); }
                }
            } catch {
                showToast('Error al actualizar favoritos', '#c0392b');
            } finally {
                wishBtn.style.opacity = '';
                wishBtn.style.pointerEvents = '';
            }
        });
    }

    // ——— Añadir al carrito (relacionados) ———
    @if($related->isNotEmpty())
    document.getElementById('related-grid').addEventListener('click', async (e) => {
        const btn = e.target.closest('.related-add-btn');
        if (!btn) return;
        e.preventDefault();
        btn.disabled = true;
        await addToCart(btn.dataset.productId, 1);
        btn.disabled = false;
    });
    @endif
</script>

@endsection
