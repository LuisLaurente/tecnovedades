<?php

namespace Controllers;

use Models\Promocion;
use Core\Database;

class PromocionController
{
    private $promocionModel;

    public function __construct()
    {
        $this->promocionModel = new Promocion();
    }

    /**
     * Muestra el listado de todas las promociones.
     */
    public function index()
    {
        $promociones = $this->promocionModel->obtenerTodas();
        require_once __DIR__ . '/../views/promocion/index.php';
    }

    /**
     * Muestra el formulario para crear una nueva promoción.
     */
    public function crear()
    {
        // Aquí podrías cargar datos necesarios para los desplegables, como categorías y productos.
        // $categorias = $this->categoriaModel->obtenerTodas();
        // $productos = $this->productoModel->obtenerTodos();
        require_once __DIR__ . '/../views/promocion/crear.php';
    }

    /**
     * Guarda una nueva promoción en la base de datos.
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . url('promocion/crear'));
            exit;
        }

        try {
            $datos = [
                'nombre'       => $_POST['nombre'] ?? '',
                'prioridad'    => $_POST['prioridad'] ?? 3,
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'fecha_fin'    => $_POST['fecha_fin'] ?? '',
                'activo'       => isset($_POST['activo']) ? 1 : 0,
                'acumulable'   => isset($_POST['acumulable']) ? 1 : 0,
                'exclusivo'    => isset($_POST['exclusivo']) ? 1 : 0,
                'condicion'    => $this->construirCondicion($_POST),
                'accion'       => $this->construirAccion($_POST),
                'tipo'         => $_POST['tipo_condicion'] ?? 'general'
            ];

            $resultado = $this->promocionModel->crear($datos);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Promoción creada correctamente.";
                header("Location: " . url('promocion/index'));
            } else {
                throw new \Exception("Hubo un problema al guardar la promoción.");
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "❌ " . $e->getMessage();
            header("Location: " . url('promocion/crear'));
        }
        exit;
    }

    /**
     * Muestra el formulario para editar una promoción existente.
     */
    public function editar($id)
    {
        $promocion = $this->promocionModel->obtenerPorId($id);
        if (!$promocion) {
            $_SESSION['error'] = "❌ Promoción no encontrada.";
            header("Location: " . url('promocion/index'));
            exit;
        }
        // Los datos de condicion y accion ya vienen decodificados desde el modelo.
        include __DIR__ . '/../views/promocion/editar.php';
    }

    /**
     * Actualiza una promoción existente en la base de datos.
     */
public function actualizar($id)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . url('promocion/editar/' . $id));
        exit;
    }

    try {
        $promocionExistente = $this->promocionModel->obtenerPorId($id);
        if (!$promocionExistente) {
            throw new \Exception("Promoción no encontrada.");
        }

        $datos = [
            'nombre'       => $_POST['nombre'] ?? '',
            'prioridad'    => $_POST['prioridad'] ?? 3,
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_fin'    => $_POST['fecha_fin'] ?? '',
            'activo'       => isset($_POST['activo']) ? 1 : 0,
            'acumulable'   => isset($_POST['acumulable']) ? 1 : 0,
            'exclusivo'    => isset($_POST['exclusivo']) ? 1 : 0,
            'condicion'    => $this->construirCondicion($_POST),
            'accion'       => $this->construirAccion($_POST),
            'tipo'         => $_POST['tipo_condicion'] ?? $promocionExistente['tipo'], // Preservar tipo existente
            'codigo'       => $promocionExistente['codigo'] // Mantener el código existente
        ];

        $resultado = $this->promocionModel->actualizar($id, $datos);

        if ($resultado) {
            $_SESSION['mensaje'] = "✅ Promoción actualizada correctamente.";
            header("Location: " . url('promocion/index'));
        } else {
            throw new \Exception("No se pudo actualizar la promoción.");
        }
    } catch (\Exception $e) {
        $_SESSION['error'] = "❌ " . $e->getMessage();
        header("Location: " . url('promocion/editar/' . $id));
    }
    exit;
}

    /**
     * Construye el array de condición basado en los datos del POST.
     * @return string JSON con la estructura de la condición.
     */
    private function construirCondicion($post)
    {
        $tipoCondicion = $post['tipo_condicion'] ?? '';
        if (empty($tipoCondicion)) {
            throw new \Exception("El tipo de condición no se especificó. Por favor, seleccione una regla válida.");
        }

        $condicion = ['tipo' => $tipoCondicion];

        switch ($tipoCondicion) {
            case 'subtotal_minimo':
                $condicion['valor'] = (float)($post['cond_subtotal_minimo'] ?? 0);
                if ($condicion['valor'] <= 0) {
                    throw new \Exception("El monto mínimo del carrito debe ser mayor a 0.");
                }
                break;
            case 'primera_compra':
                // No necesita valor adicional.
                break;
            case 'cantidad_producto_identico':
                $condicion['producto_id'] = (int)($post['cond_producto_id'] ?? 0);
                $condicion['cantidad_min'] = (int)($post['cond_cantidad_min'] ?? 1);
                if ($condicion['producto_id'] <= 0 || $condicion['cantidad_min'] <= 0) {
                    throw new \Exception("El ID del producto y la cantidad mínima deben ser mayores a 0.");
                }
                break;
            case 'cantidad_producto_categoria':
                $condicion['categoria_id'] = (int)($post['cond_categoria_id'] ?? 0);
                $condicion['cantidad_min'] = (int)($post['cond_cantidad_min_categoria'] ?? 1);
                if ($condicion['categoria_id'] <= 0 || $condicion['cantidad_min'] <= 0) {
                    throw new \Exception("El ID de la categoría y la cantidad mínima deben ser mayores a 0.");
                }
                break;
            case 'cantidad_total_productos':
                $condicion['cantidad_min'] = (int)($post['cond_cantidad_total'] ?? 0);
                if ($condicion['cantidad_min'] <= 0) {
                    throw new \Exception("La cantidad mínima de productos debe ser mayor a 0.");
                }
                break;
            default:
                throw new \Exception("Tipo de condición no reconocido: " . htmlspecialchars($tipoCondicion));
        }

        return json_encode($condicion);
    }

    /**
     * Construye el array de acción basado en los datos del POST.
     * @return string JSON con la estructura de la acción.
     */
 private function construirAccion($post)
{
    $tipoAccion = $post['tipo_accion'] ?? '';
    if (empty($tipoAccion)) {
        throw new \Exception("El tipo de acción no se especificó. Por favor, seleccione una regla válida.");
    }

    $accion = ['tipo' => $tipoAccion];

    switch ($tipoAccion) {
        case 'descuento_porcentaje':
        case 'descuento_fijo':
            $accion['valor'] = (float)($post['accion_valor_descuento'] ?? 0);
            if ($accion['valor'] <= 0) {
                throw new \Exception("El valor del descuento debe ser mayor a 0.");
            }
            break;
        case 'envio_gratis':
            break;
        case 'compra_n_paga_m':
            $accion['cantidad_lleva'] = (int)($post['accion_cantidad_lleva'] ?? 0);
            $accion['cantidad_paga'] = (int)($post['accion_cantidad_paga'] ?? 0);
            if ($accion['cantidad_lleva'] <= $accion['cantidad_paga'] || $accion['cantidad_paga'] <= 0) {
                throw new \Exception("Valores inválidos para la promoción N x M. 'Lleva' debe ser mayor que 'Paga' y ambos deben ser mayores a 0.");
            }
            break;
        case 'compra_n_paga_m_general':
            $accion['cantidad_lleva'] = (int)($post['accion_cantidad_lleva_general'] ?? 0);
            $accion['cantidad_paga'] = (int)($post['accion_cantidad_paga_general'] ?? 0);
            if ($accion['cantidad_lleva'] <= $accion['cantidad_paga'] || $accion['cantidad_paga'] <= 0) {
                throw new \Exception("Valores inválidos para la promoción N x M General. 'Lleva' debe ser mayor que 'Paga' y ambos deben ser mayores a 0.");
            }
            $accion['aplica_a'] = 'menor_valor';
            break;
        case 'descuento_enesima_unidad':
            $accion['numero_unidad'] = (int)($post['accion_numero_unidad'] ?? 0);
            $accion['descuento_unidad'] = (float)($post['accion_descuento_unidad'] ?? 0);
            if ($accion['numero_unidad'] <= 1 || $accion['descuento_unidad'] <= 0) {
                throw new \Exception("Valores inválidos para el descuento en la N-ésima unidad. La unidad debe ser mayor a 1 y el descuento mayor a 0.");
            }
            break;
        case 'descuento_menor_valor':
            $accion['valor'] = (float)($post['accion_descuento_menor_valor'] ?? 0);
            if ($accion['valor'] <= 0) {
                throw new \Exception("El porcentaje de descuento para el producto de menor valor debe ser mayor a 0.");
            }
            break;
        case 'descuento_producto_mas_barato':
            $accion['valor'] = (float)($post['accion_descuento_porcentaje'] ?? 0);
            if ($accion['valor'] <= 0) {
                throw new \Exception("El porcentaje de descuento para el producto de menor valor debe ser mayor a 0.");
            }
            $accion['aplica_a'] = 'menor_valor';
            break;
        default:
            throw new \Exception("Tipo de acción no reconocido: " . htmlspecialchars($tipoAccion));
    }

    return json_encode($accion);
}

    /**
     * Elimina una promoción de forma permanente.
     */
    public function eliminar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('promocion/index'));
            exit;
        }

        if ($this->promocionModel->eliminar($id)) {
            $_SESSION['mensaje'] = "🗑️ Promoción eliminada correctamente.";
        } else {
            $_SESSION['error'] = "❌ No se pudo eliminar la promoción o no fue encontrada.";
        }
        header('Location: ' . url('promocion/index'));
        exit;
    }

    /**
     * Cambia el estado 'activo' de una promoción.
     */
    public function toggleEstado($id)
    {
        $promocion = $this->promocionModel->obtenerPorId($id);
        if (!$promocion) {
            $_SESSION['error'] = "❌ Promoción no encontrada.";
            header('Location: ' . url('promocion/index'));
            exit;
        }

        $nuevoEstado = $promocion['activo'] ? 0 : 1;
        if ($this->promocionModel->actualizarCampo($id, 'activo', $nuevoEstado)) {
            $_SESSION['mensaje'] = $nuevoEstado ? "✅ Promoción activada." : "☑️ Promoción desactivada.";
        } else {
            $_SESSION['error'] = "❌ Error al cambiar el estado de la promoción.";
        }

        header('Location: ' . url('promocion/index'));
        exit;
    }
}
