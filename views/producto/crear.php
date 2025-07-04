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

    <button type="submit">Guardar Producto</button>
</form>
