<header>
  <nav>
    <div class="nav-logo"><a href="{{ route('shop') }}">Geek<span>Zone</span></a></div>

    <ul class="nav-links" id="nav-links">
      <li><a href="{{ route('shop') }}">Inicio</a></li>
      <li><a href="{{ route('catalog') }}">Catálogo</a></li>

      {{-- Separador + ítems de usuario (solo visibles en el menú hamburguesa) --}}
      <li class="nav-divider" aria-hidden="true"></li>

      <li class="nav-mobile-auth" id="mobile-admin-li" style="display:none">
        <a id="mobile-admin-btn" href="#">⚙️ Panel Admin</a>
      </li>
      <li class="nav-mobile-auth" id="mobile-profile-li" style="display:none">
        <a href="{{ route('panel') }}">👤 Mi Perfil</a>
      </li>
      <li class="nav-mobile-auth" id="mobile-logout-li" style="display:none">
        <button type="button" id="mobile-logout-btn" class="nav-mobile-btn">🚪 Cerrar Sesión</button>
      </li>
      <li class="nav-mobile-auth" id="mobile-guest-li" style="display:none">
        <a href="{{ route('login') }}">👤 Iniciar Sesión</a>
      </li>
    </ul>

    <div class="nav-right">
      <div id="user-menu" style="display:none;">
        <a id="admin-btn" href="#" class="nav-user" style="display:none;">⚙️ Panel Admin</a>
        <a href="{{ route('panel') }}" class="nav-user">👤 Mi Perfil</a>
        <form id="logoutForm" style="display:inline;">
          <button type="submit" class="nav-user" style="background:none;border:none;cursor:pointer;">🚪 Cerrar Sesión</button>
        </form>
      </div>

      <div id="guest-menu" style="display:none;">
        <a href="{{ route('login') }}" class="nav-user">👤 Iniciar Sesión</a>
      </div>

      <a href="{{ route('cart') }}" class="btn-cart">🛒 Carrito <span id="cartCount" class="cart-count">0</span></a>
      <button class="nav-hamburger" id="nav-hamburger" aria-label="Menú">
        <span></span><span></span><span></span>
      </button>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const token = Auth.getToken();
        const role  = Auth.getRole();

        // Referencias desktop
        const userMenu  = document.getElementById('user-menu');
        const guestMenu = document.getElementById('guest-menu');

        // Referencias móvil
        const mobileAdminLi  = document.getElementById('mobile-admin-li');
        const mobileAdminBtn = document.getElementById('mobile-admin-btn');
        const mobileProfileLi = document.getElementById('mobile-profile-li');
        const mobileLogoutLi  = document.getElementById('mobile-logout-li');
        const mobileGuestLi   = document.getElementById('mobile-guest-li');

        if (token) {
          // Desktop
          if (userMenu)  userMenu.style.display  = 'flex';
          if (guestMenu) guestMenu.style.display = 'none';

          // Móvil
          if (mobileProfileLi) mobileProfileLi.style.display = 'block';
          if (mobileLogoutLi)  mobileLogoutLi.style.display  = 'block';
          if (mobileGuestLi)   mobileGuestLi.style.display   = 'none';

          if (role === 'admin') {
            const adminBtn = document.getElementById('admin-btn');
            if (adminBtn) adminBtn.style.display = 'inline-flex';
            if (mobileAdminLi) mobileAdminLi.style.display = 'block';
          }

          // Contador del carrito
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
          // Desktop
          if (userMenu)  userMenu.style.display  = 'none';
          if (guestMenu) guestMenu.style.display = 'flex';

          // Móvil
          if (mobileProfileLi) mobileProfileLi.style.display = 'none';
          if (mobileLogoutLi)  mobileLogoutLi.style.display  = 'none';
          if (mobileGuestLi)   mobileGuestLi.style.display   = 'flex';
        }

        // Función de logout reutilizable
        async function doLogout() {
          try {
            await fetch('/api/logout', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${Auth.getToken()}`
              }
            });
          } catch (e) {
            console.error('Error al cerrar sesión:', e);
          } finally {
            Auth.clear();
            window.location.href = "{{ route('shop') }}";
          }
        }

        // Logout desktop
        const logoutForm = document.getElementById('logoutForm');
        if (logoutForm) {
          logoutForm.addEventListener('submit', e => { e.preventDefault(); doLogout(); });
        }

        // Logout móvil
        const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
        if (mobileLogoutBtn) {
          mobileLogoutBtn.addEventListener('click', doLogout);
        }

        // Hamburguesa
        const hamburger = document.getElementById('nav-hamburger');
        const navLinks  = document.getElementById('nav-links');
        if (hamburger && navLinks) {
          hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            hamburger.classList.toggle('open');
          });

          // Cerrar menú al pulsar cualquier enlace o botón interior
          navLinks.querySelectorAll('a, button').forEach(el => {
            el.addEventListener('click', () => {
              navLinks.classList.remove('open');
              hamburger.classList.remove('open');
            });
          });
        }
      });
    </script>
  </nav>
</header>
