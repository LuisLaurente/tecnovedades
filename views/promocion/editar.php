<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/promocion.css') ?>">

<body>
    <div class="flex h-screen">
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>

                <div class="promociones-page flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="admin-container">
                        <div class="dashboard-header">
                            <h1 class="dashboard-title">Editar Promoción: <?= htmlspecialchars($promocion['nombre']) ?></h1>
                            <p class="dashboard-subtitle">Modifica la configuración de la promoción.</p>
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-error">
                                <?= htmlspecialchars($_SESSION['error']) ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-container">
                            <form method="POST" action="<?= url('promocion/actualizar/' . $promocion['id']) ?>" id="promocionForm">
                                <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
                                <div class="form-section">
                                    <h3 class="section-title">1. Información General</h3>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="nombre" class="form-label">Nombre de la Promoción *</label>
                                            <input type="text" id="nombre" name="nombre" class="form-input" value="<?= htmlspecialchars($promocion['nombre']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="prioridad" class="form-label">Prioridad</label>
                                            <select id="prioridad" name="prioridad" class="form-select">
                                                <option value="1" <?= $promocion['prioridad'] == 1 ? 'selected' : '' ?>>1 - Muy Alta</option>
                                                <option value="2" <?= $promocion['prioridad'] == 2 ? 'selected' : '' ?>>2 - Alta</option>
                                                <option value="3" <?= $promocion['prioridad'] == 3 ? 'selected' : '' ?>>3 - Media</option>
                                                <option value="4" <?= $promocion['prioridad'] == 4 ? 'selected' : '' ?>>4 - Baja</option>
                                                <option value="5" <?= $promocion['prioridad'] == 5 ? 'selected' : '' ?>>5 - Muy Baja</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-input" value="<?= $promocion['fecha_inicio'] ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-input" value="<?= $promocion['fecha_fin'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-grid-small">
                                        <div class="form-checkbox">
                                            <input type="checkbox" id="activo" name="activo" <?= $promocion['activo'] ? 'checked' : '' ?>>
                                            <label for="activo">Promoción activa</label>
                                        </div>
                                        <div class="form-checkbox">
                                            <input type="checkbox" id="acumulable" name="acumulable" <?= $promocion['acumulable'] ? 'checked' : '' ?>>
                                            <label for="acumulable">Acumulable con otras promociones</label>
                                        </div>
                                        <div class="form-checkbox">
                                            <input type="checkbox" id="exclusivo" name="exclusivo" <?= $promocion['exclusivo'] ? 'checked' : '' ?>>
                                            <label for="exclusivo">Promoción exclusiva (no se combina)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECCIÓN 2: TIPO DE REGLA Y CONFIGURACIÓN -->
                                <div class="form-section">
                                    <h3 class="section-title">2. Regla de Promoción</h3>
                                    <div class="form-group">
                                        <label for="tipo_regla" class="form-label">Tipo de Regla *</label>
                                        <select id="tipo_regla" name="tipo_regla" class="form-select" required>
                                            <option value="">-- Selecciona una regla --</option>
                                            <option value="descuento_subtotal">Si el subtotal supera un monto → Aplicar descuento</option>
                                            <option value="envio_gratis_primera_compra">Si es la primera compra del usuario → Envío gratis</option>
                                            <option value="nxm_producto">Si se agregan N unidades de un producto → Paga M unidades (Ej: 3x2)</option>
                                            <option value="descuento_enesima_unidad">Si se agregan N unidades de un producto → Descuento en la N-ésima unidad</option>
                                            <option value="descuento_menor_valor_categoria">Si se agregan N productos de una categoría → Descuento en el de menor valor</option>
                                        </select>
                                    </div>
                                    <div id="campos_dinamicos" class="dynamic-fields"></div>
                                </div>

                                <div class="form-buttons">
                                    <button type="submit" class="btn-submit">💾 Actualizar Promoción</button>
                                    <a href="<?= url('promocion/index') ?>" class="btn-cancel">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
            </main>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- DATOS DE LA PROMOCIÓN (inyectados desde PHP) ---
    const promocion = <?= json_encode($promocion) ?>;

    // --- CONFIGURACIÓN INICIAL ---
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    fechaFin.min = fechaInicio.value;
    fechaInicio.addEventListener('change', () => fechaFin.min = fechaInicio.value);

    const exclusivoCheckbox = document.getElementById('exclusivo');
    const acumulableCheckbox = document.getElementById('acumulable');
    if (exclusivoCheckbox.checked) {
        acumulableCheckbox.disabled = true;
    }
    exclusivoCheckbox.addEventListener('change', function() {
        if (this.checked) {
            acumulableCheckbox.checked = false;
            acumulableCheckbox.disabled = true;
        } else {
            acumulableCheckbox.disabled = false;
        }
    });

    // --- LÓGICA DE FORMULARIO DINÁMICO ---
    const tipoReglaSelect = document.getElementById('tipo_regla');
    const camposDinamicosContainer = document.getElementById('campos_dinamicos');

    tipoReglaSelect.addEventListener('change', manejarCambioDeRegla);

    function determinarReglaActual() {
        const cond = promocion.condicion;
        const acc = promocion.accion;

        if (cond.tipo === 'subtotal_minimo' && acc.tipo === 'descuento_porcentaje') return 'descuento_subtotal';
        if (cond.tipo === 'primera_compra' && acc.tipo === 'envio_gratis') return 'envio_gratis_primera_compra';
        if (cond.tipo === 'cantidad_producto_identico' && acc.tipo === 'compra_n_paga_m') return 'nxm_producto';
        if (cond.tipo === 'cantidad_producto_identico' && acc.tipo === 'descuento_enesima_unidad') return 'descuento_enesima_unidad';
        if (cond.tipo === 'cantidad_producto_categoria' && acc.tipo === 'descuento_menor_valor') return 'descuento_menor_valor_categoria';
        
        return '';
    }

    function manejarCambioDeRegla(valores = null) {
        const regla = tipoReglaSelect.value;
        camposDinamicosContainer.innerHTML = '';

        const cond = valores ? valores.condicion : {};
        const acc = valores ? valores.accion : {};

        const tipoCondicionInput = document.createElement('input');
        tipoCondicionInput.type = 'hidden';
        tipoCondicionInput.name = 'tipo_condicion';
        
        const tipoAccionInput = document.createElement('input');
        tipoAccionInput.type = 'hidden';
        tipoAccionInput.name = 'tipo_accion';

        switch (regla) {
            case 'descuento_subtotal':
                tipoCondicionInput.value = 'subtotal_minimo';
                tipoAccionInput.value = 'descuento_porcentaje';
                camposDinamicosContainer.innerHTML = `
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">Monto mínimo del carrito (S/)</label><input type="number" name="cond_subtotal_minimo" class="form-input" value="${cond.valor || ''}" required></div>
                        <div class="form-group"><label class="form-label">Porcentaje de descuento (%)</label><input type="number" name="accion_valor_descuento" class="form-input" value="${acc.valor || ''}" required></div>
                    </div>`;
                break;
            case 'envio_gratis_primera_compra':
                tipoCondicionInput.value = 'primera_compra';
                tipoAccionInput.value = 'envio_gratis';
                camposDinamicosContainer.innerHTML = `<p class="info-text">Esta regla no necesita configuración adicional.</p>`;
                break;
            case 'nxm_producto':
                tipoCondicionInput.value = 'cantidad_producto_identico';
                tipoAccionInput.value = 'compra_n_paga_m';
                camposDinamicosContainer.innerHTML = `
                    <p class="info-text">Aplica para un producto específico. Ej: Lleva 3, Paga 2.</p>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">ID del Producto</label><input type="number" name="cond_producto_id" class="form-input" value="${cond.producto_id || ''}" required></div>
                        <div class="form-group"><label class="form-label">Cantidad que lleva (N)</label><input type="number" name="accion_cantidad_lleva" class="form-input" value="${acc.cantidad_lleva || ''}" required></div>
                        <div class="form-group"><label class="form-label">Cantidad que paga (M)</label><input type="number" name="accion_cantidad_paga" class="form-input" value="${acc.cantidad_paga || ''}" required></div>
                    </div>`;
                break;
            case 'descuento_enesima_unidad':
                tipoCondicionInput.value = 'cantidad_producto_identico';
                tipoAccionInput.value = 'descuento_enesima_unidad';
                camposDinamicosContainer.innerHTML = `
                    <p class="info-text">Aplica un descuento a una unidad específica. Ej: 50% en la 3ra unidad.</p>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">ID del Producto</label><input type="number" name="cond_producto_id" class="form-input" value="${cond.producto_id || ''}" required></div>
                        <div class="form-group"><label class="form-label">N-ésima unidad a descontar</label><input type="number" name="accion_numero_unidad" class="form-input" value="${acc.numero_unidad || ''}" required></div>
                        <div class="form-group"><label class="form-label">Porcentaje de descuento (%)</label><input type="number" name="accion_descuento_unidad" class="form-input" value="${acc.descuento_unidad || ''}" required></div>
                    </div>`;
                break;
            case 'descuento_menor_valor_categoria':
                tipoCondicionInput.value = 'cantidad_producto_categoria';
                tipoAccionInput.value = 'descuento_menor_valor';
                camposDinamicosContainer.innerHTML = `
                    <p class="info-text">Aplica un descuento al producto más barato dentro de una categoría seleccionada.</p>
                    <div class="form-grid">
                        <div class="form-group"><label class="form-label">ID de la Categoría</label><input type="number" name="cond_categoria_id" class="form-input" value="${cond.categoria_id || ''}" required></div>
                        <div class="form-group"><label class="form-label">Cantidad mínima de productos</label><input type="number" name="cond_cantidad_min_categoria" class="form-input" value="${cond.cantidad_min || ''}" required></div>
                        <div class="form-group"><label class="form-label">Porcentaje de descuento (%)</label><input type="number" name="accion_descuento_menor_valor" class="form-input" value="${acc.valor || ''}" required></div>
                    </div>`;
                break;
        }
        if (regla) {
            camposDinamicosContainer.prepend(tipoCondicionInput);
            camposDinamicosContainer.prepend(tipoAccionInput);
        }
    }

    // --- INICIALIZACIÓN DEL FORMULARIO AL CARGAR ---
    const reglaActual = determinarReglaActual();
    if (reglaActual) {
        tipoReglaSelect.value = reglaActual;
        manejarCambioDeRegla(promocion);
    }
});
</script>

</body>
</html>
