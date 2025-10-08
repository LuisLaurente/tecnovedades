<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/promocion.css') ?>">

<body>
    <div class="flex h-screen">
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <div class="promociones-page flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="admin-container">
                        <div class="dashboard-header">
                            <h1 class="dashboard-title">Crear Nueva Promoción</h1>
                            <p class="dashboard-subtitle">Configura una nueva promoción, descuento u oferta especial.</p>
                        </div>

                        <!-- Mensajes de error -->
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-error">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-container">
                            <form method="POST" action="<?= url('promocion/guardar') ?>" id="promocionForm">
                                <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
                                <div class="form-section">
                                    <h3 class="section-title">1. Información General</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="nombre" class="form-label">Nombre de la Promoción *</label>
                                            <input type="text" id="nombre" name="nombre" class="form-input" required
                                                placeholder="Ej: 20% de descuento en compras mayores a S/100">
                                        </div>
                                        <div class="form-group">
                                            <label for="prioridad" class="form-label">Prioridad</label>
                                            <select id="prioridad" name="prioridad" class="form-select">
                                                <option value="1">1 - Muy Alta</option>
                                                <option value="2">2 - Alta</option>
                                                <option value="3" selected>3 - Media</option>
                                                <option value="4">4 - Baja</option>
                                                <option value="5">5 - Muy Baja</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-input" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-input" required>
                                        </div>
                                    </div>
                                    <div class="form-grid-small">
                                        <div class="form-checkbox">
                                            <input type="checkbox" id="activo" name="activo" checked>
                                            <label for="activo">Promoción activa</label>
                                        </div>
                                        <div class="form-checkbox">
                                            <input type="checkbox" id="acumulable" name="acumulable" checked>
                                            <label for="acumulable">Acumulable con otras promociones</label>
                                        </div>
                                        <div class="form-checkbox">
                                            <input type="checkbox" id="exclusivo" name="exclusivo">
                                            <label for="exclusivo">Promoción exclusiva (no se combina)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECCIÓN 2: TIPO DE REGLA Y CONFIGURACIÓN -->
                                <div class="form-section">
                                    <h3 class="section-title">2. Regla de Promoción</h3>
                                    <div class="form-group">
                                        <label for="tipo_regla" class="form-label">Tipo de Regla *</label>
                                        <select id="tipo_regla" name="tipo_regla" class="form-select" required>
                                            <option value="">-- Selecciona una regla --</option>
                                            <option value="descuento_subtotal">Descuento % por monto mínimo</option>
                                            <option value="descuento_fijo_subtotal">Descuento fijo por monto mínimo</option>
                                            <option value="envio_gratis_primera_compra">Envío gratis primera compra</option>
                                            <option value="nxm_producto">Lleva N paga M (mismo producto)</option>
                                            <option value="descuento_enesima_unidad">Descuento en N-ésima unidad</option>
                                            <option value="descuento_menor_valor_categoria">Descuento producto más barato por categorías</option>
                                            <option value="nxm_general">Lleva N paga M (productos mixtos)</option>
                                            <option value="descuento_enesimo_producto">Descuento en N-ésimo producto más barato</option>
                                            <option value="envio_gratis_general">Envío gratis general</option>
                                            <option value="envio_gratis_monto_minimo">Envío gratis por monto mínimo</option>
                                        </select>


                                    </div>
                                    <div id="campos_dinamicos" class="dynamic-fields"></div>
                                </div>

                                <div class="form-buttons">
                                    <button type="submit" class="btn-submit">💾 Crear Promoción</button>
                                    <a href="<?= url('promocion/index') ?>" class="btn-cancel">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- CONFIGURACIÓN INICIAL ---
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');

        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayStr = `${yyyy}-${mm}-${dd}`;

        fechaInicio.setAttribute('min', todayStr);
        fechaFin.setAttribute('min', todayStr);

        fechaInicio.addEventListener('change', () => {
            fechaFin.min = fechaInicio.value;
            if (fechaFin.value < fechaInicio.value) fechaFin.value = fechaInicio.value;
        });

        const exclusivoCheckbox = document.getElementById('exclusivo');
        const acumulableCheckbox = document.getElementById('acumulable');

        exclusivoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                acumulableCheckbox.checked = false;
                acumulableCheckbox.disabled = true;
            } else {
                acumulableCheckbox.disabled = false;
            }
        });

        // --- LÓGICA DE FORMULARIO DINÁMICO ---
        const tipoReglaSelect = document.getElementById('tipo_regla');
        const camposDinamicosContainer = document.getElementById('campos_dinamicos');
        const form = document.getElementById('promocionForm');

        tipoReglaSelect.addEventListener('change', manejarCambioDeRegla);

        // Debounce util
        function debounce(fn, wait = 250) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), wait);
            };
        }

        // HTML-escape helper
        function escapeHtml(s) {
            if (!s) return '';
            return String(s).replace(/[&<>"']/g, function(m) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m];
            });
        }

        function manejarCambioDeRegla() {
            const regla = tipoReglaSelect.value;
            camposDinamicosContainer.innerHTML = '';

            const tipoCondicionInput = document.createElement('input');
            tipoCondicionInput.type = 'hidden';
            tipoCondicionInput.name = 'tipo_condicion';

            const tipoAccionInput = document.createElement('input');
            tipoAccionInput.type = 'hidden';
            tipoAccionInput.name = 'tipo_accion';

            switch (regla) {
                case 'descuento_subtotal':
                    tipoCondicionInput.value = 'subtotal_minimo';
                    tipoAccionInput.value = 'descuento_porcentaje';
                    camposDinamicosContainer.innerHTML = `
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Monto mínimo del carrito (S/)</label>
                                <input type="number" name="cond_subtotal_minimo" class="form-input" min="0" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Porcentaje de descuento (%)</label>
                                <input type="number" name="accion_valor_descuento" class="form-input" min="0" max="100" step="0.01" required>
                            </div>
                        </div>`;
                    break;

                case 'envio_gratis_primera_compra':
                    tipoCondicionInput.value = 'primera_compra';
                    tipoAccionInput.value = 'envio_gratis';
                    camposDinamicosContainer.innerHTML = `<p class="info-text">Esta regla no necesita configuración adicional.</p>`;
                    break;

                case 'nxm_producto':
                // Mantener coherencia con el backend
                tipoCondicionInput.value = 'cantidad_producto_identico';
                tipoAccionInput.value = 'compra_n_paga_m';

                camposDinamicosContainer.innerHTML = `
                    <p class="info-text">Aplica para un producto específico. Ej: Lleva 3, Paga 2.</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Buscar Producto</label>
                            <input type="text" id="buscarProductoNXM" class="form-input" placeholder="Escribe el nombre del producto..." autocomplete="off">
                            <ul id="listaProductosNXM" class="autocomplete-list border rounded mt-1 bg-white shadow-md max-h-40 overflow-y-auto hidden"></ul>
                            <input type="hidden" name="cond_producto_id" id="productoIdSeleccionadoNXM" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cantidad que lleva (N)</label>
                            <input type="number" name="accion_cantidad_lleva" class="form-input" min="2" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cantidad que paga (M)</label>
                            <input type="number" name="accion_cantidad_paga" class="form-input" min="1" required>
                        </div>
                    </div>
    `;

    // --- LÓGICA DEL AUTOCOMPLETE (modelo del caso 'descuento_enesima_unidad') ---
    (function initAutocompleteForNxM() {
        const inputBuscar = camposDinamicosContainer.querySelector('#buscarProductoNXM');
        const lista = camposDinamicosContainer.querySelector('#listaProductosNXM');
        const inputHidden = camposDinamicosContainer.querySelector('#productoIdSeleccionadoNXM');

        if (!inputBuscar || !lista || !inputHidden) return;

        let items = [];
        let focusedIndex = -1;

        // Si el usuario escribe, borrar selección previa y buscar
        inputBuscar.addEventListener('input', () => {
            inputHidden.value = '';
            focusedIndex = -1;
            doSearch(inputBuscar.value);
        });

        // Debounced search (reusa la función debounce que ya definiste)
        const doSearch = debounce(async (q) => {
            lista.innerHTML = '';
            lista.classList.add('hidden');
            if (!q || q.trim().length < 2) return;

            try {
                const res = await fetch('<?= htmlspecialchars(url("producto/autocomplete")) ?>?q=' + encodeURIComponent(q.trim()));
                if (!res.ok) throw new Error('Network response was not OK');
                const productos = await res.json();
                items = productos || [];
                renderList(items);
            } catch (err) {
                console.error('Autocomplete NXM error:', err);
                lista.innerHTML = '';
                lista.classList.add('hidden');
            }
        }, 220);

        function renderList(productos) {
            lista.innerHTML = '';
            focusedIndex = -1;
            if (!productos || productos.length === 0) {
                lista.classList.add('hidden');
                return;
            }
            productos.forEach((p, idx) => {
                const li = document.createElement('li');
                li.tabIndex = 0;
                li.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                li.dataset.id = p.id;
                li.dataset.idx = idx;
                // mostrar nombre y sku si existe (usa escapeHtml definida arriba)
                li.innerHTML = `<strong>${escapeHtml(p.nombre)}</strong>${p.sku ? ' <small>(' + escapeHtml(p.sku) + ')</small>' : ''}`;
                li.addEventListener('click', () => selectProduct(p));
                li.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') selectProduct(p);
                });
                lista.appendChild(li);
            });
            lista.classList.remove('hidden');
        }

        function selectProduct(p) {
            inputBuscar.value = p.nombre;
            inputHidden.value = p.id;
            lista.innerHTML = '';
            lista.classList.add('hidden');
        }

        // Navegación por teclado en el input
        inputBuscar.addEventListener('keydown', function(e) {
            const lis = lista.querySelectorAll('li');
            if (!lis.length) return;
            if (e.key === 'ArrowDown') {
                focusedIndex = Math.min(focusedIndex + 1, lis.length - 1);
                lis[focusedIndex].focus();
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                focusedIndex = Math.max(focusedIndex - 1, 0);
                lis[focusedIndex].focus();
                e.preventDefault();
            } else if (e.key === 'Enter') {
                if (focusedIndex >= 0 && lis[focusedIndex]) {
                    lis[focusedIndex].click();
                    e.preventDefault();
                }
            }
        });

        // Hide lists when clicking outside (registered once)
        if (!window._promocion_autocomplete_click) {
            document.addEventListener('click', function(ev) {
                document.querySelectorAll('.autocomplete-list').forEach(ul => {
                    if (!ul.contains(ev.target) && !ul.previousElementSibling?.contains(ev.target)) {
                        ul.classList.add('hidden');
                    }
                });
            });
            window._promocion_autocomplete_click = true;
        }
    })();
    break;


                case 'descuento_enesima_unidad':
                    // --- IMPORTANT: nombres de campos alineados con el controlador ---
                    // cond_producto_id (hidden) -> requerido por construirCondicion()
                    // accion_numero_unidad  -> usado por construirAccion()
                    // accion_descuento_unidad -> usado por construirAccion()
                    // accion_cantidad_lleva (hidden) -> se pone igual a accion_numero_unidad para evitar la excepción en construirCondicion()
                    tipoCondicionInput.value = 'cantidad_producto_identico';
                    tipoAccionInput.value = 'descuento_enesima_unidad';

                    camposDinamicosContainer.innerHTML = `
                        <p class="info-text">Aplica un descuento a una unidad específica. Ej: 50% en la 3ra unidad.</p>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Buscar Producto</label>
                                <input type="text" id="buscarProducto" class="form-input" placeholder="Escribe el nombre del producto..." autocomplete="off">
                                <ul id="listaProductos" class="autocomplete-list border rounded mt-1 bg-white shadow-md max-h-40 overflow-y-auto hidden"></ul>
                                <input type="hidden" name="cond_producto_id" id="productoIdSeleccionado" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">N (unidad a descontar)</label>
                                <input type="number" id="inputNumeroUnidad" name="accion_numero_unidad" class="form-input" min="2" required>
                                <input type="hidden" name="accion_cantidad_lleva" id="accionCantidadLlevaHidden">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Porcentaje de descuento (%)</label>
                                <input type="number" id="inputDescuentoUnidad" name="accion_descuento_unidad" class="form-input" min="1" max="100" step="0.01" required>
                            </div>
                        </div>`;

                    // --- LÓGICA DEL AUTOCOMPLETE (solo ahora que los elementos existen) ---
                    (function initAutocompleteForDescuentoEnesima() {
                        const inputBuscar = camposDinamicosContainer.querySelector('#buscarProducto');
                        const lista = camposDinamicosContainer.querySelector('#listaProductos');
                        const inputHidden = camposDinamicosContainer.querySelector('#productoIdSeleccionado');
                        const inputNumeroUnidad = camposDinamicosContainer.querySelector('#inputNumeroUnidad');
                        const accionCantidadHidden = camposDinamicosContainer.querySelector('#accionCantidadLlevaHidden');

                        let items = [];
                        let focusedIndex = -1;

                        // Clear selected product if user types (so we don't send stale id)
                        inputBuscar.addEventListener('input', () => {
                            inputHidden.value = '';
                            focusedIndex = -1;
                            doSearch(inputBuscar.value);
                        });

                        // Debounced search
                        const doSearch = debounce(async (q) => {
                            lista.innerHTML = '';
                            lista.classList.add('hidden');
                            if (!q || q.trim().length < 2) return;
                            try {
                                const res = await fetch('<?= htmlspecialchars(url("producto/autocomplete")) ?>?q=' + encodeURIComponent(q.trim()));
                                if (!res.ok) throw new Error('Network response was not OK');
                                const productos = await res.json();
                                items = productos || [];
                                renderList(items);
                            } catch (err) {
                                console.error('Autocomplete error:', err);
                                lista.innerHTML = '';
                                lista.classList.add('hidden');
                            }
                        }, 220);

                        function renderList(productos) {
                            lista.innerHTML = '';
                            focusedIndex = -1;
                            if (!productos || productos.length === 0) {
                                lista.classList.add('hidden');
                                return;
                            }
                            productos.forEach((p, idx) => {
                                const li = document.createElement('li');
                                li.tabIndex = 0;
                                li.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                                li.dataset.id = p.id;
                                li.dataset.idx = idx;
                                // mostrar nombre y sku si existe (escape)
                                li.innerHTML = `<strong>${escapeHtml(p.nombre)}</strong>${p.sku ? ' <small>(' + escapeHtml(p.sku) + ')</small>' : ''}`;
                                li.addEventListener('click', () => selectProduct(p));
                                li.addEventListener('keydown', (ev) => {
                                    if (ev.key === 'Enter') selectProduct(p);
                                });
                                lista.appendChild(li);
                            });
                            lista.classList.remove('hidden');
                        }

                        function selectProduct(p) {
                            inputBuscar.value = p.nombre;
                            inputHidden.value = p.id;
                            lista.innerHTML = '';
                            lista.classList.add('hidden');
                        }

                        // keyboard navigation
                        inputBuscar.addEventListener('keydown', function(e) {
                            const lis = lista.querySelectorAll('li');
                            if (!lis.length) return;
                            if (e.key === 'ArrowDown') {
                                focusedIndex = Math.min(focusedIndex + 1, lis.length - 1);
                                lis[focusedIndex].focus();
                                e.preventDefault();
                            } else if (e.key === 'ArrowUp') {
                                focusedIndex = Math.max(focusedIndex - 1, 0);
                                lis[focusedIndex].focus();
                                e.preventDefault();
                            } else if (e.key === 'Enter') {
                                if (focusedIndex >= 0 && lis[focusedIndex]) {
                                    lis[focusedIndex].click();
                                    e.preventDefault();
                                }
                            }
                        });

                        // Hide lists when clicking outside (registered once)
                        if (!window._promocion_autocomplete_click) {
                            document.addEventListener('click', function(ev) {
                                document.querySelectorAll('.autocomplete-list').forEach(ul => {
                                    if (!ul.contains(ev.target) && !ul.previousElementSibling?.contains(ev.target)) {
                                        ul.classList.add('hidden');
                                    }
                                });
                            });
                            window._promocion_autocomplete_click = true;
                        }

                        // Sincronizar el valor usado por construirCondicion()
                        inputNumeroUnidad.addEventListener('input', function() {
                            const v = parseInt(this.value) || '';
                            accionCantidadHidden.value = v;
                        });
                    })();
                    break;

                case 'descuento_menor_valor_categoria':
    tipoCondicionInput.value = 'cantidad_producto_categoria';
    tipoAccionInput.value = 'descuento_menor_valor';
    camposDinamicosContainer.innerHTML = `
        <p class="info-text">Aplica un descuento al producto más barato dentro de una categoría seleccionada.</p>
        <div class="form-grid">
            <div class="form-group relative">
                <label class="form-label">Categoría</label>
                <input type="text" id="categoria-buscador" class="form-input" placeholder="Buscar categoría...">
                <input type="hidden" name="cond_categoria_id" id="categoria-id-seleccionada">
                <ul id="categoria-sugerencias" class="absolute z-50 bg-white border border-gray-300 rounded-md shadow-md w-full hidden max-h-48 overflow-y-auto"></ul>
            </div>
            <div class="form-group">
                <label class="form-label">Cantidad mínima de productos</label>
                <input type="number" name="cond_cantidad_min_categoria" class="form-input" min="2" required>
            </div>
            <div class="form-group">
                <label class="form-label">Porcentaje de descuento (%)</label>
                <input type="number" name="accion_descuento_menor_valor" class="form-input" min="0" max="100" step="0.01" required>
            </div>
        </div>`;

    // --- Buscador dinámico de categorías (seguro y aislado) ---
    (() => {
        const buscador = document.querySelector('#categoria-buscador');
        const lista = document.querySelector('#categoria-sugerencias');
        const hiddenInput = document.querySelector('#categoria-id-seleccionada');
        let timeout = null;
        let controller = null;

        buscador?.addEventListener('input', () => {
            const termino = buscador.value.trim();
            lista.innerHTML = '';
            lista.classList.add('hidden');
            hiddenInput.value = '';

            if (termino.length < 2) return;

            clearTimeout(timeout);
            timeout = setTimeout(async () => {
                if (controller) controller.abort();
                controller = new AbortController();

                try {
                    const response = await fetch(`<?= url('categoria/buscarPorNombre') ?>?q=${encodeURIComponent(termino)}`, {
                        signal: controller.signal
                    });
                    if (!response.ok) throw new Error('Error HTTP');
                    const data = await response.json();

                    if (Array.isArray(data) && data.length > 0) {
                        lista.innerHTML = '';
                        data.forEach(cat => {
                            const li = document.createElement('li');
                            li.textContent = cat.nombre;
                            li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
                            li.addEventListener('click', () => {
                                buscador.value = cat.nombre;
                                hiddenInput.value = cat.id;
                                lista.innerHTML = '';
                                lista.classList.add('hidden');
                            });
                            lista.appendChild(li);
                        });
                        lista.classList.remove('hidden');
                    }
                } catch (e) {
                    console.error('Error en búsqueda de categorías', e);
                }
            }, 300);
        });

        // Ocultar sugerencias al hacer clic fuera
        document.addEventListener('click', e => {
            if (!buscador.contains(e.target) && !lista.contains(e.target)) {
                lista.innerHTML = '';
                lista.classList.add('hidden');
            }
        });
    })();
    break;


                case 'nxm_general':
                    tipoCondicionInput.value = 'cantidad_total_productos';
                    tipoAccionInput.value = 'compra_n_paga_m_general';
                    camposDinamicosContainer.innerHTML = `
                        <p class="info-text">Aplica para cualquier combinación de productos. Ej: Lleva 3, Paga 2 (el de menor valor gratis).</p>
                        <div class="form-grid">
                            <div class="form-group"><label class="form-label">Cantidad mínima de productos</label><input type="number" name="cond_cantidad_total" class="form-input" min="2" required></div>
                            <div class="form-group"><label class="form-label">Cantidad que lleva (N)</label><input type="number" name="accion_cantidad_lleva_general" class="form-input" min="2" required></div>
                            <div class="form-group"><label class="form-label">Cantidad que paga (M)</label><input type="number" name="accion_cantidad_paga_general" class="form-input" min="1" required></div>
                        </div>`;
                    break;

                case 'descuento_enesimo_producto':
                    tipoCondicionInput.value = 'cantidad_total_productos';
                    tipoAccionInput.value = 'descuento_producto_mas_barato';
                    camposDinamicosContainer.innerHTML = `
                        <p class="info-text">Aplica un descuento al producto más barato al llevar una cantidad mínima de productos.</p>
                        <div class="form-grid">
                            <div class="form-group"><label class="form-label">Cantidad mínima de productos</label><input type="number" name="cond_cantidad_total" class="form-input" min="2" required></div>
                            <div class="form-group"><label class="form-label">Porcentaje de descuento (%)</label><input type="number" name="accion_descuento_porcentaje" class="form-input" min="0" max="100" step="0.01" required></div>
                        </div>`;
                    break;

                case 'envio_gratis_general':
                    tipoCondicionInput.value = 'todos';
                    tipoAccionInput.value = 'envio_gratis';
                    camposDinamicosContainer.innerHTML = `<p class="info-text">Envío gratis aplica a todos los pedidos sin condiciones.</p>`;
                    break;

                case 'envio_gratis_monto_minimo':
                    tipoCondicionInput.value = 'subtotal_minimo';
                    tipoAccionInput.value = 'envio_gratis';
                    camposDinamicosContainer.innerHTML = `
                        <div class="form-group">
                            <label class="form-label">Monto mínimo del carrito (S/)</label>
                            <input type="number" name="cond_subtotal_minimo" class="form-input" required min="0" step="0.01">
                        </div>`;
                    break;

                case 'descuento_fijo_subtotal':
                    tipoCondicionInput.value = 'subtotal_minimo';
                    tipoAccionInput.value = 'descuento_fijo';
                    camposDinamicosContainer.innerHTML = `
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Monto mínimo del carrito (S/)</label>
                                <input type="number" name="cond_subtotal_minimo" class="form-input" min="0" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Monto de descuento fijo (S/)</label>
                                <input type="number" name="accion_valor_descuento_fijo" class="form-input" min="0" step="0.01" required>
                            </div>
                        </div>`;
                    break;
            }

            if (regla) {
                camposDinamicosContainer.prepend(tipoCondicionInput);
                camposDinamicosContainer.prepend(tipoAccionInput);
            }
        }

        // Validación global del formulario antes del submit
        form.addEventListener('submit', function(e) {
            const tipoRegla = tipoReglaSelect.value;
            if (!tipoRegla) {
                e.preventDefault();
                alert('Por favor, selecciona un tipo de regla para la promoción.');
                return false;
            }

            // Para reglas que requieren producto específico, asegurarnos que se haya seleccionado de la lista
            if (tipoRegla === 'descuento_enesima_unidad' || tipoRegla === 'nxm_producto') {
                // Buscar hidden cond_producto_id dentro del contenedor dinámico
                const prodHidden = camposDinamicosContainer.querySelector('input[name="cond_producto_id"]');
                // La cantidad mínima que el controlador espera (puede venir como accion_cantidad_lleva o accion_numero_unidad)
                const cantidad = camposDinamicosContainer.querySelector('input[name="accion_cantidad_lleva"]')?.value
                            || camposDinamicosContainer.querySelector('input[name="accion_numero_unidad"]')?.value
                            || '';

                if (!prodHidden || !prodHidden.value || parseInt(prodHidden.value) <= 0 || !cantidad || parseInt(cantidad) <= 0) {
                    e.preventDefault();
                    alert('Por favor selecciona un producto válido de la lista y completa la cantidad requerida.');
                    return false;
                }
            }
        });
    });
    </script>


</body>

</html>