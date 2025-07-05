<!-- views/producto/crear.php -->
<h2>Crear Nuevo Producto</h2>

<form action="/producto/guardar" method="POST">
    <!-- Este campo sirve para ingresar el nombre del producto -->
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" required><br><br>

    <!-- Este campo sirve para escribir una descripción -->
    <label for="descripcion">Descripción:</label>
    <textarea name="descripcion" id="descripcion" required></textarea><br><br>

    <!-- Este campo sirve para definir el precio del producto -->
    <label for="precio">Precio:</label>
    <input type="number" step="0.01" name="precio" id="precio" required><br><br>

    <!-- Este campo sirve para indicar cuántos hay en stock -->
    <label for="stock">Stock:</label>
    <input type="number" name="stock" id="stock" required><br><br>

    <!-- Este checkbox sirve para indicar si el producto debe mostrarse al público -->
    <label for="visible">Visible:</label>
    <input type="checkbox" name="visible" id="visible" value="1" checked><br><br>

    <!-- Este campo es para ingresar variantes del producto -->
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

    <button type="button" onclick="agregarVariante()"> Agregar Variante</button>

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

    <button type="submit">Guardar Producto</button>
</form>