<?php

namespace Models;

class Banner
{
    /**
     * Obtener la conexión (DB singleton)
     * @return \PDO|\mysqli
     */
    private static function conn()
    {
        return \Core\Database::getInstance()->getConnection();
    }

    /**
     * Devuelve los banners activos ordenados por 'orden'
     * @return array
     */
    public static function obtenerActivos(): array
    {
        $conn = self::conn();
        $banners = [];
        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->query("SELECT id, nombre_imagen, orden FROM banners WHERE activo = 1 ORDER BY orden ASC");
                $banners = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($conn instanceof \mysqli) {
                $sql = "SELECT id, nombre_imagen, orden FROM banners WHERE activo = 1 ORDER BY orden ASC";
                if ($res = $conn->query($sql)) {
                    while ($row = $res->fetch_assoc()) $banners[] = $row;
                    $res->free();
                }
            }
        } catch (\Throwable $e) {
            error_log('Banner::obtenerActivos error: ' . $e->getMessage());
        }
        return $banners;
    }

    /**
     * Devuelve todos los banners (admin)
     * @return array
     */
    public static function obtenerTodos(): array
    {
        $conn = self::conn();
        $banners = [];
        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->query("SELECT * FROM banners ORDER BY orden ASC, id DESC");
                $banners = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($conn instanceof \mysqli) {
                $sql = "SELECT * FROM banners ORDER BY orden ASC, id DESC";
                if ($res = $conn->query($sql)) {
                    while ($row = $res->fetch_assoc()) $banners[] = $row;
                    $res->free();
                }
            }
        } catch (\Throwable $e) {
            error_log('Banner::obtenerTodos error: ' . $e->getMessage());
        }
        return $banners;
    }

    /**
     * Obtener banner por id
     * @param int $id
     * @return array|null
     */
    public static function obtenerPorId(int $id): ?array
    {
        $conn = self::conn();
        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("SELECT * FROM banners WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $row ?: null;
            } elseif ($conn instanceof \mysqli) {
                $id = (int)$id;
                $res = $conn->query("SELECT * FROM banners WHERE id = $id LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) {
                    $res->free();
                    return $row;
                }
            }
        } catch (\Throwable $e) {
            error_log('Banner::obtenerPorId error: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Crear un nuevo banner
     * @param string $nombre_imagen
     * @param int $orden
     * @param int $activo
     * @return int|null devuelve id insertado o null en error
     */
    public static function crear(string $nombre_imagen, int $orden = 0, int $activo = 1): ?int
    {
        $conn = self::conn();
        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("INSERT INTO banners (nombre_imagen, orden, activo, creado_en) VALUES (:img, :ord, :act, NOW())");
                $ok = $stmt->execute([':img' => $nombre_imagen, ':ord' => $orden, ':act' => $activo]);
                if ($ok) return (int)$conn->lastInsertId();
            } elseif ($conn instanceof \mysqli) {
                $imgEsc = $conn->real_escape_string($nombre_imagen);
                $orden = (int)$orden;
                $activo = (int)$activo;
                if ($conn->query("INSERT INTO banners (nombre_imagen, orden, activo, creado_en) VALUES ('$imgEsc', $orden, $activo, NOW())")) {
                    return (int)$conn->insert_id;
                }
            }
        } catch (\Throwable $e) {
            error_log('Banner::crear error: ' . $e->getMessage());
        }
        return null;
    }


    /**
     * Actualiza solo la columna nombre_imagen para el banner dado
     * @param int $id
     * @param string $nombre_imagen
     * @return bool
     */
    public static function actualizarImagen(int $id, string $nombre_imagen): bool
    {
        $conn = self::conn();
        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("UPDATE banners SET nombre_imagen = :img WHERE id = :id");
                return $stmt->execute([':img' => $nombre_imagen, ':id' => $id]);
            } elseif ($conn instanceof \mysqli) {
                $imgEsc = $conn->real_escape_string($nombre_imagen);
                $id = (int)$id;
                return (bool)$conn->query("UPDATE banners SET nombre_imagen = '$imgEsc' WHERE id = $id");
            }
        } catch (\Throwable $e) {
            error_log('Banner::actualizarImagen error: ' . $e->getMessage());
        }
        return false;
    }


    /**
     * Actualiza campos del banner (nombre_imagen, orden, activo)
     * @param int $id
     * @param array $data ['nombre_imagen' => ..., 'orden' => ..., 'activo' => ...]
     * @return bool
     */
    public static function actualizar(int $id, array $data): bool
    {
        $conn = self::conn();
        $fields = [];
        $params = [];
        if (isset($data['nombre_imagen'])) {
            $fields[] = "nombre_imagen = :img";
            $params[':img'] = $data['nombre_imagen'];
        }
        if (isset($data['orden'])) {
            $fields[] = "orden = :ord";
            $params[':ord'] = (int)$data['orden'];
        }
        if (isset($data['activo'])) {
            $fields[] = "activo = :act";
            $params[':act'] = (int)$data['activo'];
        }
        if (empty($fields)) return true;

        $sql = "UPDATE banners SET " . implode(', ', $fields) . " WHERE id = :id";
        $params[':id'] = $id;

        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare($sql);
                return $stmt->execute($params);
            } elseif ($conn instanceof \mysqli) {
                $parts = [];
                if (isset($data['nombre_imagen'])) $parts[] = "nombre_imagen = '" . $conn->real_escape_string($data['nombre_imagen']) . "'";
                if (isset($data['orden'])) $parts[] = "orden = " . (int)$data['orden'];
                if (isset($data['activo'])) $parts[] = "activo = " . (int)$data['activo'];
                $id = (int)$id;
                $sql2 = "UPDATE banners SET " . implode(', ', $parts) . " WHERE id = $id";
                return (bool)$conn->query($sql2);
            }
        } catch (\Throwable $e) {
            error_log('Banner::actualizar error: ' . $e->getMessage());
        }
        return false;
    }


    /**
     * Elimina el registro y devuelve el nombre de la imagen eliminada (o null si no existe)
     * NOTA: no borra el archivo físico, devuelve el nombre para que el controller lo elimine.
     * @param int $id
     * @return string|null
     */
    public static function eliminar(int $id): ?string
    {
        $conn = self::conn();
        $oldName = null;
        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("SELECT nombre_imagen FROM banners WHERE id = ?");
                $stmt->execute([$id]);
                $oldName = $stmt->fetchColumn();
                $del = $conn->prepare("DELETE FROM banners WHERE id = ?");
                $del->execute([$id]);
            } elseif ($conn instanceof \mysqli) {
                $res = $conn->query("SELECT nombre_imagen FROM banners WHERE id = $id LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) $oldName = $row['nombre_imagen'];
                $conn->query("DELETE FROM banners WHERE id = $id");
            }
        } catch (\Throwable $e) {
            error_log('Banner::eliminar error: ' . $e->getMessage());
        }
        return $oldName ?: null;
    }

    /**
     * Actualiza la columna 'orden' según un array de ids en el orden deseado.
     * @param array $ids
     * @return bool
     */
    public static function ordenar(array $ids): bool
    {
        $conn = self::conn();
        try {
            if ($conn instanceof \PDO) {
                $conn->beginTransaction();
                $stmt = $conn->prepare("UPDATE banners SET orden = :pos WHERE id = :id");
                foreach ($ids as $pos => $id) {
                    $stmt->execute([':pos' => (int)$pos, ':id' => (int)$id]);
                }
                $conn->commit();
                return true;
            } elseif ($conn instanceof \mysqli) {
                foreach ($ids as $pos => $id) {
                    $id = (int)$id;
                    $pos = (int)$pos;
                    $conn->query("UPDATE banners SET orden = $pos WHERE id = $id");
                }
                return true;
            }
        } catch (\Throwable $e) {
            if ($conn instanceof \PDO) {
                try {
                    $conn->rollBack();
                } catch (\Throwable $x) {
                }
            }
            error_log('Banner::ordenar error: ' . $e->getMessage());
        }
        return false;
    }
}
