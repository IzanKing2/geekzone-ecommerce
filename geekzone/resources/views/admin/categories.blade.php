@extends('layouts.layout')
@section('title', 'GeekZone — Admin · Categorías')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/adminCategorias.css') }}">
    <style>
        .img-picker { margin-top:.5rem }
        .img-picker-area {
            display:flex;align-items:center;gap:1rem;
            padding:.8rem;background:var(--mid2);border:2px dashed var(--border);
            border-radius:6px;cursor:pointer;transition:border-color .2s;
        }
        .img-picker-area:hover { border-color:var(--cobalt) }
        .img-picker-area.has-image { border-style:solid;border-color:var(--cobalt) }
        .img-picker-area.uploading { border-color:var(--gold);cursor:wait }
        .img-preview {
            width:64px;height:64px;border-radius:4px;object-fit:cover;
            border:1px solid var(--border);flex-shrink:0;background:rgba(0,71,171,.1);
            display:flex;align-items:center;justify-content:center;font-size:1.6rem;
        }
        .img-preview img { width:100%;height:100%;object-fit:cover;border-radius:4px }
        .img-picker-info { flex:1;min-width:0 }
        .img-picker-name {
            font-size:.82rem;color:var(--white);white-space:nowrap;
            overflow:hidden;text-overflow:ellipsis;margin-bottom:.2rem;
        }
        .img-picker-hint { font-size:.75rem;color:var(--grey) }
        .img-picker-btns { display:flex;gap:.5rem;flex-shrink:0 }
        .img-picker-error {
            font-size:.78rem;color:#f87171;margin-top:.4rem;display:none;
            padding:.3rem .5rem;background:rgba(185,28,28,.12);
            border:1px solid rgba(185,28,28,.25);border-radius:4px;
        }
        .img-picker-area.error { border-color:rgba(185,28,28,.5) }
    </style>
@endpush

@section('content')
<div class="layout-sidebar">

    {{-- ══ SIDEBAR ══ --}}
    <aside class="sidebar">
        <div style="padding:1rem 1.5rem 1.2rem;border-bottom:1px solid var(--border);margin-bottom:.5rem">
            <p style="font-family:'Barlow Condensed',sans-serif;font-size:.7rem;letter-spacing:3px;text-transform:uppercase;color:rgba(156,163,175,.45);margin-bottom:.2rem">Panel de control</p>
            <p style="font-weight:600;font-size:.95rem">GeekZone Admin</p>
        </div>
        <div class="sidebar-section">
            <p class="sidebar-label">Principal</p>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
            <a href="{{ route('admin.products') }}" class="sidebar-link"><span class="icon">📦</span> Productos</a>
        </div>
        <hr class="sidebar-divider"/>
        <div class="sidebar-section">
            <p class="sidebar-label">Contenido</p>
            <a href="{{ route('admin.categories') }}" class="sidebar-link active"><span class="icon">🏷️</span> Categorías</a>
        </div>
        <hr class="sidebar-divider"/>
        <div class="sidebar-section">
            <a href="{{ route('shop') }}" class="sidebar-link"><span class="icon">🏠</span> Ver tienda</a>
            <a href="#" class="sidebar-link" id="admin-logout" style="color:var(--red)"><span class="icon">↩</span> Salir</a>
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    <main class="main-content">

        <div class="crud-header-wrap">
            <div>
                <p class="page-eyebrow" style="padding-top:0;margin-bottom:0">Gestión de contenido</p>
                <h2 class="panel-title" style="font-family:'Bebas Neue',sans-serif;font-size:2.5rem;letter-spacing:2px;margin:0">Gestión de <span style="color:var(--cobalt-light)">Categorías</span></h2>
            </div>
            <button class="btn btn-primary" id="btn-nueva-cat">+ Nueva categoría</button>
        </div>

        {{-- Alerta feedback --}}
        <div id="alert-box" style="display:none;margin-bottom:1.5rem"></div>

        {{-- ══ CATEGORY CARDS ══ --}}
        @php
            $palettes = [
                ['from'=>'#8B0000','to'=>'#E01020 50%,#0047AB'],
                ['from'=>'#0047AB','to'=>'#8A2BE2 60%,#FF69B4'],
                ['from'=>'#003080','to'=>'#0047AB 50%,#1a7f37'],
                ['from'=>'#1a3a1a','to'=>'#1a7f37 50%,#2da44e'],
                ['from'=>'#4a0072','to'=>'#8A2BE2 50%,#FF69B4'],
                ['from'=>'#7a3800','to'=>'#FF6B35 50%,#ffd700'],
            ];
        @endphp

        <div class="cat-overview-grid" id="categories-grid">
            @forelse($categories as $i => $cat)
            @php $pal = $palettes[$i % count($palettes)]; @endphp
            <div class="cat-overview-card" data-id="{{ $cat->id }}">
                <div class="cat-overview-banner" style="background:linear-gradient(135deg,{{ $pal['from'] }},{{ $pal['to'] }})">
                    <div class="cat-badge-inner">
                        <span class="badge badge-cobalt">{{ $cat->products_count }} producto{{ $cat->products_count !== 1 ? 's' : '' }}</span>
                    </div>
                    <span class="big-icon">🏷️</span>
                </div>
                <div class="cat-overview-body">
                    <p class="cat-overview-name">{{ $cat->name }}</p>
                    @if($cat->description)
                    <p style="font-size:.8rem;color:var(--grey);margin-bottom:.6rem;line-height:1.4">{{ Str::limit($cat->description, 60) }}</p>
                    @endif
                    <div class="cat-overview-stats">
                        <div class="cat-overview-stat">
                            <p class="v">{{ $cat->products_count }}</p>
                            <p class="l">Productos</p>
                        </div>
                        <div class="cat-overview-stat">
                            <p class="v" style="color:var(--gold)">{{ number_format($cat->total_ventas, 0, ',', '.') }}€</p>
                            <p class="l">Ventas</p>
                        </div>
                    </div>
                    <div class="cat-overview-actions">
                        <button class="btn btn-secondary btn-sm" style="flex:1;justify-content:center"
                            onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}', '{{ addslashes($cat->image_url ?? '') }}')">
                            ✏️ Editar
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div style="grid-column:1/-1;text-align:center;padding:4rem 0;color:var(--grey)">
                <p style="font-size:1.1rem">No hay categorías creadas aún.</p>
            </div>
            @endforelse
        </div>

    </main>
</div>

{{-- ══ MODAL: NUEVA CATEGORÍA ══ --}}
<div class="modal-overlay" id="modal-new" style="display:none" onclick="if(event.target===this)closeModal('modal-new')">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Nueva Categoría</h3>
            <button class="modal-close" onclick="closeModal('modal-new')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Nombre de la categoría *</label>
                <input type="text" id="new-name" class="form-control" placeholder="Ej: Anime, Videojuegos…"/>
                <p class="form-error" id="new-name-error" style="display:none"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea id="new-desc" class="form-control" rows="2" placeholder="Breve descripción para la tienda…" style="resize:vertical"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Imagen (opcional)</label>
                <input type="hidden" id="new-image"/>
                <input type="file" id="new-image-file" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" style="display:none" onchange="handleImageSelect('new')"/>
                <div class="img-picker">
                    <div class="img-picker-area" id="new-picker-area" onclick="document.getElementById('new-image-file').click()">
                        <div class="img-preview" id="new-img-preview">🏷️</div>
                        <div class="img-picker-info">
                            <p class="img-picker-name" id="new-img-name">Sin imagen seleccionada</p>
                            <p class="img-picker-hint" id="new-img-hint">Haz clic para seleccionar · JPG, PNG, GIF, SVG · Máx. 2MB</p>
                        </div>
                        <div class="img-picker-btns" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('new-image-file').click()">📁 Seleccionar</button>
                            <button type="button" class="btn btn-danger btn-sm" id="new-img-remove" style="display:none" onclick="removeImage('new')">✕</button>
                        </div>
                    </div>
                    <p class="img-picker-error" id="new-img-error"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-new')">Cancelar</button>
            <button class="btn btn-primary" id="btn-create" onclick="createCategory()">Crear categoría</button>
        </div>
    </div>
</div>

{{-- ══ MODAL: EDITAR CATEGORÍA ══ --}}
<div class="modal-overlay" id="modal-edit" style="display:none" onclick="if(event.target===this)closeModal('modal-edit')">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ Editar Categoría</h3>
            <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-id"/>
            <div class="alert alert-info">
                <span>✏️</span> Editando: <strong id="edit-title-label">—</strong>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" id="edit-name" class="form-control"/>
                <p class="form-error" id="edit-name-error" style="display:none"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea id="edit-desc" class="form-control" rows="2" style="resize:vertical"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Imagen</label>
                <input type="hidden" id="edit-image"/>
                <input type="file" id="edit-image-file" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml" style="display:none" onchange="handleImageSelect('edit')"/>
                <div class="img-picker">
                    <div class="img-picker-area" id="edit-picker-area" onclick="document.getElementById('edit-image-file').click()">
                        <div class="img-preview" id="edit-img-preview">🏷️</div>
                        <div class="img-picker-info">
                            <p class="img-picker-name" id="edit-img-name">Sin imagen</p>
                            <p class="img-picker-hint" id="edit-img-hint">Haz clic para cambiar · JPG, PNG, GIF, SVG · Máx. 2MB</p>
                        </div>
                        <div class="img-picker-btns" onclick="event.stopPropagation()">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('edit-image-file').click()">📁 Cambiar</button>
                            <button type="button" class="btn btn-danger btn-sm" id="edit-img-remove" style="display:none" onclick="removeImage('edit')">✕</button>
                        </div>
                    </div>
                    <p class="img-picker-error" id="edit-img-error"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger btn-sm" style="margin-right:auto" id="btn-delete" onclick="deleteCategory()">🗑️ Eliminar</button>
            <button class="btn btn-secondary" onclick="closeModal('modal-edit')">Cancelar</button>
            <button class="btn btn-primary" id="btn-save" onclick="saveCategory()">Guardar cambios</button>
        </div>
    </div>
</div>

<script>
    const API = '/api/categorias';

    function getToken() { return Auth.getToken(); }

    // ── Modales ─────────────────────────────────────────────────────
    document.getElementById('btn-nueva-cat').addEventListener('click', () => {
        document.getElementById('new-name').value = '';
        document.getElementById('new-desc').value = '';
        document.getElementById('new-name-error').style.display = 'none';
        resetPicker('new');
        document.getElementById('modal-new').style.display = 'flex';
    });

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openEditModal(id, name, desc, image) {
        document.getElementById('edit-id').value   = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-desc').value = desc;
        document.getElementById('edit-title-label').textContent = name;
        document.getElementById('edit-name-error').style.display = 'none';
        if (image) {
            setPickerImage('edit', image, image.split('/').pop());
        } else {
            resetPicker('edit');
        }
        document.getElementById('modal-edit').style.display = 'flex';
    }

    // ── Feedback ─────────────────────────────────────────────────────
    function showAlert(msg, type = 'success') {
        const box = document.getElementById('alert-box');
        const colors = {
            success: 'rgba(26,127,55,.15);border:1px solid rgba(26,127,55,.3);color:#2da44e',
            error:   'rgba(185,28,28,.15);border:1px solid rgba(185,28,28,.3);color:#f87171',
        };
        box.style.cssText = `display:flex;align-items:center;gap:.7rem;padding:.9rem 1.2rem;border-radius:4px;font-size:.9rem;background:${colors[type]}`;
        box.textContent = msg;
        clearTimeout(box._t);
        box._t = setTimeout(() => { box.style.display = 'none'; }, 4000);
    }

    function setLoading(btnId, loading) {
        const btn = document.getElementById(btnId);
        btn.disabled = loading;
        btn.style.opacity = loading ? '.6' : '1';
    }

    // ── Image picker ─────────────────────────────────────────────────

    function pickerError(prefix, msg) {
        resetPicker(prefix);
        const el = document.getElementById(`${prefix}-img-error`);
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
        document.getElementById(`${prefix}-picker-area`).classList.add('error');
    }

    function setPickerImage(prefix, url, filename) {
        document.getElementById(`${prefix}-image`).value = url;
        const preview = document.getElementById(`${prefix}-img-preview`);
        preview.innerHTML = `<img src="${url}" onerror="this.parentElement.textContent='🏷️'">`;
        document.getElementById(`${prefix}-img-name`).textContent = filename || url.split('/').pop();
        document.getElementById(`${prefix}-img-hint`).textContent  = 'Imagen cargada · Haz clic para cambiar';
        const area = document.getElementById(`${prefix}-picker-area`);
        area.classList.add('has-image');
        area.classList.remove('error');
        document.getElementById(`${prefix}-img-remove`).style.display = 'inline-flex';
        const errEl = document.getElementById(`${prefix}-img-error`);
        if (errEl) errEl.style.display = 'none';
    }

    function resetPicker(prefix) {
        document.getElementById(`${prefix}-image`).value = '';
        document.getElementById(`${prefix}-image-file`).value = '';
        document.getElementById(`${prefix}-img-preview`).innerHTML = '🏷️';
        document.getElementById(`${prefix}-img-name`).textContent  = prefix === 'new' ? 'Sin imagen seleccionada' : 'Sin imagen';
        document.getElementById(`${prefix}-img-hint`).textContent  = 'Haz clic para seleccionar · JPG, PNG, GIF, SVG · Máx. 2MB';
        const area = document.getElementById(`${prefix}-picker-area`);
        area.classList.remove('has-image', 'uploading', 'error');
        document.getElementById(`${prefix}-img-remove`).style.display = 'none';
        const errEl = document.getElementById(`${prefix}-img-error`);
        if (errEl) errEl.style.display = 'none';
    }

    function removeImage(prefix) { resetPicker(prefix); }

    async function handleImageSelect(prefix) {
        const fileInput = document.getElementById(`${prefix}-image-file`);
        const file = fileInput.files[0];
        if (!file) return;

        const errEl = document.getElementById(`${prefix}-img-error`);
        if (errEl) errEl.style.display = 'none';

        const allowed = ['image/jpeg','image/png','image/jpg','image/gif','image/svg+xml'];
        if (!allowed.includes(file.type)) {
            pickerError(prefix, 'Tipo no válido. Usa JPG, PNG, GIF o SVG.');
            fileInput.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            pickerError(prefix, 'La imagen supera el límite de 2MB.');
            fileInput.value = '';
            return;
        }

        const area = document.getElementById(`${prefix}-picker-area`);
        area.classList.add('uploading');
        area.classList.remove('error');
        document.getElementById(`${prefix}-img-name`).textContent = 'Subiendo imagen…';
        document.getElementById(`${prefix}-img-hint`).textContent  = file.name;

        const form = new FormData();
        form.append('image', file);

        try {
            const res = await fetch('/api/imagenes', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${getToken()}` },
                body: form,
            });
            const json = await res.json();
            area.classList.remove('uploading');

            if (!res.ok) {
                const msg = json.errors?.image?.[0] || json.message || 'Error al subir la imagen.';
                pickerError(prefix, `Error ${res.status}: ${msg}`);
                return;
            }
            setPickerImage(prefix, json.image, file.name);
        } catch {
            area.classList.remove('uploading');
            pickerError(prefix, 'Error de red. Comprueba la conexión e inténtalo de nuevo.');
        }
    }

    // ── Crear ────────────────────────────────────────────────────────
    async function createCategory() {
        const name  = document.getElementById('new-name').value.trim();
        const desc  = document.getElementById('new-desc').value.trim();
        const image = document.getElementById('new-image').value;

        document.getElementById('new-name-error').style.display = 'none';
        if (!name) {
            document.getElementById('new-name-error').textContent = 'El nombre es obligatorio.';
            document.getElementById('new-name-error').style.display = 'block';
            return;
        }

        setLoading('btn-create', true);
        const res = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${getToken()}` },
            body: JSON.stringify({ name, description: desc || null, image_url: image || null }),
        });
        const json = await res.json();
        setLoading('btn-create', false);

        if (!res.ok) {
            const err = json.errors?.name?.[0] || json.message || 'Error al crear la categoría.';
            document.getElementById('new-name-error').textContent = err;
            document.getElementById('new-name-error').style.display = 'block';
            return;
        }

        closeModal('modal-new');
        showAlert('Categoría creada correctamente. Recargando…');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Guardar edición ──────────────────────────────────────────────
    async function saveCategory() {
        const id    = document.getElementById('edit-id').value;
        const name  = document.getElementById('edit-name').value.trim();
        const desc  = document.getElementById('edit-desc').value.trim();
        const image = document.getElementById('edit-image').value;

        document.getElementById('edit-name-error').style.display = 'none';
        if (!name) {
            document.getElementById('edit-name-error').textContent = 'El nombre es obligatorio.';
            document.getElementById('edit-name-error').style.display = 'block';
            return;
        }

        setLoading('btn-save', true);
        const res = await fetch(`${API}/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${getToken()}` },
            body: JSON.stringify({ name, description: desc || null, image_url: image || null }),
        });
        const json = await res.json();
        setLoading('btn-save', false);

        if (!res.ok) {
            const err = json.errors?.name?.[0] || json.message || 'Error al guardar.';
            document.getElementById('edit-name-error').textContent = err;
            document.getElementById('edit-name-error').style.display = 'block';
            return;
        }

        closeModal('modal-edit');
        showAlert('Categoría actualizada correctamente. Recargando…');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Eliminar ─────────────────────────────────────────────────────
    async function deleteCategory() {
        const id   = document.getElementById('edit-id').value;
        const name = document.getElementById('edit-title-label').textContent;

        if (!confirm(`¿Eliminar la categoría "${name}"? Esta acción no se puede deshacer.`)) return;

        setLoading('btn-delete', true);
        const res = await fetch(`${API}/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${getToken()}` },
        });
        const json = await res.json();
        setLoading('btn-delete', false);

        if (!res.ok) {
            closeModal('modal-edit');
            showAlert(json.message || 'No se pudo eliminar.', 'error');
            return;
        }

        closeModal('modal-edit');
        showAlert('Categoría eliminada correctamente. Recargando…');
        setTimeout(() => location.reload(), 1200);
    }

    // ── Logout ───────────────────────────────────────────────────────
    document.getElementById('admin-logout')?.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${getToken()}` }
            });
        } catch {}
        Auth.clear();
        window.location.href = "{{ route('shop') }}";
    });
</script>
@endsection
