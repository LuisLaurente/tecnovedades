<?php

namespace Core\Helpers;

use Models\Promocion;

class PromocionHelper
{
    /**
     * Evaluar promociones activas según el carrito y el usuario
     * @param array $carrito - Lista de productos en el carrito
     * @param array|null $usuario - Datos del usuario logueado
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
     * Aplica las promociones al carrito y devuelve un resumen de los totales y descuentos.
     * @param array $carrito - Lista de productos en el carrito (por referencia: &$carrito)
     * @param array|null $usuario - Datos del usuario
     * @return array - Array con el carrito modificado y los totales
     */
    public static function aplicarPromociones(&$carrito, $usuario = null)
    {
        // 1. Resetear cualquier descuento previo
        foreach ($carrito as &$item) {
            $item['precio_final'] = $item['precio'] * $item['cantidad'];
            $item['descuento_aplicado'] = 0;
            $item['promociones'] = [];
        }

        // 2. Obtener promociones aplicables
        $promocionesAplicables = self::evaluar($carrito, $usuario);

        // 3. Inicializar variables
        $descuentoTotal = 0;
        $envioGratis = false;
        $descuentosPorPromocion = [];

        // 4. Aplicar promociones por item primero y acumular descuentos
        foreach ($promocionesAplicables as $promoData) {
            $accion = $promoData['accion'];
            $tipoAccion = $accion['tipo'] ?? '';
            $montoDescuento = 0;

            switch ($tipoAccion) {
                case 'compra_n_paga_m':
                    $montoDescuento = self::aplicarNxM($carrito, $accion, $promoData);
                    break;

                case 'descuento_enesima_unidad':
                    $montoDescuento = self::aplicarDescuentoUnidad($carrito, $accion, $promoData);
                    break;

                case 'descuento_menor_valor':
                    $montoDescuento = self::aplicarDescuentoMenorValor($carrito, $accion, $promoData);
                    break;

                case 'compra_n_paga_m_general':
                    $montoDescuento = self::aplicarNxMGeneral($carrito, $accion, $promoData);
                    break;

                case 'descuento_producto_mas_barato':
                    $montoDescuento = self::aplicarDescuentoMasBarato($carrito, $accion, $promoData);
                    break;

                case 'envio_gratis':
                    $envioGratis = true;
                    // El descuento es el costo de envío, que se aplica en el controlador
                    $descuentosPorPromocion[] = [
                        'nombre' => $promoData['promocion']['nombre'],
                        'monto' => 'Gratis'
                    ];
                    break;
            }

            if ($montoDescuento > 0) {
                $descuentosPorPromocion[] = [
                    'nombre' => $promoData['promocion']['nombre'],
                    'monto' => $montoDescuento
                ];
                $descuentoTotal += $montoDescuento;
            }
        }

        // 5. Recalcular subtotal para aplicar descuentos generales
        $subtotal = array_sum(array_map(fn($p) => $p['precio_final'], $carrito));
        
        // 6. Aplicar descuentos generales (porcentaje o fijo)
        foreach ($promocionesAplicables as $promoData) {
            $accion = $promoData['accion'];
            $tipoAccion = $accion['tipo'] ?? '';
            $montoDescuentoGeneral = 0;

            switch ($tipoAccion) {
                case 'descuento_porcentaje':
                    $montoDescuentoGeneral = $subtotal * ($accion['valor'] / 100);
                    self::distribuirDescuentoGeneral($carrito, $montoDescuentoGeneral, $subtotal);
                    $descuentosPorPromocion[] = [
                        'nombre' => $promoData['promocion']['nombre'],
                        'monto' => $montoDescuentoGeneral
                    ];
                    $descuentoTotal += $montoDescuentoGeneral;
                    break;

                case 'descuento_fijo':
                    $montoDescuentoGeneral = min($accion['valor'], $subtotal);
                    self::distribuirDescuentoGeneral($carrito, $montoDescuentoGeneral, $subtotal);
                    $descuentosPorPromocion[] = [
                        'nombre' => $promoData['promocion']['nombre'],
                        'monto' => $montoDescuentoGeneral
                    ];
                    $descuentoTotal += $montoDescuentoGeneral;
                    break;
            }
        }
        
        // 7. Calcular total final
        $total = max($subtotal - $descuentoTotal, 0);

        return [
            'carrito' => $carrito,
            'subtotal' => $subtotal,
            'descuento' => $descuentoTotal,
            'total' => $total,
            'envio_gratis' => $envioGratis,
            'promociones_aplicadas' => $descuentosPorPromocion
        ];
    }

    // --- MÉTODOS PRIVADOS CORREGIDOS PARA DEVOLVER EL MONTO DE DESCUENTO ---

    /**
     * Aplicar promoción NxM a un producto específico
     * @return float
     */
    private static function aplicarNxM(&$carrito, $accion, $promoData)
    {
        $productoId = $accion['producto_id'] ?? 0;
        $lleva = $accion['cantidad_lleva'] ?? 0;
        $paga = $accion['cantidad_paga'] ?? 0;
        $descuento = 0;

        if ($lleva <= 0 || $paga <= 0) return 0;

        foreach ($carrito as &$item) {
            if ($item['id'] == $productoId && $item['cantidad'] >= $lleva) {
                $grupos = floor($item['cantidad'] / $lleva);
                $unidadesAPagar = ($grupos * $paga) + ($item['cantidad'] % $lleva);

                $precioOriginal = $item['precio'] * $item['cantidad'];
                $nuevoPrecio = $unidadesAPagar * $item['precio'];
                $descuento = $precioOriginal - $nuevoPrecio;

                $item['precio_final'] -= $descuento;
                $item['descuento_aplicado'] += $descuento;
                // Agregamos el nombre de la promoción para trazarla
                $item['promociones'][] = $promoData['promocion']['nombre'];
                break;
            }
        }
        return $descuento;
    }

    /**
     * Aplicar descuento a una unidad específica
     * @return float
     */
    private static function aplicarDescuentoUnidad(&$carrito, $accion, $promoData)
    {
        $productoId = $accion['producto_id'] ?? 0;
        $numeroUnidad = $accion['numero_unidad'] ?? 1;
        $descuentoPorcentaje = $accion['descuento_unidad'] ?? 0;
        $descuento = 0;

        foreach ($carrito as &$item) {
            if ($item['id'] == $productoId && $item['cantidad'] >= $numeroUnidad) {
                $descuento = $item['precio'] * ($descuentoPorcentaje / 100);
                $item['precio_final'] -= $descuento;
                $item['descuento_aplicado'] += $descuento;
                $item['promociones'][] = $promoData['promocion']['nombre'];
                break;
            }
        }
        return $descuento;
    }

    /**
     * Aplicar descuento al producto de menor valor de una categoría
     * @return float
     */
    private static function aplicarDescuentoMenorValor(&$carrito, $accion, $promoData)
    {
        $categoriaId = $accion['categoria_id'] ?? 0;
        $descuentoPorcentaje = $accion['valor'] ?? 0;
        $descuento = 0;

        // Encontrar el producto más barato de la categoría
        $productoMasBarato = null;
        $precioMasBajo = PHP_FLOAT_MAX;

        foreach ($carrito as &$item) {
            if (($item['categoria_id'] ?? 0) == $categoriaId) {
                $precioUnitario = $item['precio'];
                if ($precioUnitario < $precioMasBajo) {
                    $precioMasBajo = $precioUnitario;
                    $productoMasBarato = &$item;
                }
            }
        }

        if ($productoMasBarato) {
            $descuento = ($productoMasBarato['precio'] * $productoMasBarato['cantidad']) * ($descuentoPorcentaje / 100);
            $productoMasBarato['precio_final'] -= $descuento;
            $productoMasBarato['descuento_aplicado'] += $descuento;
            $productoMasBarato['promociones'][] = $promoData['promocion']['nombre'];
        }
        return $descuento;
    }

    /**
     * Aplicar NxM general a cualquier combinación de productos
     * @return float
     */
    private static function aplicarNxMGeneral(&$carrito, $accion, $promoData)
    {
        $lleva = $accion['cantidad_lleva'] ?? 0;
        $paga = $accion['cantidad_paga'] ?? 0;
        $descuento = 0;

        if ($lleva <= 0 || $paga <= 0) return 0;
        
        $cantidadTotal = array_sum(array_column($carrito, 'cantidad'));

        if ($cantidadTotal >= $lleva) {
            $productosExpandido = [];
            foreach ($carrito as &$item) {
                for ($i = 0; $i < $item['cantidad']; $i++) {
                    $productosExpandido[] = [
                        'ref' => &$item,
                        'precio' => $item['precio']
                    ];
                }
            }

            usort($productosExpandido, fn($a, $b) => $a['precio'] <=> $b['precio']);
            $menor = $productosExpandido[$lleva - 1]; // Obtener el producto que será gratis
            $descuento = $menor['precio'];

            $menor['ref']['precio_final'] -= $descuento;
            $menor['ref']['descuento_aplicado'] += $descuento;
            $menor['ref']['promociones'][] = $promoData['promocion']['nombre'];
        }
        return $descuento;
    }


    /**
     * Aplicar descuento al producto más barato al llevar N productos
     * @return float
     */
    private static function aplicarDescuentoMasBarato(&$carrito, $accion, $promoData)
    {
        $descuentoPorcentaje = $accion['valor'] ?? 0;
        $descuento = 0;

        // Encontrar el producto más barato
        $productoMasBarato = null;
        $precioMasBajo = PHP_FLOAT_MAX;

        foreach ($carrito as &$item) {
            $precioUnitario = $item['precio'];
            if ($precioUnitario < $precioMasBajo) {
                $precioMasBajo = $precioUnitario;
                $productoMasBarato = &$item;
            }
        }

        if ($productoMasBarato) {
            $descuento = ($productoMasBarato['precio'] * $productoMasBarato['cantidad']) * ($descuentoPorcentaje / 100);
            $productoMasBarato['precio_final'] -= $descuento;
            $productoMasBarato['descuento_aplicado'] += $descuento;
            $productoMasBarato['promociones'][] = $promoData['promocion']['nombre'];
        }
        return $descuento;
    }

    /**
     * Distribuir descuento general proporcionalmente entre items
     * @return void
     */
    private static function distribuirDescuentoGeneral(&$carrito, $descuentoTotal, $subtotalOriginal)
    {
        if ($subtotalOriginal <= 0) return;

        $descuentoRestante = $descuentoTotal;

        foreach ($carrito as &$item) {
            $proporcion = ($item['precio'] * $item['cantidad']) / $subtotalOriginal;
            $descuentoItem = $descuentoTotal * $proporcion;

            $descuentoItem = min($descuentoItem, $item['precio_final']);
            
            $item['precio_final'] -= $descuentoItem;
            $item['descuento_aplicado'] += $descuentoItem;
            $descuentoRestante -= $descuentoItem;
        }

        if ($descuentoRestante > 0) {
            foreach ($carrito as &$item) {
                if ($item['precio_final'] > 0) {
                    $descuentoExtra = min($descuentoRestante, $item['precio_final']);
                    $item['precio_final'] -= $descuentoExtra;
                    $item['descuento_aplicado'] += $descuentoExtra;
                    $descuentoRestante -= $descuentoExtra;

                    if ($descuentoRestante <= 0) break;
                }
            }
        }
    }

    // --- MÉTODOS DE SOPORTE SIN CAMBIOS ---

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
                return true;
        }
    }

    private static function filtrarPromociones($aplicables)
    {
        usort($aplicables, fn($a, $b) => $a['promocion']['prioridad'] <=> $b['promocion']['prioridad']);
        $resultado = [];
        foreach ($aplicables as $promo) {
            if ($promo['promocion']['exclusivo'] && !empty($resultado)) {
                break;
            }
            $resultado[] = $promo;
            if (!$promo['promocion']['acumulable']) {
                break;
            }
        }
        return $resultado;
    }
}