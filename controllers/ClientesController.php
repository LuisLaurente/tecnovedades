<?php
namespace Controllers;

use Core\Database;

class ClientesController
{
    public function guardarConsentimientoCookies()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $consent = isset($_POST['consent']) ? (int)$_POST['consent'] : null;

            // Verifica si hay cliente logueado
            if (isset($_SESSION['cliente_id'])) {
                $clienteId = $_SESSION['cliente_id'];

                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("UPDATE clientes SET cookies_consent = ? WHERE id = ?");
                $stmt->execute([$consent, $clienteId]);
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }
}
