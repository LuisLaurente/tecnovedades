<?php
namespace Controllers;

use Models\Cupon;

class CuponController
{
    public function validar() {
        $codigo = $_POST['codigo'] ?? '';
        $usuario = $_SESSION['usuario'] ?? null;
        $carrito = $_SESSION['carrito'] ?? [];

        $cuponModel = new Cupon();
        $cupon = $cuponModel->obtenerPorCodigo($codigo);

        if (!$cupon) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Cupón inválido o vencido']);
            return;
        }

        // Validar usos globales
        if ($cupon['limite_uso'] > 0 && $cuponModel->contarUsos($cupon['id']) >= $cupon['limite_uso']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Este cupón alcanzó su límite de uso.']);
            return;
        }

        // Validar usos por usuario
        if ($usuario && $cupon['limite_por_usuario'] > 0 &&
            $cuponModel->contarUsos($cupon['id'], $usuario['id']) >= $cupon['limite_por_usuario']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Ya usaste este cupón.']);
            return;
        }

        // Validar monto mínimo
        $subtotal = array_sum(array_map(fn($p)=> $p['precio']*$p['cantidad'], $carrito));
        if ($subtotal < $cupon['monto_minimo']) {
            echo json_encode(['status' => 'error', 'mensaje' => 'Monto mínimo no alcanzado.']);
            return;
        }

        // Validar usuario autorizado (si aplica)
        if (!empty($cupon['usuarios_autorizados']) && $usuario) {
            $autorizados = json_decode($cupon['usuarios_autorizados'], true);
            if (!in_array($usuario['id'], $autorizados)) {
                echo json_encode(['status' => 'error', 'mensaje' => 'Este cupón no está disponible para tu cuenta.']);
                return;
            }
        }

        // Si pasa todo:
        $_SESSION['cupon_aplicado'] = $cupon;
        echo json_encode(['status' => 'success', 'cupon' => $cupon]);
    }
}
