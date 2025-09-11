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
     * Genera una descripción legible de la promoción
     * @param string $condicion JSON de la condición
     * @param string $accion JSON de la acción
     * @param string $tipo Tipo de la promoción
     * @return string Descripción de la promoción
     */
    public static function describirPromocion($condicion, $accion, $tipo)
    {
        $cond = json_decode($condicion, true);
        $acc = json_decode($accion, true);

        if (!$cond || !$acc || empty($cond['tipo']) || empty($acc['tipo'])) {
            return "Regla no válida";
        }

        switch ($cond['tipo']) {
            case 'subtotal_minimo':
                if ($acc['tipo'] === 'descuento_porcentaje') {
                    return "Descuento del {$acc['valor']}% por compras sobre S/{$cond['valor']}";
                } elseif ($acc['tipo'] === 'envio_gratis') {
                    return "Envío gratis por compras sobre S/{$cond['valor']}";
                }
                break;
            case 'primera_compra':
                if ($acc['tipo'] === 'envio_gratis') {
                    return "Envío gratis en la primera compra";
                }
                break;
            case 'cantidad_producto_identico':
                if ($acc['tipo'] === 'compra_n_paga_m') {
                    return "Lleva {$acc['cantidad_lleva']}, paga {$acc['cantidad_paga']} en producto ID {$cond['producto_id']}";
                } elseif ($acc['tipo'] === 'descuento_enesima_unidad') {
                    return "Descuento del {$acc['descuento_unidad']}% en la {$acc['numero_unidad']}ª unidad del producto ID {$cond['producto_id']}";
                }
                break;
            case 'cantidad_producto_categoria':
                if ($acc['tipo'] === 'descuento_menor_valor') {
                    return "Descuento del {$acc['valor']}% en el producto de menor valor de la categoría {$cond['categoria_id']} al comprar {$cond['cantidad_min']} productos";
                }
                break;
            case 'cantidad_total_productos':
                if ($acc['tipo'] === 'compra_n_paga_m_general') {
                    return "Lleva {$acc['cantidad_lleva']}, paga {$acc['cantidad_paga']} en cualquier combinación de productos";
                } elseif ($acc['tipo'] === 'descuento_producto_mas_barato') {
                    return "Descuento del {$acc['valor']}% en el producto de menor valor al comprar {$cond['cantidad_min']} productos";
                }
                break;
            case 'todos':
                if ($acc['tipo'] === 'envio_gratis') {
                    return "Envío gratis para todos los pedidos";
                }
                break;
            default:
                return "Regla personalizada";
        }
        return "Regla no válida";
    }
    /**
     * Verificar si el carrito cumple las condiciones de una promoción
     */
    private static function cumpleCondiciones($cond, $carrito, $usuario)
    {
        $cantidadTotal = array_sum(array_column($carrito, 'cantidad'));
        $subtotal = array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $carrito));

        $tipoCondicion = $cond['tipo'] ?? '';

        switch ($tipoCondicion) {
            case 'todos':
                return true;

            case 'subtotal_minimo':
                $valorMinimo = $cond['valor'] ?? 0;
                return $subtotal >= $valorMinimo;

            case 'primera_compra':
                return $usuario && ($usuario['total_compras'] ?? 0) == 0;

            case 'cantidad_producto_identico':
                $productoId = $cond['producto_id'] ?? 0;
                $cantidadRequerida = $cond['cantidad'] ?? 0;

                foreach ($carrito as $producto) {
                    if ($producto['id'] == $productoId && $producto['cantidad'] >= $cantidadRequerida) {
                        return true;
                    }
                }
                return false;

            case 'cantidad_producto_categoria':
                $categoriaId = $cond['categoria_id'] ?? 0;
                $cantidadRequerida = $cond['cantidad'] ?? 0;
                $cantidadEnCategoria = 0;

                foreach ($carrito as $producto) {
                    if (($producto['categoria_id'] ?? 0) == $categoriaId) {
                        $cantidadEnCategoria += $producto['cantidad'];
                    }
                }
                return $cantidadEnCategoria >= $cantidadRequerida;

            case 'cantidad_total_productos':
                $cantidadRequerida = $cond['cantidad'] ?? 0;
                return $cantidadTotal >= $cantidadRequerida;

            default:
                // Mantener compatibilidad con sistema antiguo si es necesario
                return true;
        }
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
            $tipoAccion = $accion['tipo'] ?? '';

            switch ($tipoAccion) {
                case 'descuento_porcentaje':
                    $descuentoTotal += $subtotal * ($accion['valor'] / 100);
                    break;

                case 'descuento_fijo':
                    $descuentoTotal += min($accion['valor'], $subtotal); // No más del subtotal
                    break;

                case 'envio_gratis':
                    $envioGratis = true;
                    break;

                // Para acciones antiguas (compatibilidad)
                default:
                    if ($tipoAccion === 'porcentaje') {
                        $descuentoTotal += $subtotal * ($accion['valor'] / 100);
                    } elseif ($tipoAccion === 'fijo') {
                        $descuentoTotal += min($accion['valor'], $subtotal);
                    }
                    break;
            }
        }

        // Asegurar que el descuento no sea mayor al subtotal
        $descuentoTotal = min($descuentoTotal, $subtotal);
        $total = max($subtotal - $descuentoTotal, 0);

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuentoTotal,
            'total' => $total,
            'envio_gratis' => $envioGratis
        ];
    }
}
