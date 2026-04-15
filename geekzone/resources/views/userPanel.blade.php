@extends('layouts.layout')
@section('title', 'GeekZone — Mi Cuenta')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/userPanel.css') }}">
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
            <a href="#" class="sidebar-link active" data-tab="datos">
                <span class="icon">👤</span> Mi perfil
            </a>
            <a href="#" class="sidebar-link" data-tab="pedidos">
                <span class="icon">📦</span> Mis pedidos
            </a>
            <a href="#" class="sidebar-link" data-tab="seguridad">
                <span class="icon">🔒</span> Seguridad
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
            <p class="page-eyebrow">Panel de usuario</p>
            <h2 class="panel-title">Mi <span>Cuenta</span></h2>
        </div>

        <div class="profile-header">
            <div class="avatar avatar-lg" id="header-initials">--</div>
            <div class="profile-info">
                <h3 id="header-fullname">Cargando…</h3>
                <p id="header-email" class="profile-email">--</p>
                <p id="header-username" class="profile-username">--</p>
            </div>
            <div class="profile-total">
                <p class="total-amount" id="header-total">--</p>
                <p class="total-label">Total comprado</p>
            </div>
        </div>

        <!-- TABS -->
        <div class="tab-bar">
            <a class="tab active" data-tab="datos">Mis datos</a>
            <a class="tab" data-tab="pedidos">Pedidos <span class="tab-badge" id="pedidos-count"></span></a>
            <a class="tab" data-tab="seguridad">Seguridad</a>
        </div>

        <!-- ── TAB: MIS DATOS ── -->
        <div class="tab-pane active" id="tab-datos">
            <div class="card">
                <div class="card-header">
                    <h3>Información personal</h3>
                </div>
                <div class="card-body">
                    <div id="alert-datos"></div>
                    <form id="form-perfil" novalidate>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="input-name">Nombre</label>
                                <input type="text" class="form-control" id="input-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="input-surname">Apellidos</label>
                                <input type="text" class="form-control" id="input-surname" name="surname">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="input-username">Nombre de usuario</label>
                                <input type="text" class="form-control" id="input-username" name="username">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="input-email">Correo electrónico</label>
                                <input type="email" class="form-control" id="input-email" name="email" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button class="btn btn-secondary" id="btn-cancelar-datos">Cancelar cambios</button>
                    <button class="btn btn-primary" id="btn-guardar-datos">Guardar cambios</button>
                </div>
            </div>
        </div>

        <!-- ── TAB: PEDIDOS ── -->
        <div class="tab-pane" id="tab-pedidos">
            <div id="pedidos-container">
                <div class="empty-state">
                    <span class="empty-icon">📦</span>
                    <p>Cargando pedidos…</p>
                </div>
            </div>
        </div>

        <!-- ── TAB: SEGURIDAD ── -->
        <div class="tab-pane" id="tab-seguridad">
            <div class="card">
                <div class="card-header">
                    <h3>Cambiar contraseña</h3>
                </div>
                <div class="card-body">
                    <div id="alert-seguridad"></div>
                    <form id="form-password" novalidate>
                        <div class="form-group">
                            <label class="form-label" for="input-password">Nueva contraseña</label>
                            <input type="password" class="form-control" id="input-password" name="password" minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="input-password-confirmation">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="input-password-confirmation" name="password_confirmation" placeholder="Repite la contraseña">
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" id="btn-cambiar-password">Actualizar contraseña</button>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
(function () {
    // ── Utilidades ─────────────────────────────────────────────────────────────
    const token = () => localStorage.getItem('token') || sessionStorage.getItem('token');

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

    const showAlert = (containerId, msg, type = 'success') => {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = `<div class="alert alert-${type}">
            <span>${type === 'success' ? '✅' : '❌'}</span> ${msg}
        </div>`;
        setTimeout(() => el.innerHTML = '', 4000);
    };

    // ── Verificar autenticación ────────────────────────────────────────────────
    if (!token()) {
        window.location.href = "{{ route('login') }}";
        return;
    }

    // ── Estado inicial del perfil ─────────────────────────────────────────────
    let originalData = {};

    // ── Cargar perfil ─────────────────────────────────────────────────────────
    async function loadProfile() {
        const { ok, data } = await api('/api/perfil');
        if (!ok) {
            window.location.href = "{{ route('login') }}";
            return;
        }
        const u = data.user;
        originalData = { name: u.name, surname: u.surname ?? '', username: u.username ?? '', email: u.email };

        // Sidebar
        document.getElementById('sidebar-initials').textContent = initials(u.name, u.surname);
        document.getElementById('sidebar-name').textContent = `${u.name} ${u.surname ?? ''}`.trim();
        document.getElementById('sidebar-role').textContent = u.role === 'admin' ? '⚙️ Administrador' : 'Cliente';

        // Header del perfil
        document.getElementById('header-initials').textContent = initials(u.name, u.surname);
        document.getElementById('header-fullname').textContent = `${u.name} ${u.surname ?? ''}`.trim();
        document.getElementById('header-email').textContent = u.email;
        document.getElementById('header-username').textContent = u.username ? `@${u.username}` : '';

        // Formulario
        document.getElementById('input-name').value = u.name ?? '';
        document.getElementById('input-surname').value = u.surname ?? '';
        document.getElementById('input-username').value = u.username ?? '';
        document.getElementById('input-email').value = u.email ?? '';
    }

    // ── Cargar pedidos ────────────────────────────────────────────────────────
    async function loadOrders() {
        const container = document.getElementById('pedidos-container');
        const { ok, data } = await api('/api/pedidos');

        if (!ok) {
            container.innerHTML = `<div class="empty-state"><span class="empty-icon">⚠️</span><p>No se pudieron cargar los pedidos.</p></div>`;
            return;
        }

        const orders = data.orders ?? [];

        // Actualizar total comprado en el header
        const totalGastado = orders.reduce((sum, o) => sum + parseFloat(o.total), 0);
        document.getElementById('header-total').textContent = `${totalGastado.toFixed(2)}€`;

        // Badge en el tab
        const badge = document.getElementById('pedidos-count');
        if (badge) badge.textContent = orders.length > 0 ? orders.length : '';

        if (orders.length === 0) {
            container.innerHTML = `<div class="empty-state"><span class="empty-icon">📦</span><p>Todavía no tienes pedidos.</p><a href="{{ route('shop') }}" class="btn btn-primary" style="margin-top:1rem">Ir a la tienda</a></div>`;
            return;
        }

        const statusLabel = {
            pendiente: { text: 'Pendiente', cls: 'status-pending' },
            procesando: { text: 'Procesando', cls: 'status-processing' },
            enviado: { text: 'Enviado', cls: 'status-shipped' },
            entregado: { text: 'Entregado', cls: 'status-delivered' },
            cancelado: { text: 'Cancelado', cls: 'status-cancelled' },
        };

        container.innerHTML = orders.map(order => {
            const s = statusLabel[order.status] ?? { text: order.status, cls: '' };
            const date = new Date(order.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
            const items = (order.details ?? []).map(d =>
                `<div class="order-item">
                    <span class="order-item-name">${d.product?.name ?? 'Producto eliminado'}</span>
                    <span class="order-item-qty">× ${d.quantity}</span>
                    <span class="order-item-price">${parseFloat(d.price).toFixed(2)}€</span>
                </div>`
            ).join('');

            return `<div class="card order-card">
                <div class="order-head">
                    <div>
                        <span class="order-id">#${String(order.id).padStart(4, '0')}</span>
                        <span class="order-date">${date}</span>
                    </div>
                    <div class="order-right">
                        <span class="order-status ${s.cls}">${s.text}</span>
                        <span class="order-total">${parseFloat(order.total).toFixed(2)}€</span>
                    </div>
                </div>
                ${items ? `<div class="order-items">${items}</div>` : ''}
            </div>`;
        }).join('');
    }

    // ── Guardar perfil ────────────────────────────────────────────────────────
    document.getElementById('btn-guardar-datos').addEventListener('click', async () => {
        const body = {
            name: document.getElementById('input-name').value.trim(),
            surname: document.getElementById('input-surname').value.trim(),
            username: document.getElementById('input-username').value.trim(),
            email: document.getElementById('input-email').value.trim(),
        };

        const { ok, data } = await api('/api/perfil', { method: 'PUT', body: JSON.stringify(body) });

        if (ok) {
            showAlert('alert-datos', 'Datos guardados correctamente.');
            originalData = { ...body };
            await loadProfile();
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join(' · ') : data.message;
            showAlert('alert-datos', errors, 'error');
        }
    });

    // ── Cancelar cambios ──────────────────────────────────────────────────────
    document.getElementById('btn-cancelar-datos').addEventListener('click', () => {
        document.getElementById('input-name').value = originalData.name ?? '';
        document.getElementById('input-surname').value = originalData.surname ?? '';
        document.getElementById('input-username').value = originalData.username ?? '';
        document.getElementById('input-email').value = originalData.email ?? '';
    });

    // ── Cambiar contraseña ────────────────────────────────────────────────────
    document.getElementById('btn-cambiar-password').addEventListener('click', async () => {
        const password = document.getElementById('input-password').value;
        const confirmation = document.getElementById('input-password-confirmation').value;

        if (!password || password.length < 6) {
            showAlert('alert-seguridad', 'La contraseña debe tener al menos 6 caracteres.', 'error');
            return;
        }
        if (password !== confirmation) {
            showAlert('alert-seguridad', 'Las contraseñas no coinciden.', 'error');
            return;
        }

        const { ok, data } = await api('/api/perfil', {
            method: 'PUT',
            body: JSON.stringify({ password, password_confirmation: confirmation }),
        });

        if (ok) {
            showAlert('alert-seguridad', 'Contraseña actualizada correctamente.');
            document.getElementById('form-password').reset();
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join(' · ') : data.message;
            showAlert('alert-seguridad', errors, 'error');
        }
    });

    // ── Navegación por tabs ───────────────────────────────────────────────────
    const allTabs = document.querySelectorAll('.tab, .sidebar-link[data-tab]');
    const allPanes = document.querySelectorAll('.tab-pane');

    allTabs.forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            const target = tab.dataset.tab;
            if (!target) return;

            allTabs.forEach(t => t.classList.toggle('active', t.dataset.tab === target));
            allPanes.forEach(p => p.classList.toggle('active', p.id === `tab-${target}`));

            if (target === 'pedidos') loadOrders();
        });
    });

    // ── Logout ────────────────────────────────────────────────────────────────
    document.getElementById('panel-logout').addEventListener('click', async e => {
        e.preventDefault();
        await api('/api/logout', { method: 'POST' }).catch(() => {});
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('role');
        window.location.href = "{{ route('shop') }}";
    });

    // ── Inicializar ───────────────────────────────────────────────────────────
    loadProfile();

    // Abrir tab pedidos si viene desde el carrito (?tab=pedidos)
    const urlTab = new URLSearchParams(window.location.search).get('tab');
    if (urlTab) {
        const trigger = document.querySelector(`.tab[data-tab="${urlTab}"]`);
        if (trigger) trigger.click();
    }
})();
</script>
@endsection
