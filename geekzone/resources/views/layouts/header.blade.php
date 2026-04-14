<header>
  <nav>
    <div class="nav-logo"><a href="{{ route('shop') }}">Geek<span>Zone</span></a></div>
    <ul class="nav-links">
      <li><a href="{{ route('shop') }}" class="active">Tienda</a></li>
      <li><a href="#categorias">Categorías</a></li>
      <li><a href="#productos">Productos</a></li>
    </ul>
    <div class="nav-right">
        <div id="user-menu" style="display:none;">
            <a href="#" class="nav-user">👤 Mi Perfil</a>
            <form id="logoutForm" style="display:inline;">
                <button type="submit" class="nav-user" style="background:none; border:none; cursor:pointer;">🚪 Cerrar Sesión</button>
            </form>
        </div>

        <div id="guest-menu" style="display:none;">
            <a href="{{ route('login') }}" class="nav-user">👤 Iniciar Sesión</a>
        </div>

        <a href="{{ route('cart') }}" class="btn-cart">🛒 Carrito <span id="cartCount" class="cart-count">0</span></a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- 1. GESTIÓN DE LA INTERFAZ (MOSTRAR/OCULTAR BOTONES) ---
            const token = localStorage.getItem('token') || sessionStorage.getItem('token');
            const userMenu = document.getElementById('user-menu');
            const guestMenu = document.getElementById('guest-menu');

            if (token) {
                if (userMenu) userMenu.style.display = 'flex';
                if (guestMenu) guestMenu.style.display = 'none';

                // --- Cargar contador del carrito ---
                fetch('/api/carrito', {
                    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
                })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (data) {
                        const total = data.cart.reduce((sum, i) => sum + i.quantity, 0);
                        document.getElementById('cartCount').textContent = total;
                    }
                })
                .catch(() => {});
            } else {
                if (userMenu) userMenu.style.display = 'none';
                if (guestMenu) guestMenu.style.display = 'flex';
            }

            // --- 2. LÓGICA DE LOGOUT ---
            const logoutForm = document.getElementById('logoutForm');

            if (logoutForm) {
                logoutForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const currentToken = localStorage.getItem('token');

                    try {
                        const response = await fetch('/api/logout', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${currentToken}`
                            }
                        });

                        if (!response.ok) {
                            console.warn('El servidor no pudo invalidar el token, pero cerraremos localmente.');
                        }

                    } catch (error) {
                        console.error('Error de red al intentar cerrar sesión:', error);
                    } finally {
                        localStorage.removeItem('token');
                        sessionStorage.removeItem('token');
                        window.location.href = "{{ route('shop') }}";
                    }
                });
            }
        });
    </script>
  </nav>
</header>
