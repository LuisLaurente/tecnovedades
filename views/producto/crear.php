<?php require_once __DIR__ . '/../../Core/helpers/urlHelper.php'; ?>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
}

.form-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    padding: 40px;
    position: relative;
}

.form-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 15px 15px 0 0;
}

h2 {
    color: #333;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    text-align: center;
    position: relative;
}

h2::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    background: #667eea;
    margin: 1rem auto;
    border-radius: 2px;
}

h3 {
    color: #444;
    font-size: 1.5rem;
    margin: 2rem 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f0f0f0;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

input[type="text"],
input[type="number"],
textarea,
select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    font-family: inherit;
}

input[type="text"]:focus,
input[type="number"]:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

textarea {
    resize: vertical;
    min-height: 100px;
}

input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 2px dashed #e1e5e9;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
}

input[type="file"]:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

select[multiple] {
    min-height: 120px;
    padding: 10px;
}

.checkbox-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e1e5e9;
}

.checkbox-container label {
    display: flex;
    align-items: center;
    margin: 8px 0;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-container input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
    transform: scale(1.2);
}

.visible-checkbox {
    display: flex;
    align-items: center;
    background: #e8f5e8;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #c3e6c3;
}

.visible-checkbox input[type="checkbox"] {
    width: auto;
    margin-right: 15px;
    transform: scale(1.3);
}

.visible-checkbox label {
    margin: 0;
    color: #2e7d32;
    font-weight: 600;
}

.variantes-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border: 1px solid #e1e5e9;
}

.variante {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px;
    padding: 20px;
    margin: 15px 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border: 1px solid #e1e5e9;
}

.variante label {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.variante input {
    margin-bottom: 0;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    min-width: 120px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    text-decoration: none;
}

.btn-secondary:hover {
    background: #5a6268;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.btn-add {
    background: #17a2b8;
    color: white;
    margin: 15px 0;
}

.btn-add:hover {
    background: #138496;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #f0f0f0;
}

@media (max-width: 768px) {
    body {
        padding: 10px;
    }
    
    .form-container {
        padding: 25px;
    }
    
    h2 {
        font-size: 2rem;
    }
    
    .variante {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<div class="form-container">
    <h2>Crear Nuevo Producto</h2>

    <form action="<?= url('producto/guardar') ?>" method="POST" enctype="multipart/form-data">
    <form action="<?= url('producto/guardar') ?>" method="POST" enctype="multipart/form-data">
        <!-- Nombre -->
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" name="nombre" id="nombre" required placeholder="Ingrese el nombre del producto">
        </div>

        <!-- Descripción -->
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" required placeholder="Describa las características del producto"></textarea>
        </div>

        <!-- Precio -->
        <div class="form-group">
            <label for="precio">Precio (S/.)</label>
            <input type="number" step="0.01" name="precio" id="precio" required placeholder="0.00">
        </div>

        <!-- Stock -->
        <div class="form-group">
            <label for="stock">Stock Inicial</label>
            <input type="number" name="stock" id="stock" required placeholder="Cantidad disponible">
        </div>

        <!-- Visible -->
        <div class="form-group">
            <div class="visible-checkbox">
                <input type="checkbox" name="visible" id="visible" value="1" checked>
                <label for="visible">Producto visible en la tienda</label>
            </div>
        </div>

        <!-- Imágenes -->
        <div class="form-group">
            <label for="imagenes">Imágenes del Producto</label>
            <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*">
        </div>

        <!-- Etiquetas -->
        <div class="form-group">
            <label for="etiquetas">Etiquetas</label>
            <select name="etiquetas[]" id="etiquetas" multiple>
                <?php foreach ($etiquetas as $et): ?>
                    <option value="<?= $et['id'] ?>" <?= in_array($et['id'], $etiquetasAsignadas ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($et['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Categorías -->
        <h3>📋 Categorías</h3>
        <div class="checkbox-container">
            <?php
            function renderCheckboxCategorias($categorias, $padre = null, $nivel = 0)
            {
                foreach ($categorias as $cat) {
                    if ($cat['id_padre'] == $padre) {
                        $margen = $nivel * 20;
                        echo "<div style='margin-left: {$margen}px'>";
                        echo "<label>";
                        echo "<input type='checkbox' name='categorias[]' value='{$cat['id']}'> ";
                        echo htmlspecialchars($cat['nombre']);
                        echo "</label>";
                        echo "</div>";
                        renderCheckboxCategorias($categorias, $cat['id'], $nivel + 1);
                    }
                }
            }
            renderCheckboxCategorias($categorias);
            ?>
        </div>

        <!-- Variantes -->
        <h3>🎨 Variantes del Producto</h3>
        <div class="variantes-section">
            <div id="variantes-container">
                <div class="variante">
                    <div>
                        <label>Talla</label>
                        <input type="text" name="variantes[talla][]" placeholder="Ej: S, M, L, XL">
                    </div>
                    <div>
                        <label>Color</label>
                        <input type="text" name="variantes[color][]" placeholder="Ej: Rojo, Azul">
                    </div>
                    <div>
                        <label>Stock</label>
                        <input type="number" name="variantes[stock][]" placeholder="Cantidad">
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-add" onclick="agregarVariante()">+ Agregar Variante</button>
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
            <button type="submit" class="btn btn-success">💾 Guardar Producto</button>
            <a href="<?= url('producto') ?>" class="btn btn-secondary">← Cancelar</a>
        </div>
    </form>
</div>

<script>
    function agregarVariante() {
        const container = document.getElementById('variantes-container');
        const html = `
            <div class="variante">
                <div>
                    <label>Talla</label>
                    <input type="text" name="variantes[talla][]" placeholder="Ej: S, M, L, XL">
                </div>
                <div>
                    <label>Color</label>
                    <input type="text" name="variantes[color][]" placeholder="Ej: Rojo, Azul">
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" name="variantes[stock][]" placeholder="Cantidad">
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    // Mejorar la experiencia del input de archivo
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('imagenes');
        
        fileInput.addEventListener('change', function() {
            const fileCount = this.files.length;
            if (fileCount > 0) {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
            }
        });
    });
</script>
