<?php
namespace Controllers;

use League\OAuth2\Client\Provider\Google;
use Core\Helpers\SessionHelper;
use Models\Usuario;
use Models\Rol;

class GoogleAuthController extends BaseController
{
    private $provider;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/oauth.template.php';
        $this->provider = new Google([
            'clientId'     => $config['google']['clientId'],
            'clientSecret' => $config['google']['clientSecret'],
            'redirectUri'  => $config['google']['redirectUri'],
        ]);
    }

    // Paso 1: Redirigir a Google
    public function login()
    {
        // ✅ USAR SessionHelper en lugar de session_start() directo
        SessionHelper::start();
        
        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => ['openid', 'email', 'profile']
        ]);
        
        $_SESSION['oauth2state'] = $this->provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    // Paso 2: Callback de Google
    public function callback()
    {
        // ✅ USAR SessionHelper consistentemente
        SessionHelper::start();

        // Validar existencia de oauth2state en sesión antes de comparar
        if (empty($_GET['state']) || !isset($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Error de seguridad: estado inválido o la sesión ha expirado. Por favor, intenta iniciar sesión nuevamente.');
        }
        
        if (!isset($_GET['code'])) {
            exit('No se recibió código de autorización.');
        }
        
        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            
            $googleUser = $this->provider->getResourceOwner($token);
            $userData = $googleUser->toArray();
            
            // Procesar login/registro
            $this->loginOrRegister($userData);
            
        } catch (\Exception $e) {
            exit('Error al autenticar con Google: ' . $e->getMessage());
        }
    }

    private function loginOrRegister($userData)
    {
        $email = $userData['email'] ?? null;
        if (!$email) {
            exit('No se pudo obtener el email de Google.');
        }
        
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorEmail($email);
        
        if (!$usuario) {
            // Registro automático
            $nuevo = [
                'nombre' => $userData['name'] ?? $userData['email'],
                'email' => $email,
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                'rol_id' => 2, // Cliente por defecto
                'activo' => 1,
                'google_id' => $userData['id'] ?? null // Guardar ID de Google
            ];
            
            $usuarioId = $usuarioModel->crear($nuevo);
            $usuario = $usuarioModel->obtenerPorEmail($email);
        } else {
            // Actualizar google_id si no está establecido
            if (empty($usuario['google_id']) && isset($userData['id'])) {
                $usuarioModel->actualizar($usuario['id'], [
                    'google_id' => $userData['id']
                ]);
            }
        }
        
        // Obtener rol
        $rolModel = new Rol();
        $rol = $rolModel->obtenerPorId($usuario['rol_id']);
        
        // Iniciar sesión
        SessionHelper::login($usuario, $rol);
        
        // Redirigir al perfil
        header('Location: ' . url('/auth/profile'));
        exit;
    }
}