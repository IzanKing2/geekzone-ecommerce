/**
 * Utils — escape de datos antes de insertarlos como HTML.
 *
 * El JWT vive en localStorage: cualquier XSS lo roba. Por eso todo dato que venga
 * de la API (nombres, descripciones, URLs…) y se inserte con innerHTML/plantillas
 * debe pasar por Utils.esc() (texto y atributos) o Utils.safeUrl() (src/href).
 * Siempre que se pueda, es preferible textContent / createElement.
 */
const Utils = (() => {
    const ENTIDADES = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '`': '&#96;',
    };

    /** Escapa un valor para usarlo como texto o dentro de un atributo HTML entre comillas. */
    function esc(valor) {
        return String(valor ?? '').replace(/[&<>"'`]/g, (c) => ENTIDADES[c]);
    }

    /**
     * Devuelve la URL ya escapada para un atributo src/href, o '' si usa un esquema
     * ejecutable (javascript:, data:, vbscript:). Se admiten http(s), rutas absolutas y relativas.
     */
    function safeUrl(url) {
        const limpia = String(url ?? '').trim();
        if (/^(javascript|data|vbscript):/i.test(limpia.replace(/[\s\u0000-\u001f]/g, ''))) {
            return '';
        }
        return esc(limpia);
    }

    return { esc, safeUrl };
})();
