<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/promocion.css') ?>">


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

                <div class="promociones-page flex-1 p-6 bg-gray-50 overflow-y-auto">

                    <div class="admin-container">
                        <!-- Header -->
                        <div class="dashboard-header">
                            <h1 class="dashboard-title">Crear Nueva Promoción</h1>
                            <p class="dashboard-subtitle">Configura una nueva promoción, descuento o cupón</p>
                        </div>

                        <!-- Mostrar errores -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-error">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <div class="form-container">
                            <h2 class="form-title">Información de la Promoción</h2>

                            <form method="POST" action="<?= url('promocion/guardar') ?>">
                                <div class="form-grid">
                                    <!-- Información básica -->
                                    <div class="form-group">
                                        <label for="nombre" class="form-label">Nombre de la Promoción *</label>
                                        <input type="text" id="nombre" name="nombre" class="form-input"
                                            placeholder="Ej: 10% descuento en primera compra" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="prioridad" class="form-label">Prioridad</label>
                                        <select id="prioridad" name="prioridad" class="form-select">
                                            <option value="1">1 - Muy Alta</option>
                                            <option value="2">2 - Alta</option>
                                            <option value="3" selected>3 - Media</option>
                                            <option value="4">4 - Baja</option>
                                            <option value="5">5 - Muy Baja</option>
                                        </select>
                                    </div>

                                    <!-- Fechas -->
                                    <div class="form-group">
                                        <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-input" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-input" required>
                                    </div>

                                    <!-- Configuración de la promoción -->
                                    <div class="form-section">
                                        <h3 class="section-title">⚙️ Configuración</h3>

                                        <div class="form-checkbox">
                                            <input type="checkbox" id="activo" name="activo" checked>
                                            <label for="activo">Promoción activa</label>
                                        </div>

                                        <div class="form-checkbox">
                                            <input type="checkbox" id="acumulable" name="acumulable" checked>
                                            <label for="acumulable">Se puede acumular con otras promociones</label>
                                        </div>

                                        <div class="form-checkbox">
                                            <input type="checkbox" id="exclusivo" name="exclusivo">
                                            <label for="exclusivo">Promoción exclusiva (no se puede combinar)</label>
                                        </div>
                                    </div>

                                    <!-- Condiciones -->
                                    <div class="form-section">
                                        <h3 class="section-title">🎯 Condiciones (¿Cuándo aplicar?)</h3>

                                        <div class="form-group">
                                            <label for="min_monto" class="form-label">Monto mínimo de compra (S/)</label>
                                            <input type="number" id="min_monto" name="min_monto" class="form-input"
                                                placeholder="0" step="0.01" min="0">
                                        </div>

                                        <div class="form-group">
                                            <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
                                            <select id="tipo_usuario" name="tipo_usuario" class="form-select">
                                                <option value="">Todos los usuarios</option>
                                                <option value="nuevo">Solo usuarios nuevos</option>
                                                <option value="recurrente">Solo usuarios recurrentes</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="categoria_id" class="form-label">Categoría específica (ID)</label>
                                            <input type="number" id="categoria_id" name="categoria_id" class="form-input"
                                                placeholder="Dejar vacío para todas las categorías">
                                        </div>

                                        <div class="form-group">
                                            <label for="producto_id" class="form-label">Producto específico (ID)</label>
                                            <input type="number" id="producto_id" name="producto_id" class="form-input"
                                                placeholder="Dejar vacío para todos los productos">
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="form-section">
                                        <h3 class="section-title">💫 Acción (¿Qué beneficio dar?)</h3>

                                        <div class="form-group">
                                            <label for="tipo_accion" class="form-label">Tipo de Beneficio *</label>
                                            <select id="tipo_accion" name="tipo_accion" class="form-select" required onchange="toggleValueField()">
                                                <option value="">Seleccionar tipo...</option>
                                                <option value="descuento_porcentaje">Descuento por porcentaje (%)</option>
                                                <option value="descuento_fijo">Descuento fijo (S/)</option>
                                                <option value="envio_gratis">Envío gratis</option>
                                                <option value="producto_gratis">Producto gratis</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="valor_group" style="display: none;">
                                            <label for="valor_descuento" class="form-label">Valor del Descuento</label>
                                            <input type="number" id="valor_descuento" name="valor_descuento" class="form-input"
                                                placeholder="Ej: 10 para 10% o 50 para S/50" step="0.01" min="0">
                                        </div>

                                        <div class="form-group" id="producto_gratis_group" style="display: none;">
                                            <label for="producto_gratis_id" class="form-label">ID del Producto Gratis</label>
                                            <input type="number" id="producto_gratis_id" name="producto_gratis_id" class="form-input"
                                                placeholder="ID del producto a regalar">
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="form-buttons">
                                        <button type="submit" class="btn-submit">💾 Guardar Promoción</button>
                                        <a href="<?= url('promocion/index') ?>" class="btn-cancel">Cancelar</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-4">
                        <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

<script>
    function toggleValueField() {
        const tipoAccion = document.getElementById('tipo_accion').value;
        const valorGroup = document.getElementById('valor_group');
        const productoGratisGroup = document.getElementById('producto_gratis_group');

        // Ocultar todos los grupos
        valorGroup.style.display = 'none';
        productoGratisGroup.style.display = 'none';

        // Mostrar según el tipo
        if (tipoAccion === 'descuento_porcentaje' || tipoAccion === 'descuento_fijo') {
            valorGroup.style.display = 'block';
            document.getElementById('valor_descuento').required = true;
        } else if (tipoAccion === 'producto_gratis') {
            productoGratisGroup.style.display = 'block';
            document.getElementById('producto_gratis_id').required = true;
        } else {
            document.getElementById('valor_descuento').required = false;
            document.getElementById('producto_gratis_id').required = false;
        }
    }

    // Configurar fecha mínima como hoy
    document.addEventListener('DOMContentLoaded', function() {
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('fecha_inicio').value = hoy;
        document.getElementById('fecha_inicio').min = hoy;
        document.getElementById('fecha_fin').min = hoy;

        // Cuando cambie fecha inicio, actualizar fecha fin mínima
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            document.getElementById('fecha_fin').min = this.value;
        });
    });

    // Manejar exclusividad
    document.getElementById('exclusivo').addEventListener('change', function() {
        const acumulable = document.getElementById('acumulable');
        if (this.checked) {
            acumulable.checked = false;
            acumulable.disabled = true;
        } else {
            acumulable.disabled = false;
        }
    });

    document.getElementById('acumulable').addEventListener('change', function() {
        const exclusivo = document.getElementById('exclusivo');
        if (!this.checked) {
            exclusivo.disabled = false;
        }
    });
</script>

</body>

</html>