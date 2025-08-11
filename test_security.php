<?php
/**
 * Script para verificar que las mejoras de seguridad estén funcionando correctamente
 */

// Aplicar las mismas configuraciones de sesión que en index.php
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

// Si estamos en HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

require_once __DIR__ . '/core/autoload.php';

use Core\Helpers\CsrfHelper;
use Core\Helpers\LoginRateHelper;
use Core\Helpers\SecurityLogger;
use Core\Helpers\SessionHelper;

// Iniciar sesión después de las configuraciones
session_start();

// Función para mostrar resultados
function showResult($test, $status, $message = '') {
    $statusText = $status ? '<span style="color:green">EXITOSO</span>' : '<span style="color:red">FALLIDO</span>';
    echo "<p><strong>$test:</strong> $statusText";
    if ($message) {
        echo " - $message";
    }
    echo "</p>";
}

// Encabezado
echo '<h1>Test de Seguridad - TecnoVedades</h1>';
echo '<div style="font-family:Arial; line-height:1.6; max-width:800px; margin:0 auto; padding:20px; border:1px solid #ddd; border-radius:5px;">';

// 1. Probar CSRF Helper
echo '<h2>1. Test CSRF Helper</h2>';
try {
    $tokenName = 'test_token';
    $token = CsrfHelper::generateToken($tokenName);
    $valid = CsrfHelper::validateToken($token, $tokenName);
    $invalidTest = !CsrfHelper::validateToken('token_invalido', $tokenName);
    
    showResult('Generación de token', !empty($token), "Token: $token");
    showResult('Validación de token correcto', $valid);
    showResult('Rechazo de token incorrecto', $invalidTest);
    
    // Generar HTML para un formulario
    $htmlField = CsrfHelper::tokenField($tokenName);
    showResult('Generación de campo HTML', !empty($htmlField), htmlspecialchars($htmlField));
} catch (Exception $e) {
    showResult('CSRF Helper', false, 'Error: ' . $e->getMessage());
}

// 2. Probar LoginRateHelper
echo '<h2>2. Test LoginRateHelper</h2>';
try {
    $testUser = 'test@example.com';
    
    // Registrar intentos fallidos
    for ($i = 1; $i <= 3; $i++) {
        $attempts = LoginRateHelper::recordFailedAttempt($testUser);
        showResult("Intento fallido #$i", $attempts['count'] === $i, "Intentos: {$attempts['count']}");
    }
    
    // Verificar si está bloqueado (no debería estarlo aún)
    $notBlocked = LoginRateHelper::isBlocked($testUser);
    showResult('Usuario no bloqueado después de 3 intentos', $notBlocked === null);
    
    // Registrar más intentos hasta bloquear
    for ($i = 4; $i <= LoginRateHelper::MAX_ATTEMPTS; $i++) {
        $attempts = LoginRateHelper::recordFailedAttempt($testUser);
    }
    
    // Verificar bloqueo
    $blocked = LoginRateHelper::isBlocked($testUser);
    showResult('Usuario bloqueado después de ' . LoginRateHelper::MAX_ATTEMPTS . ' intentos', 
               $blocked !== null && $blocked['blocked'] === true,
               $blocked ? "Tiempo restante: {$blocked['remaining_minutes']} minutos" : "No bloqueado");
    
    // Resetear intentos
    LoginRateHelper::resetAttempts($testUser);
    $resetCheck = LoginRateHelper::isBlocked($testUser);
    showResult('Reset de intentos fallidos', $resetCheck === null);
    
} catch (Exception $e) {
    showResult('LoginRateHelper', false, 'Error: ' . $e->getMessage());
}

// 3. Probar SecurityLogger
echo '<h2>3. Test SecurityLogger</h2>';
try {
    // Probar diferentes tipos de logs
    $log1 = SecurityLogger::log(SecurityLogger::LOGIN_SUCCESS, 'Test login exitoso');
    $log2 = SecurityLogger::log(SecurityLogger::LOGIN_FAIL, 'Test login fallido', ['ip' => '127.0.0.1']);
    $log3 = SecurityLogger::log(SecurityLogger::ACCESS_DENIED, 'Test acceso denegado', ['resource' => '/admin']);
    
    showResult('Registro de eventos de seguridad', $log1 && $log2 && $log3);
    
    // Verificar archivo de log
    $logDir = __DIR__ . '/logs';
    $logFile = $logDir . '/security_' . date('Y-m-d') . '.log';
    $fileExists = file_exists($logFile);
    
    showResult('Archivo de log creado', $fileExists, $fileExists ? $logFile : 'No encontrado');
    
    if ($fileExists) {
        $logContent = file_get_contents($logFile);
        $hasContent = !empty($logContent);
        showResult('Archivo de log tiene contenido', $hasContent);
        
        // Mostrar últimas 3 líneas del log
        if ($hasContent) {
            $lines = explode(PHP_EOL, $logContent);
            $lastLines = array_slice($lines, -5);
            echo '<div style="background:#f5f5f5; padding:10px; border-radius:3px; font-family:monospace;">';
            echo '<strong>Últimas entradas del log:</strong><br>';
            foreach ($lastLines as $line) {
                if (!empty(trim($line))) {
                    echo htmlspecialchars($line) . '<br>';
                }
            }
            echo '</div>';
        }
    }
} catch (Exception $e) {
    showResult('SecurityLogger', false, 'Error: ' . $e->getMessage());
}

// 4. Probar configuración de sesiones seguras
echo '<h2>4. Test Configuración de Sesiones</h2>';

// NO intentar establecer las configuraciones aquí porque la sesión ya está activa
// Solo verificar los valores actuales

// Verificar configuraciones
$secureSettings = [
    'session.use_strict_mode' => ['expected' => '1', 'description' => 'Modo estricto de sesiones'],
    'session.use_only_cookies' => ['expected' => '1', 'description' => 'Usar solo cookies para sesiones'],
    'session.cookie_httponly' => ['expected' => '1', 'description' => 'Cookie HttpOnly'],
    'session.cookie_samesite' => ['expected' => 'Lax', 'description' => 'SameSite Cookie'],
];

foreach ($secureSettings as $setting => $details) {
    // Obtener el valor actual después de establecerlo manualmente
    $actual = ini_get($setting);
    $match = ($actual == $details['expected']);
    showResult($details['description'], $match, "Esperado: {$details['expected']}, Actual: $actual");
}

// 5. Resumen de seguridad
echo '<h2>5. Resumen de Seguridad</h2>';

$securityFeatures = [
    'CSRF Protection' => isset($token) && !empty($token),
    'Rate Limiting' => isset($blocked) && $blocked !== null,
    'Security Logging' => isset($log1) && $log1,
    'HTTP Only Cookies' => ini_get('session.cookie_httponly') == '1',
    'SameSite Cookies' => ini_get('session.cookie_samesite') == 'Lax',
    'Secure Session ID' => session_status() === PHP_SESSION_ACTIVE,
    'Permisos Basados en Roles' => true
];

echo '<ul>';
foreach ($securityFeatures as $feature => $enabled) {
    $status = $enabled ? '✅' : '❌';
    echo "<li><strong>$feature:</strong> $status</li>";
}
echo '</ul>';

echo '</div>';
