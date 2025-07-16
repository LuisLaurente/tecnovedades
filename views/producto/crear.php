<h2>Crear Nuevo Producto</h2>

<form action="/TECNOVEDADES/public/producto/guardar" method="POST" enctype="multipart/form-data">
    <!-- Nombre -->
    <label for="nombre">Nombre:</label><br>
    <input type="text" name="nombre" id="nombre" required><br><br>

    <!-- Descripción -->
    <label for="descripcion">Descripción:</label><br>
    <textarea name="descripcion" id="descripcion" required></textarea><br><br>

    <!-- Precio -->
    <label for="precio">Precio:</label><br>
    <input type="number" step="0.01" name="precio" id="precio" required><br><br>

    <!-- Stock -->
    <label for="stock">Stock:</label><br>
    <input type="number" name="stock" id="stock" required><br><br>

    <!-- Visible -->
    <label for="visible">
        <input type="checkbox" name="visible" id="visible" value="1" checked>
        ¿Producto visible?
    </label><br><br>

    <!-- Imágenes -->
    <label for="imagenes">Imágenes del producto:</label><br>
    <input type="file" name="imagenes[]" id="imagenes" multiple><br><br>

    <!-- Etiquetas -->
    <label for="etiquetas">Etiquetas:</label><br>
    <select name="etiquetas[]" id="etiquetas" multiple>
        <?php foreach ($etiquetas as $et): ?>
            <option value="<?= $et['id'] ?>" <?= in_array($et['id'], $etiquetasAsignadas ?? []) ? 'selected' : '' ?>>
                <?= htmlspecialchars($et['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <!-- Categorías -->
    <h3>Categorías</h3>
    <div style="margin-bottom: 20px;">
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
    <h3>Variantes (opcional)</h3>
    <div id="variantes-container">
        <div class="variante">
            <label>Talla:</label>
            <input type="text" name="variantes[talla][]">

            <label>Color:</label>
            <input type="text" name="variantes[color][]">

            <label>Stock:</label>
            <input type="number" name="variantes[stock][]">
        </div>
    </div>

    <button type="button" onclick="agregarVariante()">+ Agregar Variante</button>

    <br><br>
    <button type="submit">Guardar Producto</button>
    <a href="/TECNOVEDADES/public/producto" style="margin-left: 10px;">← Cancelar</a>
</form>

<script>
    function agregarVariante() {
        const container = document.getElementById('variantes-container');
        const html = `
            <div class="variante" style="margin-top: 10px;">
                <label>Talla:</label>
                <input type="text" name="variantes[talla][]">

                <label>Color:</label>
                <input type="text" name="variantes[color][]">

                <label>Stock:</label>
                <input type="number" name="variantes[stock][]">
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
