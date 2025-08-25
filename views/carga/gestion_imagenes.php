<?php require_once __DIR__ . '/../../core/helpers/urlHelper.php'; ?>

<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<style>
    .gestion-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .paso {
        background: white;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 5px solid #007bff;
    }
    .paso h3 {
        color: #007bff;
        margin-top: 0;
        font-size: 1.5em;
    }
    .paso-numero {
        display: inline-block;
        background: #007bff;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        margin-right: 10px;
        font-weight: bold;
    }
    .btn {
        background: #007bff;
        color: white;
        padding: 12px 24px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
        margin: 10px 5px;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    .btn:hover {
        background: #0056b3;
    }
    .btn-success { background: #28a745; }
    .btn-success:hover { background: #1e7e34; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-warning:hover { background: #e0a800; }
    
    .upload-area {
        border: 2px dashed #007bff;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        margin: 15px 0;
    }
    .instrucciones {
        background: #e9ecef;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    .alert {
        padding: 12px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .alert-info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
    }
    .alert-danger {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .ejemplo-tabla {
        width: 100%; 
        border-collapse: collapse; 
        margin: 10px 0;
    }
    .ejemplo-tabla th, .ejemplo-tabla td {
        border: 1px solid #ddd; 
        padding: 8px; 
        text-align: left;
    }
    .ejemplo-tabla th {
        background: #f8f9fa;
    }
</style>

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

                <!-- Todo el contenido de la pagina-->
                <div class="gestion-container">
                    <h1 class="text-3xl font-bold text-gray-800 mb-6">📸 Gestión Masiva de Imágenes por Excel</h1>
                    
                    <?php if (isset($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['flash_error'] ?>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>
                    
                    <!-- PASO 1: Generar Excel -->
                    <div class="paso">
                        <h3>
                            <span class="paso-numero">1</span>
                            📊 Generar CSV con Productos
                        </h3>
                        <p class="text-gray-600 mb-4">Descarga un archivo CSV con todos los productos existentes. Este archivo incluye columnas para especificar qué imágenes corresponden a cada producto.</p>
                        
                        <div class="instrucciones">
                            <h4 class="font-semibold text-gray-700 mb-3">📋 ¿Qué contiene el CSV?</h4>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li><strong>ID_PRODUCTO:</strong> Identificador único</li>
                                <li><strong>NOMBRE_PRODUCTO:</strong> Nombre del producto</li>
                                <li><strong>SKU:</strong> Código de producto</li>
                                <li><strong>IMAGENES_ACTUALES:</strong> Cantidad de imágenes que ya tiene</li>
                                <li><strong>IMAGEN_1 a IMAGEN_5:</strong> Columnas donde escribirás los nombres de archivos</li>
                            </ul>
                        </div>
                        
                        <a href="<?= url('cargaMasiva/generarExcelImagenes') ?>" class="btn btn-success">
                            📥 Descargar CSV de Productos
                        </a>
                    </div>
                    
                    <!-- PASO 2: Completar Excel -->
                    <div class="paso">
                        <h3>
                            <span class="paso-numero">2</span>
                            ✏️ Completar CSV con Referencias de Imágenes
                        </h3>
                        <p class="text-gray-600 mb-4">Abre el CSV descargado con Excel o LibreOffice y completa las columnas IMAGEN_1 a IMAGEN_5 con los nombres exactos de tus archivos de imagen.</p>
                        
                        <div class="instrucciones">
                            <h4 class="font-semibold text-gray-700 mb-3">💡 Ejemplo de cómo completar:</h4>
                            <div class="overflow-x-auto">
                                <table class="ejemplo-tabla">
                                    <thead>
                                        <tr>
                                            <th>NOMBRE_PRODUCTO</th>
                                            <th>IMAGEN_1</th>
                                            <th>IMAGEN_2</th>
                                            <th>IMAGEN_3</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>iPhone 14</td>
                                            <td>iphone14_frontal.jpg</td>
                                            <td>iphone14_trasera.jpg</td>
                                            <td>iphone14_lateral.jpg</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <h4 class="font-semibold text-gray-700 mb-3 mt-4">⚠️ Reglas importantes:</h4>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li>Los nombres deben ser EXACTOS (respeta mayúsculas, minúsculas y caracteres especiales)</li>
                                <li>Solo el nombre del archivo, sin rutas (ejemplo: "foto.jpg" NO "carpeta/foto.jpg")</li>
                                <li>Puedes dejar columnas vacías si no tienes tantas imágenes</li>
                                <li>Formatos soportados: .jpg, .jpeg, .png, .webp, .gif</li>
                                <li><strong>Al guardar:</strong> Mantén la codificación UTF-8 para conservar tildes y caracteres especiales</li>
                                <li><strong>En Excel:</strong> Usa "Guardar como" → CSV (separado por punto y coma) → UTF-8</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- PASO 3: Subir archivos -->
                    <div class="paso">
                        <h3>
                            <span class="paso-numero">3</span>
                            📤 Subir CSV y Archivo de Imágenes
                        </h3>
                        <p class="text-gray-600 mb-4">Sube el CSV completado junto con un archivo ZIP que contenga todas las imágenes referenciadas.</p>
                        
                        <form action="<?= url('cargaMasiva/procesarExcelImagenes') ?>" method="POST" enctype="multipart/form-data">
                            <div class="upload-area">
                                <h4 class="font-semibold text-gray-700 mb-3">📊 CSV Completado</h4>
                                <input type="file" name="excel_imagenes" accept=".csv" required class="mb-2">
                                <p class="text-gray-600">Sube el archivo CSV que modificaste con las referencias de imágenes</p>
                            </div>
                            
                            <div class="upload-area">
                                <h4 class="font-semibold text-gray-700 mb-3">🗜️ Archivo ZIP con Imágenes</h4>
                                <input type="file" name="archivo_imagenes" accept=".zip" required class="mb-2">
                                <p class="text-gray-600">Comprime todas las imágenes en un archivo ZIP</p>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>📝 Antes de subir, verifica que:</strong>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Los nombres en el CSV coinciden exactamente con los archivos en el ZIP</li>
                                    <li>Todas las imágenes están en el ZIP (pueden estar en subcarpetas)</li>
                                    <li>Los archivos son imágenes válidas (JPG, PNG, WEBP, GIF)</li>
                                    <li>Cada imagen pesa menos de 5MB</li>
                                    <li>El CSV está guardado con separador de punto y coma (;)</li>
                                </ul>
                            </div>
                            
                            <div class="text-center mt-6">
                                <button type="submit" class="btn btn-success">
                                    🚀 Procesar y Enlazar Imágenes
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="text-center mt-6">
                        <a href="<?= url('producto/index') ?>" class="btn" style="background: #6c757d;">
                            ← Volver a Productos
                        </a>
                    </div>
                </div>

                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
