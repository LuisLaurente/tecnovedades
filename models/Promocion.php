<?php

namespace Models;

use Core\Database; // Importamos la clase Database con su namespace correcto
use PDO;

class Promocion
{
    private $db;

    public function __construct()
    {
        // Usamos el Singleton: Database::getInstance()->getConnection()
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las promociones activas y vigentes
     */
    public function obtenerPromocionesActivas()
    {
        $sql = "SELECT * FROM promociones 
                WHERE activo = 1 
                AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
                ORDER BY prioridad ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener una promoción por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM promociones WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear una nueva promoción
     */
    public function crear($data)
    {
        $sql = "INSERT INTO promociones 
                (nombre, condicion, accion, acumulable, exclusivo, prioridad, activo, fecha_inicio, fecha_fin)
                VALUES (:nombre, :condicion, :accion, :acumulable, :exclusivo, :prioridad, :activo, :fecha_inicio, :fecha_fin)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':condicion', json_encode($data['condicion']));
        $stmt->bindValue(':accion', json_encode($data['accion']));
        $stmt->bindValue(':acumulable', $data['acumulable'], PDO::PARAM_INT);
        $stmt->bindValue(':exclusivo', $data['exclusivo'], PDO::PARAM_INT);
        $stmt->bindValue(':prioridad', $data['prioridad'], PDO::PARAM_INT);
        $stmt->bindValue(':activo', $data['activo'], PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);

        return $stmt->execute();
    }

    /**
     * Actualizar una promoción existente
     */
    public function actualizar($id, $data)
    {
        $sql = "UPDATE promociones SET 
                    nombre = :nombre,
                    condicion = :condicion,
                    accion = :accion,
                    acumulable = :acumulable,
                    exclusivo = :exclusivo,
                    prioridad = :prioridad,
                    activo = :activo,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':condicion', json_encode($data['condicion']));
        $stmt->bindValue(':accion', json_encode($data['accion']));
        $stmt->bindValue(':acumulable', $data['acumulable'], PDO::PARAM_INT);
        $stmt->bindValue(':exclusivo', $data['exclusivo'], PDO::PARAM_INT);
        $stmt->bindValue(':prioridad', $data['prioridad'], PDO::PARAM_INT);
        $stmt->bindValue(':activo', $data['activo'], PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar una promoción
     */
    public function eliminar($id)
    {
        $sql = "DELETE FROM promociones WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
