<h1>Editar Producto</h1>

<form action="/producto/actualizar/<?= $producto['id'] ?>" method="POST">
    <input type="hidden" name="id" value="<?= $producto['id'] ?>">

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br>

    <label>Descripción:</label>
    <textarea name="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea><br>

    <label>Precio:</label>
    <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required><br>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?= htmlspecialchars($producto['stock']) ?>" required><br>

    <label>Visible:</label>
    <select name="visible">
        <option value="1" <?= $producto['visible'] ? 'selected' : '' ?>>Sí</option>
        <option value="0" <?= !$producto['visible'] ? 'selected' : '' ?>>No</option>
    </select><br><br>


    <!-- Categorías con checkboxes -->
    <h3>Categorías</h3>
    <div style="margin-bottom: 20px;">
        <?php
        function renderCheckboxCategoriasEdit($categorias, $seleccionadas, $padre = null, $nivel = 0)
        {
            foreach ($categorias as $cat) {
                if ($cat['id_padre'] == $padre) {
                    $margen = $nivel * 20;
                    $checked = in_array($cat['id'], $seleccionadas) ? 'checked' : '';
                    echo "<div style='margin-left: {$margen}px'>";
                    echo "<label>";
                    echo "<input type='checkbox' name='categorias[]' value='{$cat['id']}' $checked> ";
                    echo htmlspecialchars($cat['nombre']);
                    echo "</label>";
                    echo "</div>";
                    renderCheckboxCategoriasEdit($categorias, $seleccionadas, $cat['id'], $nivel + 1);
                }
            }
        }

        renderCheckboxCategoriasEdit($categorias, $categoriasAsignadas);
        ?>
    </div>

        <label>Etiquetas:</label>
    <select name="etiquetas[]" multiple>
    <?php foreach ($etiquetas as $et): ?>
    <option value="<?= $et['id'] ?>" <?= in_array($et['id'], $etiquetasAsignadas ?? []) ? 'selected' : '' ?>>
        <?= htmlspecialchars($et['nombre']) ?>
    </option>
    <?php endforeach; ?>
</select>


    <button type="submit">Actualizar</button>
</form>


<hr>


<h4>Etiquetas asignadas (debug):</h4>
<ul>
<?php foreach ($etiquetasAsignadas as $id_et): ?>
  <li>ID etiqueta: <?= $id_et ?></li>
<?php endforeach; ?>
</ul>



<h2>Variantes del Producto</h2>

<?php if (!empty($variantes)): ?>
    <?php foreach ($variantes as $variante): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <form action="/variante/actualizar/<?= $variante['id'] ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">

                <label>Talla:</label>
                <input type="text" name="talla" value="<?= htmlspecialchars($variante['talla']) ?>" required>

                <label>Color:</label>
                <input type="text" name="color" value="<?= htmlspecialchars($variante['color']) ?>" required>

                <label>Stock:</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($variante['stock']) ?>" required>

                <label>Imágenes del producto:</label>
                <input type="file" name="imagenes[]" multiple><br>

                <button type="submit">Actualizar Variante</button>
                <a href="/variante/eliminar/<?= $variante['id'] ?>?producto_id=<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta variante?')">❌ Eliminar</a>
            </form>
            <h2>Imágenes del Producto</h2>
            <?php foreach ($imagenes as $img): ?>
                <div style="margin-bottom: 10px;">
                    <img src="/uploads/<?= htmlspecialchars($img['nombre_imagen']) ?>" alt="Imagen" width="120">
                    <a href="/imagen/eliminar/<?= $img['id'] ?>" onclick="return confirm('¿Eliminar esta imagen?')">❌ Eliminar</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay variantes registradas.</p>
<?php endif; ?>