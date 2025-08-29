<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



$cantidadEnCarrito = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cantidadEnCarrito += $item['cantidad'];
    }
} ?> 
<!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">
    
    <div class="header-container"> <?php include_once __DIR__ . '/../admin/includes/header.php'; ?> </div>
<h2>Carrito de Compras</h2>

<!-- Estilos y botón de volver -->
<link rel="stylesheet" href="<?= url('css/carrito.css') ?>">



<!-- Contenido del carrito -->
<div class="clearfix"></div>
<?php if (!empty($productosDetallados)): ?>
    <div class="carrito-container">
        <div class="tabla-container">
            <table class="carrito-tabla">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Detalles</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productosDetallados as $item): ?>
                        <tr class="producto-fila">
                            <td class="producto-info">
                                <div class="producto-nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                            </td>
                            <td class="producto-detalles">
                                <div class="detalle-item">
                                    <span class="detalle-label">Tamaño:</span>
                                    <span class="detalle-valor"><?= htmlspecialchars($item['talla'] ?? 'N/A') ?></span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label">Color:</span>
                                    <span class="detalle-valor"><?= htmlspecialchars($item['color'] ?? 'N/A') ?></span>
                                </div>
                            </td>
                            <td class="producto-precio">
                                <span class="precio-valor">S/ <?= number_format($item['precio'], 2) ?></span>
                            </td>
                            <td class="producto-cantidad">
                                <div class="cantidad-container">
                                    <a href="<?= url('carrito/disminuir/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-disminuir" title="Disminuir cantidad">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                                        </svg>
                                    </a>
                                    <span class="cantidad-numero"><?= $item['cantidad'] ?></span>
                                    <a href="<?= url('carrito/aumentar/' . urlencode($item['clave'])) ?>" class="btn-cantidad btn-aumentar" title="Aumentar cantidad">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                            <td class="producto-subtotal">
                                <span class="subtotal-valor">S/ <?= number_format($item['subtotal'], 2) ?></span>
                            </td>
                            <td class="producto-acciones">
                                <a href="<?= url('carrito/eliminar/' . urlencode($item['clave'])) ?>"
                                    class="btn-eliminar"
                                    title="Eliminar producto"
                                    onclick="return confirm('¿Eliminar este producto del carrito?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="resumen-container">
            <div class="resumen-totales">
                <div class="resumen-item">
                    <div class="resumen-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Subtotal:
                    </div>
                    <div class="resumen-valor">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></div>
                </div>

                <div class="resumen-item">
                    <div class="resumen-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Descuento:
                    </div>
                    <div class="resumen-valor">S/ <?= number_format($totales['descuento'] ?? 0, 2) ?></div>
                </div>

                <!-- Sección para aplicar cupones -->
                <div class="resumen-item cupon-seccion">
                    <div class="cupon-titulo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M9 9h1.023M9 13h1.023M9 17h1.023M7.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196zM16.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196z" />
                        </svg>
                        ¿Tienes un cupón?
                    </div>
                    <div class="cupon-input-container">
                        <input type="text" 
                               id="codigo-cupon" 
                               placeholder="Ingresa tu código" 
                               value="<?= $_SESSION['cupon_aplicado']['codigo'] ?? '' ?>" 
                               <?= isset($_SESSION['cupon_aplicado']) ? 'readonly' : '' ?>>
                        <?php if (isset($_SESSION['cupon_aplicado'])): ?>
                            <button type="button" id="btn-remover-cupon" class="btn-cupon btn-remover">Remover</button>
                        <?php else: ?>
                            <button type="button" id="btn-aplicar-cupon" class="btn-cupon btn-aplicar">Aplicar</button>
                        <?php endif; ?>
                    </div>
                    <div id="mensaje-cupon" class="cupon-mensaje">
                        <?php if (isset($_SESSION['cupon_aplicado'])): ?>
                            <span class="cupon-exito">
                                ✓ Cupón aplicado: <strong><?= htmlspecialchars($_SESSION['cupon_aplicado']['codigo']) ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($_SESSION['cupon_aplicado']) && isset($totales['descuento_cupon'])): ?>
                    <div class="resumen-item cupon-aplicado-detalle">
                        <div class="resumen-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M9 9h1.023M9 13h1.023M9 17h1.023M7.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196zM16.5 4.218v15.564m0 0a2 2 0 100 2.196 2 2 0 000-2.196z" />
                            </svg>
                            Descuento cupón:
                        </div>
                        <div class="resumen-valor">
                            <?php 
                            $cupon = $_SESSION['cupon_aplicado'];
                            $descuento_cupon = $totales['descuento_cupon'];
                            
                            if ($cupon['tipo'] === 'descuento_porcentaje') {
                                echo '- S/ ' . number_format($descuento_cupon, 2) . ' (' . $cupon['valor'] . '%)';
                            } elseif ($cupon['tipo'] === 'descuento_fijo') {
                                echo '- S/ ' . number_format($descuento_cupon, 2);
                            } elseif ($cupon['tipo'] === 'envio_gratis') {
                                echo '🚚 Envío gratis';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="resumen-item total-final">
                    <div class="resumen-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Total:
                    </div>
                    <div class="resumen-valor">
                        <?php 
                        // Usar el total calculado por el CarritoController
                        $total_final = $totales['total'] ?? 0;
                        ?>
                        S/ <?= number_format($total_final, 2) ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($promocionesAplicadas)): ?>
                <div class="promociones-aplicadas">
                    <h4>Promociones aplicadas:</h4>
                    <ul>
                        <?php foreach ($promocionesAplicadas as $promo): ?>
                            <li><?= htmlspecialchars($promo['promocion']['nombre']) ?> (<?= $promo['accion']['tipo'] ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="carrito-vacio">
        <div class="carrito-vacio-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="60" height="60">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <div class="carrito-vacio-content">
            <h3>Tu carrito está vacío</h3>
            <p>¡Agrega algunos productos para comenzar!</p>
        </div>
    </div>
<?php endif; ?>

<div class="acciones-carrito">
    <a href="<?= url('/') ?>" class="boton-volver">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volver
    </a>
    <?php if (!empty($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
        <?php if (isset($_SESSION['usuario'])): ?>
            <a href="<?= url('pedido/checkout') ?>" class="boton-checkout">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Finalizar compra
            </a>
        <?php else: ?>
            <a href="<?= url('pedido/precheckout') ?>" class="boton-checkout">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                Continuar compra
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Script para funcionalidad de cupones -->
<script>
// Función para aplicar cupón
function aplicarCupon() {
    const codigo = document.getElementById('codigo-cupon').value;
    const mensaje = document.getElementById('mensaje-cupon');
    
    if (!codigo.trim()) {
        mensaje.innerHTML = '<span class="cupon-error">Por favor ingresa un código de cupón</span>';
        return;
    }

    // Crear formulario para enviar por POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url("carrito/aplicarCupon") ?>';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'codigo';
    input.value = codigo;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Función para remover cupón
function removerCupon() {
    if (!confirm('¿Estás seguro de que quieres remover el cupón?')) {
        return;
    }

    window.location.href = '<?= url("carrito/quitarCupon") ?>';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const btnAplicar = document.getElementById('btn-aplicar-cupon');
    const btnRemover = document.getElementById('btn-remover-cupon');
    const inputCupon = document.getElementById('codigo-cupon');

    if (btnAplicar) {
        btnAplicar.addEventListener('click', aplicarCupon);
    }

    if (btnRemover) {
        btnRemover.addEventListener('click', removerCupon);
    }

    // Permitir aplicar cupón con Enter
    if (inputCupon) {
        inputCupon.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && btnAplicar) {
                aplicarCupon();
            }
        });
    }

    // Mostrar mensajes de cupón si existen
    <?php if (isset($_SESSION['mensaje_cupon_exito'])): ?>
        const mensajeExito = document.getElementById('mensaje-cupon');
        if (mensajeExito) {
            mensajeExito.innerHTML = '<span class="cupon-exito">✓ <?= htmlspecialchars($_SESSION['mensaje_cupon_exito']) ?></span>';
        }
        <?php unset($_SESSION['mensaje_cupon_exito']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_cupon_error'])): ?>
        const mensajeError = document.getElementById('mensaje-cupon');
        if (mensajeError) {
            mensajeError.innerHTML = '<span class="cupon-error">✗ <?= htmlspecialchars($_SESSION['mensaje_cupon_error']) ?></span>';
        }
        <?php unset($_SESSION['mensaje_cupon_error']); ?>
    <?php endif; ?>
});
</script>

<!-- Estilos para la sección de cupones -->
<style>
/* Variables de colores del proyecto */
:root {
    --color-primary: #2AC1DB;
    --color-dark: #1B1B1B;
    --color-white: #FFFFFF;
    --color-secondary: #363993;
    --color-primary-light: rgba(42, 193, 219, 0.1);
    --color-primary-hover: rgba(42, 193, 219, 0.8);
    --color-secondary-light: rgba(54, 57, 147, 0.1);
    --color-gray-light: #f8f9fa;
    --color-border: rgba(27, 27, 27, 0.1);
    --color-shadow: rgba(27, 27, 27, 0.1);
}

.cupon-seccion {
    background: var(--color-white);
    border: 1px solid rgba(42, 193, 219, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin: 15px 0;
    box-shadow: 0 4px 15px rgba(27, 27, 27, 0.05);
    transition: all 0.3s ease;
}

.cupon-seccion:hover {
    border-color: var(--color-primary);
    box-shadow: 0 6px 25px rgba(42, 193, 219, 0.1);
}

.cupon-titulo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Orbitron', monospace;
    font-weight: 600;
    color: var(--color-dark);
    margin-bottom: 15px;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.cupon-input-container {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

#codigo-cupon {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid rgba(42, 193, 219, 0.3);
    border-radius: 8px;
    font-size: 14px;
    font-family: 'Outfit', sans-serif;
    transition: all 0.3s ease;
}

#codigo-cupon:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(42, 193, 219, 0.1);
}

#codigo-cupon:read-only {
    background-color: rgba(42, 193, 219, 0.05);
    color: var(--color-dark);
    font-weight: 500;
}

.btn-cupon {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Outfit', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-aplicar {
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: var(--color-white);
}

.btn-aplicar:hover {
    background: linear-gradient(135deg, var(--color-secondary), var(--color-primary));
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(42, 193, 219, 0.3);
}

.btn-remover {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: var(--color-white);
}

.btn-remover:hover {
    background: linear-gradient(135deg, #ee5a52, #ff6b6b);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(238, 90, 82, 0.3);
}

.cupon-mensaje {
    min-height: 24px;
    font-size: 14px;
    font-family: 'Outfit', sans-serif;
    margin-top: 5px;
}

.cupon-exito {
    color: var(--color-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.cupon-error {
    color: #ee5a52;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.cupon-loading {
    color: var(--color-secondary);
    font-style: italic;
    font-weight: 500;
}

.cupon-advertencia {
    color: #ff9800;
    font-weight: 500;
}

.cupon-aplicado-detalle {
    background: linear-gradient(135deg, rgba(42, 193, 219, 0.05), rgba(54, 57, 147, 0.05));
    border-left: 4px solid var(--color-primary);
    padding: 15px 20px;
    border-radius: 0 8px 8px 0;
    margin: 10px 0;
}

.cupon-aplicado-detalle .resumen-label {
    color: var(--color-primary);
    font-weight: 600;
}

.cupon-aplicado-detalle .resumen-valor {
    color: var(--color-primary);
    font-weight: 700;
}
</style>
