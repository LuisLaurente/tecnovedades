<?php

namespace Models;

use Core\Database;
use PDO;
use Exception;

class Categoria
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Obtener todas las categorías con flags
    public static function obtenerTodas()
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT c.*, 
                EXISTS (SELECT 1 FROM categorias sub WHERE sub.id_padre = c.id) AS tiene_hijos,
                EXISTS (SELECT 1 FROM producto_categoria pc WHERE pc.id_categoria = c.id) AS tiene_productos
                FROM categorias c
                ORDER BY c.id_padre ASC, c.nombre ASC";

        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function crear($nombre, $id_padre = null)
    {
        self::validar(null, $nombre, $id_padre, false);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO categorias (nombre, id_padre) VALUES (?, ?)");
        $stmt->execute([$nombre, $id_padre]);
    }

    public static function actualizar($id, $nombre, $id_padre = null)
    {
        self::validar($id, $nombre, $id_padre, true);

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE categorias SET nombre = ?, id_padre = ? WHERE id = ?");
        $stmt->execute([$nombre, $id_padre, $id]);
    }

    public static function eliminar($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function tieneHijos($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM categorias WHERE id_padre = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public static function tieneProductos($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM producto_categoria WHERE id_categoria = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    // ✅ Validación completa
    private static function validar($id, $nombre, $id_padre, $esActualizacion = false)
    {
        $db = Database::getInstance()->getConnection();

        // 1. Nombre obligatorio
        if (empty(trim($nombre))) {
            throw new Exception("El nombre no puede estar vacío.");
        }

        // 2. Nombre único
        $sql = "SELECT COUNT(*) FROM categorias WHERE nombre = ?";
        $params = [$nombre];

        if ($esActualizacion && $id) {
            $sql .= " AND id != ?";
            $params[] = $id;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Ya existe una categoría con ese nombre.");
        }

        // 3. No puede ser su propio padre
        if ($id && $id == $id_padre) {
            throw new Exception("Una categoría no puede ser su propio padre.");
        }

        // 4. Verificar existencia del padre si está definido
        if ($id_padre !== null && $id_padre !== '') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM categorias WHERE id = ?");
            $stmt->execute([$id_padre]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("La categoría padre no existe.");
            }
        }

        // 5. Evitar ciclos (el padre no puede ser descendiente)
        if ($id && $id_padre && self::esDescendiente($id_padre, $id)) {
            throw new Exception("No se puede asignar como padre a una subcategoría propia (ciclo).");
        }
    }

    private static function esDescendiente($posiblePadre, $categoriaActual)
    {
        $db = Database::getInstance()->getConnection();
        $actual = $posiblePadre;

        while ($actual !== null) {
            $stmt = $db->prepare("SELECT id_padre FROM categorias WHERE id = ?");
            $stmt->execute([$actual]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) break;

            $actual = $row['id_padre'];
            if ($actual == $categoriaActual) {
                return true;
            }
        }

        return false;
    }
}
