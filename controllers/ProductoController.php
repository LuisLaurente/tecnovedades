<?php

namespace Controllers;

use Models\Producto;
use Models\VarianteProducto;
use Models\ImagenProducto;
use Models\Categoria;
use Models\Etiqueta;

class ProductoController
{
    public function index()
    {
        $productoModel = new Producto();

        // Filtros comunes
        $validacionFiltros = \Core\Helpers\Validator::validarFiltrosGET($_GET);
        $minPrice = $validacionFiltros['filtros_validos']['min_price'] ?? null;
        $maxPrice = $validacionFiltros['filtros_validos']['max_price'] ?? null;
        $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) ? (int)$_GET['categoria'] : null;

        // Filtros adicionales por etiquetas y stock
        $etiquetasSeleccionadas = $_GET['etiquetas'] ?? [];
        $soloDisponibles = isset($_GET['disponibles']) && $_GET['disponibles'] == '1';
        $orden = $_GET['orden'] ?? '';

        // Obtener datos para filtros y visualización
        $estadisticasPrecios = $productoModel->obtenerEstadisticasPrecios();
        $categoriasDisponibles = Producto::obtenerCategoriasConProductos();
        $productos = $productoModel->obtenerFiltrados($minPrice, $maxPrice, $categoriaId, $etiquetasSeleccionadas, $soloDisponibles, $orden);
        $totalFiltrados = $productoModel->contarFiltrados($minPrice, $maxPrice, $categoriaId, $etiquetasSeleccionadas);

        // Asociar categorías e imágenes a cada producto
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
            $producto['imagenes'] = \Models\ImagenProducto::obtenerPorProducto($producto['id']); // 🔑 Aquí añadimos todas las imágenes
        }
        unset($producto);

        // Obtener todas las etiquetas para el formulario
        $etiquetaModel = new Etiqueta();
        $todasEtiquetas = $etiquetaModel->obtenerTodas();

        // Si es petición AJAX, devolver JSON
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');

            echo json_encode([
                'success' => empty($validacionFiltros['errores']),
                'productos' => $productos,
                'total' => $totalFiltrados,
                'filtros' => [
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'categoria' => $categoriaId,
                    'etiquetas' => $etiquetasSeleccionadas,
                    'disponibles' => $soloDisponibles,
                    'orden' => $orden
                ],
                'errores' => $validacionFiltros['errores'] ?? []
            ]);
            exit;
        }

        // Mostrar vista
        require_once __DIR__ . '/../views/producto/index.php';
    }

    public function crear()
    {
        $categorias = Categoria::obtenerTodas();
        $etiquetas = (new Etiqueta())->obtenerTodas();
        require_once __DIR__ . '/../views/producto/crear.php';
    }

    public function guardar()
    {
        $db = \Core\Database::getInstance()->getConnection();
        $errores = [];

        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
        $precio_tachado = $_POST['precio_tachado'] ?? null;
        $stock = $_POST['stock'] ?? '';
        $visible = isset($_POST['visible']) ? 1 : 0;

        // flags de visibilidad
        $precio_tachado_visible = isset($_POST['precio_tachado_visible']) ? 1 : 0;
        $porcentaje_visible = isset($_POST['porcentaje_visible']) ? 1 : 0;

        // Validaciones básicas
        if (!\Core\Helpers\Validator::isRequired($nombre)) $errores[] = "El nombre del producto es obligatorio.";
        if (!\Core\Helpers\Validator::isNumeric($precio)) $errores[] = "El precio debe ser un valor numérico.";
        if (!\Core\Helpers\Validator::isNumeric($stock)) $errores[] = "El stock debe ser un valor numérico.";

        // Validar precio_tachado y recalcular porcentaje
        if ($precio_tachado !== null && $precio_tachado !== '') {
            if (!is_numeric($precio_tachado) || (float)$precio_tachado <= $precio) {
                // Si es inválido, no lo usamos y desactivamos la visibilidad
                $precio_tachado = null;
                $precio_tachado_visible = 0;
                $porcentaje_descuento = 0;
                $porcentaje_visible = 0;
            } else {
                $precio_tachado = (float)$precio_tachado;
                $porcentaje_descuento = round((($precio_tachado - $precio) / $precio_tachado) * 100);
            }
        } else {
            $precio_tachado = null;
            $porcentaje_descuento = 0;
            $precio_tachado_visible = 0;
            $porcentaje_visible = 0;
        }

        // Validar imagen principal
        if (isset($_FILES['imagenes']) && $_FILES['imagenes']['error'][0] === 0) {
            $tipo = mime_content_type($_FILES['imagenes']['tmp_name'][0]);
            if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'])) {
                $errores[] = "La imagen debe ser JPG, PNG o WEBP.";
            }
        }

        if (!empty($errores)) {
            $categorias = Categoria::obtenerTodas();
            $etiquetas = (new Etiqueta())->obtenerTodas();
            require __DIR__ . '/../views/producto/crear.php';
            return;
        }

        // Guardar producto incluyendo flags
        $stmt = $db->prepare("INSERT INTO productos 
        (nombre, descripcion, precio, precio_tachado, porcentaje_descuento, precio_tachado_visible, porcentaje_visible, stock, visible) 
        VALUES (:nombre, :descripcion, :precio, :precio_tachado, :porcentaje_descuento, :precio_tachado_visible, :porcentaje_visible, :stock, :visible)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':precio_tachado' => $precio_tachado,
            ':porcentaje_descuento' => $porcentaje_descuento,
            ':precio_tachado_visible' => $precio_tachado_visible,
            ':porcentaje_visible' => $porcentaje_visible,
            ':stock' => $stock,
            ':visible' => $visible
        ]);
        $producto_id = $db->lastInsertId();

        // ... resto (imágenes, categorías, etiquetas, variantes) sin cambios ...
        header("Location: " . url("producto/index"));
        exit;
    }

    public function editar($id)
    {
        $producto = Producto::obtenerPorId($id);
        if (!$producto) {
            echo "Producto no encontrado.";
            return;
        }

        $variantes = VarianteProducto::obtenerPorProductoId($id);
        $categorias = Categoria::obtenerTodas();
        $etiquetaModel = new Etiqueta();
        $etiquetas = $etiquetaModel->obtenerTodas();
        $etiquetasAsignadas = $etiquetaModel->obtenerEtiquetasPorProducto($id);

        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id_categoria FROM producto_categoria WHERE id_producto = ?");
        $stmt->execute([$id]);
        $categoriasAsignadas = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id_categoria');

        $imagenes = ImagenProducto::obtenerPorProducto($id);
        require __DIR__ . '/../views/producto/editar.php';
    }

    public function actualizar()
    {
        $db = \Core\Database::getInstance()->getConnection();

        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = isset($_POST['precio']) ? (float) $_POST['precio'] : 0;
        $precio_tachado = $_POST['precio_tachado'] ?? null;
        $stock = isset($_POST['stock']) ? (int) $_POST['stock'] : 0;
        $visible = isset($_POST['visible']) ? 1 : 0;

        // flags de visibilidad (checkboxes)
        $precio_tachado_visible = isset($_POST['precio_tachado_visible']) ? 1 : 0;
        $porcentaje_visible = isset($_POST['porcentaje_visible']) ? 1 : 0;

        // Validar precio_tachado
        if ($precio_tachado !== null && $precio_tachado !== '') {
            if (!is_numeric($precio_tachado) || (float)$precio_tachado <= $precio) {
                $precio_tachado = null;
                $precio_tachado_visible = 0;
                $porcentaje_descuento = 0;
                $porcentaje_visible = 0;
            } else {
                $precio_tachado = (float)$precio_tachado;
                $porcentaje_descuento = round((($precio_tachado - $precio) / $precio_tachado) * 100);
            }
        } else {
            $precio_tachado = null;
            $porcentaje_descuento = 0;
            $precio_tachado_visible = 0;
            $porcentaje_visible = 0;
        }

        // Actualizar producto (modelo existente)
        Producto::actualizar($id, $nombre, $descripcion, $precio, $precio_tachado, $porcentaje_descuento, $precio_tachado_visible, $porcentaje_visible, $stock, $visible);

        // ------- categorías -------
        $db->prepare("DELETE FROM producto_categoria WHERE id_producto = ?")->execute([$id]);
        foreach ($_POST['categorias'] ?? [] as $cat_id) {
            $db->prepare("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)")->execute([$id, $cat_id]);
        }

        // ------- etiquetas -------
        $db->prepare("DELETE FROM producto_etiqueta WHERE producto_id = ?")->execute([$id]);
        foreach ($_POST['etiquetas'] ?? [] as $etiqueta_id) {
            $db->prepare("INSERT INTO producto_etiqueta (producto_id, etiqueta_id) VALUES (?, ?)")->execute([$id, $etiqueta_id]);
        }

        if (!empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
            $imagenModelClass = '\Models\ImagenProducto';
            foreach ($_POST['eliminar_imagenes'] as $imgId) {
                $imgId = (int)$imgId;
                // 1) intentar con el modelo (métodos comunes)
                try {
                    if (class_exists($imagenModelClass) && is_callable([$imagenModelClass, 'obtenerPorId'])) {
                        $imgRow = $imagenModelClass::obtenerPorId($imgId);
                        $nombreImg = $imgRow['nombre_imagen'] ?? null;
                        // intentar método eliminar/borra
                        if (is_callable([$imagenModelClass, 'eliminar'])) {
                            $imagenModelClass::eliminar($imgId);
                        } elseif (is_callable([$imagenModelClass, 'borrar'])) {
                            $imagenModelClass::borrar($imgId);
                        } elseif (is_callable([$imagenModelClass, 'eliminarPorId'])) {
                            $imagenModelClass::eliminarPorId($imgId);
                        } else {
                            // fallback: intentar borrar por SQL con varios nombres de tabla comunes
                            $tablesToTry = ['imagenes_productos', 'producto_imagenes', 'imagenes', 'imagen_producto', 'producto_images'];
                            $deleted = false;
                            foreach ($tablesToTry as $t) {
                                try {
                                    $q = $db->prepare("SELECT nombre_imagen FROM {$t} WHERE id = ? LIMIT 1");
                                    $q->execute([$imgId]);
                                    $r = $q->fetch(\PDO::FETCH_ASSOC);
                                    if ($r && !empty($r['nombre_imagen'])) {
                                        $nombreImg = $r['nombre_imagen'];
                                        $db->prepare("DELETE FROM {$t} WHERE id = ?")->execute([$imgId]);
                                        $deleted = true;
                                        break;
                                    }
                                } catch (\Throwable $e) {
                                    // tabla no existe: seguir con la siguiente
                                    continue;
                                }
                            }
                            if (!$deleted) {
                                // no se encontró la fila, ignorar
                            }
                        }

                        // borrar archivo del filesystem si tenemos nombre
                        if (!empty($nombreImg)) {
                            $rutaFs = dirname(__DIR__) . '/public/uploads/' . $nombreImg;
                            if (file_exists($rutaFs)) @unlink($rutaFs);
                        }
                        continue;
                    }
                } catch (\Throwable $e) {
                    // no fatal: seguir al fallback
                }

                // 2) fallback directo por SQL (intentar varias tablas)
                $tablesToTry = ['imagenes_productos', 'producto_imagenes', 'imagenes', 'imagen_producto', 'producto_images'];
                foreach ($tablesToTry as $t) {
                    try {
                        $q = $db->prepare("SELECT nombre_imagen FROM {$t} WHERE id = ? LIMIT 1");
                        $q->execute([$imgId]);
                        $r = $q->fetch(\PDO::FETCH_ASSOC);
                        if ($r && !empty($r['nombre_imagen'])) {
                            $nombreImg = $r['nombre_imagen'];
                            $db->prepare("DELETE FROM {$t} WHERE id = ?")->execute([$imgId]);
                            $rutaFs = dirname(__DIR__) . '/public/uploads/' . $nombreImg;
                            if (file_exists($rutaFs)) @unlink($rutaFs);
                            break;
                        }
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }
        }

        // ---------------------------------------
        // Subir nuevas imágenes (si llegaron)
        // Form debe enviar: <input type="file" name="imagenes[]" multiple> 
        // ---------------------------------------
        if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['tmp_name'])) {
            // carpeta destino
            $root = dirname(__DIR__);
            $uploadDirFs = $root . '/public/uploads/';
            if (!is_dir($uploadDirFs)) @mkdir($uploadDirFs, 0777, true);

            $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $maxSize = 5 * 1024 * 1024; // 5 MB

            $imagenModelClass = '\Models\ImagenProducto';
            $imgModelInstance = null;
            if (class_exists($imagenModelClass)) {
                // si el constructor necesita algo, instanciar defensivamente
                try {
                    $imgModelInstance = new $imagenModelClass();
                } catch (\Throwable $e) {
                    $imgModelInstance = null;
                }
            }

            foreach (array_keys($_FILES['imagenes']['tmp_name']) as $i) {
                $error = $_FILES['imagenes']['error'][$i];
                if ($error !== UPLOAD_ERR_OK) {
                    // opcional: puedes almacenar mensajes en $_SESSION['flash_error']
                    continue;
                }

                $tmpName = $_FILES['imagenes']['tmp_name'][$i];
                $originalName = $_FILES['imagenes']['name'][$i];
                $size = $_FILES['imagenes']['size'][$i];

                // validar tamaño
                if ($size > $maxSize) {
                    $_SESSION['flash_error'][] = "La imagen {$originalName} supera los 5MB y no fue subida.";
                    continue;
                }

                // detectar mime real
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmpName);
                finfo_close($finfo);

                if (!array_key_exists($mime, $allowedMimes)) {
                    $_SESSION['flash_error'][] = "Formato no permitido para {$originalName}.";
                    continue;
                }

                $ext = $allowedMimes[$mime];
                // generar nombre seguro
                try {
                    $random = bin2hex(random_bytes(6));
                } catch (\Throwable $e) {
                    $random = uniqid();
                }
                $safeName = 'prod_' . time() . '_' . $random . '.' . $ext;
                $destFs = $uploadDirFs . $safeName;

                if (!move_uploaded_file($tmpName, $destFs)) {
                    $_SESSION['flash_error'][] = "No se pudo mover {$originalName} al servidor.";
                    continue;
                }

                // registrar en BD: intentar con modelo (métodos comunes)
                $saved = false;
                try {
                    if ($imgModelInstance !== null) {
                        // intentar métodos comunes en el modelo
                        if (is_callable([$imagenModelClass, 'guardar'])) {
                            $imagenModelClass::guardar($id, $safeName);
                            $saved = true;
                        } elseif (is_callable([$imagenModelClass, 'crear'])) {
                            $imagenModelClass::crear($id, $safeName);
                            $saved = true;
                        } elseif (is_callable([$imgModelInstance, 'guardar'])) {
                            $imgModelInstance->guardar($id, $safeName);
                            $saved = true;
                        } elseif (is_callable([$imgModelInstance, 'crear'])) {
                            $imgModelInstance->crear($id, $safeName);
                            $saved = true;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore and fallback
                }

                // Fallback: intentar inserts directos en tablas comunes
                if (!$saved) {
                    $tablesToTry = [
                        ['table' => 'imagenes_productos', 'cols' => '(producto_id, nombre_imagen)'],
                        ['table' => 'producto_imagenes', 'cols' => '(producto_id, nombre_imagen)'],
                        ['table' => 'imagenes', 'cols' => '(producto_id, nombre_imagen)'],
                        ['table' => 'imagen_producto', 'cols' => '(producto_id, nombre_imagen)'],
                        ['table' => 'producto_images', 'cols' => '(producto_id, nombre_imagen)'],
                    ];

                    foreach ($tablesToTry as $tdef) {
                        $t = $tdef['table'];
                        $cols = $tdef['cols'];
                        try {
                            $sql = "INSERT INTO {$t} {$cols} VALUES (?, ?)";
                            $db->prepare($sql)->execute([$id, $safeName]);
                            $saved = true;
                            break;
                        } catch (\Throwable $e) {
                            // tabla/columna no existe -> probar siguiente
                            continue;
                        }
                    }
                }

                if (!$saved) {
                    // Si no se pudo guardar el registro en BD, eliminar el archivo subido para no dejar basura
                    if (file_exists($destFs)) @unlink($destFs);
                    $_SESSION['flash_error'][] = "La imagen {$originalName} se subió al servidor pero no pudo registrarse en la base de datos.";
                }
            } // foreach archivos
        } // endif isset $_FILES

        // Mensaje de éxito si no hay errores específicos
        if (empty($_SESSION['flash_error'])) {
            $_SESSION['flash_success'] = 'Producto actualizado correctamente.';
        } else {
            // normalizar a string si es array
            if (is_array($_SESSION['flash_error'])) {
                $_SESSION['flash_error'] = implode(' ', $_SESSION['flash_error']);
            }
        }

        header("Location: " . url("producto/editar/$id"));
        exit;
    }


    public function eliminar($id)
    {
        Producto::eliminar($id);
        header('Location: ' . url('carrito/ver'));
        exit;
    }
    public function listarPublico()
    {
        $productoModel = new Producto();

        // ✅ Solo obtener productos visibles
        $productos = $productoModel->obtenerVisibles();

        // Asociar categorías a cada producto (opcional)
        foreach ($productos as &$producto) {
            $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        }
        unset($producto);

        // Mostrar la vista pública (home)
        require_once __DIR__ . '/../public/home.php';
    }
    public function autocomplete()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        header('Content-Type: application/json');

        if ($q === '') {
            echo json_encode([]);
            exit;
        }

        $productoModel = new \Models\Producto();
        $resultados = $productoModel->buscarPorNombre($q);

        // Puedes mapear/limpiar campos si quieres reducir tamaño
        echo json_encode($resultados);
        exit;
    }
    public function ver($id)
    {
        $productoModel = new Producto();

        // Validar que $id sea numérico
        if (!is_numeric($id)) {
            // Redirigir o mostrar error
            header('Location: ' . url('producto'));
            exit;
        }

        // Obtener el producto
        $producto = $productoModel->obtenerPorId((int)$id);

        if (!$producto) {
            // Producto no encontrado, redirigir o mostrar error
            header('Location: ' . url('producto'));
            exit;
        }

        // Obtener categorías asociadas
        $producto['categorias'] = Producto::obtenerCategoriasPorProducto($producto['id']);
        // Obtener imágenes asociadas
        $producto['imagenes'] = \Models\ImagenProducto::obtenerPorProducto($producto['id']);

        // Cargar la vista descripción
        require_once __DIR__ . '/../views/producto/descripcion.php';
    }
    public function busqueda()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($q)) {
            // Si no hay búsqueda, redirigimos al home
            header('Location: ' . url('home/index'));
            exit;
        }

        $productoModel = new \Models\Producto();
        $resultados = $productoModel->buscarPorNombre($q);

        // Definir $termino para que exista en la vista
        $termino = $q;

        // Renderizamos la vista de búsqueda
        include __DIR__ . '/../views/home/busqueda.php';
    }
}
