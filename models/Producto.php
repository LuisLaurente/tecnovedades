<?php

namespace Models;

use Core\Database;
use PDO;

class Producto
{
    private $db;

    public function __construct()
    {
        // Conectamos a la base de datos usando la clase Database
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodos()
    {
        // Consulta SQL para obtener todos los productos visibles
        $sql = "SELECT * FROM productos WHERE visible = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
   

    public static function obtenerPorId($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function actualizar($id, $nombre, $descripcion, $precio, $stock, $visible)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, visible = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $stock, $visible, $id]);
    }
    public static function eliminar($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE productos SET visible = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function obtenerCategoriasPorProducto($productoId)
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
        SELECT c.nombre
        FROM categorias c
        INNER JOIN producto_categoria pc ON pc.id_categoria = c.id
        WHERE pc.id_producto = ?
    ");
        $stmt->execute([$productoId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nombre');

     //etiqueta
    public function obtenerEtiquetasPorProducto($id_producto) {
    $stmt = $this->db->prepare("SELECT etiqueta_id FROM producto_etiqueta WHERE producto_id = ?");
    $stmt->execute([$id_producto]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve array con IDs

    }
}
