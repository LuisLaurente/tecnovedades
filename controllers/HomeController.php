<?php

namespace Controllers;

use Core\Database;
use PDOException;

class HomeController
{
    public function index()
    {
        try {
            // Obtener la instancia de la base de datos
            $db = Database::getInstance()->getConnection();

            // Ejecutar una consulta simple para verificar la conexión
            $stmt = $db->query("SELECT NOW()");
            $fecha = $stmt->fetchColumn();

            // Mostrar resultado
            echo "✅ Conexión OK. Hora actual desde MySQL: " . $fecha;

        } catch (PDOException $e) {
            // Si hay error en la consulta, mostrar mensaje
            echo "❌ Error ejecutando consulta: " . $e->getMessage();
        } catch (\Throwable $t) {
            // Captura otros errores inesperados
            echo "⚠️ Error inesperado: " . $t->getMessage();
        }
    }
}
