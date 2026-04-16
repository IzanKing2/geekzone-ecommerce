@extends('layouts.layout')
@section('title', 'GeekZone — Carrito')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endpush

@section('content')
  <div class="page-header">
    <div class="page-header-inner">
      <p class="page-eyebrow">Resumen de compra</p>
      <h1 class="page-title">Tu <span>Carrito</span></h1>
      <p class="page-sub">Revisa tus productos antes de finalizar el pedido.</p>
    </div>
  </div>

  <section class="section">

    {{-- Estado: no autenticado --}}
    <div id="cart-guest" style="display:none; text-align:center; padding:4rem 0;">
      <p style="font-size:4rem; margin-bottom:1rem;">🔒</p>
      <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; margin-bottom:.5rem;">Inicia sesión para ver tu carrito</h2>
      <p style="color:var(--grey); margin-bottom:2rem;">Necesitas una cuenta para guardar tus productos.</p>
      <a href="{{ route('login') }}" class="btn btn-gold">Iniciar sesión</a>
    </div>

    {{-- Estado: pedido completado --}}
    <div id="cart-success" style="display:none; text-align:center; padding:4rem 0;">
      <p style="font-size:4rem; margin-bottom:1rem;">🎉</p>
      <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2.5rem; margin-bottom:.5rem; letter-spacing:2px;">
        ¡Pedido realizado!
      </h2>
      <p style="color:var(--grey); margin-bottom:.5rem;">
        Tu pedido <strong id="success-order-id" style="color:var(--gold)"></strong> ha sido registrado correctamente.
      </p>
      <p style="color:var(--grey); font-size:.9rem; margin-bottom:2rem;">
        Total: <strong id="success-order-total" style="color:var(--white)"></strong>
      </p>
      <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="{{ route('panel') }}?tab=pedidos" class="btn btn-gold">Ver mis pedidos</a>
        <a href="{{ route('shop') }}" class="btn" style="background:var(--mid2); color:var(--white); border:1px solid var(--border);">
          Seguir comprando
        </a>
      </div>
    </div>

    {{-- Estado: carrito vacío --}}
    <div id="cart-empty" style="display:none; text-align:center; padding:4rem 0;">
      <p style="font-size:4rem; margin-bottom:1rem;">🛒</p>
      <h2 style="font-family:'Bebas Neue',sans-serif; font-size:2rem; margin-bottom:.5rem;">Tu carrito está vacío</h2>
      <p style="color:var(--grey); margin-bottom:2rem;">Añade algún producto desde la tienda.</p>
      <a href="{{ route('shop') }}" class="btn btn-gold">Ir a la tienda</a>
    </div>

    {{-- Layout principal --}}
    <div class="cart-layout" id="cart-layout" style="display:none;">

      {{-- ITEMS --}}
      <div>
        <div class="card">
          <div class="card-header">
            <h3>Productos (<span id="cart-count-label">0</span>)</h3>
            <button id="clear-cart-btn"
              style="color:var(--red);font-size:.82rem;font-family:'Barlow Condensed',sans-serif;letter-spacing:1px;text-transform:uppercase;background:none;border:none;cursor:pointer;">
              Vaciar carrito
            </button>
          </div>
          <div id="cart-items"></div>
        </div>
      </div>

      {{-- RESUMEN --}}
      <div class="order-summary">
        <div class="card">
          <div class="card-header"><h3>Resumen del pedido</h3></div>
          <div class="card-body">
            <div class="summary-row">
              <span>Subtotal</span>
              <strong id="summary-subtotal">0,00€</strong>
            </div>
            <div class="summary-row">
              <span>Envío</span>
              <strong id="summary-shipping">—</strong>
            </div>
            <div class="summary-total">
              <span>Total</span>
              <span id="summary-total">0,00€</span>
            </div>
            <button id="checkout-btn" class="btn btn-gold btn-block btn-lg" style="margin-top:.5rem">
              Finalizar compra →
            </button>
            <div style="display:flex;align-items:center;justify-content:center;gap:.5rem;margin-top:1rem;color:var(--grey);font-size:.8rem">
              <span>🔒</span> Pago 100% seguro y protegido
            </div>
          </div>
        </div>

        <div class="card" id="free-shipping-banner" style="margin-top:1rem;display:none;">
          <div class="card-body" style="display:flex;gap:1rem;align-items:center">
            <span style="font-size:1.5rem">🚚</span>
            <div>
              <p style="font-family:'Barlow Condensed',sans-serif;font-weight:700;font-size:.95rem;color:var(--white)">Envío gratis</p>
              <p style="font-size:.82rem;color:var(--grey)">Tu pedido supera los 50€. ¡Envío gratuito aplicado!</p>
            </div>
          </div>
        </div>

        <div style="margin-top:1rem;text-align:center">
          <a href="{{ route('shop') }}"
            style="color:var(--cobalt-light);font-size:.88rem;font-family:'Barlow Condensed',sans-serif;letter-spacing:1px;text-transform:uppercase">
            ← Seguir comprando
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- Toast de notificaciones --}}
  <div id="cart-toast"
    style="position:fixed;bottom:2rem;right:2rem;padding:.8rem 1.5rem;border-radius:6px;font-family:'Barlow Condensed',sans-serif;letter-spacing:1px;font-size:.9rem;text-transform:uppercase;color:#fff;opacity:0;transition:opacity .3s;pointer-events:none;z-index:9999;">
  </div>

  <script>
    const token = Auth.getToken();
    const SHIPPING_THRESHOLD = 50;
    const SHIPPING_COST = 4.99;

    function fmt(n) {
      return n.toFixed(2).replace('.', ',') + '€';
    }

    function showToast(msg, color = '#0047AB') {
      const t = document.getElementById('cart-toast');
      t.textContent = msg;
      t.style.background = color;
      t.style.opacity = '1';
      clearTimeout(t._timer);
      t._timer = setTimeout(() => t.style.opacity = '0', 2500);
    }

    async function apiFetch(url, opts = {}) {
      return fetch(url, {
        ...opts,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
          ...(opts.headers || {}),
        },
      });
    }

    function updateSummary(items) {
      const subtotal = items.reduce((a, i) => a + i.product.price * i.quantity, 0);
      const shipping = subtotal >= SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
      const total = subtotal + shipping;

      document.getElementById('summary-subtotal').textContent = fmt(subtotal);
      const shippingEl = document.getElementById('summary-shipping');
      shippingEl.textContent = shipping === 0 ? 'Gratis' : fmt(shipping);
      shippingEl.style.color = shipping === 0 ? 'var(--green)' : 'var(--white)';
      document.getElementById('summary-total').textContent = fmt(total);
      document.getElementById('free-shipping-banner').style.display = shipping === 0 ? 'block' : 'none';
    }

    function renderCart(items) {
      const emptyEl   = document.getElementById('cart-empty');
      const layoutEl  = document.getElementById('cart-layout');
      const countLabel = document.getElementById('cart-count-label');
      const headerCount = document.getElementById('cartCount');

      if (items.length === 0) {
        emptyEl.style.display  = 'block';
        layoutEl.style.display = 'none';
        if (headerCount) headerCount.textContent = '0';
        return;
      }

      emptyEl.style.display  = 'none';
      layoutEl.style.display = 'grid';

      const totalQty = items.reduce((a, i) => a + i.quantity, 0);
      countLabel.textContent = items.length;
      if (headerCount) headerCount.textContent = totalQty;

      document.getElementById('cart-items').innerHTML = items.map(item => `
        <div class="cart-item" id="item-${item.id}">
          <div class="cart-thumb" style="background:var(--mid2);overflow:hidden;">
            <img src="${item.product.image_url || ''}" alt=""
              style="width:100%;height:100%;object-fit:cover;"
              onerror="this.style.display='none';this.parentElement.textContent='📦'">
          </div>
          <div>
            <p class="cart-item-cat">${item.product.category?.name ?? ''}</p>
            <p class="cart-item-name">${item.product.name}</p>
            <div class="qty-control">
              <button class="qty-btn" onclick="changeQty(${item.id}, ${item.quantity - 1})">−</button>
              <span class="qty-num">${item.quantity}</span>
              <button class="qty-btn" onclick="changeQty(${item.id}, ${item.quantity + 1})">+</button>
            </div>
          </div>
          <div class="cart-item-right">
            <p class="cart-item-price">${fmt(item.product.price * item.quantity)}</p>
            <button class="cart-remove" onclick="removeItem(${item.id})">✕ Eliminar</button>
          </div>
        </div>
      `).join('');

      updateSummary(items);
    }

    async function loadCart() {
      const res = await apiFetch('/api/carrito');
      if (!res.ok) { showToast('Error al cargar el carrito', '#c0392b'); return; }
      const data = await res.json();
      currentCartItems = data.cart;
      renderCart(data.cart);
    }

    async function changeQty(id, newQty) {
      if (newQty < 1) { removeItem(id); return; }
      const res = await apiFetch(`/api/carrito/${id}`, {
        method: 'PUT',
        body: JSON.stringify({ quantity: newQty }),
      });
      if (res.ok) {
        loadCart();
      } else {
        const err = await res.json();
        showToast(err.message || 'Sin stock suficiente', '#c0392b');
      }
    }

    async function removeItem(id) {
      const res = await apiFetch(`/api/carrito/${id}`, { method: 'DELETE' });
      if (res.ok) { loadCart(); showToast('Producto eliminado'); }
    }

    document.getElementById('checkout-btn').addEventListener('click', async () => {
      const btn = document.getElementById('checkout-btn');
      btn.disabled = true;
      btn.textContent = 'Procesando…';

      const res = await apiFetch('/api/pedidos', { method: 'POST' });
      const data = await res.json().catch(() => ({}));

      if (res.ok) {
        // Ocultar todo el layout y mostrar confirmación
        document.getElementById('cart-layout').style.display = 'none';
        document.getElementById('success-order-id').textContent = `#${String(data.order.id).padStart(4, '0')}`;
        document.getElementById('success-order-total').textContent = fmt(parseFloat(data.order.total));
        document.getElementById('cart-success').style.display = 'block';

        // Resetear contador del header
        const headerCount = document.getElementById('cartCount');
        if (headerCount) headerCount.textContent = '0';
      } else {
        btn.disabled = false;
        btn.textContent = 'Finalizar compra →';
        showToast(data.message || 'Error al procesar el pedido', '#c0392b');
      }
    });

    document.getElementById('clear-cart-btn').addEventListener('click', async () => {
      const ids = [...document.querySelectorAll('[id^="item-"]')]
        .map(el => el.id.replace('item-', ''));
      if (ids.length === 0) return;
      await Promise.all(ids.map(id => apiFetch(`/api/carrito/${id}`, { method: 'DELETE' })));
      loadCart();
      showToast('Carrito vaciado');
    });

    // Inicialización
    if (!token) {
      document.getElementById('cart-guest').style.display = 'block';
    } else {
      loadCart();
    }
  </script>
@endsection
