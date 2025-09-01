<?php
namespace Models;

class Banner
{
    public static function obtenerActivos(): array
    {
        $conn = \Core\Database::getInstance()->getConnection();
        $banners = [];

        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->query("SELECT id, nombre_imagen FROM banners WHERE activo = 1 ORDER BY orden ASC");
                $banners = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($conn instanceof \mysqli) {
                $sql = "SELECT id, nombre_imagen FROM banners WHERE activo = 1 ORDER BY orden ASC";
                if ($res = $conn->query($sql)) {
                    while ($row = $res->fetch_assoc()) $banners[] = $row;
                    $res->free();
                }
            }
        } catch (\Throwable $e) {
            error_log('Banner::obtenerActivos error: '.$e->getMessage());
        }

        return $banners;
    }
}
