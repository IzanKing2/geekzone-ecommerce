/**
 * Auth — gestión centralizada del token JWT.
 *
 * Evita que localStorage y sessionStorage se superpongan:
 * cada setToken() limpia ambos antes de escribir en el storage correcto.
 */
const Auth = (() => {
    const TOKEN_KEY = 'token';
    const ROLE_KEY  = 'role';

    /** Devuelve el token activo (localStorage tiene prioridad). */
    function getToken() {
        return localStorage.getItem(TOKEN_KEY) || sessionStorage.getItem(TOKEN_KEY) || null;
    }

    /** Devuelve el rol activo. */
    function getRole() {
        return localStorage.getItem(ROLE_KEY) || sessionStorage.getItem(ROLE_KEY) || null;
    }

    /**
     * Guarda token y rol.
     * @param {string}  token
     * @param {string}  role
     * @param {boolean} remember  true → localStorage, false → sessionStorage
     */
    function setToken(token, role, remember = false) {
        clear(); // Eliminar cualquier token previo en ambos storages
        const storage = remember ? localStorage : sessionStorage;
        storage.setItem(TOKEN_KEY, token);
        if (role != null) storage.setItem(ROLE_KEY, role);
    }

    /** Elimina token y rol de ambos storages. */
    function clear() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(ROLE_KEY);
        sessionStorage.removeItem(TOKEN_KEY);
        sessionStorage.removeItem(ROLE_KEY);
    }

    /** ¿Hay sesión activa? */
    function isLoggedIn() {
        return getToken() !== null;
    }

    return { getToken, getRole, setToken, clear, isLoggedIn };
})();
