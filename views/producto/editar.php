<h1>Editar Producto</h1>
<?php $base = '/TECNOVEDADES/public/'; ?>

<form action="<?= $base ?>producto/actualizar" method="POST">
    <input type="hidden" name="id" value="<?= $producto['id'] ?>">

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br>

    <label>SKU:</label>
    <input type="text" name="sku" value="<?= htmlspecialchars($producto['sku']) ?>" readonly><br>

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

    <h3>Gestión de Etiquetas</h3>
       <form action="/producto/actualizar/<?= $producto['id'] ?>" method="POST">
    <!-- tus campos existentes -->
    <h4>Cambiar etiqueta:</h4>
    <select name="etiquetas[]" multiple>
        <?php foreach ($etiquetas as $et): ?>
        <option value="<?= $et['id'] ?>" <?= in_array($et['id'], $etiquetasAsignadas ?? []) ? 'selected' : '' ?>>
            <?= htmlspecialchars($et['nombre']) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Actualizar</button>
</form>

<!-- 🟡 Gestión completa de etiquetas (crear, editar, eliminar) -->



<table border="1" style="margin-top: 10px;">
    <tr>
        <th>Nombre</th>
        <th>Acción</th>
    </tr>
    <?php foreach ($etiquetas as $et): ?>
        <tr>
            <form method="POST" action="<?= $base ?>etiqueta/actualizar">
                <td>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($et['nombre']) ?>">
                </td>
                <td>
                    <input type="hidden" name="id" value="<?= $et['id'] ?>">
                    <a href="<?= $base ?>etiqueta/eliminar/<?= $et['id'] ?>?redirect=<?= urlencode("/TECNOVEDADES/public/producto/editar/" . $producto['id']) ?>" onclick="return confirm('¿Eliminar esta etiqueta?')">❌ Eliminar</a>

                </td>
            </form>
        </tr>
    <?php endforeach; ?>
</table>

<!-- ➕ Formulario para nueva etiqueta -->
<form method="POST" action="<?= $base ?>etiqueta/guardar" style="margin-top: 10px;">
    <input type="text" name="nombre" placeholder="Nueva etiqueta" required>
    <button type="submit">➕ Agregar</button>
</form>

    
</form>


<hr>


<h4>Etiquetas asignadas (debug):</h4>
<ul>
<?php foreach ($etiquetasAsignadas as $id_et): ?>
  <li>ID etiqueta: <?= $id_et ?></li>
<?php endforeach; ?>
</ul>

<hr>

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

                <button type="submit">Actualizar Variante</button>
                <a href="/variante/eliminar/<?= $variante['id'] ?>?producto_id=<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta variante?')">❌ Eliminar</a>
            </form>    
                <!-- 🧩 Formulario SEPARADO para subir imagen -->
                <form action="<?= $base ?>imagen/subir" method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
                    <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                    <label>Imágenes del producto:</label>
                    <input type="file" name="imagen" required>
                    <button type="submit">📤 Subir Imagen</button>
                </form>

                <h2>Imágenes del Producto</h2>
                <?php foreach ($imagenes as $img): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= $base ?>uploads/<?= htmlspecialchars($img['nombre_imagen']) ?>" alt="Imagen" width="120">
                        <a href="<?= $base ?>imagen/eliminar/<?= $img['id'] ?>" onclick="return confirm('¿Eliminar esta imagen?')">❌ Eliminar</a>
                    </div>
                <?php endforeach; ?>

            
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No hay variantes registradas.</p>
<?php endif; ?>