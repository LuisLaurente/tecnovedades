<?php require_once __DIR__ . '/../../Core/helpers/urlHelper.php'; ?>
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

        <!-- Precio -->
        <div class="form-group">
            <label for="precio">Precio (S/.)</label>
            <input type="number" step="0.01" name="precio" id="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required>
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
            function renderCheckboxCategoriasEdit($categorias, $seleccionadas, $padre = null, $nivel = 0) {
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
