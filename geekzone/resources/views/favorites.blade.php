@extends('layouts.layout')
@section('title', 'GeekZone — Mis Favoritos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/userPanel.css') }}">
    <link rel="stylesheet" href="{{ asset('css/favoritosUsuario.css') }}">
@endpush

@section('content')
<div class="layout-sidebar">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="avatar avatar-md" id="sidebar-initials">--</div>
            <div>
                <p class="sidebar-name" id="sidebar-name">Cargando…</p>
                <p class="sidebar-role" id="sidebar-role">usuario</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <p class="sidebar-label">Mi cuenta</p>
            <a href="{{ route('panel') }}" class="sidebar-link">
                <span class="icon">👤</span> Mi perfil
            </a>
            <a href="{{ route('panel') }}?tab=pedidos" class="sidebar-link">
                <span class="icon">📦</span> Mis pedidos
            </a>
            <a href="{{ route('favorites') }}" class="sidebar-link active">
                <span class="icon">❤️</span> Favoritos
                <span class="tab-badge" id="sidebar-fav-count" style="margin-left:auto"></span>
            </a>
        </nav>

        <hr class="sidebar-divider">
        <a href="#" class="sidebar-link sidebar-logout" id="panel-logout">
            <span class="icon">↩</span> Cerrar sesión
        </a>
    </aside>

    <!-- ══ MAIN ══ -->
    <main class="main-content">

        <div class="panel-header">
            <p class="page-eyebrow">Lista de deseos</p>
            <h2 class="panel-title">Mis <span>Favoritos</span></h2>
        </div>

        <!-- COLLECTION BAR -->
        <div class="collection-bar">
            <div class="collection-name">
                <span style="font-size:1.4rem">❤️</span>
                <div>
                    <h3>Mi lista principal</h3>
                    <span id="fav-count-label">Cargando…</span>
                </div>
            </div>
            <div class="collection-bar-right">
                <div class="wish-filter-bar" id="filter-bar">
                    <button class="filter-pill active" data-filter="all">Todos</button>
                </div>
                <select class="filter-select" id="sort-select">
                    <option value="recent">Recientes primero</option>
                    <option value="price_asc">Precio: menor a mayor</option>
                    <option value="price_desc">Precio: mayor a menor</option>
                    <option value="available">Disponibles primero</option>
                </select>
            </div>
        </div>

        <!-- WISHLIST GRID -->
        <div class="wishlist-grid" id="favorites-grid">
            <div style="grid-column:1/-1;text-align:center;padding:4rem 0;color:var(--grey)">
                <p style="font-size:1.5rem">⏳</p>
                <p>Cargando favoritos…</p>
            </div>
        </div>

    </main>
</div>

<script>
(function () {
    const token = () => Auth.getToken();

    const api = async (url, options = {}) => {
        const res = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token()}`,
                ...options.headers,
            },
            ...options,
        });
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data };
    };

    const initials = (name, surname) =>
        ((name?.[0] ?? '') + (surname?.[0] ?? '')).toUpperCase() || '?';

    // ── Autenticación ─────────────────────────────────────────────────────────
    if (!token()) {
        window.location.href = "{{ route('login') }}";
        return;
    }

    // ── URLs base ─────────────────────────────────────────────────────────────
    const productBaseUrl = "{{ url('/producto') }}";

    // ── Estado ────────────────────────────────────────────────────────────────
    let allFavorites = [];
    let activeFilter = 'all';

    // ── Cargar perfil (sidebar) ───────────────────────────────────────────────
    async function loadProfile() {
        const { ok, data } = await api('/api/perfil');
        if (!ok) { window.location.href = "{{ route('login') }}"; return; }
        const u = data.user;
        document.getElementById('sidebar-initials').textContent = initials(u.name, u.surname);
        document.getElementById('sidebar-name').textContent = `${u.name} ${u.surname ?? ''}`.trim();
        document.getElementById('sidebar-role').textContent = u.role === 'admin' ? '⚙️ Administrador' : 'Cliente';
    }

    // ── Cargar favoritos ──────────────────────────────────────────────────────
    async function loadFavorites() {
        const { ok, data } = await api('/api/favoritos');
        if (!ok) { renderEmpty('No se pudieron cargar los favoritos.'); return; }

        allFavorites = data.favorites ?? [];
        buildFilterPills();
        renderGrid();
        updateCountLabel();
    }

    function updateCountLabel() {
        const count = allFavorites.length;
        document.getElementById('fav-count-label').textContent =
            count === 1 ? '1 producto guardado' : `${count} productos guardados`;
        const badge = document.getElementById('sidebar-fav-count');
        if (badge) badge.textContent = count > 0 ? count : '';
    }

    // ── Construir pills de categorías ─────────────────────────────────────────
    function buildFilterPills() {
        const categories = [...new Set(
            allFavorites
                .map(f => f.product?.category?.name)
                .filter(Boolean)
        )];

        const bar = document.getElementById('filter-bar');
        bar.innerHTML = `<button class="filter-pill ${activeFilter === 'all' ? 'active' : ''}" data-filter="all">Todos</button>`;

        categories.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'filter-pill' + (activeFilter === cat ? ' active' : '');
            btn.dataset.filter = cat;
            btn.textContent = cat;
            bar.appendChild(btn);
        });

        bar.querySelectorAll('.filter-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                activeFilter = btn.dataset.filter;
                bar.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                renderGrid();
            });
        });
    }

    // ── Ordenar y filtrar ─────────────────────────────────────────────────────
    function getSorted() {
        let list = activeFilter === 'all'
            ? [...allFavorites]
            : allFavorites.filter(f => f.product?.category?.name === activeFilter);

        const sort = document.getElementById('sort-select').value;
        if (sort === 'price_asc')   list.sort((a, b) => a.product.price - b.product.price);
        if (sort === 'price_desc')  list.sort((a, b) => b.product.price - a.product.price);
        if (sort === 'available')   list.sort((a, b) => (b.product.stock > 0) - (a.product.stock > 0));
        // 'recent' is already newest-first from API

        return list;
    }

    // ── Render grid ───────────────────────────────────────────────────────────
    function renderGrid() {
        const grid = document.getElementById('favorites-grid');
        const list = getSorted();

        if (list.length === 0) {
            grid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:4rem 0;color:var(--grey)">
                    <p style="font-size:3rem;margin-bottom:1rem">💔</p>
                    <p style="font-family:'Barlow Condensed',sans-serif;font-size:1.1rem;letter-spacing:2px;text-transform:uppercase">
                        ${activeFilter === 'all' ? 'Todavía no tienes favoritos' : 'No hay productos en esta categoría'}
                    </p>
                    <a href="{{ route('shop') }}" class="btn btn-primary" style="margin-top:1.5rem">Ir a la tienda</a>
                </div>`;
            return;
        }

        grid.innerHTML = list.map(fav => cardHTML(fav)).join('') + addMoreCard();
        grid.querySelectorAll('.wish-remove').forEach(btn => {
            btn.addEventListener('click', () => removeFromFavorites(btn.dataset.productId));
        });
        grid.querySelectorAll('.btn-add-cart').forEach(btn => {
            btn.addEventListener('click', () => addToCart(btn.dataset.productId));
        });
    }

    function cardHTML(fav) {
        const p = fav.product;
        const inStock = p.stock > 0;
        const lowStock = p.stock > 0 && p.stock <= 3;
        const catName = p.category?.name ?? '';

        const stockTag = inStock
            ? (lowStock
                ? `<span class="wish-stock-tag" style="background:rgba(255,215,0,.15);color:var(--gold);border:1px solid rgba(255,215,0,.3)">⚠️ Últimas ${p.stock}</span>`
                : `<span class="wish-stock-tag" style="background:rgba(34,197,94,.2);color:var(--green);border:1px solid rgba(34,197,94,.3)">En stock</span>`)
            : `<span class="wish-stock-tag" style="background:rgba(224,16,32,.2);color:var(--red);border:1px solid rgba(224,16,32,.3)">Sin stock</span>`;

        const priceHTML = `<p class="wish-price">${parseFloat(p.price).toFixed(2)}€</p>`;

        const actionsHTML = inStock
            ? `<button class="btn btn-primary btn-sm btn-add-cart" style="flex:1;justify-content:center" data-product-id="${p.id}">🛒 Añadir</button>
               <a href="${productBaseUrl}/${p.id}" class="btn btn-secondary btn-sm">👁</a>`
            : `<button class="btn btn-secondary btn-sm" style="flex:1;justify-content:center;cursor:not-allowed;opacity:.6" disabled>Sin stock</button>
               <button class="btn btn-secondary btn-sm" title="Avísame cuando esté disponible">🔔</button>
               <a href="${productBaseUrl}/${p.id}" class="btn btn-secondary btn-sm">👁</a>`;

        const imgContent = p.image_url
            ? `<img src="${p.image_url}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0">`
            : `<span style="font-size:3.5rem;z-index:1">🛍️</span>`;

        return `
        <div class="wish-card${!inStock ? ' out-of-stock' : ''}">
            <div class="wish-img" style="position:relative">
                ${imgContent}
                <button class="wish-remove" title="Quitar de favoritos" data-product-id="${p.id}">♡</button>
                ${stockTag}
            </div>
            <div class="wish-info">
                <p class="wish-cat">${catName}</p>
                <p class="wish-name">${p.name}</p>
                <div class="wish-price-row">${priceHTML}</div>
                <div class="wish-actions">${actionsHTML}</div>
            </div>
        </div>`;
    }

    function addMoreCard() {
        return `
        <div style="background:transparent;border:2px dashed rgba(0,71,171,.3);border-radius:10px;
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:.8rem;padding:2rem;cursor:pointer;transition:all .25s;min-height:250px"
            onmouseover="this.style.borderColor='var(--cobalt)';this.style.background='rgba(0,71,171,.05)'"
            onmouseout="this.style.borderColor='rgba(0,71,171,.3)';this.style.background='transparent'">
            <span style="font-size:2.5rem;opacity:.35">＋</span>
            <p style="font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:3px;
                text-transform:uppercase;color:rgba(156,163,175,.45);text-align:center">
                Explorar tienda<br/>para añadir más
            </p>
            <a href="{{ route('shop') }}" class="btn btn-secondary btn-sm">Ir a la tienda</a>
        </div>`;
    }

    function renderEmpty(msg) {
        document.getElementById('favorites-grid').innerHTML = `
            <div style="grid-column:1/-1;text-align:center;padding:4rem 0;color:var(--grey)">
                <p style="font-size:2rem">⚠️</p><p>${msg}</p>
            </div>`;
    }

    // ── Quitar de favoritos ───────────────────────────────────────────────────
    async function removeFromFavorites(productId) {
        const { ok } = await api(`/api/favoritos/${productId}`, { method: 'DELETE' });
        if (ok) {
            allFavorites = allFavorites.filter(f => f.product_id != productId);
            buildFilterPills();
            renderGrid();
            updateCountLabel();
        }
    }

    // ── Añadir al carrito ─────────────────────────────────────────────────────
    async function addToCart(productId) {
        const { ok, data } = await api('/api/carrito', {
            method: 'POST',
            body: JSON.stringify({ product_id: parseInt(productId), quantity: 1 }),
        });

        const btn = document.querySelector(`.btn-add-cart[data-product-id="${productId}"]`);
        if (!btn) return;

        if (ok) {
            const original = btn.textContent;
            btn.textContent = '✅ Añadido';
            btn.disabled = true;
            setTimeout(() => { btn.textContent = original; btn.disabled = false; }, 2000);
        } else {
            const original = btn.textContent;
            btn.textContent = data.message ?? '❌ Error';
            btn.disabled = true;
            setTimeout(() => { btn.textContent = original; btn.disabled = false; }, 2500);
        }
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    document.getElementById('panel-logout').addEventListener('click', async e => {
        e.preventDefault();
        await api('/api/logout', { method: 'POST' }).catch(() => {});
        Auth.clear();
        window.location.href = "{{ route('shop') }}";
    });

    // ── Ordenar al cambiar el select ──────────────────────────────────────────
    document.getElementById('sort-select').addEventListener('change', renderGrid);

    // ── Inicializar ───────────────────────────────────────────────────────────
    loadProfile();
    loadFavorites();
})();
</script>
@endsection
