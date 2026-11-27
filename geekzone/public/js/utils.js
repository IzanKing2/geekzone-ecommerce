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

/**
 * Delegación de eventos: sustituye a los atributos onclick=/onchange=/oninput=/onerror= inline,
 * que la política CSP (script-src con nonce) bloquea. El HTML declara qué hacer con data-*:
 *
 *   data-action="fn"            click → llama a fn(...args)   (fn es una función global)
 *   data-args='[1,"a"]'         argumentos JSON de la llamada (opcional)
 *   data-change="fn"            change → fn(...args)
 *   data-overlay-close="id"     click directamente sobre el fondo de un modal → closeModal(id)
 *   data-click-target="id"      click → hace click en otro elemento (p. ej. un <input type=file>)
 *   data-prevent-default        click → event.preventDefault()
 *   data-autosubmit             change → envía el formulario
 *   data-autosubmit-delay="ms"  input → envía el formulario tras una pausa (búsqueda mientras escribes)
 *   data-fallback="hide-sibling|emoji" en <img>: qué hacer si la imagen no carga
 */
(() => {
    const funcion = (nombre) => (typeof window[nombre] === 'function' ? window[nombre] : null);

    function argumentos(el) {
        try {
            return el.dataset.args ? JSON.parse(el.dataset.args) : [];
        } catch {
            return [];
        }
    }

    function llamar(el, atributo) {
        const fn = funcion(el.getAttribute(atributo));
        if (fn) fn.call(el, ...argumentos(el));
    }

    document.addEventListener('click', (e) => {
        const fondo = e.target.closest('[data-overlay-close]');
        if (fondo && e.target === fondo) funcion('closeModal')?.(fondo.dataset.overlayClose);

        // data-stop es una barrera (antes event.stopPropagation()): un clic dentro de ella no
        // activa un data-click-target situado en un ancestro, pero sí uno dentro de la propia barrera.
        const destino = e.target.closest('[data-click-target]');
        const barrera = e.target.closest('[data-stop]');
        const cruzaBarrera = barrera && destino !== barrera && destino?.contains(barrera);
        if (destino && !cruzaBarrera) document.getElementById(destino.dataset.clickTarget)?.click();

        if (e.target.closest('[data-prevent-default]')) e.preventDefault();

        const accion = e.target.closest('[data-action]');
        if (accion) llamar(accion, 'data-action');
    });

    document.addEventListener('change', (e) => {
        const cambio = e.target.closest('[data-change]');
        if (cambio) llamar(cambio, 'data-change');

        const auto = e.target.closest('[data-autosubmit]');
        if (auto?.form) auto.form.submit();
    });

    document.addEventListener('input', (e) => {
        const auto = e.target.closest('[data-autosubmit-delay]');
        if (!auto?.form) return;
        clearTimeout(auto._temporizador);
        auto._temporizador = setTimeout(() => auto.form.submit(), Number(auto.dataset.autosubmitDelay) || 500);
    });

    // El evento "error" de una imagen no burbujea: se escucha en fase de captura
    document.addEventListener('error', (e) => {
        const img = e.target;
        if (!(img instanceof HTMLImageElement)) return;

        if (img.dataset.fallback === 'hide-sibling') {
            img.style.display = 'none';
            if (img.nextElementSibling) img.nextElementSibling.style.display = 'flex';
        } else if (img.dataset.fallback === 'emoji') {
            img.style.display = 'none';
            if (img.parentElement) img.parentElement.textContent = img.dataset.fallbackText || '📦';
        }
    }, true);
})();
