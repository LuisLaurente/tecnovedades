<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . url('pedido/precheckout'));
    exit;
}

use Core\Helpers\PromocionHelper;
use Models\Cupon;

$errores = [];
if (isset($_SESSION['errores_checkout']) && is_array($_SESSION['errores_checkout'])) {
    $errores = $_SESSION['errores_checkout'];
}
unset($_SESSION['errores_checkout']);

$usuario = $_SESSION['usuario'];

// Obtener direcciones del usuario
$direcciones = [];
try {
    $conexion = \Core\Database::getConexion();
    $stmt = $conexion->prepare("SELECT * FROM direcciones WHERE usuario_id = ? AND activa = 1 ORDER BY es_principal DESC, created_at DESC");
    $stmt->execute([$usuario['id']]);
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $direcciones = [];
}

// Obtener detalles del usuario
$usuario_detalles = [];
try {
    $conexion = \Core\Database::getConexion();
    $stmt = $conexion->prepare("SELECT * FROM usuario_detalles WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $usuario_detalles = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $usuario_detalles = [
        'telefono' => $usuario['telefono'] ?? ''
    ];
}

// Preparar datos de carrito
$productosDetallados = [];
$carrito = $_SESSION['carrito'] ?? [];

if (!empty($carrito)) {
    $productoModel = new \Models\Producto();
    foreach ($carrito as $clave => $item) {
        $producto = $productoModel->obtenerPorId($item['producto_id']);
        if ($producto) {
            $producto['nombre'] = $producto['nombre'];
            $producto['cantidad'] = $item['cantidad'];
            $producto['talla'] = $item['talla'];
            $producto['color'] = $item['color'];
            $producto['clave'] = $clave;
            $producto['precio'] = $item['precio'];
            $producto['subtotal'] = $producto['precio'] * $item['cantidad'];
            $productosDetallados[] = $producto;
        }
    }
}

// Convertir carrito para promociones
$carritoParaPromociones = [];
foreach ($carrito as $item) {
    $producto = $productoModel->obtenerPorId($item['producto_id']);
    if ($producto) {
        $carritoParaPromociones[] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => (float)$item['precio'],
            'cantidad' => (int)$item['cantidad'],
            'categoria_id' => $producto['categoria_id'] ?? null,
            'precio_final' => 0,
            'descuento_aplicado' => 0,
            'promociones' => []
        ];
    }
}

// Calcular promociones y totales
$resultado = PromocionHelper::aplicarPromociones($carritoParaPromociones, $usuario);
$totales = [
    'subtotal' => $resultado['subtotal'],
    'descuento' => $resultado['descuento'],
    'total' => $resultado['total'],
    'envio_gratis' => $resultado['envio_gratis']
];

// Aplicar cupón si existe
$cupon_aplicado = $_SESSION['cupon_aplicado'] ?? null;
$descuento_cupon = 0;
if ($cupon_aplicado) {
    if ($cupon_aplicado['tipo'] === 'descuento_porcentaje') {
        $descuento_cupon = $totales['subtotal'] * ($cupon_aplicado['valor'] / 100);
    } elseif ($cupon_aplicado['tipo'] === 'descuento_fijo') {
        $descuento_cupon = min($cupon_aplicado['valor'], $totales['subtotal']);
    }
}

// Costo de envío inicial (Lima por defecto)
$costo_envio_inicial = 8; // Siempre cobrar envío inicialmente

// Calcular total final incluyendo envío inicial
$total_final = max(0, $totales['total'] - $descuento_cupon + $costo_envio_inicial);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Bytebox</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= url('image/faviconT.ico') ?>" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= url('image/faviconT.png') ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Enlace al archivo CSS específico de checkout -->
    <link rel="stylesheet" href="<?= url('css/checkout.css') ?>">
    <style>
        /* Estilos para secciones desplegables */
        .checkout-section {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            padding: 16px 20px;
            background: #f8f9fa;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .section-header:hover {
            background: #e9ecef;
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
        }

        .section-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
        }

        .section-content.active {
            padding: 20px;
            max-height: 1000px;
        }

        .section-subtitle {
            margin-top: 0;
            margin-bottom: 15px;
            color: #555;
            font-size: 1rem;
        }

        /* Estilos para el costo de envío */
        .envio-costo {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>

<body>

    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="container-principal">
        <h2 class="page-title">Finalizar Compra</h2>

        <!-- Información del usuario -->
        <div class="checkout-card">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                </div>
                <div class="user-details">
                    <h3>👋 Hola, <?= htmlspecialchars($usuario['nombre']) ?>!</h3>
                    <p><?= htmlspecialchars($usuario['email']) ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="checkout-card">
                <div class="error-messages">
                    <h4>Por favor, corrige los siguientes errores:</h4>
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="main-grid">

            <!-- Columna izquierda: Formulario de checkout -->
            <div class="productos-container">

                <form method="POST" action="<?= url('/pedido/registrar') ?>" id="checkoutForm">
                    <!-- FORMULARIO DE DATOS DE ENVÍO (Desplegable) -->
                    <div class="checkout-section">
                        <div class="section-header" onclick="toggleSection('envio-section')">
                            <h3>📦 Datos de Envío</h3>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div id="envio-section" class="section-content">

                            <!-- Selección de dirección existente -->
                            <?php if (!empty($direcciones)): ?>
                                <div class="addresses-section">
                                    <h4 class="section-subtitle">Selecciona una dirección guardada</h4>
                                    <div class="addresses-grid">
                                        <?php foreach ($direcciones as $index => $direccion): ?>
                                            <div class="address-card" data-direccion='<?= json_encode($direccion) ?>'
                                                onclick="selectAddress(this)">
                                                <div class="radio-button"></div>

                                                <!-- Botón de eliminar -->
                                                <button type="button" class="btn-eliminar-direccion"
                                                    onclick="eliminarDireccion(event, <?= $direccion['id'] ?>)">
                                                    🗑️
                                                </button>

                                                <div class="address-content">
                                                    <h4><?= htmlspecialchars($direccion['nombre_direccion'] ?: ucfirst($direccion['tipo'])) ?>
                                                        <?php if ($direccion['es_principal']): ?>
                                                            <span class="principal-badge">Principal</span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <p><?= htmlspecialchars($direccion['direccion']) ?></p>
                                                    <?php if ($direccion['distrito']): ?>
                                                        <p><?= htmlspecialchars($direccion['distrito']) ?><?= $direccion['provincia'] ? ', ' . $direccion['provincia'] : '' ?><?= $direccion['departamento'] ? ', ' . $direccion['departamento'] : '' ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario de nueva dirección -->
                            <div id="newAddressForm" class="<?= !empty($direcciones) ? 'hidden' : '' ?>">
                                <div class="new-address-form">
                                    <h4 class="section-subtitle">Nueva dirección de envío</h4>

                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Nombre completo *</label>
                                            <input type="text" name="nombre" required
                                                value="<?= htmlspecialchars($usuario['nombre']) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Celular *</label>
                                            <input type="tel" name="telefono" required
                                                value="<?= htmlspecialchars($usuario_detalles['telefono'] ?? '') ?>"
                                                placeholder="999 999 999">
                                        </div>
                                    </div>

                                    <!-- Selector de ubicación (Lima/Provincia) -->
                                    <div class="form-group">
                                        <label>Ubicación *</label>
                                        <select name="ubicacion" id="envio-ubicacion" required onchange="actualizarCostoEnvio()">
                                            <option value="">Seleccionar ubicación</option>
                                            <option value="lima">Lima Metropolitana (+S/ 8)</option>
                                            <option value="provincia">Provincia (+S/ 12)</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Dirección completa *</label>
                                        <textarea name="direccion" required rows="3"
                                            placeholder="Av. Principal 123, Urbanización..."></textarea>
                                    </div>

                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Distrito</label>
                                            <input type="text" name="distrito" placeholder="Ej: Miraflores">
                                        </div>
                                        <div class="form-group">
                                            <label>Provincia</label>
                                            <input type="text" name="provincia" placeholder="Ej: Lima">
                                        </div>
                                        <div class="form-group">
                                            <label>Departamento</label>
                                            <input type="text" name="departamento" placeholder="Ej: Lima">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Referencia (opcional)</label>
                                        <input type="text" name="referencia"
                                            placeholder="Ej: Casa amarilla frente al parque">
                                    </div>

                                    <!-- Opción para guardar dirección -->
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="guardar_direccion" name="guardar_direccion" value="1" checked>
                                        <label for="guardar_direccion">
                                            💾 <strong>Guardar esta dirección</strong> para futuras compras
                                        </label>
                                    </div>

                                    <!-- Tipo de dirección si se va a guardar -->
                                    <div id="tipoDereccion" class="form-grid">
                                        <div class="form-group">
                                            <label>Tipo de dirección</label>
                                            <select name="tipo_direccion">
                                                <option value="casa">🏠 Casa</option>
                                                <option value="trabajo">🏢 Trabajo</option>
                                                <option value="envio">📦 Solo envío</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Nombre (opcional)</label>
                                            <input type="text" name="nombre_direccion"
                                                placeholder="Ej: Casa de mamá">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos ocultos para dirección seleccionada -->
                            <input type="hidden" id="direccion_id_seleccionada" name="direccion_id" value="">
                        </div>
                    </div>

                    <!-- FORMULARIO DE DATOS DE FACTURACIÓN (Desplegable) -->
                    <div class="checkout-section">
                        <div class="section-header" onclick="toggleSection('facturacion-section')">
                            <h3>🧾 Datos de Facturación</h3>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div id="facturacion-section" class="section-content">

                            <div class="form-group">
                                <label>Tipo de documento *</label>
                                <select name="facturacion_tipo_documento" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="dni">DNI</option>
                                    <option value="ruc">RUC</option>
                                </select>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Número de documento *</label>
                                    <input type="text" name="facturacion_numero_documento" required
                                        placeholder="Número de DNI o RUC">
                                </div>
                                <div class="form-group">
                                    <label>Correo electrónico *</label>
                                    <input type="email" name="facturacion_email" required
                                        value="<?= htmlspecialchars($usuario['email']) ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Nombre o Razón Social *</label>
                                <input type="text" name="facturacion_nombre" required
                                    value="<?= htmlspecialchars($usuario['nombre']) ?>">
                            </div>

                            <div class="form-group">
                                <label>Dirección Fiscal *</label>
                                <textarea name="facturacion_direccion" required rows="3"
                                    placeholder="Dirección completa para la factura..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- MÉTODO DE PAGO (Desplegable) -->
                    <div class="checkout-section">
                        <div class="section-header" onclick="toggleSection('pago-section')">
                            <h3>💳 Método de Pago</h3>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div id="pago-section" class="section-content">
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" id="contrareembolso" name="metodo_pago" value="contrareembolso" checked>
                                    <label for="contrareembolso">💵 Pago contra entrega</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="transferencia" name="metodo_pago" value="transferencia">
                                    <label for="transferencia">🏦 Transferencia bancaria</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Términos y condiciones -->
                    <div class="terms-section">
                        <div class="terms-checkbox">
                            <input type="checkbox" id="terminos" name="terminos" required>
                            <div class="terms-text">
                                Acepto los
                                <span class="terms-link" id="open-terms-modal">términos y condiciones</span>
                                y autorizo el procesamiento de mis datos personales para el procesamiento de este pedido. *
                            </div>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <button type="submit" id="confirm-order-btn" class="btn-finalizar" disabled>
                        Pagar Ahora - S/ <span id="total-final"><?= number_format($total_final, 2) ?></span>
                    </button>
                </form>
            </div>

            <!-- Columna derecha: Resumen del pedido -->
            <div class="resumen-container">
                <div class="resumen-header">
                    <h3>📋 Resumen del Pedido</h3>
                </div>
                <div class="resumen-body">

                    <?php if (!empty($productosDetallados)): ?>
                        <div class="productos-resumen">
                            <?php foreach ($productosDetallados as $item): ?>
                                <div class="producto-resumen-item">
                                    <div class="producto-resumen-imagen">
                                        <span style="color: #999; font-size: 10px;">IMG</span>
                                    </div>
                                    <div class="producto-resumen-info">
                                        <div class="producto-resumen-nombre"><?= htmlspecialchars($item['nombre']) ?></div>
                                        <div class="producto-resumen-detalles">
                                            <?php if ($item['talla'] || $item['color']): ?>
                                                <?= ($item['talla'] ? 'Talla: ' . htmlspecialchars($item['talla']) : '') ?>
                                                <?= ($item['talla'] && $item['color'] ? ' • ' : '') ?>
                                                <?= ($item['color'] ? 'Color: ' . htmlspecialchars($item['color']) : '') ?>
                                                <br>
                                            <?php endif; ?>
                                            Cantidad: <?= $item['cantidad'] ?>
                                        </div>
                                    </div>
                                    <div class="producto-resumen-precio">
                                        S/ <?= number_format($item['subtotal'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Totales -->
                        <div class="resumen-item">
                            <span class="resumen-label">Subtotal:</span>
                            <span class="resumen-valor">S/ <?= number_format($totales['subtotal'] ?? 0, 2) ?></span>
                        </div>

                        <?php if (($totales['descuento'] ?? 0) > 0): ?>
                            <div class="resumen-item">
                                <span class="resumen-label">🎁 Descuento promociones:</span>
                                <span class="resumen-valor" style="color: var(--success-color);">-S/ <?= number_format($totales['descuento'], 2) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($cupon_aplicado && $descuento_cupon > 0): ?>
                            <div class="resumen-item">
                                <span class="resumen-label">🏷️ Cupón "<?= htmlspecialchars($cupon_aplicado['codigo']) ?>":</span>
                                <span class="resumen-valor" style="color: var(--success-color);">-S/ <?= number_format($descuento_cupon, 2) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="resumen-item">
    <span class="resumen-label">🚚 Envío:</span>
    <span class="resumen-valor envio-costo" id="costo-envio-display">
        S/ <span id="costo-envio-valor">0.00</span>
    </span>
</div>

                        <div class="resumen-item total-final">
                            <span class="resumen-label">💰 Total:</span>
                            <span class="resumen-valor">S/ <span id="total-final-display"><?= number_format($total_final, 2) ?></span></span>
                        </div>

                        <!-- Promociones aplicadas -->
                        <!-- Promociones aplicadas -->
<?php if (!empty($promociones) && is_array($promociones)): ?>
    <div class="resumen-promociones">
        <h4>🎉 Promociones Aplicadas:</h4>
        <?php 
        // Calcular descuento por promoción para mostrar
        $subtotal = $totales['subtotal'] ?? 0;
        $promocionesConInfo = [];
        
        foreach ($promociones as $promocion) {
            $accion = $promocion['accion'] ?? [];
            $tipoAccion = $accion['tipo'] ?? '';
            $descuento = 0;
            
            // Calcular descuento según tipo de acción
            if ($tipoAccion === 'descuento_porcentaje') {
                $descuento = $subtotal * ($accion['valor'] / 100);
            } elseif ($tipoAccion === 'descuento_fijo') {
                $descuento = min($accion['valor'], $subtotal);
            }
            
            if ($descuento > 0) {
                $promocionesConInfo[] = [
                    'nombre' => $promocion['promocion']['nombre'] ?? 'Promoción',
                    'descuento' => $descuento
                ];
            }
        }
        ?>
        
        <?php if (!empty($promocionesConInfo)): ?>
            <?php foreach ($promocionesConInfo as $promo): ?>
                <div class="promocion-item">
                    <span class="promocion-nombre">
                        <?= htmlspecialchars($promo['nombre']) ?>
                    </span>
                    <span class="promocion-descuento">
                        -S/ <?= number_format($promo['descuento'], 2) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: var(--gray-dark); font-size: 0.9rem; text-align: center; padding: 10px;">
                No se aplicaron descuentos por promociones
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>
                    <?php else: ?>
                        <p style="color: #999; text-align: center; padding: 32px 0;">No hay productos en el carrito</p>
                    <?php endif; ?>

                    <!-- Información adicional -->
                    <div style="margin-top: 24px; padding: 16px; background: #f8f9fa; border-radius: 8px;">
                        <h4 style="margin: 0 0 12px 0; color: var(--dark-color); font-weight: 600;">🔒 Compra Segura</h4>
                        <div style="font-size: 0.85rem; color: var(--gray-dark); line-height: 1.6;">
                            <p style="margin: 4px 0;">• ✅ Tus datos están protegidos</p>
                            <p style="margin: 4px 0;">• 📦 Envío gratuito en compras +S/100</p>
                            <p style="margin: 4px 0;">• 🔄 Garantía de satisfacción</p>
                            <p style="margin: 4px 0;">• 📱 Seguimiento en tiempo real</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acciones-carrito">
            <a href="<?= url('carrito/ver') ?>" class="boton-volver">← Volver al carrito</a>
        </div>
    </div>

    <script>
        let selectedAddressCard = null;
        const subtotalBase = <?= $totales['subtotal'] ?? 0 ?>;
        const descuentoPromociones = <?= $totales['descuento'] ?? 0 ?>;
        const descuentoCupon = <?= $descuento_cupon ?>;

        // Función para toggle de secciones
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = section.previousElementSibling.querySelector('.toggle-icon');

            section.classList.toggle('active');
            icon.textContent = section.classList.contains('active') ? '▲' : '▼';
        }

        // Función para actualizar costo de envío
function actualizarCostoEnvio() {
    const ubicacionSelect = document.getElementById('envio-ubicacion');
    const costoEnvioDisplay = document.getElementById('costo-envio-display');
    const costoEnvioValor = document.getElementById('costo-envio-valor');

    if (!ubicacionSelect) return;

    const ubicacion = ubicacionSelect.value;
    let costoEnvio = 0;

    // Calcular costo según ubicación
    if (ubicacion === 'lima') {
        costoEnvio = 8;
    } else if (ubicacion === 'provincia') {
        costoEnvio = 12;
    }

    // MOSTRAR SIEMPRE EL COSTO DE ENVÍO (sin condición de gratis)
    if (costoEnvioValor) {
        costoEnvioValor.textContent = costoEnvio.toFixed(2);
    } else {
        costoEnvioDisplay.innerHTML = `S/ <span id="costo-envio-valor">${costoEnvio.toFixed(2)}</span>`;
    }

    // Recalcular el total final
    recalcularTotalFinal(costoEnvio);
}


        // Función para recalcular el total final
function recalcularTotalFinal(costoEnvio) {
    const subtotal = <?= $totales['subtotal'] ?? 0 ?>;
    const descuento = <?= $totales['descuento'] ?? 0 ?>;
    const descuentoCupon = <?= $descuento_cupon ?? 0 ?>;

    // Calcular nuevo total - SIEMPRE sumar envío
    let total = subtotal - descuento - descuentoCupon + costoEnvio;
    
    total = Math.max(total, 0);

    // Actualizar botón y total display
    const btnFinalizar = document.querySelector('.btn-finalizar');
    const totalDisplay = document.querySelector('.resumen-item.total-final .resumen-valor');
    const totalSpan = document.getElementById('total-final');
    
    if (btnFinalizar && totalSpan) {
        totalSpan.textContent = total.toFixed(2);
    }
    
    if (totalDisplay) {
        totalDisplay.textContent = `S/ ${total.toFixed(2)}`;
    }
}


        // Función auxiliar para manejar atributos required
        function toggleRequiredFields(enableRequired) {
            const direccionField = document.querySelector('textarea[name="direccion"]');

            if (enableRequired) {
                if (direccionField) direccionField.setAttribute('required', '');
            } else {
                if (direccionField) direccionField.removeAttribute('required');
            }
        }

        function selectAddress(card) {
            // Deseleccionar dirección anterior
            if (selectedAddressCard) {
                selectedAddressCard.classList.remove('selected');
            }

            // Seleccionar nueva dirección
            selectedAddressCard = card;
            card.classList.add('selected');

            // Obtener datos de la dirección
            const direccionData = JSON.parse(card.dataset.direccion);

            // Llenar el formulario con los datos de la dirección seleccionada
            document.getElementById('direccion_id_seleccionada').value = direccionData.id;
            document.querySelector('textarea[name="direccion"]').value = direccionData.direccion;
            document.querySelector('input[name="distrito"]').value = direccionData.distrito || '';
            document.querySelector('input[name="provincia"]').value = direccionData.provincia || '';
            document.querySelector('input[name="departamento"]').value = direccionData.departamento || '';
            document.querySelector('input[name="referencia"]').value = direccionData.referencia || '';

            // Ocultar campos de nueva dirección y opciones de guardado
            const addressFormFields = document.getElementById('newAddressForm').querySelector('.new-address-form');
            if (addressFormFields) {
                // Ocultar todos los campos de dirección excepto nombre y teléfono
                const direccionField = document.querySelector('textarea[name="direccion"]');
                const distritoField = document.querySelector('input[name="distrito"]');
                const provinciaField = document.querySelector('input[name="provincia"]');
                const departamentoField = document.querySelector('input[name="departamento"]');
                const referenciaField = document.querySelector('input[name="referencia"]');

                // Encontrar los contenedores y ocultarlos
                if (direccionField) {
                    const direccionGroup = direccionField.closest('.form-group');
                    if (direccionGroup) direccionGroup.style.display = 'none';
                }

                // Ocultar el grid de distrito/provincia/departamento
                if (distritoField) {
                    const locationGrid = distritoField.closest('.form-grid');
                    if (locationGrid) locationGrid.style.display = 'none';
                }

                // Ocultar referencia
                if (referenciaField) {
                    const referenciaGroup = referenciaField.closest('.form-group');
                    if (referenciaGroup) referenciaGroup.style.display = 'none';
                }

                // Ocultar checkbox y opciones de guardado
                const checkboxGroup = addressFormFields.querySelector('.checkbox-group');
                const tipoDiv = document.getElementById('tipoDereccion');

                if (checkboxGroup) checkboxGroup.style.display = 'none';
                if (tipoDiv) tipoDiv.style.display = 'none';

                const guardarCheckbox = document.querySelector('input[name="guardar_direccion"]');
                if (guardarCheckbox) guardarCheckbox.checked = false;
            }

            // Deshabilitar validación required en campos ocultos
            toggleRequiredFields(false);

            // Mostrar el formulario de envío
            document.getElementById('newAddressForm').classList.remove('hidden');
        }

        // Inicializar secciones y eventos
        document.addEventListener('DOMContentLoaded', function() {
            // Abrir primera sección por defecto
            toggleSection('envio-section');
            // Actualizar costo inicial
            actualizarCostoEnvio();
            // Event listener para cambios en la ubicación
            const ubicacionSelect = document.getElementById('envio-ubicacion');
            if (ubicacionSelect) {
                ubicacionSelect.addEventListener('change', actualizarCostoEnvio);
            }

            // Preseleccionar primera dirección si existe
            const firstAddress = document.querySelector('.address-card');
            if (firstAddress) {
                selectAddress(firstAddress);
            }

            // Inicializar estado de campos de tipo de dirección
            const guardarCheckbox = document.getElementById('guardar_direccion');
            const tipoDiv = document.getElementById('tipoDereccion');
            if (guardarCheckbox && tipoDiv) {
                tipoDiv.style.display = guardarCheckbox.checked ? 'grid' : 'none';
            }
        });

        // Toggle para nueva dirección
        document.getElementById('toggleNewAddress')?.addEventListener('click', function() {
            const form = document.getElementById('newAddressForm');
            const isHidden = form.classList.contains('hidden');

            if (isHidden) {
                form.classList.remove('hidden');
                this.innerHTML = '<strong>➖ Usar dirección guardada</strong>';

                // Limpiar selección de direcciones
                if (selectedAddressCard) {
                    selectedAddressCard.classList.remove('selected');
                    selectedAddressCard = null;
                }

                // Limpiar formulario y mostrar campos
                document.getElementById('direccion_id_seleccionada').value = '';

                // Mostrar todos los campos de dirección y restaurar atributos required
                const addressFormFields = document.getElementById('newAddressForm').querySelector('.new-address-form');
                if (addressFormFields) {
                    const formGroups = addressFormFields.querySelectorAll('.form-group');
                    const formGrids = addressFormFields.querySelectorAll('.form-grid');
                    const checkboxGroup = addressFormFields.querySelector('.checkbox-group');

                    formGroups.forEach(group => {
                        group.style.display = 'block';
                    });

                    formGrids.forEach(grid => {
                        grid.style.display = 'grid';
                    });

                    if (checkboxGroup) checkboxGroup.style.display = 'block';

                    // Restaurar atributo required en dirección
                    const direccionField = document.querySelector('textarea[name="direccion"]');
                    if (direccionField) {
                        direccionField.setAttribute('required', '');
                    }
                }

                // Habilitar validación required para nueva dirección
                toggleRequiredFields(true);

                const tipoDiv = document.getElementById('tipoDereccion');
                if (tipoDiv) tipoDiv.style.display = 'grid';

                const guardarCheckbox = document.querySelector('input[name="guardar_direccion"]');
                if (guardarCheckbox) guardarCheckbox.checked = true;

                // Limpiar campos del formulario
                document.querySelector('textarea[name="direccion"]').value = '';
                document.querySelector('input[name="distrito"]').value = '';
                document.querySelector('input[name="provincia"]').value = '';
                document.querySelector('input[name="departamento"]').value = '';
                document.querySelector('input[name="referencia"]').value = '';

                // Foco en el primer campo
                const direccionField = document.querySelector('textarea[name="direccion"]');
                if (direccionField) {
                    setTimeout(() => direccionField.focus(), 100);
                }
            } else {
                form.classList.add('hidden');
                this.innerHTML = '<strong>➕ Agregar nueva dirección</strong>';
            }
        });

        // Mostrar/ocultar campos de tipo de dirección según checkbox
        document.getElementById('guardar_direccion')?.addEventListener('change', function() {
            const tipoDiv = document.getElementById('tipoDereccion');
            if (tipoDiv) {
                tipoDiv.style.display = this.checked ? 'grid' : 'none';
            }
        });

        // Validación del formulario antes del envío
        document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
            const direccionId = document.getElementById('direccion_id_seleccionada').value;
            const newAddressForm = document.getElementById('newAddressForm');
            const isNewAddressVisible = !newAddressForm.classList.contains('hidden');

            // Si hay una dirección seleccionada, permitir envío inmediato
            if (direccionId) {
                return true;
            }

            // Si no hay dirección seleccionada y el formulario de nueva dirección está oculto
            if (!direccionId && !isNewAddressVisible) {
                e.preventDefault();
                alert('Por favor, selecciona una dirección o completa el formulario de nueva dirección.');
                return false;
            }

            // Si el formulario de nueva dirección está visible, validar campos requeridos
            if (isNewAddressVisible && !direccionId) {
                const direccionField = document.querySelector('textarea[name="direccion"]');
                if (!direccionField || !direccionField.value.trim()) {
                    e.preventDefault();
                    alert('Por favor, completa la dirección.');
                    if (direccionField) direccionField.focus();
                    return false;
                }
            }

            return true;
        });

        // Función adicional para prevenir errores de validación HTML5 en campos ocultos
        document.addEventListener('invalid', function(e) {
            const target = e.target;
            // Si el campo que está causando el error está oculto, prevenirlo
            if (target && target.closest('.form-group') &&
                target.closest('.form-group').style.display === 'none') {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, true);

        // Modal de términos y condiciones
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('terms-modal');
            const openBtn = document.getElementById('open-terms-modal');
            const closeBtn = document.getElementById('close-terms-modal');
            const closeBtn2 = document.getElementById('close-terms-btn');
            const acceptBtn = document.getElementById('accept-terms-btn');
            const checkbox = document.getElementById('terminos');
            const submitBtn = document.getElementById('confirm-order-btn');

            if (!modal || !openBtn) {
                return;
            }

            // Función para abrir
            function openModal() {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            // Función para cerrar
            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Event listeners
            openBtn.onclick = function(e) {
                e.preventDefault();
                openModal();
            };

            if (closeBtn) closeBtn.onclick = closeModal;
            if (closeBtn2) closeBtn2.onclick = closeModal;

            // Cerrar al hacer clic fuera
            modal.onclick = function(e) {
                if (e.target === modal) closeModal();
            };

            // Aceptar términos
            if (acceptBtn && checkbox && submitBtn) {
                acceptBtn.onclick = function() {
                    checkbox.checked = true;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('disabled');
                    closeModal();
                };
            }

            // Validar checkbox
            if (checkbox && submitBtn) {
                checkbox.onchange = function() {
                    if (this.checked) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('disabled');
                    } else {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('disabled');
                    }
                };
            }

            // ESC para cerrar
            document.onkeydown = function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            };
        });
        // Función para eliminar dirección
        function eliminarDireccion(event, direccionId) {
            event.stopPropagation(); // Evita que se seleccione la dirección

            if (confirm('¿Estás seguro de que quieres eliminar esta dirección?')) {
                fetch('<?= url("direccion/eliminar") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + direccionId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recargar la página para ver los cambios
                            location.reload();
                        } else {
                            alert('Error al eliminar la dirección: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al eliminar la dirección');
                    });
            }
        }
    </script>

    <!-- Modal de términos y condiciones -->
    <div id="terms-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <!-- Header del modal -->
            <div class="modal-header">
                <div class="modal-title-container">
                    <h2 class="modal-title">
                        📋 Términos y Condiciones
                    </h2>
                    <p class="modal-subtitle">
                        Políticas y condiciones de uso de ByteBox
                    </p>
                </div>
                <button type="button" id="close-terms-modal" class="modal-close">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <div class="terms-section">
                    <h3 class="section-title">
                        <span class="section-number">📋</span>
                        Información General
                    </h3>
                    <p class="section-content">
                        Bienvenido a <strong>ByteBox</strong>. Al utilizar nuestro sitio web y realizar compras,
                        usted acepta estar sujeto a los siguientes términos y condiciones de uso y venta.
                    </p>
                </div>

                <div class="terms-section">
                    <h3 class="section-title">
                        <span class="section-number">💰</span>
                        Productos y Precios
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> Todos los precios están expresados en soles peruanos (S/) e incluyen IGV</p>
                        <p class="check-item"><span class="check-icon">✓</span> Los precios están sujetos a cambios sin previo aviso</p>
                        <p class="check-item"><span class="check-icon">✓</span> Los productos están sujetos a disponibilidad de stock</p>
                        <p class="check-item"><span class="check-icon">✓</span> Nos reservamos el derecho de limitar las cantidades de compra por cliente</p>
                    </div>
                </div>

                <div class="terms-section">
                    <h3 class="section-title">
                        <span class="section-number">🚚</span>
                        Política de Envío
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> <strong>Envío gratuito</strong> a todo el Perú en compras mayores a S/ 100</p>
                        <p class="check-item"><span class="check-icon">✓</span> Tiempo de entrega: 2-5 días hábiles en Lima, 3-7 días en provincias</p>
                        <p class="check-item"><span class="check-icon">✓</span> Horarios de entrega: Lunes a Viernes de 9:00 AM a 6:00 PM</p>
                        <p class="check-item"><span class="check-icon">✓</span> El cliente debe estar presente en el momento de la entrega</p>
                    </div>
                </div>

                <div class="terms-section">
                    <h3 class="section-title">
                        <span class="section-number">🔄</span>
                        Devoluciones y Cambios
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> Plazo para devoluciones: <strong>30 días</strong> calendarios desde la recepción</p>
                        <p class="check-item"><span class="check-icon">✓</span> Los productos deben estar en perfecto estado, sin uso y con embalaje original</p>
                        <p class="check-item"><span class="check-icon">✓</span> No se aceptan devoluciones de productos personalizados</p>
                        <p class="check-item"><span class="check-icon">✓</span> Los gastos de envío para devoluciones corren por cuenta del cliente</p>
                    </div>
                </div>

                <div class="terms-section">
                    <h3 class="section-title">
                        <span class="section-number">🔒</span>
                        Protección de Datos
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> Respetamos su privacidad conforme a la Ley de Protección de Datos Personales</p>
                        <p class="check-item"><span class="check-icon">✓</span> Sus datos serán utilizados únicamente para procesar pedidos</p>
                        <p class="check-item"><span class="check-icon">✓</span> No compartimos información personal con terceros sin consentimiento</p>
                    </div>
                </div>

                <div class="terms-section">
                    <h3 class="section-title">
                        <span class="section-number">📞</span>
                        Contacto y Soporte
                    </h3>
                    <div class="section-content">
                        <p>Para consultas, reclamos o soporte técnico:</p>
                        <div class="contact-info">
                            <p class="contact-item"><span class="contact-icon">📧</span> <strong>Email:</strong> info@bytebox.com</p>
                            <p class="contact-item"><span class="contact-icon">📱</span> <strong>Teléfono:</strong> +51 999 123 456</p>
                            <p class="contact-item"><span class="contact-icon">🕒</span> <strong>Horario:</strong> Lunes a Sábado 9:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer del modal -->
            <div class="modal-footer">
                <button type="button" id="close-terms-btn" class="btn-secondary">
                    Cerrar
                </button>
                <button type="button" id="accept-terms-btn" class="btn-accept-terms">
                    ✅ Acepto los Términos
                </button>
            </div>
        </div>
    </div>

</body>

</html>