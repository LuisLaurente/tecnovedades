<?php require_once __DIR__ . '/../../Core/Helpers/urlHelper.php'; ?>
<link rel="stylesheet" href="<?= url('/css/editar-producto.css') ?>">

<div class="form-container">
    <h2>✏️ Editar Producto</h2>

    <form action="<?= url('producto/actualizar') ?>" method="POST" enctype="multipart/form-data"> <input type="hidden" name="id" value="<?= $producto['id'] ?>">

        <!-- Nombre -->
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
        </div>

        <!-- Descripción -->
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
        </div>

        <!-- Especificaciones (texto libre, cada línea -> lista) -->
        <div class="form-row">
            <label for="especificaciones">Especificaciones (una por línea)</label>
            <textarea name="especificaciones" id="especificaciones" rows="5"><?= htmlspecialchars($producto['especificaciones'] ?? '') ?></textarea>
        </div>

        <!-- Productos relacionados (checkboxes con buscador) -->
        <div class="form-row">
            <label for="buscador-productos">🔍 Buscar Productos Relacionados</label>
            <input
                type="text"
                id="buscador-productos"
                placeholder="Escribe para buscar productos..."
                class="buscador">

            <!-- Área de productos seleccionados -->
            <div class="productos-seleccionados-container" id="productos-seleccionados-container"
                style="margin: 10px 0; padding: 10px; background: #e8f5e8; border-radius: 4px; border: 1px solid #c3e6c3; <?= empty($producto['productos_relacionados']) ? 'display: none;' : '' ?>">
                <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #2d5016;">✅ Productos Seleccionados:</h4>
                <div id="lista-productos-seleccionados" class="lista-productos-seleccionados">
                    <?php if (!empty($producto['productos_relacionados'])): ?>
                        <?php foreach ($allProducts as $p): ?>
                            <?php if (in_array((int)$p['id'], $producto['productos_relacionados'] ?? [])): ?>
                                <div class="producto-seleccionado" data-product-id="<?= (int)$p['id'] ?>">
                                    <span><?= htmlspecialchars($p['nombre']) ?></span>
                                    <button type="button" class="btn-eliminar-seleccion" onclick="deseleccionarProducto(<?= (int)$p['id'] ?>)">×</button>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contenedor de checkboxes -->
            <div class="checkboxes-container" id="productos-relacionados-container"
                style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 10px; background: #f9f9f9;">

                <!-- Checkboxes iniciales (todos los productos visibles) -->
                <?php foreach ($allProducts as $p): ?>
                    <?php if (isset($producto['id']) && $p['id'] == $producto['id']) continue; ?>
                    <div class="checkbox-item" data-product-id="<?= (int)$p['id'] ?>" data-product-name="<?= htmlspecialchars(strtolower($p['nombre'])) ?>">
                        <label>
                            <input
                                type="checkbox"
                                name="productos_relacionados[]"
                                value="<?= (int)$p['id'] ?>"
                                <?= in_array((int)$p['id'], $producto['productos_relacionados'] ?? []) ? 'checked' : '' ?>
                                onchange="actualizarProductosSeleccionados(this)">
                            <?= htmlspecialchars($p['nombre']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <small>Selecciona los productos relacionados con checkboxes.</small>
        </div>

        <!-- Precio Original (tachado) -->
        <div class="form-group">
            <label for="precio_tachado">Precio Original (tachado) (S/.)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="precio_tachado"
                id="precio_tachado"
                placeholder="Opcional — ej. 120.00"
                value="<?= isset($producto['precio_tachado']) ? htmlspecialchars($producto['precio_tachado']) : '' ?>">
        </div>

        <!-- Checkbox visibilidad para precio tachado -->
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox"
                name="precio_tachado_visible" id="precio_tachado_visible"
                <?= isset($producto['precio_tachado_visible']) && $producto['precio_tachado_visible'] ? 'checked' : (empty($producto) ? 'checked' : '') ?>>
            <label class="form-check-label" for="precio_tachado_visible">
                Mostrar precio tachado en la tarjeta
            </label>
        </div>

        <!-- Precio Final -->
        <div class="form-group">
            <label for="precio">Precio Final (S/.)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="precio"
                id="precio"
                required
                value="<?= isset($producto['precio']) ? htmlspecialchars($producto['precio']) : '' ?>">
        </div>

        <!-- Porcentaje (solo lectura para el admin) -->
        <div class="form-group">
            <label for="porcentaje_descuento_readonly">Porcentaje de Descuento</label>
            <input type="text" id="porcentaje_descuento_readonly" readonly
                value="<?= isset($producto['precio_tachado']) && $producto['precio_tachado'] > $producto['precio'] ?
                            number_format((($producto['precio_tachado'] - $producto['precio']) / $producto['precio_tachado']) * 100, 2) . '%' : '0.00%' ?>"
                style="background:#f5f5f5; border:1px solid #ddd; padding:6px;">

            <!-- Hidden para enviar el porcentaje al backend -->
            <input type="hidden" name="porcentaje_descuento" id="porcentaje_descuento"
                value="<?= isset($producto['precio_tachado']) && $producto['precio_tachado'] > $producto['precio'] ?
                            number_format((($producto['precio_tachado'] - $producto['precio']) / $producto['precio_tachado']) * 100, 2) : '0' ?>">
        </div>

        <!-- Checkbox visibilidad para porcentaje -->
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox"
                name="porcentaje_visible" id="porcentaje_visible"
                <?= isset($producto['porcentaje_visible']) && $producto['porcentaje_visible'] ? 'checked' : (empty($producto) ? 'checked' : '') ?>>
            <label class="form-check-label" for="porcentaje_visible">
                Mostrar porcentaje de descuento en la tarjeta
            </label>
        </div>


        <!-- Stock -->
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" name="stock" id="stock" value="<?= htmlspecialchars($producto['stock']) ?>" required>
        </div>

        <!-- Visible -->
        <div class="form-group">
            <div class="visible-checkbox">
                <input type="checkbox" name="visible" id="visible" value="1" <?= $producto['visible'] ? 'checked' : '' ?>>
                <label for="visible">Producto visible en la tienda</label>
            </div>
        </div>

        <!-- Imágenes -->
        <div class="form-group">
            <label>📷 Imágenes del Producto</label>

            <!-- Mostrar imágenes actuales -->
            <?php if (!empty($imagenes)): ?>
                <div class="imagenes-actuales">
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #333;">🖼️ Imágenes Actuales:</h4>
                    <div class="preview-grid">
                        <?php foreach ($imagenes as $img): ?>
                            <div class="preview-item existing-image">
                                <img src="<?= url('uploads/' . $img['nombre_imagen']) ?>" alt="Imagen" class="preview-image">
                                <div class="preview-info">
                                    <?= htmlspecialchars($img['nombre_imagen']) ?>
                                    <br>Imagen actual
                                </div>
                                <a href="<?= url('imagen/eliminar/' . $img['id']) ?>"
                                    class="preview-remove"
                                    onclick="return confirm('¿Eliminar esta imagen permanentemente?')">×</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Área para agregar nuevas imágenes -->
            <div class="dropzone-container" id="dropzone-container" style="margin-top: 20px;">
                <div class="dropzone-area" id="dropzone-area">
                    <div class="dropzone-content">
                        <i class="upload-icon">📁</i>
                        <p>Arrastra y suelta NUEVAS imágenes aquí o haz clic para seleccionar</p>
                        <small>Formatos: JPG, PNG, WEBP (Máx. 5MB por imagen)</small>
                    </div>
                    <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" style="display: none;">
                </div>
            </div>

            <!-- Contenedor de previsualización de NUEVAS imágenes -->
            <div class="preview-container" id="preview-container" style="display: none;">
                <h4 style="margin: 15px 0 10px 0; font-size: 14px; color: #333;">📷 Nuevas Imágenes:</h4>
                <div class="preview-grid" id="preview-grid">
                    <!-- Las miniaturas de nuevas imágenes aparecerán aquí -->
                </div>
            </div>
        </div>
        <style>
            /* Estilos para el sistema de imágenes */
.dropzone-container {
    margin-bottom: 15px;
}

.dropzone-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    background: #fafafa;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.dropzone-area:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.dropzone-area.dragover {
    border-color: #28a745;
    background: #e8f5e8;
}

.dropzone-content {
    color: #666;
}

.upload-icon {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

.dropzone-area p {
    margin: 0 0 5px 0;
    font-weight: 500;
}

.dropzone-area small {
    color: #888;
}

/* Contenedor de previsualización */
.preview-container {
    margin-top: 20px;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.preview-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.preview-item:hover {
    transform: translateY(-2px);
}

.preview-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.preview-info {
    padding: 8px;
    background: white;
    font-size: 12px;
    color: #666;
    text-align: center;
    border-top: 1px solid #eee;
}

.preview-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.9;
    transition: opacity 0.2s ease;
    text-decoration: none;
}

.preview-remove:hover {
    opacity: 1;
    background: #c82333;
}

/* Estilo especial para imágenes existentes */
.existing-image {
    border: 2px solid #28a745;
}

.existing-image .preview-info {
    background: #e8f5e8;
    color: #155724;
}

/* Indicador de cantidad */
.file-count {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #007bff;
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: bold;
}
        </style>

        <!-- Etiquetas -->
        <div class="form-group">
            <label for="etiquetas">🏷️ Etiquetas</label>
            <select name="etiquetas[]" id="etiquetas" multiple>
                <?php foreach ($etiquetas as $et): ?>
                    <option value="<?= $et['id'] ?>" <?= in_array($et['id'], $etiquetasAsignadas ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($et['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Categorías -->
        <h3>📋 Categorías</h3>
        <div class="checkbox-container">
            <?php
            function renderCheckboxCategoriasEdit($categorias, $seleccionadas, $padre = null, $nivel = 0)
            {
                foreach ($categorias as $cat) {
                    if ($cat['id_padre'] == $padre) {
                        $checked = in_array($cat['id'], $seleccionadas) ? 'checked' : '';
                        $margen = $nivel * 20;
                        echo "<div style='margin-left: {$margen}px'>";
                        echo "<label><input type='checkbox' name='categorias[]' value='{$cat['id']}' $checked> " . htmlspecialchars($cat['nombre']) . "</label>";
                        echo "</div>";
                        renderCheckboxCategoriasEdit($categorias, $seleccionadas, $cat['id'], $nivel + 1);
                    }
                }
            }
            renderCheckboxCategoriasEdit($categorias, $categoriasAsignadas);
            ?>
        </div>

        <!-- Variantes -->
        <h3>🎨 Variantes del Producto</h3>
        <div id="variantes-container">
            <?php if (!empty($variantes)): ?>
                <?php foreach ($variantes as $var): ?>
                    <div class="variante">
                        <input type="hidden" name="variantes[id][]" value="<?= $var['id'] ?>">
                        <div>
                            <label>Talla</label>
                            <input type="text" name="variantes[talla][]" value="<?= htmlspecialchars($var['talla']) ?>">
                        </div>
                        <div>
                            <label>Color</label>
                            <input type="text" name="variantes[color][]" value="<?= htmlspecialchars($var['color']) ?>">
                        </div>
                        <div>
                            <label>Stock</label>
                            <input type="number" name="variantes[stock][]" value="<?= htmlspecialchars($var['stock']) ?>">
                        </div>
                        <a href="<?= url('variante/eliminar/' . $var['id'] . '?producto_id=' . $producto['id']) ?>" onclick="return confirm('¿Eliminar esta variante?')">❌ Eliminar</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay variantes registradas.</p>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-add" onclick="agregarVariante()">+ Agregar Variante</button>


        <div class="form-group">
            <label>
                <input type="checkbox" name="destacado" value="1" <?= $producto['destacado'] ? 'checked' : '' ?>>
                Producto destacado
            </label>
        </div>
        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
            <a href="<?= url('producto') ?>" class="btn btn-secondary">← Atrás</a>
        </div>
    </form>
</div>

<script>
    function agregarVariante() {
        const container = document.getElementById('variantes-container');
        const html = `
            <div class="variante">
                <input type="hidden" name="variantes[id][]" value="">
                <div>
                    <label>Talla</label>
                    <input type="text" name="variantes[talla][]" placeholder="Ej: S, M, L, XL">
                </div>
                <div>
                    <label>Color</label>
                    <input type="text" name="variantes[color][]" placeholder="Ej: Rojo, Azul">
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" name="variantes[stock][]" placeholder="Cantidad">
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
<script>
    (function() {
        const precioTachadoEl = document.getElementById('precio_tachado');
        const precioFinalEl = document.getElementById('precio');
        const porcentajeReadonlyEl = document.getElementById('porcentaje_descuento_readonly');
        const porcentajeHiddenEl = document.getElementById('porcentaje_descuento');

        // Calcula porcentaje a partir del precio tachado y precio final
        function calcularPorcentaje(precioTachado, precioFinal) {
            if (!precioTachado || precioTachado <= 0) return 0;
            // Si precioFinal >= precioTachado no hay descuento
            if (!precioFinal || precioFinal >= precioTachado) return 0;
            const diff = precioTachado - precioFinal;
            const pct = (diff / precioTachado) * 100;
            return parseFloat(pct.toFixed(2));
        }

        // Actualiza la UI y el hidden
        function actualizarPorcentajeUI(pct) {
            porcentajeReadonlyEl.value = (pct > 0) ? pct.toFixed(2) + '%' : '0.00%';
            porcentajeHiddenEl.value = (pct > 0) ? pct.toFixed(2) : '0';
        }

        // Handler cuando cambia precio tachado o precio final
        function onChangeCampos() {
            const precioTachado = parseFloat(precioTachadoEl.value) || 0;
            const precioFinal = parseFloat(precioFinalEl.value) || 0;

            const pct = calcularPorcentaje(precioTachado, precioFinal);
            actualizarPorcentajeUI(pct);
        }

        // Listeners
        precioTachadoEl.addEventListener('input', function() {
            // El admin puede editar precio tachado; no forzamos cambios en precio final,
            // solo recalculamos % informativo.
            onChangeCampos();
        });

        precioFinalEl.addEventListener('input', function() {
            // El admin edita el precio final (este es el campo importante).
            // Recalculamos % informativo basado en precio_tachado (si existe).
            onChangeCampos();
        });

        // Inicializa al cargar
        document.addEventListener('DOMContentLoaded', function() {
            onChangeCampos();
        });
    })();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscador-productos');
        const container = document.getElementById('productos-relacionados-container');
        const containerSeleccionados = document.getElementById('productos-seleccionados-container');
        const listaSeleccionados = document.getElementById('lista-productos-seleccionados');
        let checkboxesOriginales = container.innerHTML;

        // Inicializar productos seleccionados al cargar
        actualizarVisibilidadSeleccionados();

        // Función para filtrar productos locales
        function filtrarProductosLocales(termino) {
            const items = container.querySelectorAll('.checkbox-item');
            let encontrados = 0;

            items.forEach(item => {
                const nombre = item.getAttribute('data-product-name');
                if (nombre.includes(termino.toLowerCase())) {
                    item.style.display = 'block';
                    encontrados++;
                } else {
                    item.style.display = 'none';
                }
            });

            return encontrados;
        }

        // Función para buscar productos en el servidor
        function buscarProductosServidor(termino) {
            container.innerHTML = '<p>Buscando productos...</p>';

            fetch(`<?= url('producto/autocomplete') ?>?q=${encodeURIComponent(termino)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta');
                    return response.json();
                })
                .then(productos => {
                    container.innerHTML = '';

                    if (productos.length === 0) {
                        container.innerHTML = '<p>No se encontraron productos</p>';
                        return;
                    }

                    productos.forEach(producto => {
                        const estabaSeleccionado = <?= json_encode($producto['productos_relacionados'] ?? []) ?>.includes(producto.id);

                        const div = document.createElement('div');
                        div.className = 'checkbox-item';
                        div.setAttribute('data-product-id', producto.id);
                        div.setAttribute('data-product-name', producto.nombre.toLowerCase());
                        div.innerHTML = `
                        <label>
                            <input type="checkbox" name="productos_relacionados[]" 
                                   value="${producto.id}" ${estabaSeleccionado ? 'checked' : ''}
                                   onchange="actualizarProductosSeleccionados(this)">
                            ${producto.nombre}
                        </label>
                    `;
                        container.appendChild(div);
                    });
                })
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    container.innerHTML = '<p>Error al buscar productos</p>';
                });
        }

        // Event listener para el buscador
        buscador.addEventListener('input', function() {
            const filtro = this.value.trim();

            if (filtro.length === 0) {
                container.innerHTML = checkboxesOriginales;
                // Re-asignar event listeners a los checkboxes restaurados
                setTimeout(() => {
                    document.querySelectorAll('.checkbox-item input[type="checkbox"]').forEach(checkbox => {
                        checkbox.onchange = function() {
                            actualizarProductosSeleccionados(this);
                        };
                    });
                }, 0);
            } else {
                const encontradosLocales = filtrarProductosLocales(filtro);

                if (encontradosLocales === 0 && filtro.length >= 2) {
                    buscarProductosServidor(filtro);
                }
            }
        });

        // Prevenir envío del formulario al presionar Enter en el buscador
        buscador.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    });

    // Función global para actualizar la lista de productos seleccionados
    function actualizarProductosSeleccionados(checkbox) {
        const productId = checkbox.value;
        const productName = checkbox.parentElement.textContent.trim();
        const listaSeleccionados = document.getElementById('lista-productos-seleccionados');

        if (checkbox.checked) {
            // Agregar a la lista de seleccionados
            if (!document.querySelector(`.producto-seleccionado[data-product-id="${productId}"]`)) {
                const div = document.createElement('div');
                div.className = 'producto-seleccionado';
                div.setAttribute('data-product-id', productId);
                div.innerHTML = `
                <span>${productName}</span>
                <button type="button" class="btn-eliminar-seleccion" onclick="deseleccionarProducto(${productId})">×</button>
            `;
                listaSeleccionados.appendChild(div);
            }
        } else {
            // Remover de la lista de seleccionados
            const elemento = document.querySelector(`.producto-seleccionado[data-product-id="${productId}"]`);
            if (elemento) {
                elemento.remove();
            }
        }

        actualizarVisibilidadSeleccionados();
    }

    // Función para deseleccionar un producto desde el botón ×
    function deseleccionarProducto(productId) {
        // Desmarcar el checkbox correspondiente
        const checkbox = document.querySelector(`.checkbox-item input[value="${productId}"]`);
        if (checkbox) {
            checkbox.checked = false;
        }

        // Remover de la lista de seleccionados
        const elemento = document.querySelector(`.producto-seleccionado[data-product-id="${productId}"]`);
        if (elemento) {
            elemento.remove();
        }

        actualizarVisibilidadSeleccionados();
    }

    // Función para mostrar/ocultar el contenedor de seleccionados
    function actualizarVisibilidadSeleccionados() {
        const container = document.getElementById('productos-seleccionados-container');
        const lista = document.getElementById('lista-productos-seleccionados');

        if (lista.children.length > 0) {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }
</script>
<script>
    // Sistema de previsualización de imágenes PARA EDITAR
document.addEventListener('DOMContentLoaded', function() {
    const dropzoneArea = document.getElementById('dropzone-area');
    const fileInput = document.getElementById('imagenes');
    const previewContainer = document.getElementById('preview-container');
    const previewGrid = document.getElementById('preview-grid');
    const fileCount = document.createElement('div');
    
    fileCount.className = 'file-count';
    fileCount.style.display = 'none';
    dropzoneArea.appendChild(fileCount);

    // Click en el dropzone
    dropzoneArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Cambio en el input de archivo
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    // Drag and drop
    dropzoneArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    dropzoneArea.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });

    dropzoneArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFiles(e.dataTransfer.files);
        }
    });

    // Función para manejar archivos
    function handleFiles(files) {
        if (files.length === 0) return;

        // Actualizar contador
        fileCount.textContent = files.length;
        fileCount.style.display = 'block';

        // Mostrar contenedor de previsualización
        previewContainer.style.display = 'block';

        // Procesar cada archivo
        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            
            reader.onload = function(e) {
                createPreviewItem(e.target.result, file.name, file.size, index);
            };
            
            reader.readAsDataURL(file);
        });
    }

    // Función para crear item de previsualización
    function createPreviewItem(src, name, size, index) {
        const previewItem = document.createElement('div');
        previewItem.className = 'preview-item';
        previewItem.setAttribute('data-index', index);

        const sizeMB = (size / (1024 * 1024)).toFixed(2);
        
        previewItem.innerHTML = `
            <img src="${src}" alt="${name}" class="preview-image">
            <div class="preview-info">
                ${name.length > 15 ? name.substring(0, 15) + '...' : name}
                <br>${sizeMB} MB
            </div>
            <button type="button" class="preview-remove" onclick="removePreviewImage(${index})">×</button>
        `;

        previewGrid.appendChild(previewItem);
    }
});

// Función para eliminar imagen de la previsualización (NUEVAS imágenes)
function removePreviewImage(index) {
    const fileInput = document.getElementById('imagenes');
    const files = Array.from(fileInput.files);
    files.splice(index, 1);
    
    // Crear nuevo FileList
    const newFileList = new DataTransfer();
    files.forEach(file => newFileList.items.add(file));
    fileInput.files = newFileList.files;
    
    // Actualizar UI
    updatePreviewUI();
}

// Función para actualizar la UI después de eliminar
function updatePreviewUI() {
    const fileInput = document.getElementById('imagenes');
    const previewGrid = document.getElementById('preview-grid');
    const previewContainer = document.getElementById('preview-container');
    const fileCount = document.querySelector('.file-count');
    
    // Limpiar previsualización
    previewGrid.innerHTML = '';
    
    if (fileInput.files.length === 0) {
        previewContainer.style.display = 'none';
        fileCount.style.display = 'none';
        return;
    }
    
    // Actualizar contador
    fileCount.textContent = fileInput.files.length;
    
    // Recrear previsualizaciones
    Array.from(fileInput.files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.setAttribute('data-index', index);

            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="${file.name}" class="preview-image">
                <div class="preview-info">
                    ${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}
                    <br>${sizeMB} MB
                </div>
                <button type="button" class="preview-remove" onclick="removePreviewImage(${index})">×</button>
            `;

            previewGrid.appendChild(previewItem);
        };
        
        reader.readAsDataURL(file);
    });
}
</script>