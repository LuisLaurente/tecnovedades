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

        <!-- Precio -->
        <div class="form-group">
            <label for="precio">Precio (S/.)</label>
            <input type="number" step="0.01" name="precio" id="precio" required placeholder="0.00">
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
