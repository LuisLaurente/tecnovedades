<?php
namespace Controllers;

use League\OAuth2\Client\Provider\Google;
use Core\Helpers\SessionHelper;

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
        // Asegurar que la sesión está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
        // Aquí deberías buscar el usuario por email en tu base de datos
        // Si existe, iniciar sesión. Si no, crear el usuario y luego iniciar sesión.
        $email = $userData['email'] ?? null;
        if (!$email) {
            exit('No se pudo obtener el email de Google.');
        }
        $usuarioModel = new \Models\Usuario();
        $usuario = $usuarioModel->obtenerPorEmail($email);
        if (!$usuario) {
            // Registro automático
            $nuevo = [
                'nombre' => $userData['name'] ?? $userData['email'],
                'email' => $email,
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                'rol_id' => 2, // Cambia por el rol por defecto
                'activo' => 1
            ];
            $usuarioId = $usuarioModel->crear($nuevo);
            $usuario = $usuarioModel->obtenerPorEmail($email);
        }
        // Obtener rol
        $rolModel = new \Models\Rol();
        $rol = $rolModel->obtenerPorId($usuario['rol_id']);
        // Iniciar sesión
        SessionHelper::login($usuario, $rol);
        header('Location: ' . url('/auth/profile'));
        exit;
    }
}
