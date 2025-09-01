<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <main class="flex-1 ml-64 p-2 bg-gray-50 overflow-y-auto">
            <!-- Incluir header superior fijo -->
            <div class="sticky top-0 z-40">
                <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
            </div>

            <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                <div class="max-w-6xl mx-auto">
                    
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">📦 Mis Pedidos</h1>
                                <p class="text-gray-600 mt-1">Historial completo de tus compras</p>
                            </div>
                            <div class="flex gap-3">
                                <a href="<?= url('/producto/index') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    Seguir Comprando
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-xl p-6 shadow-sm border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Pedidos</p>
                                    <p class="text-2xl font-bold text-gray-900"><?= count($pedidos) ?></p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl p-6 shadow-sm border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Gastado</p>
                                    <p class="text-2xl font-bold text-gray-900">S/ <?= number_format(array_sum(array_column($pedidos, 'monto_total')), 2) ?></p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl p-6 shadow-sm border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Entregados</p>
                                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($pedidos, fn($p) => $p['estado'] === 'entregado')) ?></p>
                                </div>
                                <div class="p-3 bg-emerald-100 rounded-lg">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl p-6 shadow-sm border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">En Proceso</p>
                                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($pedidos, fn($p) => in_array($p['estado'], ['pendiente', 'procesando', 'enviado']))) ?></p>
                                </div>
                                <div class="p-3 bg-yellow-100 rounded-lg">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Pedidos -->
                    <div class="bg-white rounded-xl shadow-sm border">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Historial de Pedidos</h2>
                        </div>
                        
                        <?php if (empty($pedidos)): ?>
                            <div class="p-12 text-center">
                                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Sin pedidos aún</h3>
                                <p class="text-gray-600 mb-6">¡Comienza a explorar nuestros productos y realiza tu primera compra!</p>
                                <a href="<?= url('/producto/index') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    Explorar Productos
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($pedidos as $pedidozz): ?>
                                    <div class="p-6 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                        <span class="text-white font-bold">#<?= $pedidozz['id'] ?></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-medium text-gray-900">Pedido #<?= $pedidozz['id'] ?></h3>
                                                    <p class="text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($pedidozz['creado_en'])) ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <div class="text-right">
                                                    <?php 
                                                    // Calcular total del pedido
                                                    $totalPedido = $pedidozz['total'] ?? $pedidozz['monto_total'] ?? 0;
                                                    if ($totalPedido == 0 && isset($pedidozz['detalles']) && is_array($pedidozz['detalles'])) {
                                                        foreach ($pedidozz['detalles'] as $detalle) {
                                                            $precio = floatval($detalle['precio_unitario'] ?? 0);
                                                            $cantidad = intval($detalle['cantidad'] ?? 0);
                                                            $totalPedido += $precio * $cantidad;
                                                            
                                                        }
                                                    }
                                                    ?>
                                                    <p class="text-lg font-bold text-gray-900">$<?= number_format($totalPedido, 2) ?></p>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        <?php 
                                                        switch($pedido['estado']) {
                                                            case 'entregado':
                                                                echo 'bg-green-100 text-green-800';
                                                                break;
                                                            case 'enviado':
                                                                echo 'bg-blue-100 text-blue-800';
                                                                break;
                                                            case 'procesando':
                                                                echo 'bg-yellow-100 text-yellow-800';
                                                                break;
                                                            case 'cancelado':
                                                                echo 'bg-red-100 text-red-800';
                                                                break;
                                                            default:
                                                                echo 'bg-gray-100 text-gray-800';
                                                        }
                                                        ?>">
                                                        <?= ucfirst($pedido['estado']) ?>
                                                    </span>
                                                </div>
                                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                                <button onclick="mostrarDetallePedido(<?= $pedidozz['id'] ?>)" 
                                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                                    Ver detalles →
                                                </button>
                                                
                                                <button onclick="abrirModalResena(<?= $pedidozz['id'] ?>)" 
                                                    class="text-yellow-600 hover:text-yellow-800 font-medium">
                                                Dejar reseña
                                            </button>

                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Información adicional -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                            <div>
                                                <strong>Dirección de envío:</strong><br>
                                                <?= htmlspecialchars($pedidozz['direccion_envio'] ?? 'No disponible') ?>
                                            </div>
                                            <div>
                                                <strong>Productos:</strong><br>
                                                <?php 
                                                $totalItems = count($pedidozz['detalles'] ?? []);
                                                if ($totalItems > 0): 
                                                ?>
                                                    <?= $totalItems ?> producto<?= $totalItems > 1 ? 's' : '' ?>
                                                <?php else: ?>
                                                    Sin detalles disponibles
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para mostrar detalles del pedido -->
    <div id="modalDetallePedido" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop">

        <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Header del modal -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        <span class="mr-2">📦</span>
                        Detalles del Pedido #<span id="modalPedidoId"></span>
                    </h2>
                    <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                        ×
                    </button>
                </div>

                <!-- Contenido del modal -->
                <div id="modalContenido" class="space-y-6">
                    <!-- El contenido se cargará dinámicamente -->
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="text-gray-500 mt-2">Cargando detalles...</p>
                    </div>
                </div>

                <!-- Footer del modal -->
                <div class="flex justify-end pt-6 border-t">
                    <button onclick="cerrarModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reseña -->
<div id="modalResena" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 modal-backdrop"">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-lg">
        <h2 class="text-xl font-bold text-gray-900 mb-4">📝 Dejar Reseña</h2>

        <form id="formResena" method="POST" action="<?= url('review/guardar') ?>">
            <!-- <input type="hidden" name="producto_id" id="resenaProductoId"> -->
            <input type="hidden" name="pedido_id" id="resenaOrdenId">

            <!-- Estrellas -->
            <div class="flex items-center gap-1 mb-4">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="estrella text-3xl cursor-pointer text-gray-300" data-value="<?= $i ?>">★</span>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="puntuacion" id="resenaPuntuacion" required>

            <!-- Título -->
            <div class="mb-3">
                <label class="block text-sm font-medium">Título</label>
                <input type="text" name="titulo" maxlength="255" class="w-full border rounded-lg p-2">
            </div>

            <!-- Comentario -->
            <div class="mb-3">
                <label class="block text-sm font-medium">Comentario</label>
                <textarea name="texto" class="w-full border rounded-lg p-2" rows="3" required></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="cerrarModalResena()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Cancelar</button>
                <button type="submit" href="javascript:history.back()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Enviar</button>
            </div>
        </form>
    </div>
</div>


    <script>
        // Datos de pedidos disponibles (pasados desde PHP)
        const pedidosData = <?= json_encode($pedidos) ?>;

        function mostrarDetallePedido(pedidoId) {
            const pedido = pedidosData.find(p => p.id == pedidoId);
            if (!pedido) {
                // Si no se encuentra en los datos locales, cargar vía AJAX
                cargarDetallePedidoAjax(pedidoId);
                return;
            }

            mostrarModalConDatos(pedido);
        }

        function mostrarModalConDatos(pedido) {
            // Mostrar el modal
            document.getElementById('modalDetallePedido').classList.remove('hidden');
            document.getElementById('modalPedidoId').textContent = pedido.id;

            // Generar el contenido del modal
            const contenido = generarContenidoDetalle(pedido);
            document.getElementById('modalContenido').innerHTML = contenido;
        }

        function cargarDetallePedidoAjax(pedidoId) {
            // Mostrar modal con loading
            document.getElementById('modalDetallePedido').classList.remove('hidden');
            document.getElementById('modalPedidoId').textContent = pedidoId;
            document.getElementById('modalContenido').innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="text-gray-500 mt-2">Cargando detalles...</p>
                </div>
            `;

            // Hacer petición AJAX
            fetch('<?= url("/usuario/detallePedido/") ?>' + pedidoId, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contenido = generarContenidoDetalle(data.pedido);
                    document.getElementById('modalContenido').innerHTML = contenido;
                } else {
                    document.getElementById('modalContenido').innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-red-500 text-6xl mb-4">⚠️</div>
                            <p class="text-red-600 font-semibold">Error al cargar detalles</p>
                            <p class="text-gray-500">${data.error || 'Error desconocido'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('modalContenido').innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-500 text-6xl mb-4">❌</div>
                        <p class="text-red-600 font-semibold">Error de conexión</p>
                        <p class="text-gray-500">No se pudo cargar la información</p>
                    </div>
                `;
            });
        }

        function cerrarModal() {
            document.getElementById('modalDetallePedido').classList.add('hidden');
        }

        function generarContenidoDetalle(pedido) {
            const estadoClass = getEstadoClass(pedido.estado);
            const fechaCreacion = new Date(pedido.creado_en).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Calcular total desde los detalles si el total es 0 o no existe
            let totalCalculado = parseFloat(pedido.total || 0);
            if (totalCalculado === 0 && pedido.detalles && pedido.detalles.length > 0) {
                totalCalculado = pedido.detalles.reduce((sum, detalle) => {
                    const precio = parseFloat(detalle.precio_unitario || 0);
                    const cantidad = parseInt(detalle.cantidad || 0);
                    return sum + (precio * cantidad);
                }, 0);
            }

            let productosHtml = '';
            if (pedido.detalles && pedido.detalles.length > 0) {
                productosHtml = pedido.detalles.map(detalle => `
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${detalle.producto_nombre || 'Producto'}</h4>
                            <p class="text-sm text-gray-600">
                                Cantidad: ${detalle.cantidad} × $${parseFloat(detalle.precio_unitario || 0).toFixed(2)}
                            </p>
                            ${detalle.variante_nombre ? `<p class="text-xs text-gray-500">Variante: ${detalle.variante_nombre}</p>` : ''}
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-900">
                                $${(parseFloat(detalle.precio_unitario || 0) * parseInt(detalle.cantidad || 0)).toFixed(2)}
                            </p>
                        </div>
                    </div>
                `).join('');
            } else {
                productosHtml = '<p class="text-gray-500 text-center py-4">No hay detalles de productos disponibles</p>';
            }

            return `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Información del pedido -->
                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-900 mb-2">📋 Información General</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fecha:</span>
                                    <span class="font-medium">${fechaCreacion}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Estado:</span>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium ${estadoClass}">
                                        ${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1)}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-bold text-lg text-green-600">$${totalCalculado.toFixed(2)}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dirección de envío -->
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-900 mb-2">🚚 Dirección de Envío</h3>
                            <p class="text-sm text-gray-700">
                                ${pedido.direccion_envio || 'Dirección no disponible'}
                            </p>
                        </div>
                    </div>

                    <!-- Productos del pedido -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">🛍️ Productos (${pedido.detalles ? pedido.detalles.length : 0})</h3>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            ${productosHtml}
                        </div>
                        ${pedido.detalles && pedido.detalles.length > 0 ? `
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900">Total del Pedido:</span>
                                <span class="font-bold text-xl text-green-600">$${totalCalculado.toFixed(2)}</span>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        function getEstadoClass(estado) {
            switch(estado.toLowerCase()) {
                case 'pendiente':
                    return 'bg-yellow-100 text-yellow-800';
                case 'confirmado':
                    return 'bg-blue-100 text-blue-800';
                case 'en_proceso':
                    return 'bg-purple-100 text-purple-800';
                case 'enviado':
                    return 'bg-indigo-100 text-indigo-800';
                case 'entregado':
                    return 'bg-green-100 text-green-800';
                case 'cancelado':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('modalDetallePedido').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });

        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });


        //script del modal reseña

        function abrirModalResena(ordenId, productoId = null) {
            document.getElementById('modalResena').classList.remove('hidden');
            document.getElementById('resenaOrdenId').value = ordenId;
            document.getElementById('resenaProductoId').value = productoId ?? 0;
        }

        function cerrarModalResena() {
            document.getElementById('modalResena').classList.add('hidden');
        }

        // Manejo de estrellas
        document.querySelectorAll('.estrella').forEach(star => {
            star.addEventListener('click', function () {
                let valor = this.dataset.value;
                document.getElementById('resenaPuntuacion').value = valor;

                // Resetear colores
                document.querySelectorAll('.estrella').forEach(s => s.classList.remove('text-yellow-400'));
                // Pintar hasta la seleccionada
                for (let i = 0; i < valor; i++) {
                    document.querySelectorAll('.estrella')[i].classList.add('text-yellow-400');
                }
            });
        });

    </script>
</body>
</html>