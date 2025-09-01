<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
    <link rel="stylesheet" href="<?= url('css/cupon.css') ?>">

<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">

            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <!-- Incluir header superior fijo -->
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="cupon-admin">
                        <!-- Header -->
                        <div class="cupon-header">
                            <h1>Editar Cupón: <?= htmlspecialchars($cupon['codigo']) ?></h1>
                            <a href="<?= url('cupon') ?>" class="btn-secondary">
                                ← Volver a la lista
                            </a>
                        </div>

                        <!-- Alertas de error general -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form method="POST" action="<?= url('cupon/actualizar/' . $cupon['id']) ?>" class="formulario-cupon">
                            <h2>Información del Cupón</h2>

                            <!-- Código y Tipo -->
                            <div class="form-row">
                                <div class="form-group <?= isset($errores['codigo']) ? 'has-error' : '' ?>">
                                    <label for="codigo">Código del Cupón *</label>
                                    <input type="text"
                                        id="codigo"
                                        name="codigo"
                                        value="<?= htmlspecialchars($codigo ?? $cupon['codigo']) ?>"
                                        placeholder="Ej: DESCUENTO20"
                                        maxlength="20"
                                        required>
                                    <?php if (isset($errores['codigo'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['codigo']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group <?= isset($errores['tipo']) ? 'has-error' : '' ?>">
                                    <label for="tipo">Tipo de Descuento *</label>
                                    <select id="tipo" name="tipo" required onchange="toggleValorInput()">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="porcentaje" <?= ($tipo ?? $cupon['tipo']) === 'porcentaje' ? 'selected' : '' ?>>
                                            Porcentaje (%)
                                        </option>
                                        <option value="monto_fijo" <?= ($tipo ?? $cupon['tipo']) === 'monto_fijo' ? 'selected' : '' ?>>
                                            Monto Fijo (S/)
                                        </option>
                                    </select>
                                    <?php if (isset($errores['tipo'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['tipo']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Valor y Monto mínimo -->
                            <div class="form-row">
                                <div class="form-group <?= isset($errores['valor']) ? 'has-error' : '' ?>">
                                    <label for="valor">
                                        <span id="valor-label">Valor del Descuento *</span>
                                    </label>
                                    <input type="number"
                                        id="valor"
                                        name="valor"
                                        value="<?= htmlspecialchars($valor ?? $cupon['valor']) ?>"
                                        step="0.01"
                                        min="0"
                                        required>
                                    <?php if (isset($errores['valor'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['valor']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group <?= isset($errores['monto_minimo']) ? 'has-error' : '' ?>">
                                    <label for="monto_minimo">Monto Mínimo de Compra (S/)</label>
                                    <input type="number"
                                        id="monto_minimo"
                                        name="monto_minimo"
                                        value="<?= htmlspecialchars($monto_minimo ?? $cupon['monto_minimo']) ?>"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00">
                                    <?php if (isset($errores['monto_minimo'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['monto_minimo']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div class="form-row">
                                <div class="form-group <?= isset($errores['fecha_inicio']) ? 'has-error' : '' ?>">
                                    <label for="fecha_inicio">Fecha de Inicio *</label>
                                    <input type="date"
                                        id="fecha_inicio"
                                        name="fecha_inicio"
                                        value="<?= htmlspecialchars($fecha_inicio ?? $cupon['fecha_inicio']) ?>"
                                        required>
                                    <?php if (isset($errores['fecha_inicio'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['fecha_inicio']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group <?= isset($errores['fecha_fin']) ? 'has-error' : '' ?>">
                                    <label for="fecha_fin">Fecha de Fin *</label>
                                    <input type="date"
                                        id="fecha_fin"
                                        name="fecha_fin"
                                        value="<?= htmlspecialchars($fecha_fin ?? $cupon['fecha_fin']) ?>"
                                        required>
                                    <?php if (isset($errores['fecha_fin'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['fecha_fin']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Límites -->
                            <h2>Configuración de Límites</h2>

                            <div class="form-row">
                                <div class="form-group <?= isset($errores['limite_uso']) ? 'has-error' : '' ?>">
                                    <label for="limite_uso">Límite Total de Usos</label>
                                    <input type="number"
                                        id="limite_uso"
                                        name="limite_uso"
                                        value="<?= htmlspecialchars($limite_uso ?? $cupon['limite_uso']) ?>"
                                        min="1"
                                        placeholder="Sin límite">
                                    <small style="color: #666; font-size: 0.85rem;">
                                        Deja vacío para uso ilimitado
                                    </small>
                                    <?php if (isset($errores['limite_uso'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['limite_uso']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group <?= isset($errores['limite_por_usuario']) ? 'has-error' : '' ?>">
                                    <label for="limite_por_usuario">Límite por Usuario</label>
                                    <input type="number"
                                        id="limite_por_usuario"
                                        name="limite_por_usuario"
                                        value="<?= htmlspecialchars($limite_por_usuario ?? $cupon['limite_por_usuario']) ?>"
                                        min="1"
                                        placeholder="Sin límite">
                                    <small style="color: #666; font-size: 0.85rem;">
                                        Máximo de veces que un usuario puede usar este cupón
                                    </small>
                                    <?php if (isset($errores['limite_por_usuario'])): ?>
                                        <span class="error"><?= htmlspecialchars($errores['limite_por_usuario']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Usuarios autorizados -->
                            <div class="form-group">
                                <label for="usuarios_autorizados">Usuarios Autorizados (opcional)</label>
                                <textarea id="usuarios_autorizados"
                                    name="usuarios_autorizados"
                                    rows="3"
                                    placeholder="IDs de usuarios separados por comas (ej: 1,2,3). Deja vacío para todos los usuarios."><?php
                                                                                                                                        // Mostrar usuarios autorizados correctamente
                                                                                                                                        $usuarios_mostrar = $usuarios_autorizados ?? '';
                                                                                                                                        if (empty($usuarios_mostrar) && !empty($cupon['usuarios_autorizados'])) {
                                                                                                                                            $usuarios_json = json_decode($cupon['usuarios_autorizados'], true);
                                                                                                                                            if (is_array($usuarios_json)) {
                                                                                                                                                $usuarios_mostrar = implode(',', $usuarios_json);
                                                                                                                                            } else {
                                                                                                                                                $usuarios_mostrar = $cupon['usuarios_autorizados'];
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                        echo htmlspecialchars($usuarios_mostrar);
                                                                                                                                        ?></textarea>
                                <small style="color: #666; font-size: 0.85rem;">
                                    Si especificas usuarios, solo ellos podrán usar este cupón
                                </small>
                            </div>

                            <!-- Estado -->
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox"
                                        id="activo"
                                        name="activo"
                                        value="1"
                                        <?= (isset($activo) && $activo) || (!isset($activo) && $cupon['activo']) ? 'checked' : '' ?>>
                                    <label for="activo">Cupón activo</label>
                                </div>
                                <small style="color: #666; font-size: 0.85rem;">
                                    Los cupones inactivos no pueden ser utilizados
                                </small>
                            </div>

                            <!-- Información de uso -->
                            <?php
                            $cuponModel = new \Models\Cupon();
                            $usosActuales = $cuponModel->contarUsos($cupon['id']);
                            ?>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                                <h3 style="margin: 0 0 10px 0; color: #666;">Información de Uso</h3>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                    <div>
                                        <strong>Usos actuales:</strong> <?= $usosActuales ?>
                                        <?php if ($cupon['limite_uso']): ?>
                                            / <?= $cupon['limite_uso'] ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong>Fecha de creación:</strong>
                                        <?= isset($cupon['created_at']) ? date('d/m/Y H:i', strtotime($cupon['created_at'])) : 'No disponible' ?>
                                    </div>
                                    <div>
                                        <strong>Estado actual:</strong>
                                        <?php
                                        $controller = new \Controllers\CuponController();
                                        $reflection = new ReflectionClass($controller);
                                        $method = $reflection->getMethod('determinarEstadoVigencia');
                                        $method->setAccessible(true);
                                        $estadoVigencia = $method->invoke($controller, $cupon);

                                        switch ($estadoVigencia) {
                                            case 'vigente':
                                                echo '<span style="color: #28a745;">Vigente</span>';
                                                break;
                                            case 'expirado':
                                                echo '<span style="color: #dc3545;">Expirado</span>';
                                                break;
                                            case 'pendiente':
                                                echo '<span style="color: #ffc107;">Pendiente</span>';
                                                break;
                                            case 'inactivo':
                                                echo '<span style="color: #6c757d;">Inactivo</span>';
                                                break;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    Actualizar Cupón
                                </button>
                                <a href="<?= url('cupon') ?>" class="btn-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Función para cambiar el label del valor según el tipo
        function toggleValorInput() {
            const tipo = document.getElementById('tipo').value;
            const valorLabel = document.getElementById('valor-label');
            const valorInput = document.getElementById('valor');

            if (tipo === 'porcentaje') {
                valorLabel.textContent = 'Porcentaje de Descuento (%) *';
                valorInput.setAttribute('max', '100');
                valorInput.setAttribute('placeholder', 'Ej: 20');
            } else if (tipo === 'monto_fijo') {
                valorLabel.textContent = 'Monto de Descuento (S/) *';
                valorInput.removeAttribute('max');
                valorInput.setAttribute('placeholder', 'Ej: 50.00');
            } else {
                valorLabel.textContent = 'Valor del Descuento *';
                valorInput.removeAttribute('max');
                valorInput.setAttribute('placeholder', '');
            }
        }

        // Validación de fechas
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            const fechaInicio = this.value;
            const fechaFin = document.getElementById('fecha_fin');

            if (fechaInicio) {
                fechaFin.setAttribute('min', fechaInicio);

                // Si la fecha fin es anterior a la de inicio, limpiarla
                if (fechaFin.value && fechaFin.value < fechaInicio) {
                    fechaFin.value = '';
                }
            }
        });

        // Advertencia si se modifica un cupón con usos
        const usosActuales = <?= $usosActuales ?>;
        if (usosActuales > 0) {
            const form = document.querySelector('.formulario-cupon');
            const warning = document.createElement('div');
            warning.className = 'alert alert-warning';
            warning.innerHTML = `
                <strong>⚠️ Atención:</strong> Este cupón ya ha sido utilizado ${usosActuales} vez(es). 
                Modificarlo puede afectar a usuarios que ya lo han usado.
            `;
            warning.style.cssText = `
                background-color: #fff3cd;
                color: #856404;
                border: 1px solid #ffeaa7;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            `;
            form.insertBefore(warning, form.firstChild.nextSibling);
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            toggleValorInput();
        });
    </script>
</body>

</html>