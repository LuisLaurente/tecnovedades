<?php require_once __DIR__ . '/../../Core/Helpers/urlHelper.php'; ?>
<link rel="stylesheet" href="<?= url('/css/editar-producto.css') ?>">

<div class="form-container">
    <h2>✏️ Editar Producto</h2>

    <form action="<?= url('producto/actualizar/' . $producto['id']) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $producto['id'] ?>">

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
            <div class="imagenes-actuales">
                <?php if (!empty($imagenes)): ?>
                    <?php foreach ($imagenes as $img): ?>
                        <div class="imagen-item">
                            <img src="<?= url('uploads/' . $img['nombre_imagen']) ?>" alt="Imagen" width="120">
                            <a href="<?= url('imagen/eliminar/' . $img['id']) ?>" onclick="return confirm('¿Eliminar esta imagen?')">❌ Eliminar</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay imágenes cargadas.</p>
                <?php endif; ?>
            </div>
            <input type="file" name="imagenes[]" multiple accept="image/*">
        </div>

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