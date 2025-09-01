<?php

namespace Controllers;

use Models\Cupon;

class CuponController
{
    public function validar()
    {
        $codigo = $_POST['codigo'] ?? '';
        $carrito = $_SESSION['carrito'] ?? [];

        $cuponModel = new Cupon();
        $cupon = $cuponModel->obtenerPorCodigo($codigo);

        if (!$cupon) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Cupón inválido o vencido']);
            return;
        }

        // Validaciones básicas (fechas, estado activo)
        $hoy = date('Y-m-d');
        if (!$cupon['activo'] || $hoy < $cupon['fecha_inicio'] || $hoy > $cupon['fecha_fin']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Cupón inválido o vencido']);
            return;
        }

        // Validar usos globales
        if ($cupon['limite_uso'] > 0 && $cuponModel->contarUsos($cupon['id']) >= $cupon['limite_uso']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Este cupón alcanzó su límite de uso.']);
            return;
        }

        // Validar monto mínimo
        $subtotal = array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $carrito));
        if ($subtotal < $cupon['monto_minimo']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Monto mínimo no alcanzado.']);
            return;
        }

        // NOTA: La validación de clientes específicos se hará en el checkout
        // cuando tengamos la información del cliente
        if (!empty($cupon['usuarios_autorizados'])) {
            $_SESSION['cupon_aplicado'] = $cupon;
            echo json_encode([
                'status' => 'success', 
                'mensaje' => 'Cupón aplicado temporalmente. Se validará en el checkout.',
                'cupon' => $cupon,
                'advertencia' => 'Este cupón puede estar restringido a clientes específicos.'
            ]);
            return;
        } else {
            $_SESSION['cupon_aplicado'] = $cupon;
            echo json_encode(['status' => 'success', 'mensaje' => 'Cupón aplicado correctamente.', 'cupon' => $cupon]);
            return;
        }
    }

    /**
     * Remover cupón aplicado
     */
    public function remover()
    {
        unset($_SESSION['cupon_aplicado']);
        echo json_encode(['status' => 'success', 'mensaje' => 'Cupón removido correctamente']);
    }

    /**
     * Mostrar lista de cupones
     */
    public function index()
    {
        $cuponModel = new Cupon();
        $cupones = $cuponModel->obtenerTodos();
        $estadisticas = $cuponModel->obtenerEstadisticas();

        // Calcular estadísticas adicionales para cada cupón
        foreach ($cupones as &$cupon) {
            $cupon['usos_totales'] = $cuponModel->contarUsos($cupon['id']);
            $cupon['estado_vigencia'] = $this->determinarEstadoVigencia($cupon);
        }

        require_once __DIR__ . '/../views/cupon/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear()
    {
        require_once __DIR__ . '/../views/cupon/crear.php';
    }

    /**
     * Guardar nuevo cupón
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('cupon'));
            exit;
        }

        $cuponModel = new Cupon();
        $datos = $this->validarDatos($_POST);

        if (!empty($datos['errores'])) {
            require_once __DIR__ . '/../views/cupon/crear.php';
            return;
        }

        // Verificar que el código no exista
        if ($cuponModel->existeCodigo($datos['codigo'])) {
            $datos['errores']['codigo'] = 'El código del cupón ya existe';
            require_once __DIR__ . '/../views/cupon/crear.php';
            return;
        }

        // Procesar usuarios autorizados
        $usuarios_autorizados = null;
        if (!empty($datos['usuarios_autorizados'])) {
            // Convertir string de IDs separados por comas a array JSON
            $ids = array_map('trim', explode(',', $datos['usuarios_autorizados']));
            $ids = array_filter($ids, 'is_numeric'); // Solo números
            $ids = array_map('intval', $ids); // Convertir a enteros
            if (!empty($ids)) {
                $usuarios_autorizados = json_encode(array_values($ids));
            }
        }

        $cuponData = [
            'codigo' => $datos['codigo'],
            'tipo' => $datos['tipo'],
            'valor' => $datos['valor'],
            'monto_minimo' => $datos['monto_minimo'] ?: 0,
            'limite_uso' => $datos['limite_uso'] ?: null,
            'limite_por_usuario' => $datos['limite_por_usuario'] ?: null,
            'usuarios_autorizados' => $usuarios_autorizados,
            'activo' => isset($datos['activo']) ? 1 : 0,
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin']
        ];

        if ($cuponModel->crear($cuponData)) {
            header('Location: ' . url('cupon') . '?success=created');
        } else {
            $datos['error'] = 'Error al crear el cupón';
            require_once __DIR__ . '/../views/cupon/crear.php';
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id)
    {
        $cuponModel = new Cupon();
        $cupon = $cuponModel->obtenerPorId($id);

        if (!$cupon) {
            header('Location: ' . url('cupon') . '?error=not_found');
            exit;
        }

        require_once __DIR__ . '/../views/cupon/editar.php';
    }

    /**
     * Actualizar cupón
     */
    public function actualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('cupon'));
            exit;
        }

        $cuponModel = new Cupon();
        $cupon = $cuponModel->obtenerPorId($id);
        if (!$cupon) {
            header('Location: ' . url('cupon') . '?error=not_found');
            exit;
        }

        $datos = $this->validarDatos($_POST, $id);

        if (!empty($datos['errores'])) {
            require_once __DIR__ . '/../views/cupon/editar.php';
            return;
        }

        // Verificar que el código no exista (excluyendo el actual)
        if ($cuponModel->existeCodigo($datos['codigo'], $id)) {
            $datos['errores']['codigo'] = 'El código del cupón ya existe';
            require_once __DIR__ . '/../views/cupon/editar.php';
            return;
        }

        // Procesar usuarios autorizados
        $usuarios_autorizados = null;
        if (!empty($datos['usuarios_autorizados'])) {
            // Convertir string de IDs separados por comas a array JSON
            $ids = array_map('trim', explode(',', $datos['usuarios_autorizados']));
            $ids = array_filter($ids, 'is_numeric'); // Solo números
            $ids = array_map('intval', $ids); // Convertir a enteros
            if (!empty($ids)) {
                $usuarios_autorizados = json_encode(array_values($ids));
            }
        }

        $cuponData = [
            'codigo' => $datos['codigo'],
            'tipo' => $datos['tipo'],
            'valor' => $datos['valor'],
            'monto_minimo' => $datos['monto_minimo'] ?: 0,
            'limite_uso' => $datos['limite_uso'] ?: null,
            'limite_por_usuario' => $datos['limite_por_usuario'] ?: null,
            'usuarios_autorizados' => $usuarios_autorizados,
            'activo' => isset($datos['activo']) ? 1 : 0,
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin']
        ];

        if ($cuponModel->actualizar($id, $cuponData)) {
            header('Location: ' . url('cupon') . '?success=updated');
        } else {
            $datos['error'] = 'Error al actualizar el cupón';
            require_once __DIR__ . '/../views/cupon/editar.php';
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleEstado($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('cupon'));
            exit;
        }

        $cuponModel = new Cupon();
        $cupon = $cuponModel->obtenerPorId($id);
        if (!$cupon) {
            header('Location: ' . url('cupon') . '?error=not_found');
            exit;
        }

        if ($cuponModel->toggleEstado($id)) {
            header('Location: ' . url('cupon') . '?success=status_changed');
        } else {
            header('Location: ' . url('cupon') . '?error=status_change_failed');
        }
    }

    /**
     * Validar datos del formulario
     */
    private function validarDatos($datos, $excluirId = null)
    {
        $errores = [];

        // Código
        if (empty($datos['codigo'])) {
            $errores['codigo'] = 'El código es requerido';
        } elseif (strlen($datos['codigo']) < 3) {
            $errores['codigo'] = 'El código debe tener al menos 3 caracteres';
        } elseif (strlen($datos['codigo']) > 20) {
            $errores['codigo'] = 'El código no puede tener más de 20 caracteres';
        }

        // Tipo
        if (empty($datos['tipo'])) {
            $errores['tipo'] = 'El tipo es requerido';
        } elseif (!in_array($datos['tipo'], ['porcentaje', 'monto_fijo'])) {
            $errores['tipo'] = 'Tipo de descuento inválido';
        }

        // Valor
        if (empty($datos['valor'])) {
            $errores['valor'] = 'El valor es requerido';
        } elseif (!is_numeric($datos['valor'])) {
            $errores['valor'] = 'El valor debe ser numérico';
        } elseif ($datos['valor'] <= 0) {
            $errores['valor'] = 'El valor debe ser mayor a 0';
        } elseif ($datos['tipo'] === 'porcentaje' && $datos['valor'] > 100) {
            $errores['valor'] = 'El porcentaje no puede ser mayor a 100';
        }

        // Monto mínimo
        if (!empty($datos['monto_minimo']) && !is_numeric($datos['monto_minimo'])) {
            $errores['monto_minimo'] = 'El monto mínimo debe ser numérico';
        }

        // Límite de uso
        if (!empty($datos['limite_uso']) && (!is_numeric($datos['limite_uso']) || $datos['limite_uso'] <= 0)) {
            $errores['limite_uso'] = 'El límite de uso debe ser un número mayor a 0';
        }

        // Límite por usuario
        if (!empty($datos['limite_por_usuario']) && (!is_numeric($datos['limite_por_usuario']) || $datos['limite_por_usuario'] <= 0)) {
            $errores['limite_por_usuario'] = 'El límite por usuario debe ser un número mayor a 0';
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
     * Determinar el estado de vigencia de un cupón
     */
    private function determinarEstadoVigencia($cupon)
    {
        $hoy = date('Y-m-d');
        $inicio = $cupon['fecha_inicio'];
        $fin = $cupon['fecha_fin'];

        if (!$cupon['activo']) {
            return 'inactivo';
        } elseif ($hoy < $inicio) {
            return 'pendiente';
        } elseif ($hoy > $fin) {
            return 'expirado';
        } else {
            return 'vigente';
        }
    }

    /**
     * Ver historial de uso de un cupón
     */
    public function historial()
    {
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            header('Location: ' . url('cupon'));
            exit;
        }

        $cuponModel = new Cupon();
        $cupon = $cuponModel->obtenerPorId($id);
        if (!$cupon) {
            header('Location: ' . url('cupon'));
            exit;
        }

        $historial = $cuponModel->obtenerHistorialUso($id);
        $cupon['estado_vigencia'] = $this->determinarEstadoVigencia($cupon);

        require_once __DIR__ . '/../views/cupon/historial.php';
    }

    /**
     * Aplicar cupón en el checkout (para usar en CarritoController o PedidoController)
     */
    public function aplicarEnPedido($codigo, $cliente_id, $monto_total)
    {
        $cuponModel = new Cupon();
        $validacion = $cuponModel->puedeUsarCupon($cupon['id'] ?? 0, $cliente_id, $monto_total);

        if (!$validacion['valido']) {
            return ['exito' => false, 'mensaje' => $validacion['mensaje']];
        }

        $cupon = $validacion['cupon'];
        $descuento = 0;

        if ($cupon['tipo'] === 'porcentaje') {
            $descuento = $monto_total * ($cupon['valor'] / 100);
        } else {
            $descuento = $cupon['valor'];
        }

        return [
            'exito' => true,
            'cupon' => $cupon,
            'descuento' => $descuento,
            'nuevo_total' => max(0, $monto_total - $descuento)
        ];
    }
}
