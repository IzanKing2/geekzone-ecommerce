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
    <main class="main-content">

        <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2rem">
            <div>
                <p class="page-eyebrow" style="padding-top:0">Bienvenido de nuevo</p>
                <h2 style="font-family:'Bebas Neue',sans-serif;font-size:2.5rem;letter-spacing:2px">Dashboard <span style="color:var(--cobalt-light)">Admin</span></h2>
            </div>
        </div>

        {{-- ══ STATS ══ --}}
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
            <div class="stat-card">
                <div>
                    <p class="stat-label">Pedidos</p>
                    <p class="stat-value">{{ number_format($totalPedidos) }}</p>
                </div>
                <div class="stat-icon">🛒</div>
            </div>
            <div class="stat-card">
                <div>
                    <p class="stat-label">Usuarios</p>
                    <p class="stat-value">{{ number_format($totalUsuarios) }}</p>
                </div>
                <div class="stat-icon">👥</div>
            </div>
            <div class="stat-card">
                <div>
                    <p class="stat-label">Productos</p>
                    <p class="stat-value">{{ number_format($totalProductos) }}</p>
                </div>
                <div class="stat-icon">🏷️</div>
            </div>
            <div class="stat-card">
                <div>
                    <p class="stat-label">Ingresos totales</p>
                    <p class="stat-value" style="font-size:1.8rem">{{ number_format($ingresosTotales, 2, ',', '.') }}€</p>
                </div>
                <div class="stat-icon" style="background:rgba(212,175,55,.15)">💰</div>
            </div>
        </div>

        {{-- ══ ROW: VENTAS POR CATEGORÍA + INGRESOS MENSUALES ══ --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

            {{-- Ventas por categoría --}}
            <div class="card">
                <div class="card-header">
                    <h3>Ventas por categoría</h3>
                    <span class="badge badge-grey">Acumulado</span>
                </div>
                <div class="card-body">
                    @forelse($ventasPorCategoria as $cat)
                    <div style="margin-bottom:1.1rem">
                        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
                            <span style="font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:2px;text-transform:uppercase;color:var(--grey)">{{ $cat->name }}</span>
                            <span style="font-family:'Bebas Neue',sans-serif;color:var(--white)">{{ number_format($cat->total_ventas, 2, ',', '.') }}€</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ round(($cat->total_ventas / $maxVentas) * 100) }}%;background:linear-gradient(90deg,var(--cobalt),var(--cobalt-light))"></div>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--grey);font-size:.88rem;text-align:center;padding:1rem 0">Sin datos de ventas aún</p>
                    @endforelse
                </div>
            </div>

            {{-- Ingresos últimos 6 meses --}}
            <div class="card">
                <div class="card-header">
                    <h3>Ingresos mensuales</h3>
                    <span class="badge badge-grey">Últimos 6 meses</span>
                </div>
                <div class="card-body">
                    @forelse($ingresosMensuales as $mes)
                    <div style="margin-bottom:1.1rem">
                        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
                            <span style="font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:2px;text-transform:uppercase;color:var(--grey)">{{ $mes->mes_label }}</span>
                            <span style="font-family:'Bebas Neue',sans-serif;color:var(--white)">{{ number_format($mes->ingresos, 2, ',', '.') }}€</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:{{ round(($mes->ingresos / $maxIngresos) * 100) }}%;background:linear-gradient(90deg,var(--gold),#f5c842)"></div>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--grey);font-size:.88rem;text-align:center;padding:1rem 0">Sin datos de ingresos aún</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ══ ROW: TOP PRODUCTOS + PEDIDOS RECIENTES ══ --}}
        <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:1.5rem;margin-bottom:1.5rem">

            {{-- Top 5 productos --}}
            <div class="card">
                <div class="card-header">
                    <h3>Top productos</h3>
                    <span class="badge badge-grey">Más vendidos</span>
                </div>
                <div class="card-body" style="padding:0">
                    @forelse($topProductos as $i => $item)
                    <div style="display:flex;align-items:center;gap:1rem;padding:.9rem 1.5rem;border-bottom:1px solid var(--border);{{ $loop->last ? 'border-bottom:none' : '' }}">
                        <span style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:rgba(156,163,175,.35);width:1.5rem;text-align:center">{{ $i + 1 }}</span>
                        @if($item->product)
                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                            style="width:38px;height:38px;object-fit:cover;border-radius:4px;border:1px solid var(--border);flex-shrink:0">
                        <div style="flex:1;min-width:0">
                            <p style="font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:.88rem;color:var(--white);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $item->product->name }}</p>
                            <p style="font-size:.75rem;color:var(--grey)">{{ $item->total_vendido }} uds vendidas</p>
                        </div>
                        <span style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--gold);flex-shrink:0">{{ number_format($item->ingresos_generados, 0, ',', '.') }}€</span>
                        @endif
                    </div>
                    @empty
                    <p style="color:var(--grey);font-size:.88rem;text-align:center;padding:2rem 0">Sin ventas registradas aún</p>
                    @endforelse
                </div>
            </div>

            {{-- Pedidos recientes --}}
            <div class="card">
                <div class="card-header">
                    <h3>Pedidos recientes</h3>
                    <span class="badge badge-grey">Últimos {{ $pedidosRecientes->count() }}</span>
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
                        <tbody>
                            @forelse($pedidosRecientes as $pedido)
                            <tr style="border-bottom:1px solid var(--border);transition:background .15s" onmouseover="this.style.background='rgba(0,71,171,.06)'" onmouseout="this.style.background=''">
                                <td style="padding:.75rem 1.5rem;font-family:'Bebas Neue',sans-serif;font-size:1rem;color:var(--cobalt-light)">#{{ $pedido->id }}</td>
                                <td style="padding:.75rem 1rem">
                                    @if($pedido->user)
                                    <p style="font-size:.88rem;color:var(--white);font-weight:600">{{ $pedido->user->name }}</p>
                                    <p style="font-size:.75rem;color:var(--grey)">{{ $pedido->user->email }}</p>
                                    @else
                                    <p style="font-size:.88rem;color:var(--grey)">Usuario eliminado</p>
                                    @endif
                                </td>
                                <td style="padding:.75rem 1rem">
                                    @php
                                        $statusMap = [
                                            'pending'   => ['Pendiente',  'background:rgba(212,175,55,.15);color:#d4af37;border:1px solid rgba(212,175,55,.3)'],
                                            'paid'      => ['Pagado',     'background:rgba(26,127,55,.15);color:#2da44e;border:1px solid rgba(26,127,55,.3)'],
                                            'shipped'   => ['Enviado',    'background:rgba(0,71,171,.15);color:var(--cobalt-light);border:1px solid rgba(0,71,171,.3)'],
                                            'delivered' => ['Entregado',  'background:rgba(26,127,55,.2);color:#2da44e;border:1px solid rgba(26,127,55,.4)'],
                                            'cancelled' => ['Cancelado',  'background:rgba(185,28,28,.15);color:#f87171;border:1px solid rgba(185,28,28,.3)'],
                                        ];
                                        [$label, $style] = $statusMap[$pedido->status] ?? [$pedido->status, 'background:rgba(156,163,175,.1);color:var(--grey)'];
                                    @endphp
                                    <span style="display:inline-block;font-family:'Barlow Condensed',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:.2rem .65rem;border-radius:3px;{{ $style }}">{{ $label }}</span>
                                </td>
                                <td style="padding:.75rem 1.5rem;text-align:right;font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--gold)">{{ number_format($pedido->total, 2, ',', '.') }}€</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding:2rem;text-align:center;color:var(--grey);font-size:.88rem">Sin pedidos registrados aún</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>
</div>

<script>
    document.getElementById('admin-logout')?.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${Auth.getToken()}`
                }
            });
        } catch {}
        Auth.clear();
        window.location.href = "{{ route('shop') }}";
    });
</script>
@endsection
