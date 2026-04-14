<header>
  <nav>
    <div class="nav-logo">Geek<span>Zone</span></div>
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

        <a href="#" class="btn-cart">🛒 Carrito <span id="cartCount" class="cart-count">0</span></a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- 1. GESTIÓN DE LA INTERFAZ (MOSTRAR/OCULTAR BOTONES) ---
            const token = localStorage.getItem('token');
            const userMenu = document.getElementById('user-menu'); // Contenedor de Perfil/Logout
            const guestMenu = document.getElementById('guest-menu'); // Contenedor de Login/Register

            if (token) {
                if (userMenu) userMenu.style.display = 'flex';
                if (guestMenu) guestMenu.style.display = 'none';
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

                        // Independientemente de si el servidor responde OK o Error (ej. token expirado),
                        // procedemos a limpiar el navegador para que el usuario no se quede "atrapado".
                        if (!response.ok) {
                            console.warn('El servidor no pudo invalidar el token, pero cerraremos localmente.');
                        }

                    } catch (error) {
                        console.error('Error de red al intentar cerrar sesión:', error);
                    } finally {
                        // Limpiar local y redirigir siempre
                        localStorage.removeItem('token');
                        window.location.href = "{{ route('shop') }}";
                    }
                });
            }
        });
    </script>
  </nav>
</header>
