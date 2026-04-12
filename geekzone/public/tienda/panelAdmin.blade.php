<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>GeekZone — Panel Admin</title>
  <link rel="stylesheet" href="css/admin.css"/>
</head>
<body>
  <div class="layout-sidebar">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div style="padding:1rem 1.5rem 1.2rem;border-bottom:1px solid var(--border);margin-bottom:.5rem">
        <p style="font-family:'Barlow Condensed',sans-serif;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.45);margin-bottom:.2rem">Panel de control</p>
        <p style="font-weight:600;font-size:.95rem">GeekZone Admin</p>
      </div>
      <div class="sidebar-section">
        <p class="sidebar-label">Principal</p>
        <a href="admin.html" class="sidebar-link active"><span class="icon">📊</span> Dashboard</a>
        <a href="crud.html" class="sidebar-link"><span class="icon">📦</span> Productos</a>
        <a href="adminPedidos.html" class="sidebar-link"><span class="icon">🛒</span> Pedidos</a>
        <a href="adminUsuarios.html" class="sidebar-link"><span class="icon">👥</span> Usuarios</a>
      </div>
      <hr class="sidebar-divider"/>
      <div class="sidebar-section">
        <p class="sidebar-label">Contenido</p>
        <a href="adminCategorias.html" class="sidebar-link"><span class="icon">🏷️</span> Categorías</a>
      </div>
      <hr class="sidebar-divider"/>
      <div class="sidebar-section">
        <a href="login.html" class="sidebar-link" style="color:var(--red)"><span class="icon">↩</span> Salir</a>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
      <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2rem">
        <div>
          <p class="page-eyebrow" style="padding-top:0">Bienvenido de nuevo</p>
          <h2 style="font-family:'Bebas Neue',sans-serif;font-size:2.5rem;letter-spacing:2px">Dashboard <span style="color:var(--cobalt-light)">Admin</span></h2>
        </div>
        <div style="display:flex;gap:.8rem">
          <a href="crud.html" class="btn btn-primary btn-sm">+ Nuevo producto</a>
        </div>
      </div>

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div>
            <p class="stat-label">Pedidos</p>
            <p class="stat-value">47</p>
          </div>
          <div class="stat-icon">📦</div>
        </div>
        <div class="stat-card">
          <div>
            <p class="stat-label">Usuarios</p>
            <p class="stat-value">3.241</p>
          </div>
          <div class="stat-icon">👥</div>
        </div>
        <div class="stat-card">
          <div>
            <p class="stat-label">Productos</p>
            <p class="stat-value">128</p>
          </div>
          <div class="stat-icon">🏷️</div>
        </div>
      </div>

      <!-- CHARTS -->
      <div style="display:flex;flex-direction: column;gap:1.5rem;margin-bottom:1.5rem">

        <div class="card">
          <div class="card-header"><h3>Ventas por categoría</h3><span class="badge badge-grey">Este mes</span></div>
          <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:1.2rem">
              <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
                  <span style="font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:2px;text-transform:uppercase;color:var(--grey)">🕷️ Marvel</span>
                  <span style="font-family:'Bebas Neue',sans-serif;color:var(--white)">8.340€ · 52%</span>
                </div>
                <div class="bar-track"><div class="bar-fill" style="width:52%;background:linear-gradient(90deg,#5c0a12,var(--red))"></div></div>
              </div>
              <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
                  <span style="font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:2px;text-transform:uppercase;color:var(--grey)">🎵 K-Pop</span>
                  <span style="font-family:'Bebas Neue',sans-serif;color:var(--white)">5.120€ · 32%</span>
                </div>
                <div class="bar-track"><div class="bar-fill" style="width:32%;background:linear-gradient(90deg,#3d0066,#8A2BE2)"></div></div>
              </div>
              <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:.4rem">
                  <span style="font-family:'Barlow Condensed',sans-serif;font-size:.82rem;letter-spacing:2px;text-transform:uppercase;color:var(--grey)">⚽ Deportes</span>
                  <span style="font-family:'Bebas Neue',sans-serif;color:var(--white)">2.580€ · 16%</span>
                </div>
                <div class="bar-track"><div class="bar-fill" style="width:16%;background:linear-gradient(90deg,#003d1a,#1a7f37)"></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>