/**
 * Api — cliente único para llamar a /api desde las vistas.
 *
 * Sustituye a las copias de fetch + "Authorization: Bearer" que había en cada vista:
 *  - añade el token JWT si hay sesión;
 *  - envía JSON por defecto (no si el cuerpo es FormData, para subir imágenes);
 *  - si la API responde 401 teniendo token (sesión caducada o cerrada), borra la sesión y,
 *    en las páginas privadas, redirige al login.
 *
 * Requiere auth.js (objeto Auth) cargado antes.
 */
const Api = (() => {
    const RUTAS_PRIVADAS = /^\/(cart|panel|favoritos|admin)(\/|$)/;

    function sesionCaducada() {
        Auth.clear();
        if (RUTAS_PRIVADAS.test(window.location.pathname)) {
            window.location.replace('/login');
        }
    }

    /** fetch con token y cabeceras JSON. Devuelve la Response de fetch. */
    async function http(url, options = {}) {
        const headers = { 'Accept': 'application/json', ...(options.headers || {}) };

        const tieneCuerpo = options.body !== undefined && options.body !== null;
        if (tieneCuerpo && !(options.body instanceof FormData) && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

        const token = Auth.getToken();
        if (token) headers['Authorization'] = 'Bearer ' + token;

        const res = await fetch(url, { ...options, headers });

        // Sin token, un 401 es simplemente "credenciales incorrectas" (login): no es una sesión caducada
        if (res.status === 401 && token) sesionCaducada();

        return res;
    }

    /** Como http(), pero devuelve { ok, status, data } con el cuerpo JSON ya leído. */
    async function request(url, options = {}) {
        const res = await http(url, options);
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data };
    }

    return { fetch: http, request };
})();
