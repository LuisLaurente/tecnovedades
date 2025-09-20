<?php include_once __DIR__ . 
'/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/bannerAdmi.css') ?>">

<body>
    <?php include_once __DIR__ . 
'/../admin/includes/header.php'; ?>

    <?php $baseUploadUrl = rtrim($uploadDirUrl ?? 'uploads/banners/', '/') . '/'; ?>

    <style>
        /* Puedes añadir o modificar estilos aquí si es necesario */
        .section-divider {
            border-top: 2px solid #e0e0e0;
            margin: 40px 0;
            padding-top: 20px;
        }
        .management-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            align-items: start;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            padding: 20px;
        }
        .dropzone {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color .3s ease;
        }
        .dropzone:hover {
            border-color: #007bff;
        }
        .preview-nuevo img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 15px;
            border-radius: 8px;
        }
        .admin-productos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .admin-productos-table th, .admin-productos-table td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        .admin-productos-table th {
            background-color: #f8f8f8;
        }
        .admin-productos-table img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
        }
        .drag-handle {
            cursor: grab;
            font-size: 1.2em;
            text-align: center;
        }
        .row-ghost {
            opacity: 0.5;
            background-color: #f0f0f0;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
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
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 20px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }
        input:checked + .slider:before {
            -webkit-transform: translateX(20px);
            -ms-transform: translateX(20px);
            transform: translateX(20px);
        }
        .btn-xs {
            padding: 4px 8px;
            font-size: 0.8em;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            margin-right: 5px;
        }
        .btn-primary-xs {
            background-color: #007bff;
            color: white;
        }
        .btn-danger-xs {
            background-color: #dc3545;
            color: white;
        }
        .btn-guardar-nuevo {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            margin-top: 15px;
        }
        #toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .toast.success {
            background-color: #28a745;
        }
        .toast.error {
            background-color: #dc3545;
        }
        .toast.show {
            opacity: 1;
        }
    </style>

    <div class="container">
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <!-- ======================================= -->
        <!-- GESTIÓN DE BANNERS PRINCIPALES (CARRUSEL) -->
        <!-- ======================================= -->
        <div id="banner-app-principal" class="banner-manager" data-tipo="principal"
            data-ruta-ordenar="<?= url('banner/ordenar') ?>"
            data-ruta-toggle="<?= url('banner/toggle') ?>"
            data-ruta-eliminar="<?= url('banner/eliminar') ?>"
            data-ruta-guardar="<?= url('banner/guardar') ?>"
            data-ruta-actualizar="<?= url('banner/actualizar-imagen') ?>"
            data-base-upload-url="<?= htmlspecialchars($baseUploadUrl, ENT_QUOTES) ?>">
            
            <div class="management-grid">
                <!-- IZQUIERDA: LISTA -->
                <div>
                    <h1>Banners Principales (Carrusel)</h1>
                    <p style="margin:8px 0 16px; color:#666;">Arrastra para cambiar el orden. Las imágenes deben ser de 1024 x 425 píxeles.</p>
                    
                    <table class="admin-productos-table">
                        <thead>
                            <tr>
                                <th style="width:34px;"></th><th>#</th><th>Imagen</th><th style="width:120px;">Activo</th><th style="width:220px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-list">
                            <?php if (empty($banners_principales)): ?>
                                <tr><td colspan="5" style="color:#777;">No hay banners principales.</td></tr>
                            <?php else: ?>
                                <?php foreach ($banners_principales as $i => $b): ?>
                                    <tr data-id="<?= (int)$b['id'] ?>">
                                        <td class="drag-handle" title="Arrastrar">☰</td>
                                        <td class="row-index"><?= $i + 1 ?></td>
                                        <td><img src="<?= url($baseUploadUrl . $b['nombre_imagen']) ?>" alt="banner" style="max-width:260px; max-height:120px; object-fit:cover; border-radius:8px;"></td>
                                        <td>
                                            <label class="switch"><input type="checkbox" class="switch-activo" <?= !empty($b['activo']) ? 'checked' : '' ?>><span class="slider"></span></label>
                                        </td>
                                        <td>
                                            <button class="btn-xs btn-primary-xs btn-reemplazar">Reemplazar</button>
                                            <input type="file" class="file-reemplazo" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
                                            <button class="btn-xs btn-danger-xs btn-eliminar">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- DERECHA: NUEVO -->
                <div class="card">
                    <h2>Nuevo Banner Principal</h2>
                    <p style="margin-top:-6px; color:#666;">Formatos: JPG, PNG, WEBP, GIF.</p>
                    <div class="dropzone"><p>Arrastra o haz clic para subir</p><input type="file" class="input-nuevo-file" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;"></div>
                    <div class="preview-nuevo" aria-live="polite"></div>
                    <div style="margin:14px 0;"><label style="display:flex; align-items:center; gap:10px;"><span>Activo</span><label class="switch"><input type="checkbox" class="nuevo-activo" checked><span class="slider"></span></label></label></div>
                    <button class="btn btn-guardar-nuevo">GUARDAR</button>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- ======================================= -->
        <!-- GESTIÓN DE BANNERS SECUNDARIOS - IZQUIERDA -->
        <!-- ======================================= -->
        <div id="banner-app-secundario-izquierda" class="banner-manager" data-tipo="secundario_izquierda"
            data-ruta-ordenar="<?= url('banner/ordenar') ?>"
            data-ruta-toggle="<?= url('banner/toggle') ?>"
            data-ruta-eliminar="<?= url('banner/eliminar') ?>"
            data-ruta-guardar="<?= url('banner/guardar') ?>"
            data-ruta-actualizar="<?= url('banner/actualizar-imagen') ?>"
            data-base-upload-url="<?= htmlspecialchars($baseUploadUrl, ENT_QUOTES) ?>">
            
            <div class="management-grid">
                <!-- IZQUIERDA: LISTA -->
                <div>
                    <h1>Banner Secundario (Izquierda)</h1>
                    <p style="margin:8px 0 16px; color:#666;">Solo se mostrará el primer banner activo. Dimensiones recomendadas: 1024 x 425 píxeles</p>
                    
                    <table class="admin-productos-table">
                        <thead>
                            <tr>
                                <th style="width:34px;"></th><th>#</th><th>Imagen</th><th style="width:120px;">Activo</th><th style="width:220px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-list">
                            <?php if (empty($banners_secundarios_izquierda)): ?>
                                <tr><td colspan="5" style="color:#777;">No hay banners secundarios izquierdos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($banners_secundarios_izquierda as $i => $b): ?>
                                    <tr data-id="<?= (int)$b['id'] ?>">
                                        <td class="drag-handle" title="Arrastrar">☰</td>
                                        <td><?= $i + 1 ?></td>
                                        <td><img src="<?= url($baseUploadUrl . $b['nombre_imagen']) ?>" alt="banner" style="max-width:260px; max-height:120px; object-fit:cover; border-radius:8px;"></td>
                                        <td>
                                            <label class="switch"><input type="checkbox" class="switch-activo" <?= !empty($b['activo']) ? 'checked' : '' ?>><span class="slider"></span></label>
                                        </td>
                                        <td>
                                            <button class="btn-xs btn-primary-xs btn-reemplazar">Reemplazar</button>
                                            <input type="file" class="file-reemplazo" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
                                            <button class="btn-xs btn-danger-xs btn-eliminar">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- DERECHA: NUEVO -->
                <div class="card">
                    <h2>Nuevo Banner Secundario Izquierdo</h2>
                    <p style="margin-top:-6px; color:#666;">Formatos: JPG, PNG, WEBP, GIF.</p>
                    <div class="dropzone"><p>Arrastra o haz clic para subir</p><input type="file" class="input-nuevo-file" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;"></div>
                    <div class="preview-nuevo" aria-live="polite"></div>
                    <div style="margin:14px 0;"><label style="display:flex; align-items:center; gap:10px;"><span>Activo</span><label class="switch"><input type="checkbox" class="nuevo-activo" checked><span class="slider"></span></label></label></div>
                    <button class="btn btn-guardar-nuevo">GUARDAR</button>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <!-- ======================================= -->
        <!-- GESTIÓN DE BANNERS SECUNDARIOS - DERECHA -->
        <!-- ======================================= -->
        <div id="banner-app-secundario-derecha" class="banner-manager" data-tipo="secundario_derecha"
            data-ruta-ordenar="<?= url('banner/ordenar') ?>"
            data-ruta-toggle="<?= url('banner/toggle') ?>"
            data-ruta-eliminar="<?= url('banner/eliminar') ?>"
            data-ruta-guardar="<?= url('banner/guardar') ?>"
            data-ruta-actualizar="<?= url('banner/actualizar-imagen') ?>"
            data-base-upload-url="<?= htmlspecialchars($baseUploadUrl, ENT_QUOTES) ?>">
            
            <div class="management-grid">
                <!-- IZQUIERDA: LISTA -->
                <div>
                    <h1>Banner Secundario (Derecha)</h1>
                    <p style="margin:8px 0 16px; color:#666;">Solo se mostrará el primer banner activo. Dimensiones recomendadas: 1024 x 425 píxeles</p>
                    
                    <table class="admin-productos-table">
                        <thead>
                            <tr>
                                <th style="width:34px;"></th><th>#</th><th>Imagen</th><th style="width:120px;">Activo</th><th style="width:220px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-list">
                            <?php if (empty($banners_secundarios_derecha)): ?>
                                <tr><td colspan="5" style="color:#777;">No hay banners secundarios derechos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($banners_secundarios_derecha as $i => $b): ?>
                                    <tr data-id="<?= (int)$b['id'] ?>">
                                        <td class="drag-handle" title="Arrastrar">☰</td>
                                        <td><?= $i + 1 ?></td>
                                        <td><img src="<?= url($baseUploadUrl . $b['nombre_imagen']) ?>" alt="banner" style="max-width:260px; max-height:120px; object-fit:cover; border-radius:8px;"></td>
                                        <td>
                                            <label class="switch"><input type="checkbox" class="switch-activo" <?= !empty($b['activo']) ? 'checked' : '' ?>><span class="slider"></span></label>
                                        </td>
                                        <td>
                                            <button class="btn-xs btn-primary-xs btn-reemplazar">Reemplazar</button>
                                            <input type="file" class="file-reemplazo" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
                                            <button class="btn-xs btn-danger-xs btn-eliminar">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- DERECHA: NUEVO -->
                <div class="card">
                    <h2>Nuevo Banner Secundario Derecho</h2>
                    <p style="margin-top:-6px; color:#666;">Formatos: JPG, PNG, WEBP, GIF.</p>
                    <div class="dropzone"><p>Arrastra o haz clic para subir</p><input type="file" class="input-nuevo-file" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;"></div>
                    <div class="preview-nuevo" aria-live="polite"></div>
                    <div style="margin:14px 0;"><label style="display:flex; align-items:center; gap:10px;"><span>Activo</span><label class="switch"><input type="checkbox" class="nuevo-activo" checked><span class="slider"></span></label></label></div>
                    <button class="btn btn-guardar-nuevo">GUARDAR</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <?php include_once __DIR__ . 
'/../admin/includes/footer.php'; ?>

    <script>
    (function() {
        function initBannerManager(containerSelector) {
            const app = document.querySelector(containerSelector);
            if (!app) return;

            const $ = (sel, ctx = app) => ctx.querySelector(sel);
            const $$ = (sel, ctx = app) => Array.from(ctx.querySelectorAll(sel));
            
            const tipoBanner = app.dataset.tipo;
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
                const container = document.querySelector('#toast');
                const box = document.createElement('div');
                box.className = 'toast ' + (type === 'error' ? 'error' : 'success');
                box.textContent = msg;
                container.appendChild(box);
                setTimeout(() => box.remove(), 3500);
            }

            function safeSetImageSrc(img, url) {
                if (!url) return;
                if (url.startsWith('blob:') || url.startsWith('data:')) {
                    img.src = url;
                    return;
                }
                const sep = url.includes('?') ? '&' : '?';
                img.src = url + sep + 'v=' + Date.now();
            }

            async function postForm(url, formData = null) {
                const headers = { 'X-Requested-With': 'XMLHttpRequest' };
                if (csrf) headers['X-CSRF-Token'] = csrf;
                try {
                    const res = await fetch(url, { method: 'POST', headers, body: formData });
                    const txt = await res.text();
                    let data;
                    try { data = JSON.parse(txt); } catch (e) { data = { ok: res.ok, message: txt || (res.ok ? 'ok' : 'Error') }; }
                    if (!res.ok) data.ok = false;
                    return data;
                } catch (err) {
                    return { ok: false, message: 'Error de red.' };
                }
            }

            function extractFilename(resp) {
                return resp?.data?.nombre_imagen || resp?.nombre_imagen || null;
            }

            function renumerarFilas() {
                $$('.sortable-list tr').forEach((tr, idx) => {
                    const idxEl = tr.querySelector('.row-index');
                    if (idxEl) idxEl.textContent = idx + 1;
                });
            }

            async function guardarOrden() {
                const rows = $$('.sortable-list tr');
                if (!rows.length) return;
                const fd = new FormData();
                rows.forEach(tr => fd.append('orden[]', tr.dataset.id));
                const resp = await postForm(rutas.ordenar, fd);
                if (resp?.ok) showToast('Orden guardado');
                else showToast(resp?.message || 'No se pudo guardar el orden', 'error');
            }

            let draggedRow = null;
            function makeRowsDraggable() {
                $$('.sortable-list tr').forEach(tr => {
                    tr.setAttribute('draggable', 'true');
                    tr.addEventListener('dragstart', e => { draggedRow = tr; tr.classList.add('row-ghost'); e.dataTransfer.effectAllowed = 'move'; });
                    tr.addEventListener('dragend', () => { if (draggedRow) draggedRow.classList.remove('row-ghost'); draggedRow = null; });
                    tr.addEventListener('dragover', e => e.preventDefault());
                    tr.addEventListener('drop', async e => {
                        e.preventDefault();
                        const target = e.currentTarget;
                        if (!draggedRow || draggedRow === target) return;
                        const tbody = target.parentNode;
                        const rect = target.getBoundingClientRect();
                        const isAfter = e.clientY > rect.top + rect.height / 2;
                        if (isAfter) tbody.insertBefore(draggedRow, target.nextSibling);
                        else tbody.insertBefore(draggedRow, target);
                        renumerarFilas();
                        await guardarOrden();
                    });
                });
            }

            function readFileAsDataURL(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            // Eventos para la subida de nuevo banner
            const dropzone = $('.dropzone');
            const inputNuevoFile = $('.input-nuevo-file');
            const previewNuevo = $('.preview-nuevo');
            const nuevoActivo = $('.nuevo-activo');
            const btnGuardarNuevo = $('.btn-guardar-nuevo');
            let nuevoArchivo = null;

            dropzone?.addEventListener('click', () => inputNuevoFile.click());
            dropzone?.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('dragover'); });
            dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
            dropzone?.addEventListener('drop', e => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    inputNuevoFile.files = files;
                    inputNuevoFile.dispatchEvent(new Event('change'));
                }
            });

            inputNuevoFile?.addEventListener('change', async e => {
                const file = e.target.files[0];
                if (file) {
                    nuevoArchivo = file;
                    const imgUrl = await readFileAsDataURL(file);
                    previewNuevo.innerHTML = `<img src="${imgUrl}" alt="Previsualización">`;
                } else {
                    nuevoArchivo = null;
                    previewNuevo.innerHTML = '';
                }
            });

            btnGuardarNuevo?.addEventListener('click', async () => {
                if (!nuevoArchivo) {
                    showToast('Por favor, selecciona una imagen.', 'error');
                    return;
                }

                const fd = new FormData();
                fd.append('imagen', nuevoArchivo);
                fd.append('tipo', tipoBanner); // <-- ¡IMPORTANTE! Aquí se añade el tipo de banner
                const nextOrden = $$('.sortable-list tr').length;
                fd.append('orden', String(nextOrden));
                if (nuevoActivo.checked) fd.append('activo', '1');

                const resp = await postForm(rutas.guardar, fd);

                if (resp?.ok) {
                    showToast('Banner guardado exitosamente');
                    const newRow = document.createElement('tr');
                    newRow.dataset.id = resp.data.id;
                    newRow.innerHTML = `
                        <td class="drag-handle" title="Arrastrar">☰</td>
                        <td>${$$('.sortable-list tr').length + 1}</td>
                        <td><img src="${baseUploadUrl}${resp.data.nombre_imagen}" alt="banner" style="max-width:260px; max-height:120px; object-fit:cover; border-radius:8px;"></td>
                        <td>
                            <label class="switch"><input type="checkbox" class="switch-activo" ${resp.data.activo ? 'checked' : ''}><span class="slider"></span></label>
                        </td>
                        <td>
                            <button class="btn-xs btn-primary-xs btn-reemplazar">Reemplazar</button>
                            <input type="file" class="file-reemplazo" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;">
                            <button class="btn-xs btn-danger-xs btn-eliminar">Eliminar</button>
                        </td>
                    `;
                    $('.sortable-list').appendChild(newRow);
                    makeRowsDraggable(); // Re-aplicar drag and drop a la nueva fila
                    attachRowEvents(newRow); // Adjuntar eventos a la nueva fila
                    inputNuevoFile.value = '';
                    previewNuevo.innerHTML = '';
                    nuevoArchivo = null;
                    nuevoActivo.checked = true;
                } else {
                    showToast(resp?.message || 'Error al guardar el banner', 'error');
                }
            });

            // Eventos para filas existentes (toggle, eliminar, reemplazar)
            function attachRowEvents(row) {
                const id = row.dataset.id;
                const switchActivo = row.querySelector('.switch-activo');
                const btnEliminar = row.querySelector('.btn-eliminar');
                const btnReemplazar = row.querySelector('.btn-reemplazar');
                const fileReemplazo = row.querySelector('.file-reemplazo');
                const imgElement = row.querySelector('img');

                switchActivo?.addEventListener('change', async e => {
                    const activo = e.target.checked ? 1 : 0;
                    const fd = new FormData();
                    fd.append('id', id);
                    fd.append('activo', activo);
                    const resp = await postForm(rutas.toggle, fd);
                    if (!resp?.ok) {
                        showToast(resp?.message || 'Error al cambiar estado', 'error');
                        e.target.checked = !e.target.checked; // Revertir si falla
                    }
                });

                btnEliminar?.addEventListener('click', async () => {
                    if (!confirm('¿Estás seguro de eliminar este banner?')) return;
                    const fd = new FormData();
                    fd.append('id', id);
                    const resp = await postForm(rutas.eliminar, fd);
                    if (resp?.ok) {
                        showToast('Banner eliminado');
                        row.remove();
                        renumerarFilas();
                    } else {
                        showToast(resp?.message || 'Error al eliminar banner', 'error');
                    }
                });

                btnReemplazar?.addEventListener('click', () => fileReemplazo.click());
                fileReemplazo?.addEventListener('change', async e => {
                    const file = e.target.files[0];
                    if (!file) return;

                    const fd = new FormData();
                    fd.append('id', id);
                    fd.append('imagen', file);

                    const resp = await postForm(rutas.actualizar, fd);
                    if (resp?.ok) {
                        showToast('Imagen actualizada');
                        safeSetImageSrc(imgElement, baseUploadUrl + extractFilename(resp));
                    } else {
                        showToast(resp?.message || 'Error al actualizar imagen', 'error');
                    }
                });
            }

            // Inicializar eventos para todas las filas existentes al cargar
            $$('.sortable-list tr').forEach(attachRowEvents);
            makeRowsDraggable();
        }

        // Inicializar ambos gestores de banners
        document.addEventListener('DOMContentLoaded', function() {
            initBannerManager('#banner-app-principal');
            initBannerManager('#banner-app-secundario-izquierda');
            initBannerManager('#banner-app-secundario-derecha');
        });
    })();
    </script>


