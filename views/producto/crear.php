<h2>Crear Nuevo Producto</h2>

<form action="/producto/guardar" method="POST">
    <!-- Nombre -->
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" required><br><br>

    <!-- Descripción -->
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" required></textarea><br><br>

    <!-- Precio -->
    <label for="precio">Precio:</label>
    <input type="number" step="0.01" name="precio" id="precio" required><br><br>

    <!-- Stock -->
    <label for="stock">Stock:</label>
    <input type="number" name="stock" id="stock" required><br><br>

    <!-- Visible -->
    <label for="visible">Visible:</label>
    <input type="checkbox" name="visible" id="visible" value="1" checked><br><br>

    <!-- Múltiples categorías -->
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
                    // Mostrar subcategorías
                    renderCheckboxCategorias($categorias, $cat['id'], $nivel + 1);
                }
            }
        }

        renderCheckboxCategorias($categorias);
        ?>
    </div>
    
    <br><br>

    <!-- Variantes -->
    <h3>Variantes</h3>
    <div id="variantes-container">
        <div class="variante">
            <label>Talla:</label>
            <input type="text" name="variantes[talla][]" required>

            <label>Color:</label>
            <input type="text" name="variantes[color][]" required>

            <label>Stock:</label>
            <input type="number" name="variantes[stock][]" required>
        </div>
    </div>

    <button type="button" onclick="agregarVariante()">Agregar Variante</button>

    <script>
        function agregarVariante() {
            const container = document.getElementById('variantes-container');
            const html = `
                <div class="variante">
                    <label>Talla:</label>
                    <input type="text" name="variantes[talla][]" required>

                    <label>Color:</label>
                    <input type="text" name="variantes[color][]" required>

                    <label>Stock:</label>
                    <input type="number" name="variantes[stock][]" required>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>

    <br>
    <button type="submit">Guardar Producto</button>
</form>