@extends('layouts.layout')
@section('title', 'GeekZone — Panel Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link active"><span class="icon">📊</span> Dashboard</a>
            <a href="{{ route('admin.products') }}" class="sidebar-link"><span class="icon">📦</span> Productos</a>
        </div>
        <hr class="sidebar-divider"/>
        <div class="sidebar-section">
            <p class="sidebar-label">Contenido</p>
            <a href="{{ route('admin.categories') }}" class="sidebar-link"><span class="icon">🏷️</span> Categorías</a>
        </div>
        <hr class="sidebar-divider"/>
        <div class="sidebar-section">
            <a href="{{ route('shop') }}" class="sidebar-link"><span class="icon">🏠</span> Ver tienda</a>
            <a href="#" class="sidebar-link sidebar-logout" id="admin-logout" style="color:var(--red)"><span class="icon">↩</span> Salir</a>
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    {{-- Esta página no lleva datos: se cargan por API (/api/admin/dashboard/*) con el JWT de un administrador. --}}
    <main class="main-content">

        <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2rem">
            <div>
                <p class="page-eyebrow" style="padding-top:0">Bienvenido de nuevo</p>
                <h2 style="font-family:'Bebas Neue',sans-serif;font-size:2.5rem;letter-spacing:2px">Dashboard <span style="color:var(--cobalt-light)">Admin</span></h2>
            </div>
        </div>

        {{-- ══ STATS ══ --}}
        <div class="dashboard-grid-4">
            <div class="stat-card">
                <div>
                    <p class="stat-label">Pedidos</p>
                    <p class="stat-value" id="stat-pedidos">…</p>
                </div>
                <div class="stat-icon">🛒</div>
            </div>
            <div class="stat-card">
                <div>
                    <p class="stat-label">Usuarios</p>
                    <p class="stat-value" id="stat-usuarios">…</p>
                </div>
                <div class="stat-icon">👥</div>
            </div>
            <div class="stat-card">
                <div>
                    <p class="stat-label">Productos</p>
                    <p class="stat-value" id="stat-productos">…</p>
                </div>
                <div class="stat-icon">🏷️</div>
            </div>
            <div class="stat-card">
                <div>
                    <p class="stat-label">Ingresos totales</p>
                    <p class="stat-value" id="stat-ingresos" style="font-size:1.8rem">…</p>
                </div>
                <div class="stat-icon" style="background:rgba(212,175,55,.15)">💰</div>
            </div>
        </div>

        {{-- ══ ROW: VENTAS POR CATEGORÍA + INGRESOS MENSUALES ══ --}}
        <div class="dashboard-row-equal">

            <div class="card">
                <div class="card-header">
                    <h3>Ventas por categoría</h3>
                    <span class="badge badge-grey">Acumulado</span>
                </div>
                <div class="card-body" id="ventas-categoria"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Ingresos mensuales</h3>
                    <span class="badge badge-grey">Últimos 6 meses</span>
                </div>
                <div class="card-body" id="ingresos-mensuales"></div>
            </div>

        </div>

        {{-- ══ ROW: TOP PRODUCTOS + PEDIDOS RECIENTES ══ --}}
        <div class="dashboard-row-unequal">

            <div class="card">
                <div class="card-header">
                    <h3>Top productos</h3>
                    <span class="badge badge-grey">Más vendidos</span>
                </div>
                <div class="card-body" style="padding:0" id="top-productos"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Pedidos recientes</h3>
                    <span class="badge badge-grey" id="pedidos-count">Últimos pedidos</span>
                </div>
                <div style="overflow-x:auto">
                    <table style="width:100%;border-collapse:collapse">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border)">
                                <th style="padding:.7rem 1.5rem;text-align:left;font-family:'Barlow Condensed',sans-serif;font-size:.72rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.5);font-weight:400">ID</th>
                                <th style="padding:.7rem 1rem;text-align:left;font-family:'Barlow Condensed',sans-serif;font-size:.72rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.5);font-weight:400">Cliente</th>
                                <th style="padding:.7rem 1rem;text-align:left;font-family:'Barlow Condensed',sans-serif;font-size:.72rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.5);font-weight:400">Estado</th>
                                <th style="padding:.7rem 1.5rem;text-align:right;font-family:'Barlow Condensed',sans-serif;font-size:.72rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.5);font-weight:400">Total</th>
                            </tr>
                        </thead>
                        <tbody id="pedidos-recientes"></tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>
</div>

<script src="{{ asset('js/admin.js') }}"></script>
<script>
(() => {
    if (!AdminUI.guard()) return;

    const { api, money, number, monthLabel, el, fill, message } = AdminUI;
    const $ = (id) => document.getElementById(id);

    document.getElementById('admin-logout')?.addEventListener('click', (e) => {
        e.preventDefault();
        AdminUI.logout();
    });

    const LABEL = "font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:2px;text-transform:uppercase;color:var(--grey)";
    const VALUE = "font-family:'Bebas Neue',sans-serif;color:var(--white)";

    // Estados reales de la BD (enum de la tabla orders) → etiqueta y estilo
    const ESTADOS = {
        pendiente:  ['Pendiente',  'background:rgba(212,175,55,.15);color:#d4af37;border:1px solid rgba(212,175,55,.3)'],
        procesando: ['Procesando', 'background:rgba(26,127,55,.15);color:#2da44e;border:1px solid rgba(26,127,55,.3)'],
        enviado:    ['Enviado',    'background:rgba(0,71,171,.15);color:var(--cobalt-light);border:1px solid rgba(0,71,171,.3)'],
        entregado:  ['Entregado',  'background:rgba(26,127,55,.2);color:#2da44e;border:1px solid rgba(26,127,55,.4)'],
        cancelado:  ['Cancelado',  'background:rgba(185,28,28,.15);color:#f87171;border:1px solid rgba(185,28,28,.3)'],
    };

    /** Fila "etiqueta … importe" con barra proporcional. */
    function barRow(label, amount, percent, gradient) {
        return el('div', { style: 'margin-bottom:1.1rem' },
            el('div', { style: 'display:flex;justify-content:space-between;margin-bottom:.4rem' },
                el('span', { text: label, style: LABEL }),
                el('span', { text: money(amount), style: VALUE })
            ),
            el('div', { className: 'bar-track' },
                el('div', { className: 'bar-fill', style: `width:${percent}%;background:${gradient}` })
            )
        );
    }

    function renderBars(container, rows, gradient, emptyText) {
        if (!rows.length) return message(container, emptyText);
        const max = Math.max(...rows.map(r => r.value)) || 1;
        fill(container, ...rows.map(r => barRow(r.label, r.value, Math.round((r.value / max) * 100), gradient)));
    }

    function renderStats(d) {
        $('stat-pedidos').textContent = number(d.total_pedidos);
        $('stat-usuarios').textContent = number(d.total_usuarios);
        $('stat-productos').textContent = number(d.total_productos);
        $('stat-ingresos').textContent = money(d.ingresos_totales);
    }

    function renderTopProducts(container, items) {
        if (!items.length) return message(container, 'Sin ventas registradas aún', '2rem 0');

        fill(container, ...items.map((item, i) => {
            const row = el('div', {
                style: 'display:flex;align-items:center;gap:1rem;padding:.9rem 1.5rem;'
                    + (i === items.length - 1 ? '' : 'border-bottom:1px solid var(--border)'),
            }, el('span', {
                text: String(i + 1),
                style: "font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:rgba(156,163,175,.35);width:1.5rem;text-align:center",
            }));

            if (item.product) {
                const img = el('img', {
                    style: 'width:38px;height:38px;object-fit:cover;border-radius:4px;border:1px solid var(--border);flex-shrink:0',
                    attrs: { alt: item.product.name, loading: 'lazy' },
                });
                img.src = item.product.image_url || '';

                row.append(
                    img,
                    el('div', { style: 'flex:1;min-width:0' },
                        el('p', {
                            text: item.product.name,
                            style: "font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:.88rem;color:var(--white);white-space:nowrap;overflow:hidden;text-overflow:ellipsis",
                        }),
                        el('p', { text: `${number(item.total_vendido)} uds vendidas`, style: 'font-size:.75rem;color:var(--grey)' })
                    ),
                    el('span', {
                        text: money(item.ingresos_generados, 0),
                        style: "font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--gold);flex-shrink:0",
                    })
                );
            }
            return row;
        }));
    }

    function renderRecentOrders(tbody, orders) {
        if (!orders.length) {
            return fill(tbody, el('tr', {}, el('td', {
                text: 'Sin pedidos registrados aún',
                style: 'padding:2rem;text-align:center;color:var(--grey);font-size:.88rem',
                attrs: { colspan: '4' },
            })));
        }

        $('pedidos-count').textContent = `Últimos ${orders.length}`;

        fill(tbody, ...orders.map(order => {
            const [label, style] = ESTADOS[order.status]
                ?? [order.status, 'background:rgba(156,163,175,.1);color:var(--grey)'];

            const cliente = order.user
                ? el('td', { style: 'padding:.75rem 1rem' },
                    el('p', { text: order.user.name, style: 'font-size:.88rem;color:var(--white);font-weight:600' }),
                    el('p', { text: order.user.email, style: 'font-size:.75rem;color:var(--grey)' }))
                : el('td', { style: 'padding:.75rem 1rem' },
                    el('p', { text: 'Usuario eliminado', style: 'font-size:.88rem;color:var(--grey)' }));

            const tr = el('tr', { style: 'border-bottom:1px solid var(--border);transition:background .15s' },
                el('td', {
                    text: '#' + order.id,
                    style: "padding:.75rem 1.5rem;font-family:'Bebas Neue',sans-serif;font-size:1rem;color:var(--cobalt-light)",
                }),
                cliente,
                el('td', { style: 'padding:.75rem 1rem' }, el('span', {
                    text: label,
                    style: "display:inline-block;font-family:'Barlow Condensed',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:.2rem .65rem;border-radius:3px;" + style,
                })),
                el('td', {
                    text: money(order.total),
                    style: "padding:.75rem 1.5rem;text-align:right;font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--gold)",
                })
            );
            tr.addEventListener('mouseover', () => { tr.style.background = 'rgba(0,71,171,.06)'; });
            tr.addEventListener('mouseout', () => { tr.style.background = ''; });
            return tr;
        }));
    }

    // Cada bloque carga por separado: si uno falla, el resto sigue funcionando.
    const SECTIONS = [
        {
            container: 'ventas-categoria',
            request: () => api('ventas-por-categoria'),
            render: (box, d) => renderBars(box,
                d.ventas_por_categoria.map(c => ({ label: c.name, value: c.total_ventas })),
                'linear-gradient(90deg,var(--cobalt),var(--cobalt-light))', 'Sin datos de ventas aún'),
        },
        {
            container: 'ingresos-mensuales',
            request: () => api('ingresos?meses=6'),
            render: (box, d) => renderBars(box,
                d.ingresos_mensuales.map(m => ({ label: monthLabel(m.mes), value: m.ingresos })),
                'linear-gradient(90deg,var(--gold),#f5c842)', 'Sin datos de ingresos aún'),
        },
        {
            container: 'top-productos',
            request: () => api('top-productos'),
            render: (box, d) => renderTopProducts(box, d.top_productos),
        },
        {
            container: 'pedidos-recientes',
            request: () => api('pedidos-recientes'),
            render: (box, d) => renderRecentOrders(box, d.pedidos_recientes),
        },
    ];

    SECTIONS.forEach(({ container }) => {
        const box = $(container);
        if (box.tagName === 'TBODY') {
            fill(box, el('tr', {}, el('td', { text: 'Cargando…', attrs: { colspan: '4' }, style: 'padding:2rem;text-align:center;color:var(--grey);font-size:.88rem' })));
        } else {
            message(box, 'Cargando…');
        }
    });

    api('resumen').then(renderStats).catch(() => {
        ['stat-pedidos', 'stat-usuarios', 'stat-productos', 'stat-ingresos'].forEach(id => { $(id).textContent = '—'; });
    });

    SECTIONS.forEach(({ container, request, render }) => {
        const box = $(container);
        request().then(data => render(box, data)).catch(() => {
            if (box.tagName === 'TBODY') {
                fill(box, el('tr', {}, el('td', { text: 'No se pudo cargar', attrs: { colspan: '4' }, style: 'padding:2rem;text-align:center;color:var(--grey);font-size:.88rem' })));
            } else {
                message(box, 'No se pudo cargar', '2rem 0');
            }
        });
    });
})();
</script>
@endsection
