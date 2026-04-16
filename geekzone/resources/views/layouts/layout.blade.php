<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'GeekZone')</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
    @stack('styles')
    <script src="{{ asset('js/auth.js') }}"></script>
</head>
<body>

    <!--
        ═══════════════════════════════════════════════════
        LOADER DE PÁGINA
        ───────────────────────────────────────────────────
        Este div aparece encima de todo mientras la página
        carga. Cuando el JS detecta que la página está lista,
        le añade la clase "loader-hidden" y desaparece con
        una transición suave de opacidad.
        ═══════════════════════════════════════════════════
    -->
    <div id="page-loader" role="status" aria-label="Cargando página">
        <!-- Logo animado -->
        <div class="loader-logo">Geek<span>Zone</span></div>

        <!-- Spinner de doble círculo -->
        <div class="loader-spinner" aria-hidden="true"></div>

        <!-- Texto de estado -->
        <p class="loader-text">Cargando…</p>
    </div>
    @include('layouts.header')

    @yield('content')

    @include('layouts.footer')

    <!--
        ═══════════════════════════════════════════════════
        SCRIPT GLOBAL: Loader + Animaciones de entrada
        ───────────────────────────────────────────────────
        Este script hace DOS cosas:

        1. OCULTAR EL LOADER:
           Escucha el evento "load" de la ventana (que se
           dispara cuando TODO ha cargado: HTML, CSS, imágenes).
           Entonces añade la clase "loader-hidden" al loader,
           que activa la transición de desvanecimiento.
           Pasado 600ms (duración de la transición), lo elimina
           del DOM para que no bloquee interacciones.

        2. ANIMACIONES DE ENTRADA (IntersectionObserver):
           El IntersectionObserver es una API del navegador que
           avisa cuando un elemento entra en el viewport (la
           parte visible de la pantalla). Cuando detecta que
           un elemento con .fade-in-up o .fade-in es visible,
           le añade la clase .visible que activa la transición
           CSS de aparición suave.
        ═══════════════════════════════════════════════════
    -->
    <script>
        // ─── 1. OCULTAR EL LOADER CUANDO LA PÁGINA ESTÁ LISTA ───

        // Referencia al elemento del loader
        const paginaLoader = document.getElementById('page-loader');

        // Seguridad: si el loader no existe, no hacemos nada
        if (paginaLoader) {

            // Función que oculta el loader
            function ocultarLoader() {
                // Añadir clase que activa la transición de desvanecimiento
                paginaLoader.classList.add('loader-hidden');

                // Tras 600ms (duración de la transición), lo ocultamos
                // completamente para que no interfiera con el usuario
                setTimeout(() => {
                    paginaLoader.style.display = 'none';
                }, 600);
            }

            // Escuchar el evento 'load':
            // Se dispara cuando la página (HTML + todos los recursos) ha cargado
            if (document.readyState === 'complete') {
                // Si ya estaba cargada (caché), ocultar inmediatamente
                ocultarLoader();
            } else {
                // Si aún no ha cargado, esperar al evento
                window.addEventListener('load', ocultarLoader);
            }
        }

        // ─── 2. ANIMACIONES DE ENTRADA CON IntersectionObserver ───

        // IntersectionObserver: vigila qué elementos están en pantalla
        const observadorAnimaciones = new IntersectionObserver(
            (entradas) => {
                entradas.forEach(entrada => {
                    // Si el elemento es visible en pantalla...
                    if (entrada.isIntersecting) {
                        // ...añadir clase .visible que activa la animación CSS
                        entrada.target.classList.add('visible');

                        // Dejar de observar este elemento (la animación ya ocurrió)
                        observadorAnimaciones.unobserve(entrada.target);
                    }
                });
            },
            {
                // El elemento debe estar al menos un 10% visible para activarse
                threshold: 0.1,
                // Empieza la animación un poco antes de que entre en pantalla
                rootMargin: '0px 0px -40px 0px'
            }
        );

        // Seleccionar todos los elementos que tienen clase de animación
        // y empezar a observarlos
        document.querySelectorAll('.fade-in-up, .fade-in')
            .forEach(elemento => observadorAnimaciones.observe(elemento));
    </script>
</body>
</html>
