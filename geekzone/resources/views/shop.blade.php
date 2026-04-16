@extends('layouts.layout')
@section('title', 'GeekZone - Tienda')

@section('content')
    <section class="hero">
        <div class="hero-content fade-in-up">
            <p class="hero-eyebrow">✦ La tienda friki definitiva ✦</p>
            <h1 class="hero-title">
                Tu <span>universo</span><br>
                friki<br>
                te espera
            </h1>
            <p class="hero-sub">
                Figuras, ropa, coleccionables y mucho más. Marvel, K-Pop y Fútbol en un solo lugar.
            </p>
            <div class="hero-cta">
                <a href="{{ route('catalog') }}" class="btn-primary">Explorar tienda</a>
            </div>
        </div>
        <div class="scroll-hint fade-in delay-4">
            <span>Scroll</span>
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <div class="ticker">
        <div class="ticker-inner">
            <span>MARVEL</span><span class="dot">✦</span><span>K-POP</span><span class="dot">✦</span>
            <span>FÚTBOL</span><span class="dot">✦</span><span>ENVÍOS GRATIS +50€</span><span class="dot">✦</span>
            <span>NUEVOS PRODUCTOS CADA SEMANA</span><span class="dot">✦</span>
            <span>MARVEL</span><span class="dot">✦</span><span>K-POP</span><span class="dot">✦</span>
            <span>FÚTBOL</span><span class="dot">✦</span><span>ENVÍOS GRATIS +50€</span><span class="dot">✦</span>
            <span>NUEVOS PRODUCTOS CADA SEMANA</span><span class="dot">✦</span>
        </div>
    </div>

    <section class="section fade-in-up" id="categorias">
        <div class="section-header fade-in-up delay-1">
            <div>
                <p class="section-eyebrow">Explora por género</p>
                <h2 class="section-title">Nuestras Categorías</h2>
            </div>
        </div>

        <div class="cat-grid">
            @foreach ($categories as $category)
                <div class="cat-card">
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy">
                    <div class="cat-bg"></div>
                    <div class="cat-pattern"></div>
                    <div class="cat-content">
                        <h3 class="cat-name">{{ $category->name }}</h3>
                        <p class="cat-desc">{{ $category->description }}</p>
                        <a href="{{ route('shop', ['category' => $category->id]) }}#productos" class="cat-btn">Explorar</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section" id="productos">
        <div class="section-header">
            <div>
                <p class="section-eyebrow">Lo más top</p>
                <h2 class="section-title">Productos Destacados</h2>
            </div>
            <a href="{{ route('catalog') }}" class="btn-secondary">Ver catálogo completo →</a>
        </div>

        <div class="prod-grid" id="prod-grid">
            @foreach ($products as $product)
                <a href="{{ route('product.show', $product->id) }}" class="prod-card{{ $product->stock <= 0 ? ' out-of-stock' : '' }}" style="text-decoration:none">
                    <div class="prod-img">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">
                        <div class="prod-badge new">Destacado</div>
                        @if($product->stock <= 0)
                            <div class="prod-badge out-of-stock" style="top:.7rem;left:auto;right:.7rem">Sin stock</div>
                        @endif
                    </div>
                    <div class="prod-info">
                        <p class="prod-cat">{{ $product->category->name }}</p>
                        <p class="prod-name">{{ $product->name }}</p>
                        <div class="prod-footer">
                            <p class="prod-price">{{ $product->price }}€</p>
                            <button class="add-btn" data-product-id="{{ $product->id }}"
                                    onclick="event.preventDefault()"
                                    {{ $product->stock <= 0 ? 'disabled' : '' }}
                                    title="{{ $product->stock <= 0 ? 'Sin stock' : 'Añadir al carrito' }}">+</button>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <script>
            // ——— Añadir al carrito ———
            function showToast(msg, color = '#0047AB') {
                let t = document.getElementById('shop-toast');
                if (!t) {
                    t = document.createElement('div');
                    t.id = 'shop-toast';
                    t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;padding:.8rem 1.5rem;border-radius:6px;font-family:"Barlow Condensed",sans-serif;letter-spacing:1px;font-size:.9rem;text-transform:uppercase;color:#fff;opacity:0;transition:opacity .3s;pointer-events:none;z-index:9999;';
                    document.body.appendChild(t);
                }
                t.textContent = msg;
                t.style.background = color;
                t.style.opacity = '1';
                clearTimeout(t._timer);
                t._timer = setTimeout(() => t.style.opacity = '0', 2500);
            }

            document.getElementById('prod-grid').addEventListener('click', async (e) => {
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
                    body: JSON.stringify({ product_id: parseInt(productId), quantity: 1 })
                });

                btn.disabled = false;

                if (res.ok) {
                    showToast('Añadido al carrito ✓');
                    // Actualizar contador en header
                    const countEl = document.getElementById('cartCount');
                    if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + 1;
                } else {
                    const err = await res.json();
                    showToast(err.message || 'Error al añadir', '#c0392b');
                }
            });
        </script>

        {{ $products->appends(request()->query())->links('vendor.pagination.custom') }}
    </section>
