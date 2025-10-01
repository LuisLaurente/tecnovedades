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

// Costo de envío inicial
$costo_envio_inicial = 8;

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
</head>

<body>

    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

    <div class="container-principal">
        <h2 class="page-title">Finalizar Compra</h2>

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
                    <!-- FORMULARIO DE DATOS DE ENVÍO -->
                    <div class="checkout-section">
                        <div class="section-header" onclick="toggleSection('envio-section')">
                            <h3>Datos de Envío</h3>
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
                                                    ×
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

                                    <!-- Selector de ubicación (Departamento/Provincia/Distrito) -->
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Departamento *</label>
                                            <select name="departamento" id="departamento" required onchange="cargarProvincias()">
                                                <option value="">Seleccionar departamento</option>
                                                <!-- Se cargará dinámicamente -->
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Provincia *</label>
                                            <select name="provincia" id="provincia" required onchange="actualizarMetodosPago()" disabled>
                                                <option value="">Primero selecciona un departamento</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Distrito *</label>
                                            <input type="text" name="distrito" id="distrito" required placeholder="Ingresa tu distrito">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Dirección completa *</label>
                                        <textarea name="direccion" required rows="3"
                                            placeholder="Av. Principal 123, Urbanización..."></textarea>
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
                                            <strong>Guardar esta dirección</strong> para futuras compras
                                        </label>
                                    </div>

                                    <!-- Tipo de dirección si se va a guardar -->
                                    <div id="tipoDereccion" class="form-grid">
                                        <div class="form-group">
                                            <label>Tipo de dirección</label>
                                            <select name="tipo_direccion">
                                                <option value="casa">Casa</option>
                                                <option value="trabajo">Trabajo</option>
                                                <option value="envio">Solo envío</option>
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

                    <!-- FORMULARIO DE DATOS DE FACTURACIÓN -->
                    <div class="checkout-section">
                        <div class="section-header" onclick="toggleSection('facturacion-section')">
                            <h3>Datos de Facturación</h3>
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

                    <!-- MÉTODO DE PAGO -->
                    <div class="checkout-section">
                        <div class="section-header" onclick="toggleSection('pago-section')">
                            <h3>Método de Pago</h3>
                            <span class="toggle-icon">▼</span>
                        </div>
                        <div id="pago-section" class="section-content">
                            <div class="payment-methods" id="payment-methods-container">
                                <!-- Se cargará dinámicamente según la ubicación -->
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
                    <h3>Resumen del Pedido</h3>
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
                                <span class="resumen-label">Descuento promociones:</span>
                                <span class="resumen-valor" style="color: var(--success-color);">-S/ <?= number_format($totales['descuento'], 2) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($cupon_aplicado && $descuento_cupon > 0): ?>
                            <div class="resumen-item">
                                <span class="resumen-label">Cupón "<?= htmlspecialchars($cupon_aplicado['codigo']) ?>":</span>
                                <span class="resumen-valor" style="color: var(--success-color);">-S/ <?= number_format($descuento_cupon, 2) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="resumen-item">
                            <span class="resumen-label">Envío:</span>
                            <span class="resumen-valor envio-costo" id="costo-envio-display">
                                S/ <span id="costo-envio-valor"><?= number_format($costo_envio_inicial, 2) ?></span>
                            </span>
                        </div>

                        <div class="resumen-item total-final">
                            <span class="resumen-label">Total:</span>
                            <span class="resumen-valor">S/ <span id="total-final-display"><?= number_format($total_final, 2) ?></span></span>
                        </div>

                        <!-- Promociones aplicadas -->
                        <?php if (!empty($resultado['promociones_aplicadas'])): ?>
                            <div class="resumen-promociones">
                                <h4>Promociones Aplicadas:</h4>
                                <?php foreach ($resultado['promociones_aplicadas'] as $promocion): ?>
                                    <?php if (is_numeric($promocion['monto']) && $promocion['monto'] > 0): ?>
                                        <div class="promocion-item">
                                            <span class="promocion-nombre">
                                                <?= htmlspecialchars($promocion['nombre']) ?>
                                            </span>
                                            <span class="promocion-descuento">
                                                -S/ <?= number_format($promocion['monto'], 2) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: #999; text-align: center; padding: 32px 0;">No hay productos en el carrito</p>
                    <?php endif; ?>

                    <!-- Información adicional -->
                    <div style="margin-top: 24px; padding: 16px; background: var(--gray-light); border-radius: 8px;">
                        <h4 style="margin: 0 0 12px 0; color: var(--dark-color); font-weight: 600;">Compra Segura</h4>
                        <div style="font-size: 0.85rem; color: var(--gray-dark); line-height: 1.6;">
                            <p style="margin: 4px 0;">• Tus datos están protegidos</p>
                            <p style="margin: 4px 0;">• Envío gratuito en compras +S/100</p>
                            <p style="margin: 4px 0;">• Garantía de satisfacción</p>
                            <p style="margin: 4px 0;">• Seguimiento en tiempo real</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="acciones-carrito">
            <a href="<?= url('carrito/ver') ?>" class="boton-volver">Volver al carrito</a>
        </div>
    </div>

    <script>
        let selectedAddressCard = null;
        const subtotalBase = <?= $totales['subtotal'] ?? 0 ?>;
        const descuentoPromociones = <?= $totales['descuento'] ?? 0 ?>;
        const descuentoCupon = <?= $descuento_cupon ?>;

        // Datos de departamentos y provincias (simplificado)
        const departamentosData = [
            { id: '15', nombre: 'Lima' },
            { id: '01', nombre: 'Amazonas' },
            { id: '02', nombre: 'Áncash' },
            { id: '03', nombre: 'Apurímac' },
            { id: '04', nombre: 'Arequipa' },
            { id: '05', nombre: 'Ayacucho' },
            { id: '06', nombre: 'Cajamarca' },
            { id: '07', nombre: 'Callao' },
            { id: '08', nombre: 'Cusco' },
            { id: '09', nombre: 'Huancavelica' },
            { id: '10', nombre: 'Huánuco' },
            { id: '11', nombre: 'Ica' },
            { id: '12', nombre: 'Junín' },
            { id: '13', nombre: 'La Libertad' },
            { id: '14', nombre: 'Lambayeque' },
            { id: '16', nombre: 'Loreto' },
            { id: '17', nombre: 'Madre de Dios' },
            { id: '18', nombre: 'Moquegua' },
            { id: '19', nombre: 'Pasco' },
            { id: '20', nombre: 'Piura' },
            { id: '21', nombre: 'Puno' },
            { id: '22', nombre: 'San Martín' },
            { id: '23', nombre: 'Tacna' },
            { id: '24', nombre: 'Tumbes' },
            { id: '25', nombre: 'Ucayali' }
        ];

        const provinciasData = {
            '15': [
                { id: '1501', nombre: 'Lima' },
                { id: '1507', nombre: 'Huaura' },
                { id: '1508', nombre: 'Huarochirí' },
                { id: '1509', nombre: 'Cañete' }
            ]
            // Agregar más provincias según sea necesario
        };

        // Función para toggle de secciones
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = section.previousElementSibling.querySelector('.toggle-icon');

            section.classList.toggle('active');
            icon.textContent = section.classList.contains('active') ? '▲' : '▼';
        }

        // Función para cargar departamentos
        function cargarDepartamentos() {
            const departamentoSelect = document.getElementById('departamento');
            departamentosData.forEach(depto => {
                const option = document.createElement('option');
                option.value = depto.id;
                option.textContent = depto.nombre;
                departamentoSelect.appendChild(option);
            });
        }

        // Función para cargar provincias
        function cargarProvincias() {
            const departamentoSelect = document.getElementById('departamento');
            const provinciaSelect = document.getElementById('provincia');
            const distritoInput = document.getElementById('distrito');
            
            const departamentoId = departamentoSelect.value;
            
            // Reset provincias
            provinciaSelect.innerHTML = '<option value="">Seleccionar provincia</option>';
            provinciaSelect.disabled = true;
            
            // Reset distrito
            distritoInput.value = '';
            
            if (departamentoId && provinciasData[departamentoId]) {
                provinciaSelect.disabled = false;
                provinciasData[departamentoId].forEach(prov => {
                    const option = document.createElement('option');
                    option.value = prov.id;
                    option.textContent = prov.nombre;
                    provinciaSelect.appendChild(option);
                });
            }
            
            // Actualizar métodos de pago
            actualizarMetodosPago();
            actualizarCostoEnvio();
        }

        // Función para actualizar métodos de pago según ubicación
        function actualizarMetodosPago() {
            const departamentoSelect = document.getElementById('departamento');
            const provinciaSelect = document.getElementById('provincia');
            const paymentMethodsContainer = document.getElementById('payment-methods-container');
            
            const departamentoId = departamentoSelect.value;
            const provinciaId = provinciaSelect.value;
            
            let html = '';
            
            // Verificar si es Lima
            const esLima = departamentoId === '15' && provinciaId;
            
            if (esLima) {
                html = `
                    <div class="payment-option">
                        <input type="radio" id="contrareembolso" name="metodo_pago" value="contrareembolso" checked>
                        <label for="contrareembolso">Pago contra entrega</label>
                    </div>
                `;
            }
            
            html += `
                <div class="payment-option">
                    <input type="radio" id="tarjeta" name="metodo_pago" value="tarjeta" ${!esLima ? 'checked' : ''}>
                    <label for="tarjeta">Pago con tarjeta</label>
                </div>
            `;
            
            paymentMethodsContainer.innerHTML = html;
        }

        // Función para actualizar costo de envío
        function actualizarCostoEnvio() {
            const departamentoSelect = document.getElementById('departamento');
            const costoEnvioValor = document.getElementById('costo-envio-valor');
            
            if (!departamentoSelect || !costoEnvioValor) return;

            const departamentoId = departamentoSelect.value;
            let costoEnvio = 0;

            // Calcular costo según ubicación
            if (departamentoId === '15') {
                costoEnvio = 8; // Lima
            } else if (departamentoId) {
                costoEnvio = 12; // Provincia
            }

            costoEnvioValor.textContent = costoEnvio.toFixed(2);
            recalcularTotalFinal(costoEnvio);
        }

        // Función para recalcular el total final
        function recalcularTotalFinal(costoEnvio) {
            const subtotal = <?= $totales['subtotal'] ?? 0 ?>;
            const descuento = <?= $totales['descuento'] ?? 0 ?>;
            const descuentoCupon = <?= $descuento_cupon ?? 0 ?>;

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

        // Función para seleccionar dirección
        function selectAddress(card) {
            if (selectedAddressCard) {
                selectedAddressCard.classList.remove('selected');
            }

            selectedAddressCard = card;
            card.classList.add('selected');

            const direccionData = JSON.parse(card.dataset.direccion);
            document.getElementById('direccion_id_seleccionada').value = direccionData.id;

            // Ocultar formulario de nueva dirección
            document.getElementById('newAddressForm').classList.add('hidden');
        }

        // Función para eliminar dirección
        function eliminarDireccion(event, direccionId) {
            event.stopPropagation();

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

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar departamentos
            cargarDepartamentos();
            
            // Abrir primera sección por defecto
            toggleSection('envio-section');
            
            // Inicializar métodos de pago
            actualizarMetodosPago();
            
            // Preseleccionar primera dirección si existe
            const firstAddress = document.querySelector('.address-card');
            if (firstAddress) {
                selectAddress(firstAddress);
            }

            // Event listeners
            const guardarCheckbox = document.getElementById('guardar_direccion');
            const tipoDiv = document.getElementById('tipoDereccion');
            if (guardarCheckbox && tipoDiv) {
                tipoDiv.style.display = guardarCheckbox.checked ? 'grid' : 'none';
                guardarCheckbox.addEventListener('change', function() {
                    tipoDiv.style.display = this.checked ? 'grid' : 'none';
                });
            }

            // Validación del formulario
            document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
                const direccionId = document.getElementById('direccion_id_seleccionada').value;
                const newAddressForm = document.getElementById('newAddressForm');
                const isNewAddressVisible = !newAddressForm.classList.contains('hidden');

                if (!direccionId && !isNewAddressVisible) {
                    e.preventDefault();
                    alert('Por favor, selecciona una dirección o completa el formulario de nueva dirección.');
                    return false;
                }

                return true;
            });

            // Modal de términos
            const modal = document.getElementById('terms-modal');
            const openBtn = document.getElementById('open-terms-modal');
            const closeBtn = document.querySelector('#close-terms-modal');
            const acceptBtn = document.getElementById('accept-terms-btn');
            const checkbox = document.getElementById('terminos');
            const submitBtn = document.getElementById('confirm-order-btn');

            if (openBtn) {
                openBtn.onclick = function(e) {
                    e.preventDefault();
                    modal.style.display = 'flex';
                };
            }

            if (closeBtn) {
                closeBtn.onclick = function() {
                    modal.style.display = 'none';
                };
            }

            if (acceptBtn && checkbox && submitBtn) {
                acceptBtn.onclick = function() {
                    checkbox.checked = true;
                    submitBtn.disabled = false;
                    modal.style.display = 'none';
                };
            }

            if (checkbox && submitBtn) {
                checkbox.onchange = function() {
                    submitBtn.disabled = !this.checked;
                };
            }

            // Cerrar modal al hacer clic fuera
            if (modal) {
                modal.onclick = function(e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                };
            }
        });
    </script>

    <!-- Modal de términos y condiciones -->
    <div id="terms-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-container">
                    <h2 class="modal-title">Términos y Condiciones</h2>
                    <p class="modal-subtitle">Políticas y condiciones de uso de ByteBox</p>
                </div>
                <button type="button" id="close-terms-modal" class="modal-close">×</button>
            </div>

            <div class="modal-body">
                <div class="terms-section-modal">
                    <h3 class="section-title-modal">
                        <span class="section-number">1</span>
                        Información General
                    </h3>
                    <div class="section-content">
                        <p>Bienvenido a <strong>ByteBox</strong>. Al utilizar nuestro sitio web y realizar compras,
                        usted acepta estar sujeto a los siguientes términos y condiciones de uso y venta.</p>
                    </div>
                </div>

                <div class="terms-section-modal">
                    <h3 class="section-title-modal">
                        <span class="section-number">2</span>
                        Productos y Precios
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> Todos los precios están expresados en soles peruanos (S/) e incluyen IGV</p>
                        <p class="check-item"><span class="check-icon">✓</span> Los precios están sujetos a cambios sin previo aviso</p>
                        <p class="check-item"><span class="check-icon">✓</span> Los productos están sujetos a disponibilidad de stock</p>
                    </div>
                </div>

                <div class="terms-section-modal">
                    <h3 class="section-title-modal">
                        <span class="section-number">3</span>
                        Política de Envío
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> <strong>Envío gratuito</strong> a todo el Perú en compras mayores a S/ 100</p>
                        <p class="check-item"><span class="check-icon">✓</span> Tiempo de entrega: 2-5 días hábiles en Lima, 3-7 días en provincias</p>
                        <p class="check-item"><span class="check-icon">✓</span> Pago contra entrega disponible solo en Lima Metropolitana</p>
                    </div>
                </div>

                <div class="terms-section-modal">
                    <h3 class="section-title-modal">
                        <span class="section-number">4</span>
                        Métodos de Pago
                    </h3>
                    <div class="section-content">
                        <p class="check-item"><span class="check-icon">✓</span> <strong>Pago contra entrega:</strong> Solo disponible en Lima</p>
                        <p class="check-item"><span class="check-icon">✓</span> <strong>Pago con tarjeta:</strong> Disponible a nivel nacional</p>
                        <p class="check-item"><span class="check-icon">✓</span> Todas las transacciones son seguras y encriptadas</p>
                    </div>
                </div>

                <div class="terms-section-modal">
                    <h3 class="section-title-modal">
                        <span class="section-number">5</span>
                        Contacto y Soporte
                    </h3>
                    <div class="section-content">
                        <p>Para consultas, reclamos o soporte técnico:</p>
                        <div class="contact-info">
                            <p class="contact-item"><strong>Email:</strong> info@bytebox.com</p>
                            <p class="contact-item"><strong>Teléfono:</strong> +51 999 123 456</p>
                            <p class="contact-item"><strong>Horario:</strong> Lunes a Sábado 9:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="close-terms-btn" class="btn-secondary">Cerrar</button>
                <button type="button" id="accept-terms-btn" class="btn-accept-terms">Acepto los Términos</button>
            </div>
        </div>
    </div>

</body>

</html>