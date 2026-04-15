@extends('layouts.layout')
@section('title', 'GeekZone - Tienda')

@section('content')
    <section class="hero">
        <div class="hero-content">
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
                <a href="#categorias" class="btn-primary">Explorar tienda</a>
            </div>
        </div>
        <div class="scroll-hint">
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

    <section class="section" id="categorias">
        <div class="section-header">
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
        <div class="filter-bar">
            <a href="{{ route('shop') }}#productos"
               class="filter-pill {{ is_null($activeCategory) ? 'active' : '' }}">Todos</a>
            @foreach ($categories as $category)
                <a href="{{ route('shop', ['category' => $category->id]) }}#productos"
                   class="filter-pill {{ $activeCategory == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
            <div class="filter-right">
                <div class="search-wrap">
                    <input id="search-input" type="text" class="form-control" placeholder="Buscar producto…"
                        style="width:220px;padding:.45rem 1rem .45rem 2.2rem" />
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <p style="color:var(--grey);font-size:.88rem">
                <strong id="product-count" style="color:var(--white)">{{ $products->total() }} productos</strong> encontrados
            </p>
        </div>

        <div class="prod-grid" id="prod-grid">
            @foreach ($products as $product)
                <div class="prod-card" data-name="{{ strtolower($product->name) }}">
                    <div class="prod-img">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                        <div class="prod-badge new">Nuevo</div>
                    </div>
                    <div class="prod-info">
                        <p class="prod-cat">{{ $product->category->name }}</p>
                        <p class="prod-name">{{ $product->name }}</p>
                        <div class="prod-footer">
                            <p class="prod-price">{{ $product->price }}€</p>
                            <button class="add-btn" data-product-id="{{ $product->id }}">+</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <script>
            // Búsqueda en tiempo real
            const searchInput = document.getElementById('search-input');
            const cards = document.querySelectorAll('#prod-grid .prod-card');
            const countEl = document.getElementById('product-count');

            const totalProducts = {{ $products->total() }};
            searchInput.addEventListener('input', () => {
                const term = searchInput.value.toLowerCase().trim();
                if (!term) {
                    cards.forEach(card => card.style.display = '');
                    countEl.textContent = `${totalProducts} productos`;
                    return;
                }
                let visible = 0;
                cards.forEach(card => {
                    const matches = card.dataset.name.includes(term);
                    card.style.display = matches ? '' : 'none';
                    if (matches) visible++;
                });
                countEl.textContent = `${visible} productos`;
            });

            // Scroll automático a #productos si hay un filtro activo en la URL
            if (window.location.hash === '#productos' || new URLSearchParams(window.location.search).has('category')) {
                document.getElementById('productos').scrollIntoView({ behavior: 'smooth' });
            }

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
                if (!btn) return;

                const token = localStorage.getItem('token') || sessionStorage.getItem('token');
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
