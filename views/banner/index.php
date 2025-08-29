<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/bannerAdmi.css') ?>">

<body>
    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <?php
    // Base para URLs de imágenes
    $baseUploadUrl = rtrim($uploadDirUrl ?? 'uploads/banners/', '/') . '/';
    ?>

    <style>
        /* ---- Layout principal en 2 columnas ---- */
        #banner-app {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            align-items: start;
        }

        @media (max-width: 980px) {
            #banner-app {
                grid-template-columns: 1fr;
            }
        }

        /* ---- Tabla de banners ---- */
        .admin-productos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-productos-table th,
        .admin-productos-table td {
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
            vertical-align: middle;
        }

        .admin-productos-table th {
            background: #f7f7f7;
            text-align: left;
        }

        .drag-handle {
            cursor: move;
            width: 34px;
            text-align: center;
            user-select: none;
            font-size: 18px;
        }

        .row-index {
            width: 40px;
            text-align: center;
            font-weight: 600;
            color: #444;
        }

        /* ---- Switch ---- */
        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
            vertical-align: middle;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ccc;
            transition: .2s;
            border-radius: 999px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background: white;
            transition: .2s;
            border-radius: 50%;
        }

        .switch input:checked+.slider {
            background: #2ecc71;
        }

        .switch input:checked+.slider:before {
            transform: translateX(22px);
        }

        /* ---- Botones ---- */
        .btn-xs {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
        }

        .btn-danger-xs {
            border-color: #f5b5b5;
            color: #b30000;
        }

        .btn-primary-xs {
            border-color: #a6c8ff;
            color: #0b64d6;
        }

        /* ---- Drag & drop visual ---- */
        .row-ghost {
            opacity: 0.55;
        }

        /* ---- Panel derecho (nuevo banner) ---- */
        .card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .04);
            padding: 16px;
        }

        .card h2 {
            margin-top: 0;
        }

        .dropzone {
            border: 2px dashed #c7c7c7;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            cursor: pointer;
        }

        .dropzone.dragover {
            border-color: #0b64d6;
            background: #f6fbff;
        }

        .preview {
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }

        .preview img {
            max-width: 100%;
            max-height: 180px;
            border-radius: 10px;
        }

        /* ---- Toast ---- */
        #toast {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            background: #111;
            color: #fff;
            padding: 10px 12px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .2);
            font-size: 14px;
        }

        .toast.success {
            background: #0c7a43;
        }

        .toast.error {
            background: #b00020;
        }
    </style>

    <div class="container"
        id="banner-app"
        data-ruta-ordenar="<?= url('banner/ordenar') ?>"
        data-ruta-toggle="<?= url('banner/toggle') ?>"
        data-ruta-eliminar="<?= url('banner/eliminar') ?>"
        data-ruta-guardar="<?= url('banner/guardar') ?>"
        data-ruta-actualizar="<?= url('banner/actualizar-imagen') ?>"
        data-base-upload-url="<?= htmlspecialchars($baseUploadUrl, ENT_QUOTES) ?>">
        <!-- IZQUIERDA: LISTA DE BANNERS -->
        <div>
            <h1>Gestión de Banners</h1>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']);
                                                unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <p style="margin:8px 0 16px; color:#666;">Arrastra las filas con el ícono ☰ para cambiar el orden. Se guarda automáticamente.</p>

            <table class="admin-productos-table">
                <thead>
                    <tr>
                        <th style="width:34px;"></th> <!-- drag -->
                        <th style="width:40px;">#</th> <!-- índice -->
                        <th>Imagen</th>
                        <th style="width:120px;">Activo</th>
                        <th style="width:220px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="sortable">
                    <?php if (empty($banners)): ?>
                        <tr>
                            <td colspan="5" style="color:#777;">No hay banners. Usa el panel derecho para subir uno nuevo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($banners as $i => $b): ?>
                            <tr data-id="<?= (int)$b['id'] ?>">
                                <td class="drag-handle" title="Arrastrar">☰</td>
                                <td class="row-index"><?= $i + 1 ?></td>
                                <td>
                                    <img src="<?= url($baseUploadUrl . $b['nombre_imagen']) ?>"
                                        alt="banner"
                                        style="max-width:260px; max-height:120px; object-fit:cover; border-radius:8px;">
                                </td>
                                <td>
                                    <label class="switch" title="Activar/Desactivar">
                                        <input type="checkbox" class="switch-activo" data-id="<?= (int)$b['id'] ?>" <?= !empty($b['activo']) ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                        <button class="btn-xs btn-primary-xs btn-reemplazar" data-id="<?= (int)$b['id'] ?>">Reemplazar imagen</button>
                                        <input type="file" class="hidden file-reemplazo" data-id="<?= (int)$b['id'] ?>" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
                                        <button class="btn-xs btn-danger-xs btn-eliminar" data-id="<?= (int)$b['id'] ?>">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- DERECHA: NUEVO BANNER -->
        <div class="card" id="panel-nuevo">
            <h2>Nuevo Banner</h2>
            <p style="margin-top:-6px; color:#666;">Formatos permitidos: JPG, PNG, WEBP, GIF.</p>

            <div id="dropzone" class="dropzone">
                <p>Arrastra y suelta la imagen aquí o haz clic para seleccionar</p>
                <input id="input-nuevo-file" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
            </div>
            <div id="preview-nuevo" class="preview" aria-live="polite"></div>

            <div style="margin:14px 0;">
                <label style="display:flex; align-items:center; gap:10px;">
                    <span>Activo</span>
                    <label class="switch" title="Activar/Desactivar">
                        <input type="checkbox" id="nuevo-activo" checked>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button id="btn-guardar-nuevo" class="btn">GUARDAR</button>
        </div>
    </div>

    <div id="toast"></div>

    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>

    <script>
        /* ===========================
   Script corregido y robusto
   =========================== */
        (function() {
            // ---------- Helpers ----------
            const $ = (sel, ctx = document) => ctx.querySelector(sel);
            const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
            const app = $('#banner-app');
            const rutas = {
                ordenar: app.dataset.rutaOrdenar,
                toggle: app.dataset.rutaToggle,
                eliminar: app.dataset.rutaEliminar,
                guardar: app.dataset.rutaGuardar,
                actualizar: app.dataset.rutaActualizar,
            };
            const baseUploadUrl = app.dataset.baseUploadUrl || '';
            const csrf = (document.querySelector('meta[name="csrf"]')?.content || '').trim();

            function showToast(msg, type = 'success') {
                const box = document.createElement('div');
                box.className = 'toast ' + (type === 'error' ? 'error' : 'success');
                box.textContent = msg;
                $('#toast').appendChild(box);
                setTimeout(() => box.remove(), 3500);
            }

            // Safe setter que evita añadir ?v a blob/data URLs y añade cache-buster solo a HTTP URLs
            function safeSetImageSrc(img, url) {
                if (!url) return;
                if (url.startsWith('blob:') || url.startsWith('data:')) {
                    img.src = url;
                    return;
                }
                const sep = url.includes('?') ? '&' : '?';
                img.src = url + sep + 'v=' + Date.now();
            }

            // PostForm robusto: intenta parsear JSON, si viene texto/HTML devuelve message
            async function postForm(url, formData = null) {
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest'
                };
                if (csrf) headers['X-CSRF-Token'] = csrf;
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers,
                        body: formData
                    });
                    const contentType = (res.headers.get('content-type') || '').toLowerCase();
                    let data;
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    } else {
                        // intentar parsear JSON desde texto (por si el servidor devuelve application/text)
                        const txt = await res.text();
                        try {
                            data = JSON.parse(txt);
                        } catch (e) {
                            data = {
                                ok: res.ok,
                                message: txt || (res.ok ? 'ok' : 'Error')
                            };
                        }
                    }
                    if (!res.ok) data.ok = false;
                    return data;
                } catch (err) {
                    console.error('Network/fetch error:', err);
                    return {
                        ok: false,
                        message: 'Error de red: no se pudo comunicar con el servidor.'
                    };
                }
            }

            // Intenta extraer nombre de archivo desde distintas claves de respuesta
            function extractFilename(resp) {
                if (!resp) return null;
                if (resp.data && (resp.data.nombre_imagen || resp.data.filename || resp.data.fileName)) {
                    return resp.data.nombre_imagen || resp.data.filename || resp.data.fileName;
                }
                return resp.nombre_imagen || resp.filename || resp.fileName || null;
            }

            function renumerarFilas() {
                $$('#sortable tr').forEach((tr, idx) => {
                    const idxEl = tr.querySelector('.row-index');
                    if (idxEl) idxEl.textContent = idx + 1;
                });
            }

            async function guardarOrden() {
                const rows = $$('#sortable tr');
                if (!rows.length) return;
                const fd = new FormData();
                rows.forEach(tr => fd.append('orden[]', tr.dataset.id));
                const resp = await postForm(rutas.ordenar, fd);
                if (resp?.ok) showToast('Orden guardado');
                else showToast(resp?.message || 'No se pudo guardar el orden', 'error');
            }

            // ---------- Drag & Drop para ordenar ----------
            let draggedRow = null;

            function makeRowsDraggable() {
                $$('#sortable tr').forEach(tr => {
                    tr.setAttribute('draggable', 'true');
                    tr.addEventListener('dragstart', e => {
                        draggedRow = tr;
                        tr.classList.add('row-ghost');
                        e.dataTransfer.effectAllowed = 'move';
                    });
                    tr.addEventListener('dragend', () => {
                        if (draggedRow) draggedRow.classList.remove('row-ghost');
                        draggedRow = null;
                    });
                    tr.addEventListener('dragover', e => e.preventDefault());
                    tr.addEventListener('drop', async e => {
                        e.preventDefault();
                        const target = tr;
                        const tbody = target.parentNode;
                        if (!draggedRow || draggedRow === target) return;
                        const rows = Array.from(tbody.children);
                        const draggedIndex = rows.indexOf(draggedRow);
                        const targetIndex = rows.indexOf(target);
                        if (draggedIndex < targetIndex) tbody.insertBefore(draggedRow, target.nextSibling);
                        else tbody.insertBefore(draggedRow, target);
                        renumerarFilas();
                        await guardarOrden();
                    });
                });
            }

            // ---------- FileReader helper ----------
            function readFileAsDataURL(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onerror = () => reject(new Error('Error leyendo archivo'));
                    reader.onload = () => resolve(reader.result);
                    reader.readAsDataURL(file);
                });
            }

            // ---------- Handlers por fila ----------
            function attachRowHandlers(tr) {
                if (!tr) return;

                // Toggle activo
                const switchEl = tr.querySelector('.switch-activo');
                if (switchEl) {
                    switchEl.addEventListener('change', async (e) => {
                        const id = tr.dataset.id;
                        const checked = e.target.checked;
                        const resp = await postForm(`${rutas.toggle}/${id}`, new FormData());
                        if (!resp?.ok) {
                            e.target.checked = !checked;
                            showToast(resp?.message || 'No se pudo cambiar el estado', 'error');
                        } else {
                            showToast(checked ? 'Banner activado' : 'Banner desactivado');
                        }
                    });
                }

                // Eliminar
                const btnEliminar = tr.querySelector('.btn-eliminar');
                if (btnEliminar) {
                    btnEliminar.addEventListener('click', async () => {
                        const id = tr.dataset.id;
                        if (!confirm('¿Eliminar banner?')) return;
                        const resp = await postForm(`${rutas.eliminar}/${id}`, new FormData());
                        if (resp?.ok) {
                            tr.remove();
                            renumerarFilas();
                            showToast('Banner eliminado');
                        } else {
                            showToast(resp?.message || 'No se pudo eliminar', 'error');
                        }
                    });
                }

                // Reemplazar imagen
                const btnReemplazar = tr.querySelector('.btn-reemplazar');
                const fileInput = tr.querySelector('.file-reemplazo');
                const img = tr.querySelector('img');

                const openPicker = () => fileInput?.click();
                btnReemplazar?.addEventListener('click', openPicker);

                // listener de cambio de archivo
                fileInput?.addEventListener('change', async (e) => {
                    const file = e.target.files?.[0];
                    if (!file) return;

                    // Preview inmediato (data URL)
                    try {
                        const dataUrl = await readFileAsDataURL(file);
                        safeSetImageSrc(img, dataUrl);
                    } catch (err) {
                        showToast('No se pudo previsualizar la imagen', 'error');
                        console.error(err);
                        e.target.value = '';
                        return;
                    }

                    // Enviar al backend: incluimos id en FormData como fallback
                    const id = tr.dataset.id || fileInput.dataset.id || null;
                    if (!id) {
                        console.error('ID no encontrado en la fila para reemplazo', tr);
                        showToast('ID del banner no encontrado', 'error');
                        e.target.value = '';
                        return;
                    }

                    const fd = new FormData();
                    fd.append('imagen', file);
                    fd.append('id', id); // IMPORTANTE: fallback por si la ruta no pasa id

                    // Lock UI opcional: desactivar input mientras sube
                    fileInput.disabled = true;
                    btnReemplazar.disabled = true;

                    console.log('Subiendo reemplazo de imagen', {
                        url: `${rutas.actualizar}/${id}`,
                        id,
                        fileName: file.name
                    });

                    const resp = await postForm(rutas.actualizar, fd);

                    console.log('Respuesta del servidor (reemplazo):', resp);

                    // Re-enable UI
                    fileInput.disabled = false;
                    btnReemplazar.disabled = false;

                    if (!resp?.ok) {
                        showToast(resp?.message || 'No se pudo actualizar la imagen', 'error');
                        console.error('Upload failed response:', resp);
                        e.target.value = '';
                        return;
                    }

                    const newName = extractFilename(resp);
                    if (newName) {
                        const serverUrl = '<?= url('') ?>' + baseUploadUrl + newName;
                        // Intentamos confirmar que la URL esté disponible antes de usarla (evita 404 visual)
                        const tester = new Image();
                        tester.onload = () => {
                            safeSetImageSrc(img, serverUrl);
                            showToast('Imagen actualizada');
                        };
                        tester.onerror = () => {
                            console.warn('Server image not yet accessible:', serverUrl);
                            // Mantenemos la preview data: hasta que el servidor sirva el archivo
                            showToast('Imagen subida pero aún no disponible. Si no la ves, refresca la página.', 'error');
                        };
                        // forzamos cache-buster en la petición de prueba
                        tester.src = serverUrl + (serverUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
                    } else {
                        showToast('Imagen subida correctamente (sin nombre devuelto). Si no aparece, refresca.', 'success');
                    }

                    // permitir volver a seleccionar el mismo archivo
                    e.target.value = '';
                });
            }

            function attachAllRowHandlers() {
                $$('#sortable tr').forEach(attachRowHandlers);
            }

            // ---------- Crear nuevo banner (panel derecho) ----------
            let nuevoArchivo = null;
            const dropzone = $('#dropzone');
            const inputNuevoFile = $('#input-nuevo-file');
            const previewNuevo = $('#preview-nuevo');
            const nuevoActivo = $('#nuevo-activo');
            const btnGuardarNuevo = $('#btn-guardar-nuevo');

            dropzone?.addEventListener('click', () => inputNuevoFile.click());
            dropzone?.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
            dropzone?.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                const file = e.dataTransfer.files?.[0];
                if (file) setNuevoArchivo(file);
            });
            inputNuevoFile?.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                if (file) setNuevoArchivo(file);
            });

            function setNuevoArchivo(file) {
                nuevoArchivo = file;
                previewNuevo.innerHTML = '';
                const img = document.createElement('img');
                readFileAsDataURL(file).then(dataUrl => {
                    safeSetImageSrc(img, dataUrl);
                    previewNuevo.appendChild(img);
                }).catch(err => {
                    showToast('No se pudo previsualizar la imagen', 'error');
                    console.error(err);
                });
            }

            btnGuardarNuevo?.addEventListener('click', async () => {
                if (!nuevoArchivo) {
                    showToast('Selecciona una imagen para continuar', 'error');
                    return;
                }

                // Construir FormData
                const fd = new FormData();
                fd.append('imagen', nuevoArchivo);
                const nextOrden = $$('#sortable tr').length + 1;
                fd.append('orden', String(nextOrden));
                if (nuevoActivo.checked) fd.append('activo', '1');

                // UX: bloquear botón
                btnGuardarNuevo.disabled = true;

                const resp = await postForm(rutas.guardar, fd);

                btnGuardarNuevo.disabled = false;

                if (resp?.ok) {
                    const b = resp.data || {};
                    const id = b.id || (resp.id ?? null);
                    const nombre = extractFilename(resp) || b.nombre_imagen || null;

                    // Crear fila nueva en tabla
                    const tr = document.createElement('tr');
                    if (id) tr.dataset.id = id;
                    tr.innerHTML = `
                <td class="drag-handle" title="Arrastrar">☰</td>
                <td class="row-index"></td>
                <td>
                    <img alt="banner" style="max-width:260px; max-height:120px; object-fit:cover; border-radius:8px;">
                </td>
                <td>
                    <label class="switch" title="Activar/Desactivar">
                        <input type="checkbox" class="switch-activo" ${ (b.activo || resp.activo || nuevoActivo.checked) ? 'checked' : '' }>
                        <span class="slider"></span>
                    </label>
                </td>
                <td>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <button class="btn-xs btn-primary-xs btn-reemplazar" ${ id ? `data-id="${id}"` : '' }>Reemplazar imagen</button>
                        <input type="file" class="hidden file-reemplazo" ${ id ? `data-id="${id}"` : '' } accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
                        <button class="btn-xs btn-danger-xs btn-eliminar" ${ id ? `data-id="${id}"` : '' }>Eliminar</button>
                    </div>
                </td>
            `;
                    const imgEl = tr.querySelector('img');

                    if (nombre) {
                        // Asignar URL del servidor (safe)
                        safeSetImageSrc(imgEl, '<?= url('') ?>' + baseUploadUrl + nombre);
                    } else {
                        // Preview local (data URL)
                        try {
                            const dataUrl = await readFileAsDataURL(nuevoArchivo);
                            safeSetImageSrc(imgEl, dataUrl);
                        } catch (err) {
                            console.error('Preview local falló:', err);
                        }
                    }

                    // Agregar fila, attach handlers y renumerar
                    $('#sortable').appendChild(tr);
                    attachRowHandlers(tr);
                    makeRowsDraggable();
                    renumerarFilas();

                    // Reset UI nuevo
                    nuevoArchivo = null;
                    previewNuevo.innerHTML = '';
                    inputNuevoFile.value = '';
                    nuevoActivo.checked = true;

                    showToast('Banner creado');
                } else {
                    showToast(resp?.message || 'No se pudo guardar el banner', 'error');
                }
            });

            // ---------- Init ----------
            attachAllRowHandlers();
            makeRowsDraggable();
            renumerarFilas();
        })();
    </script>


</body>