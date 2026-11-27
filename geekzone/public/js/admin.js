/**
 * AdminUI — utilidades compartidas de las páginas de administración.
 *
 * La seguridad real está en la API (JWT + rol admin en cada endpoint). Esto solo
 * evita mostrar un panel vacío a quien no es admin y centraliza la llamada a la
 * API, el formato de importes y la creación segura de DOM.
 *
 * Todo el contenido dinámico se inserta con textContent / createElement,
 * nunca con innerHTML, para que un dato malicioso no pueda ejecutar código.
 */
const AdminUI = (() => {
    const LOGIN_URL = '/login';
    const SHOP_URL = '/';

    /** Redirige al login si no hay sesión de administrador. Devuelve true si puede continuar. */
    function guard() {
        if (!Auth.getToken() || Auth.getRole() !== 'admin') {
            window.location.replace(LOGIN_URL);
            return false;
        }
        return true;
    }

    /** GET a /api/admin/dashboard/{path}; devuelve el campo `data` de la respuesta. */
    async function api(path) {
        // Api.fetch añade el token y, ante un 401, borra la sesión y redirige al login
        const res = await Api.fetch('/api/admin/dashboard/' + path);

        if (res.status === 401) {
            throw new Error('Sesión no válida');
        }
        if (res.status === 403) {
            window.location.replace(SHOP_URL);
            throw new Error('Sin permisos de administrador');
        }
        if (!res.ok) {
            throw new Error('Error ' + res.status);
        }

        return (await res.json()).data;
    }

    /** Importe en euros con formato 1.234,56€ */
    function money(value, decimals = 2) {
        return new Intl.NumberFormat('de-DE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(value) + '€';
    }

    /** Número con separador de miles: 1.234 */
    function number(value) {
        return new Intl.NumberFormat('de-DE').format(value);
    }

    /** "2026-11" → "nov 2026" */
    function monthLabel(yyyyMm) {
        const [y, m] = String(yyyyMm).split('-').map(Number);
        return new Date(y, m - 1, 1).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
    }

    /**
     * Crea un elemento. options: { text, style, className, attrs: {…} }.
     * Los hijos pueden ser nodos o cadenas (se insertan como texto).
     */
    function el(tag, options = {}, ...children) {
        const node = document.createElement(tag);
        if (options.text !== undefined) node.textContent = options.text;
        if (options.style) node.style.cssText = options.style;
        if (options.className) node.className = options.className;
        for (const [name, value] of Object.entries(options.attrs || {})) {
            node.setAttribute(name, value);
        }
        children.forEach(child => node.append(child));
        return node;
    }

    /** Sustituye el contenido de un contenedor. */
    function fill(container, ...nodes) {
        container.replaceChildren(...nodes);
    }

    /** Mensaje de estado (cargando / vacío / error) dentro de un contenedor. */
    function message(container, text, padding = '1rem 0') {
        fill(container, el('p', {
            text,
            style: `color:var(--grey);font-size:.88rem;text-align:center;padding:${padding}`,
        }));
    }

    /** Cierra sesión (invalida el token en la API) y vuelve a la tienda. */
    async function logout() {
        try {
            await Api.fetch('/api/logout', { method: 'POST' });
        } catch { /* sin red: igualmente se limpia la sesión local */ }
        Auth.clear();
        window.location.href = SHOP_URL;
    }

    return { guard, api, money, number, monthLabel, el, fill, message, logout };
})();
