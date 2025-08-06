<?php

namespace Controllers;

use Models\Reclamacion;

class ReclamacionController
{
    public function formulario()
    {
        require_once __DIR__ . '/../views/reclamacion/formulario.php';
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $mensaje = trim($_POST['mensaje'] ?? '');

            if (!$nombre || !$mensaje) {
                echo "Nombre y mensaje son obligatorios.";
                return;
            }

            $reclamacion = new Reclamacion();
            $reclamacion->guardar($nombre, $correo, $telefono, $mensaje);

            echo "<p>✅ ¡Gracias por enviar tu reclamación!</p>";
        }
    }
    public function enviar()
    {
        // Validar y procesar el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $mensaje = $_POST['mensaje'] ?? '';

            $errores = [];

            if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo inválido.";
            if (empty($mensaje)) $errores[] = "El mensaje no puede estar vacío.";

            if (count($errores) === 0) {
                $reclamacion = new \Models\Reclamacion();
                $reclamacion->guardar($nombre, $correo, $telefono, $mensaje);
                header('Location: ' . url('reclamacion/formulario') . '?exito=1');
                exit;
            } else {
                $_SESSION['errores_reclamo'] = $errores;
                header('Location: ' . url('reclamacion/formulario'));
                exit;
            }
        }

        // Si no es POST, redirigir
        header('Location: ' . url('reclamacion/formulario'));
        exit;
        
    }
}
