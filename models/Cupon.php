<?php
namespace Models;

use Core\Database;
use PDO;

class Cupon
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerPorCodigo($codigo) {
        $sql = "SELECT * FROM cupones WHERE codigo = :codigo";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarUso($cupon_id, $cliente_id, $pedido_id) {
        $sql = "INSERT INTO cupon_usado (cupon_id, cliente_id, pedido_id)
                VALUES (:cupon_id, :cliente_id, :pedido_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cupon_id', $cupon_id);
        $stmt->bindValue(':cliente_id', $cliente_id);
        $stmt->bindValue(':pedido_id', $pedido_id);
        return $stmt->execute();
    }

    public function contarUsos($cupon_id, $cliente_id = null) {
        $sql = "SELECT COUNT(*) FROM cupon_usado WHERE cupon_id = :cupon_id";
        if ($cliente_id) {
            $sql .= " AND cliente_id = :cliente_id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cupon_id', $cupon_id);
        if ($cliente_id) $stmt->bindValue(':cliente_id', $cliente_id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Obtener todos los cupones (para administración)
     */
    public function obtenerTodos()
    {
        $sql = "SELECT * FROM cupones ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un cupón por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM cupones WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo cupón
     */
    public function crear($data)
    {
        $sql = "INSERT INTO cupones 
                (codigo, tipo, valor, monto_minimo, limite_uso, limite_por_usuario, usuarios_autorizados, activo, fecha_inicio, fecha_fin)
                VALUES (:codigo, :tipo, :valor, :monto_minimo, :limite_uso, :limite_por_usuario, :usuarios_autorizados, :activo, :fecha_inicio, :fecha_fin)";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':valor', $data['valor']);
        $stmt->bindValue(':monto_minimo', $data['monto_minimo']);
        $stmt->bindValue(':limite_uso', $data['limite_uso'], PDO::PARAM_INT);
        $stmt->bindValue(':limite_por_usuario', $data['limite_por_usuario'], PDO::PARAM_INT);
        $stmt->bindValue(':usuarios_autorizados', $data['usuarios_autorizados']);
        $stmt->bindValue(':activo', $data['activo'], PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);

        return $stmt->execute();
    }

    /**
     * Actualizar un cupón
     */
    public function actualizar($id, $data)
    {
        $sql = "UPDATE cupones SET 
                    codigo = :codigo,
                    tipo = :tipo,
                    valor = :valor,
                    monto_minimo = :monto_minimo,
                    limite_uso = :limite_uso,
                    limite_por_usuario = :limite_por_usuario,
                    usuarios_autorizados = :usuarios_autorizados,
                    activo = :activo,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $data['codigo']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':valor', $data['valor']);
        $stmt->bindValue(':monto_minimo', $data['monto_minimo']);
        $stmt->bindValue(':limite_uso', $data['limite_uso'], PDO::PARAM_INT);
        $stmt->bindValue(':limite_por_usuario', $data['limite_por_usuario'], PDO::PARAM_INT);
        $stmt->bindValue(':usuarios_autorizados', $data['usuarios_autorizados']);
        $stmt->bindValue(':activo', $data['activo'], PDO::PARAM_INT);
        $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
        $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Obtener estadísticas de cupones
     */
    public function obtenerEstadisticas()
    {
        $stats = [];
        
        // Total de cupones
        $sql = "SELECT COUNT(*) as total FROM cupones";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Cupones activos
        $sql = "SELECT COUNT(*) as activos FROM cupones WHERE activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['activos'];
        
        // Cupones vigentes
        $sql = "SELECT COUNT(*) as vigentes FROM cupones 
                WHERE activo = 1 AND CURDATE() BETWEEN fecha_inicio AND fecha_fin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['vigentes'] = $stmt->fetch(PDO::FETCH_ASSOC)['vigentes'];
        
        // Cupones usados
        $sql = "SELECT COUNT(DISTINCT cupon_id) as usados FROM cupon_usado";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['usados'] = $stmt->fetch(PDO::FETCH_ASSOC)['usados'];
        
        // Total de usos
        $sql = "SELECT COUNT(*) as total_usos FROM cupon_usado";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total_usos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_usos'];
        
        return $stats;
    }

    /**
     * Verificar si un código ya existe
     */
    public function existeCodigo($codigo, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) FROM cupones WHERE codigo = :codigo";
        if ($excluirId) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        if ($excluirId) {
            $stmt->bindValue(':id', $excluirId, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Cambiar estado activo/inactivo de un cupón
     */
    public function toggleEstado($id)
    {
        $sql = "UPDATE cupones SET activo = !activo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Obtener historial de uso de un cupón con información de clientes
     */
    public function obtenerHistorialUso($cupon_id)
    {
        $sql = "SELECT cu.*, c.nombre_completo, c.correo, p.monto_total
                FROM cupon_usado cu
                LEFT JOIN clientes c ON cu.cliente_id = c.id
                LEFT JOIN pedidos p ON cu.pedido_id = p.id
                WHERE cu.cupon_id = :cupon_id
                ORDER BY cu.fecha_uso DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cupon_id', $cupon_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validar si un cliente puede usar un cupón
     */
    public function puedeUsarCupon($cupon_id, $cliente_id, $monto_carrito)
    {
        $cupon = $this->obtenerPorId($cupon_id);
        if (!$cupon || !$cupon['activo']) {
            return ['valido' => false, 'mensaje' => 'Cupón inválido o inactivo'];
        }

        // Verificar fechas
        $hoy = date('Y-m-d');
        if ($hoy < $cupon['fecha_inicio'] || $hoy > $cupon['fecha_fin']) {
            return ['valido' => false, 'mensaje' => 'Cupón fuera del período de validez'];
        }

        // Verificar monto mínimo
        if ($monto_carrito < $cupon['monto_minimo']) {
            return ['valido' => false, 'mensaje' => "Monto mínimo requerido: S/. {$cupon['monto_minimo']}"];
        }

        // Verificar límite global de uso
        if ($cupon['limite_uso'] && $this->contarUsos($cupon_id) >= $cupon['limite_uso']) {
            return ['valido' => false, 'mensaje' => 'Cupón agotado'];
        }

        // Verificar límite por cliente (solo si ya es un cliente registrado)
        if ($cliente_id && $cupon['limite_por_usuario'] && $this->contarUsos($cupon_id, $cliente_id) >= $cupon['limite_por_usuario']) {
            return ['valido' => false, 'mensaje' => 'Ya has usado este cupón el máximo de veces permitidas'];
        }

        // NOTA: La validación de usuarios autorizados se hace en el PedidoController
        // antes de llamar este método

        return ['valido' => true, 'cupon' => $cupon];
    }

}
