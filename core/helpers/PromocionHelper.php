<?php

namespace Core\Helpers;
use Models\Promocion; // ✅ Importamos el modelo Promocion
class PromocionHelper
{
    /**
     * Evaluar promociones activas según el carrito y el usuario
     * @param array $carrito - Lista de productos en el carrito (cada producto debe tener: id, nombre, precio, cantidad, etc.)
     * @param array|null $usuario - Datos del usuario logueado (si aplica, para tipo de usuario)
     * @return array - Promociones aplicables con detalle de acción
     */
    public static function evaluar($carrito, $usuario = null)
    {
        $promocionModel = new Promocion();
        $promociones = $promocionModel->obtenerPromocionesActivas();
        $aplicables = [];

        foreach ($promociones as $promo) {
            $cond = json_decode($promo['condicion'], true);
            $accion = json_decode($promo['accion'], true);

            if (self::cumpleCondiciones($cond, $carrito, $usuario)) {
                $aplicables[] = [
                    'promocion' => $promo,
                    'accion' => $accion
                ];
            }
        }

        return self::filtrarPromociones($aplicables);
    }

    /**
     * Verificar si el carrito cumple las condiciones de una promoción
     */
    private static function cumpleCondiciones($cond, $carrito, $usuario)
    {
        $cantidad = array_sum(array_column($carrito, 'cantidad'));
        $monto = array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $carrito));

        // Condición: cantidad mínima de productos
        if (isset($cond['min_cantidad']) && $cantidad < $cond['min_cantidad']) {
            return false;
        }

        // Condición: monto mínimo del carrito
        if (isset($cond['min_monto']) && $monto < $cond['min_monto']) {
            return false;
        }

        // Condición: tipo de usuario (si aplica)
        if (isset($cond['tipo_usuario'])) {
            if (!$usuario || !isset($usuario['tipo']) || $usuario['tipo'] !== $cond['tipo_usuario']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filtrar promociones según exclusividad, acumulabilidad y prioridad
     */
    private static function filtrarPromociones($aplicables)
    {
        // Ordenar por prioridad (menor número = mayor prioridad)
        usort($aplicables, fn($a, $b) => $a['promocion']['prioridad'] <=> $b['promocion']['prioridad']);

        $resultado = [];

        foreach ($aplicables as $promo) {
            // Si la promoción es exclusiva y ya hay una aplicada, detenemos
            if ($promo['promocion']['exclusivo'] && !empty($resultado)) {
                break;
            }

            $resultado[] = $promo;

            // Si no es acumulable, detenemos después de agregarla
            if (!$promo['promocion']['acumulable']) {
                break;
            }
        }

        return $resultado;
    }

    /**
     * Calcular el total con las promociones aplicadas
     * @param array $carrito
     * @param array $promociones
     * @return array - Detalle con subtotal, descuentos y total final
     */
    public static function calcularTotales($carrito, $promociones)
    {
        $subtotal = array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $carrito));
        $descuentoTotal = 0;
        $envioGratis = false;

        foreach ($promociones as $p) {
            $accion = $p['accion'];

            if ($accion['tipo'] === 'descuento_porcentaje') {
                $descuentoTotal += $subtotal * ($accion['valor'] / 100);
            } elseif ($accion['tipo'] === 'descuento_fijo') {
                $descuentoTotal += $accion['valor'];
            } elseif ($accion['tipo'] === 'envio_gratis') {
                $envioGratis = true;
            }
        }

        $total = max($subtotal - $descuentoTotal, 0);

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuentoTotal,
            'total' => $total,
            'envio_gratis' => $envioGratis
        ];
    }
}
