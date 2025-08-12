<?php

namespace Models;

use Core\Database;

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los usuarios con información del rol
     */
    public function obtenerTodos()
    {
        try {
            $sql = "SELECT 
                        u.id, 
                        u.nombre, 
                        u.email, 
                        u.rol_id,
                        r.nombre as rol_nombre,
                        r.descripcion as rol_descripcion,
                        u.activo, 
                        u.fecha_creacion, 
                        u.fecha_actualizacion 
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id
                    ORDER BY u.fecha_creacion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener usuario por ID con información del rol
     */
    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT 
                        u.*, 
                        r.nombre as rol_nombre,
                        r.descripcion as rol_descripcion,
                        r.permisos as rol_permisos
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id
                    WHERE u.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo usuario
     */
    public function crear($datos)
    {
        try {
            // Crear el usuario básico primero
            $sql = "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $result = $stmt->execute([
                $datos['nombre'],
                $datos['email'],
                $passwordHash,
                $datos['rol_id'] ?? 2, // Por defecto rol 'usuario'
                $datos['activo'] ?? 1
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                
                // Si se proporcionaron datos adicionales, guardarlos en usuario_detalles
                if (!empty($datos['telefono']) || !empty($datos['fecha_nacimiento']) || !empty($datos['genero'])) {
                    try {
                        $sqlDetalles = "INSERT INTO usuario_detalles (usuario_id, telefono, fecha_nacimiento, genero) VALUES (?, ?, ?, ?)";
                        $stmtDetalles = $this->db->prepare($sqlDetalles);
                        $stmtDetalles->execute([
                            $userId,
                            $datos['telefono'] ?? null,
                            $datos['fecha_nacimiento'] ?? null,
                            $datos['genero'] ?? null
                        ]);
                    } catch (\PDOException $e) {
                        // Si la tabla usuario_detalles no existe, continuar sin error
                        error_log("No se pudieron guardar detalles del usuario: " . $e->getMessage());
                    }
                }
                
                return $userId;
            }
            
            return false;
        } catch (\PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     */
    public function actualizar($id, $datos)
    {
        try {
            $campos = [];
            $valores = [];

            if (isset($datos['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = $datos['nombre'];
            }
            if (isset($datos['email'])) {
                $campos[] = "email = ?";
                $valores[] = $datos['email'];
            }
            if (isset($datos['password']) && !empty($datos['password'])) {
                $campos[] = "password = ?";
                $valores[] = password_hash($datos['password'], PASSWORD_DEFAULT);
            }
            if (isset($datos['rol_id'])) {
                $campos[] = "rol_id = ?";
                $valores[] = $datos['rol_id'];
            }
            if (isset($datos['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = $datos['activo'];
            }

            $valores[] = $id;

            $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($valores);
        } catch (\PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado activo del usuario
     */
    public function cambiarEstado($id, $activo)
    {
        try {
            error_log("Usuario::cambiarEstado llamado con ID: $id, activo: $activo");
            $sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([$activo, $id]);
            error_log("Resultado de la actualización: " . ($resultado ? 'true' : 'false'));
            error_log("Filas afectadas: " . $stmt->rowCount());
            return $resultado;
        } catch (\PDOException $e) {
            error_log("Error al cambiar estado del usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function obtenerEstadisticas()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN u.activo = 1 THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN u.activo = 0 THEN 1 ELSE 0 END) as inactivos,
                        SUM(CASE WHEN r.nombre = 'admin' THEN 1 ELSE 0 END) as administradores,
                        SUM(CASE WHEN r.nombre = 'usuario' THEN 1 ELSE 0 END) as usuarios_normales
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener estadísticas de usuarios: " . $e->getMessage());
            return [
                'total' => 0,
                'activos' => 0,
                'inactivos' => 0,
                'administradores' => 0,
                'usuarios_normales' => 0
            ];
        }
    }

    /**
     * Verificar si existe un email
     */
    public function existeEmail($email, $excluirId = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
            $valores = [$email];
            
            if ($excluirId) {
                $sql .= " AND id != ?";
                $valores[] = $excluirId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($valores);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error al verificar email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por email (para login)
     */
    public function obtenerPorEmail($email)
    {
        try {
            $sql = "SELECT 
                        u.*, 
                        r.nombre as rol_nombre,
                        r.descripcion as rol_descripcion,
                        r.permisos as rol_permisos
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id
                    WHERE u.email = ? AND u.activo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuario por email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario tiene un permiso específico
     */
    public function tienePermiso($usuarioId, $permiso)
    {
        try {
            $usuario = $this->obtenerPorId($usuarioId);
            if (!$usuario || !$usuario['activo']) {
                return false;
            }

            $permisos = json_decode($usuario['rol_permisos'], true);
            return is_array($permisos) && in_array($permiso, $permisos);
        } catch (\Exception $e) {
            error_log("Error al verificar permiso de usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener permisos de un usuario
     */
    public function obtenerPermisos($usuarioId)
    {
        try {
            $usuario = $this->obtenerPorId($usuarioId);
            if (!$usuario) {
                return [];
            }

            $permisos = json_decode($usuario['rol_permisos'], true);
            return is_array($permisos) ? $permisos : [];
        } catch (\Exception $e) {
            error_log("Error al obtener permisos de usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener usuarios por rol
     */
    public function obtenerPorRol($rolId)
    {
        try {
            $sql = "SELECT 
                        u.id, 
                        u.nombre, 
                        u.email, 
                        u.activo, 
                        u.fecha_creacion,
                        r.nombre as rol_nombre
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id
                    WHERE u.rol_id = ?
                    ORDER BY u.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$rolId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuarios por rol: " . $e->getMessage());
            return [];
        }
    }
}