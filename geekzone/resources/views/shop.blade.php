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

            <!-- Marvel -->
            <div class="cat-card marvel">
                <div class="cat-bg"></div>
                <div class="cat-pattern"></div>
                <div class="cat-icon">🕷️</div>
                <div class="cat-content">
                    <span class="cat-tag">Más vendido</span>
                    <h3 class="cat-name">Marvel</h3>
                    <p class="cat-desc">Figuras, cómics, ropa y accesorios de tus héroes favoritos del universo Marvel.
                    </p>
                    <a href="#" class="cat-btn">Explorar</a> <!-- Tengo que hacer las webs de cada tema -->
                </div>
            </div>

            <!-- K-Pop -->
            <div class="cat-card kpop">
                <div class="cat-bg"></div>
                <div class="cat-pattern"></div>
                <div class="cat-icon">🎵</div>
                <div class="cat-content">
                    <span class="cat-tag">Tendencia</span>
                    <h3 class="cat-name">K-Pop</h3>
                    <p class="cat-desc">Álbumes, photocards, lightsticks y merchandise oficial de tus grupos favoritos.
                    </p>
                    <a href="#" class="cat-btn">Explorar</a> <!-- Tengo que hacer las webs de cada tema -->
                </div>
            </div>

            <!-- Fútbol -->
            <div class="cat-card futbol">
                <div class="cat-bg"></div>
                <div class="cat-pattern"></div>
                <div class="cat-icon">⚽</div>
                <div class="cat-content">
                    <span class="cat-tag">Nuevo</span>
                    <h3 class="cat-name">Fútbol</h3>
                    <p class="cat-desc">Camisetas retro, cromos, figuras y coleccionables de las leyendas del deporte.
                    </p>
                    <a href="#" class="cat-btn">Explorar</a> <!-- Tengo que hacer las webs de cada tema -->
                </div>
            </div>

        </div>
    </section>

    <section class="section">
        <div class="filter-bar">
            <button class="filter-pill active">Todos</button>
            <button class="filter-pill">🕷️ Marvel</button>
            <button class="filter-pill">🎵 K-Pop</button>
            <button class="filter-pill">⚽ Fútbol</button>
            <button class="filter-pill">🔥 Ofertas</button>
            <button class="filter-pill">⭐ Novedades</button>
            <div class="filter-right">
                <div class="search-wrap">
                    <input type="text" class="form-control" placeholder="Buscar producto…"
                        style="width:220px;padding:.45rem 1rem .45rem 2.2rem" />
                </div>
                <select class="filter-select">
                    <option>Ordenar: Relevancia</option>
                    <option>Precio: menor a mayor</option>
                    <option>Precio: mayor a menor</option>
                    <option>Más recientes</option>
                </select>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <p style="color:var(--grey);font-size:.88rem"><strong style="color:var(--white)">24 productos</strong>
                encontrados</p>
        </div>

        <div class="prod-grid">
            <div class="prod-card">
                <div class="prod-img marvel-bg">
                    <div class="prod-badge new">Nuevo</div>🕸️
                </div>
                <div class="prod-info">
                    <p class="prod-cat">Marvel</p>
                    <p class="prod-name">Figura Spider-Man Deluxe 30cm</p>
                    <div class="prod-footer">
                        <p class="prod-price">34,99€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img kpop-bg">
                    <div class="prod-badge hot">Hot</div>🎤
                </div>
                <div class="prod-info">
                    <p class="prod-cat">K-Pop</p>
                    <p class="prod-name">BTS — Álbum "Butter" Ed. Especial</p>
                    <div class="prod-footer">
                        <p class="prod-price"><small>28€</small>22,50€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img futbol-bg">
                    <div class="prod-badge sale">-30%</div>🏆
                </div>
                <div class="prod-info">
                    <p class="prod-cat">Fútbol</p>
                    <p class="prod-name">Camiseta Retro Cruyff Coleccionista</p>
                    <div class="prod-footer">
                        <p class="prod-price"><small>59€</small>41,99€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img marvel-bg">
                    <div class="prod-badge new">Nuevo</div>🦸
                </div>
                <div class="prod-info">
                    <p class="prod-cat">Marvel</p>
                    <p class="prod-name">Funko Pop Iron Man Edición Oro</p>
                    <div class="prod-footer">
                        <p class="prod-price">18,99€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img kpop-bg">
                    <div class="prod-badge hot">Hot</div>💫
                </div>
                <div class="prod-info">
                    <p class="prod-cat">K-Pop</p>
                    <p class="prod-name">BLACKPINK Lightstick Ver.2 Oficial</p>
                    <div class="prod-footer">
                        <p class="prod-price">47,00€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img deporte-bg">
                    <div class="prod-badge new">Nuevo</div>⚽
                </div>
                <div class="prod-info">
                    <p class="prod-cat">Fútbol</p>
                    <p class="prod-name">Balón Oficial World Cup 1994 Réplica</p>
                    <div class="prod-footer">
                        <p class="prod-price">29,99€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img marvel-bg">
                    <div class="prod-badge sale">-20%</div>🛡️
                </div>
                <div class="prod-info">
                    <p class="prod-cat">Marvel</p>
                    <p class="prod-name">Réplica Escudo Capitán América 45cm</p>
                    <div class="prod-footer">
                        <p class="prod-price"><small>75€</small>59,99€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
            <div class="prod-card">
                <div class="prod-img kpop-bg">
                    <div class="prod-badge sale">-15%</div>📸
                </div>
                <div class="prod-info">
                    <p class="prod-cat">K-Pop</p>
                    <p class="prod-name">Pack Photocards STRAY KIDS — 50 uds</p>
                    <div class="prod-footer">
                        <p class="prod-price"><small>20€</small>16,99€</p><button class="add-btn">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="pagination">
            <button class="page-btn">‹</button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">›</button>
        </div>
    </section>
