<?php
echo "=== DEBUG INICIO ===<br>";

// Test 1: Verificar que PHP básico funciona
echo "✅ PHP funcionando<br>";

// Test 2: Verificar autoload
echo "Intentando cargar autoload...<br>";
if (file_exists(__DIR__ . '/autoload.php')) {
    echo "✅ autoload.php encontrado<br>";
    try {
        require_once __DIR__ . '/autoload.php';
        echo "✅ autoload.php cargado<br>";
    } catch (Exception $e) {
        echo "❌ Error en autoload: " . $e->getMessage() . "<br>";
        exit;
    }
} else {
    echo "❌ autoload.php NO encontrado<br>";
    exit;
}

// Test 3: Verificar Router
echo "Intentando cargar Router...<br>";
if (file_exists(__DIR__ . '/core/Router.php')) {
    echo "✅ Router.php encontrado<br>";
    try {
        require_once __DIR__ . '/core/Router.php';
        echo "✅ Router.php cargado<br>";
    } catch (Exception $e) {
        echo "❌ Error en Router: " . $e->getMessage() . "<br>";
        exit;
    }
} else {
    echo "❌ Router.php NO encontrado<br>";
    exit;
}

// Test 4: Verificar que la clase existe
if (class_exists('Core\Router')) {
    echo "✅ Clase Core\Router existe<br>";
} else {
    echo "❌ Clase Core\Router NO existe<br>";
}

echo "=== DEBUG FIN ===<br>";
?>
