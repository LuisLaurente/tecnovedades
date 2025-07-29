<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cupón - TecnoVedades</title>
    <link rel="stylesheet" href="<?= url('css/cupon.css') ?>">
</head>
<body>
    <div class="cupon-admin">
        <!-- Header -->
        <div class="cupon-header">
            <h1>Crear Nuevo Cupón</h1>
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
        <form method="POST" action="<?= url('cupon/guardar') ?>" class="formulario-cupon">
            <h2>Información del Cupón</h2>

            <!-- Código y Tipo -->
            <div class="form-row">
                <div class="form-group <?= isset($errores['codigo']) ? 'has-error' : '' ?>">
                    <label for="codigo">Código del Cupón *</label>
                    <input type="text" 
                           id="codigo" 
                           name="codigo" 
                           value="<?= htmlspecialchars($codigo ?? '') ?>"
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
                        <option value="porcentaje" <?= isset($tipo) && $tipo === 'porcentaje' ? 'selected' : '' ?>>
                            Porcentaje (%)
                        </option>
                        <option value="monto_fijo" <?= isset($tipo) && $tipo === 'monto_fijo' ? 'selected' : '' ?>>
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
                           value="<?= htmlspecialchars($valor ?? '') ?>"
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
                           value="<?= htmlspecialchars($monto_minimo ?? '') ?>"
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
                           value="<?= htmlspecialchars($fecha_inicio ?? date('Y-m-d')) ?>"
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
                           value="<?= htmlspecialchars($fecha_fin ?? '') ?>"
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
                           value="<?= htmlspecialchars($limite_uso ?? '') ?>"
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
                           value="<?= htmlspecialchars($limite_por_usuario ?? '') ?>"
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
                          placeholder="IDs de usuarios separados por comas (ej: 1,2,3). Deja vacío para todos los usuarios."><?= htmlspecialchars($usuarios_autorizados ?? '') ?></textarea>
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
                           <?= isset($activo) && $activo ? 'checked' : 'checked' ?>>
                    <label for="activo">Cupón activo</label>
                </div>
                <small style="color: #666; font-size: 0.85rem;">
                    Los cupones inactivos no pueden ser utilizados
                </small>
            </div>

            <!-- Botones -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Crear Cupón
                </button>
                <a href="<?= url('cupon') ?>" class="btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
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

        // Auto-generar código basado en tipo y valor
        document.getElementById('tipo').addEventListener('change', generateCode);
        document.getElementById('valor').addEventListener('input', generateCode);

        function generateCode() {
            const codigo = document.getElementById('codigo');
            if (codigo.value) return; // No sobrescribir si ya hay código
            
            const tipo = document.getElementById('tipo').value;
            const valor = document.getElementById('valor').value;
            
            if (tipo && valor) {
                if (tipo === 'porcentaje') {
                    codigo.value = `DESC${valor}PCT`;
                } else {
                    codigo.value = `DESC${valor}SOL`;
                }
            }
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            toggleValorInput();
            
            // Configurar fecha mínima
            const fechaInicio = document.getElementById('fecha_inicio');
            const hoy = new Date().toISOString().split('T')[0];
            fechaInicio.setAttribute('min', hoy);
        });
    </script>
</body>
</html>
