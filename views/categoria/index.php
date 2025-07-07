<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías</title>
</head>
<body>
    <h1>📂 Gestión de Categorías</h1>

    <p>
        <a href="/categoria/crear" style="font-weight: bold; color: green;">+ Crear nueva categoría</a>
    </p>

    <hr>

    <?php if (!empty($categorias)): ?>
        <ul>
            <?php
            function mostrarCategorias($categorias, $padre = null, $nivel = 0)
            {
                foreach ($categorias as $categoria) {
                    if ($categoria['id_padre'] == $padre) {
                        echo "<li style='margin-left: " . ($nivel * 20) . "px'>";
                        echo "<strong>" . htmlspecialchars($categoria['nombre']) . "</strong>";

                        $puedeEditar = !$categoria['tiene_hijos'] && !$categoria['tiene_productos'];
                        $puedeEliminar = !$categoria['tiene_hijos'] && !$categoria['tiene_productos'];

                        if ($puedeEditar) {
                            echo " | <a href='/categoria/editar/{$categoria['id']}' style='color:blue;'>Editar</a>";
                        }

                        if ($puedeEliminar) {
                            echo " | <a href='/categoria/eliminar/{$categoria['id']}' style='color:red;' onclick=\"return confirm('¿Estás seguro de eliminar esta categoría?')\">Eliminar</a>";
                        }

                        echo "</li>";

                        // Mostrar hijos recursivamente
                        mostrarCategorias($categorias, $categoria['id'], $nivel + 1);
                    }
                }
            }

            mostrarCategorias($categorias);
            ?>
        </ul>
    <?php else: ?>
        <p>No hay categorías registradas.</p>
    <?php endif; ?>
</body>
</html>
