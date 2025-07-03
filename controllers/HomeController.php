<?php

namespace Controllers;

use Core\Database;
use Core\Helpers\Validator;
use Core\Helpers\Sanitizer;
use Core\Helpers\SessionHelper;
use PDOException;

class HomeController
{
    public function index()
    {
        try {
            // ✅ Prueba de conexión a base de datos
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT NOW()");
            $fecha = $stmt->fetchColumn();
            echo "✅ Conexión OK. Hora actual desde MySQL: " . $fecha;

            // ==========================
            // 🧪 PRUEBA 1: Validator.php
            // ==========================
            echo "<br><br>🧪 Prueba de Validator:";
            $email = "ejemplo@correo.com";
            $vacio = "";
            $texto = "Hola mundo";

            echo "<br>isEmail: " . (Validator::isEmail($email) ? 'Válido' : 'Inválido');
            echo "<br>isRequired (vacio): " . (Validator::isRequired($vacio) ? 'Válido' : 'Inválido');
            echo "<br>isRequired (texto): " . (Validator::isRequired($texto) ? 'Válido' : 'Inválido');
            echo "<br>minLength (texto, 5): " . (Validator::minLength($texto, 5) ? 'Válido' : 'Inválido');

            // ==========================
            // 🧪 PRUEBA 2: Sanitizer.php
            // ==========================
            echo "<br><br>🧪 Prueba de Sanitizer:";
            $sucio = "<script>alert('xss')</script>   Hola <b>Mundo</b> ";
            echo "<br>Original: $sucio";
            echo "<br>cleanString: " . Sanitizer::cleanString($sucio);
            echo "<br>stripTags: " . Sanitizer::stripTags($sucio);

            // ==========================
            // 🧪 PRUEBA 3: SessionHelper.php
            // ==========================
            echo "<br><br>🧪 Prueba de SessionHelper:";
            SessionHelper::start();
            SessionHelper::set('usuario', 'Luis');
            echo "<br>Valor en sesión: " . SessionHelper::get('usuario');
            SessionHelper::remove('usuario');
            echo "<br>Después de eliminar: " . (SessionHelper::get('usuario') ?? 'No existe');

        } catch (PDOException $e) {
            echo "❌ Error ejecutando consulta: " . $e->getMessage();
        } catch (\Throwable $t) {
            echo "⚠️ Error inesperado: " . $t->getMessage();
        }
    }
}
