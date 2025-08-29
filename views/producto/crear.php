<?php require_once __DIR__ . '/../../Core/Helpers/urlHelper.php'; ?>
<link rel="stylesheet" href="<?= url('/css/crearProducto.css') ?>">

<div class="form-container">
    <h2>Crear Nuevo Producto</h2>

    <form action="<?= url('producto/guardar') ?>" method="POST" enctype="multipart/form-data">
        <form action="<?= url('producto/guardar') ?>" method="POST" enctype="multipart/form-data">
            <!-- Nombre -->
            <div class="form-group">
                <label for="nombre">Nombre del Producto</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ingrese el nombre del producto">
            </div>

            <!-- Descripción -->
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" required placeholder="Describa las características del producto"></textarea>
            </div>
            <!-- Especificaciones (texto libre, cada línea -> lista) -->
            <div class="form-row">
                <label for="especificaciones">Especificaciones (una por línea)</label>
                <textarea name="especificaciones" id="especificaciones" rows="5"><?= htmlspecialchars($producto['especificaciones'] ?? '') ?></textarea>
            </div>

            <!-- Productos relacionados (multiselect) -->
            <div class="form-row">
                <label for="productos_relacionados">Productos relacionados</label>
                <select name="productos_relacionados[]" id="productos_relacionados" multiple style="min-height:120px;">
                    <?php foreach ($allProducts as $p): ?>
                        <?php if (isset($producto['id']) && $p['id'] == $producto['id']) continue; // no enlazarse a sí mismo 
                        ?>
                        <option value="<?= (int)$p['id'] ?>"
                            <?= in_array((int)$p['id'], $producto['productos_relacionados'] ?? []) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Selecciona productos relacionados (mantén Ctrl/Cmd para múltiples).</small>
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
                <label for="stock">Stock Inicial</label>
                <input type="number" name="stock" id="stock" required placeholder="Cantidad disponible">
            </div>

            <!-- Visible -->
            <div class="form-group">
                <div class="visible-checkbox">
                    <input type="checkbox" name="visible" id="visible" value="1" checked>
                    <label for="visible">Producto visible en la tienda</label>
                </div>
            </div>

            <!-- Imágenes -->
            <div class="form-group">
                <label for="imagenes">Imágenes del Producto</label>
                <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*">
            </div>

            <!-- Etiquetas -->
            <div class="form-group">
                <label for="etiquetas">Etiquetas</label>
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
                function renderCheckboxCategorias($categorias, $padre = null, $nivel = 0)
                {
                    foreach ($categorias as $cat) {
                        if ($cat['id_padre'] == $padre) {
                            $margen = $nivel * 20;
                            echo "<div style='margin-left: {$margen}px'>";
                            echo "<label>";
                            echo "<input type='checkbox' name='categorias[]' value='{$cat['id']}'> ";
                            echo htmlspecialchars($cat['nombre']);
                            echo "</label>";
                            echo "</div>";
                            renderCheckboxCategorias($categorias, $cat['id'], $nivel + 1);
                        }
                    }
                }
                renderCheckboxCategorias($categorias);
                ?>
            </div>

            <!-- Variantes -->
            <h3>🎨 Variantes del Producto</h3>
            <div class="variantes-section">
                <div id="variantes-container">
                    <div class="variante">
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
                    </div>
                </div>
                <button type="button" class="btn btn-add" onclick="agregarVariante()">+ Agregar Variante</button>
            </div>

            <!-- Destacado -->
            <div class="form-group">
                <div class="visible-checkbox">
                    <input type="checkbox" name="destacado" id="destacado" value="1"
                        <?= isset($producto['destacado']) && $producto['destacado'] ? 'checked' : '' ?>>
                    <label for="destacado">Marcar como producto destacado ⭐</label>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Guardar Producto</button>
                <a href="<?= url('producto') ?>" class="btn btn-secondary">← Atrás</a>
            </div>
        </form>
</div>

<script>
    function agregarVariante() {
        const container = document.getElementById('variantes-container');
        const html = `
            <div class="variante">
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

    // Mejorar la experiencia del input de archivo
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('imagenes');

        fileInput.addEventListener('change', function() {
            const fileCount = this.files.length;
            if (fileCount > 0) {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
            }
        });
    });
</script>
<script>
    (function() {
        const precioTachadoEl = document.getElementById('precio_tachado');
        const precioFinalEl = document.getElementById('precio');
        const porcentajeReadonlyEl = document.getElementById('porcentaje_descuento_readonly');
        const porcentajeHiddenEl = document.getElementById('porcentaje_descuento');

        function calcularPorcentaje(precioTachado, precioFinal) {
            if (!precioTachado || precioTachado <= 0) return 0;
            if (!precioFinal || precioFinal >= precioTachado) return 0;
            const diff = precioTachado - precioFinal;
            return parseFloat(((diff / precioTachado) * 100).toFixed(2));
        }

        function actualizarPorcentaje() {
            const precioTachado = parseFloat(precioTachadoEl.value) || 0;
            const precioFinal = parseFloat(precioFinalEl.value) || 0;
            const pct = calcularPorcentaje(precioTachado, precioFinal);

            porcentajeReadonlyEl.value = (pct > 0) ? pct.toFixed(2) + '%' : '0.00%';
            porcentajeHiddenEl.value = (pct > 0) ? pct.toFixed(2) : '0';
        }

        precioTachadoEl.addEventListener('input', actualizarPorcentaje);
        precioFinalEl.addEventListener('input', actualizarPorcentaje);

        document.addEventListener('DOMContentLoaded', actualizarPorcentaje);
    })();
</script>