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
     * Mostrar listado de promociones
     */
    public function index()
    {
        $promociones = $this->promocionModel->obtenerTodas();
        $estadisticas = $this->promocionModel->obtenerEstadisticas();

        // Calcular estadísticas adicionales para cada promoción
        foreach ($promociones as &$promocion) {
            $promocion['estado_vigencia'] = $this->determinarEstadoVigencia($promocion);
        }

        require_once __DIR__ . '/../views/promocion/index.php';
    }

    /**
     * Mostrar formulario para crear promoción
     */
    public function crear()
    {
        require_once __DIR__ . '/../views/promocion/crear.php';
    }

    /**
     * Procesar creación de promoción
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->validarDatos($_POST);
            
            if (!empty($datos['errores'])) {
                require_once __DIR__ . '/../views/promocion/crear.php';
                return;
            }

            $data = [
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
                'tipo' => $datos['tipo'],
                'valor' => $datos['valor'],
                'condicion' => $this->procesarCondiciones($datos),
                'accion' => $this->procesarAcciones($datos),
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'activo' => isset($datos['activo']) ? 1 : 0
            ];

            if ($this->promocionModel->crear($data)) {
                header('Location: ' . url('promocion') . '?success=created');
                exit;
            } else {
                $datos['error'] = "Error al crear la promoción";
                require_once __DIR__ . '/../views/promocion/crear.php';
            }
        }
    }

    /**
     * Mostrar formulario para editar promoción
     */
    public function editar($id)
    {
        $promocion = $this->promocionModel->obtenerPorId($id);
        if (!$promocion) {
            header('Location: ' . url('promocion') . '?error=not_found');
            exit;
        }
        
        // Decodificar JSON para mostrar en el formulario
        $promocion['condiciones'] = json_decode($promocion['condicion'] ?? '{}', true);
        $promocion['acciones'] = json_decode($promocion['accion'] ?? '{}', true);
        
        require_once __DIR__ . '/../views/promocion/editar.php';
    }

    /**
     * Procesar actualización de promoción
     */
    public function actualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $promocion = $this->promocionModel->obtenerPorId($id);
            if (!$promocion) {
                header('Location: ' . url('promocion') . '?error=not_found');
                exit;
            }

            $datos = $this->validarDatos($_POST, $id);
            
            if (!empty($datos['errores'])) {
                $datos['promocion'] = $promocion;
                require_once __DIR__ . '/../views/promocion/editar.php';
                return;
            }

            $data = [
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
                'tipo' => $datos['tipo'],
                'valor' => $datos['valor'],
                'condicion' => $this->procesarCondiciones($datos),
                'accion' => $this->procesarAcciones($datos),
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'activo' => isset($datos['activo']) ? 1 : 0
            ];

            if ($this->promocionModel->actualizar($id, $data)) {
                header('Location: ' . url('promocion') . '?success=updated');
                exit;
            } else {
                $datos['error'] = "Error al actualizar la promoción";
                $datos['promocion'] = $promocion;
                require_once __DIR__ . '/../views/promocion/editar.php';
            }
        }
    }

    /**
     * Eliminar promoción
     */
    public function eliminar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('promocion'));
            exit;
        }

        $promocion = $this->promocionModel->obtenerPorId($id);
        if (!$promocion) {
            header('Location: ' . url('promocion') . '?error=not_found');
            exit;
        }

        if ($this->promocionModel->eliminar($id)) {
            header('Location: ' . url('promocion') . '?success=deleted');
        } else {
            header('Location: ' . url('promocion') . '?error=delete_failed');
        }
        exit;
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleEstado($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('promocion'));
            exit;
        }

        $promocion = $this->promocionModel->obtenerPorId($id);
        if (!$promocion) {
            header('Location: ' . url('promocion') . '?error=not_found');
            exit;
        }

        if ($this->promocionModel->toggleEstado($id)) {
            header('Location: ' . url('promocion') . '?success=status_changed');
        } else {
            header('Location: ' . url('promocion') . '?error=status_change_failed');
        }
        exit;
    }

    /**
     * Procesar condiciones del formulario
     */
    private function procesarCondicion($post)
    {
        $condicion = [];
        
        if (!empty($post['min_monto'])) {
            $condicion['min_monto'] = (float)$post['min_monto'];
        }
        
        if (!empty($post['tipo_usuario'])) {
            $condicion['tipo_usuario'] = $post['tipo_usuario'];
        }
        
        if (!empty($post['categoria_id'])) {
            $condicion['categoria_id'] = (int)$post['categoria_id'];
        }
        
        if (!empty($post['producto_id'])) {
            $condicion['producto_id'] = (int)$post['producto_id'];
        }
        
        return $condicion;
    }

    /**
     * Procesar acciones del formulario
     */
    private function procesarAccion($post)
    {
        $accion = [];
        
        if (!empty($post['tipo_accion'])) {
            $accion['tipo'] = $post['tipo_accion'];
            
            switch ($post['tipo_accion']) {
                case 'descuento_porcentaje':
                case 'descuento_fijo':
                    $accion['valor'] = (float)($post['valor_descuento'] ?? 0);
                    break;
                case 'envio_gratis':
                    // No necesita valor adicional
                    break;
                case 'producto_gratis':
                    $accion['producto_id'] = (int)($post['producto_gratis_id'] ?? 0);
                    break;
            }
        }
        
        return $accion;
    }

    /**
     * Obtener estadísticas de promociones
     */
    public function estadisticas()
    {
        $stats = $this->promocionModel->obtenerEstadisticas();
        header('Content-Type: application/json');
        echo json_encode($stats);
    }

    /**
     * Validar datos del formulario
     */
    private function validarDatos($datos, $excluirId = null)
    {
        $errores = [];

        // Nombre
        if (empty($datos['nombre'])) {
            $errores['nombre'] = 'El nombre es requerido';
        } elseif (strlen($datos['nombre']) < 3) {
            $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres';
        } elseif (strlen($datos['nombre']) > 255) {
            $errores['nombre'] = 'El nombre no puede tener más de 255 caracteres';
        }

        // Descripción
        if (empty($datos['descripcion'])) {
            $errores['descripcion'] = 'La descripción es requerida';
        }

        // Tipo
        if (empty($datos['tipo'])) {
            $errores['tipo'] = 'El tipo es requerido';
        } elseif (!in_array($datos['tipo'], ['porcentaje', 'monto_fijo', 'compra_x_paga_y', 'envio_gratis'])) {
            $errores['tipo'] = 'Tipo de promoción inválido';
        }

        // Valor
        if (empty($datos['valor']) && $datos['tipo'] !== 'envio_gratis') {
            $errores['valor'] = 'El valor es requerido';
        } elseif (!empty($datos['valor']) && !is_numeric($datos['valor'])) {
            $errores['valor'] = 'El valor debe ser numérico';
        } elseif (!empty($datos['valor']) && $datos['valor'] <= 0) {
            $errores['valor'] = 'El valor debe ser mayor a 0';
        } elseif ($datos['tipo'] === 'porcentaje' && $datos['valor'] > 100) {
            $errores['valor'] = 'El porcentaje no puede ser mayor a 100';
        }

        // Fechas
        if (empty($datos['fecha_inicio'])) {
            $errores['fecha_inicio'] = 'La fecha de inicio es requerida';
        }

        if (empty($datos['fecha_fin'])) {
            $errores['fecha_fin'] = 'La fecha de fin es requerida';
        }

        if (empty($errores['fecha_inicio']) && empty($errores['fecha_fin'])) {
            if (strtotime($datos['fecha_inicio']) > strtotime($datos['fecha_fin'])) {
                $errores['fecha_fin'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }

        return array_merge($datos, ['errores' => $errores]);
    }

    /**
     * Procesar condiciones del formulario
     */
    private function procesarCondiciones($datos)
    {
        $condiciones = [];
        
        if (!empty($datos['min_cantidad'])) {
            $condiciones['min_cantidad'] = (int)$datos['min_cantidad'];
        }
        
        if (!empty($datos['monto_minimo'])) {
            $condiciones['monto_minimo'] = (float)$datos['monto_minimo'];
        }
        
        if (!empty($datos['categorias'])) {
            $condiciones['categorias'] = is_array($datos['categorias']) ? $datos['categorias'] : explode(',', $datos['categorias']);
        }
        
        if (!empty($datos['productos'])) {
            $condiciones['productos'] = is_array($datos['productos']) ? $datos['productos'] : explode(',', $datos['productos']);
        }
        
        return json_encode($condiciones);
    }

    /**
     * Procesar acciones del formulario
     */
    private function procesarAcciones($datos)
    {
        $acciones = [];
        
        switch ($datos['tipo']) {
            case 'porcentaje':
                $acciones['descuento_porcentaje'] = (float)$datos['valor'];
                $acciones['mensaje'] = $datos['mensaje'] ?? "¡{$datos['valor']}% de descuento!";
                break;
            case 'monto_fijo':
                $acciones['descuento_fijo'] = (float)$datos['valor'];
                $acciones['mensaje'] = $datos['mensaje'] ?? "¡S/ {$datos['valor']} de descuento!";
                break;
            case 'compra_x_paga_y':
                $acciones['lleva'] = (int)$datos['valor'];
                $acciones['paga'] = (int)($datos['paga'] ?? 1);
                $acciones['mensaje'] = $datos['mensaje'] ?? "¡Compra {$datos['valor']} y paga {$acciones['paga']}!";
                break;
            case 'envio_gratis':
                $acciones['envio_gratis'] = true;
                $acciones['mensaje'] = $datos['mensaje'] ?? "¡Envío gratis!";
                break;
        }
        
        return json_encode($acciones);
    }

    /**
     * Determinar el estado de vigencia de una promoción
     */
    private function determinarEstadoVigencia($promocion)
    {
        $hoy = date('Y-m-d');
        $inicio = $promocion['fecha_inicio'];
        $fin = $promocion['fecha_fin'];

        if (!$promocion['activo']) {
            return 'inactivo';
        } elseif ($hoy < $inicio) {
            return 'pendiente';
        } elseif ($hoy > $fin) {
            return 'expirado';
        } else {
            return 'vigente';
        }
    }
}
