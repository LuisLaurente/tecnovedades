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
     * Obtener todas las promociones (para administración)
     */
    public function obtenerTodas()
    {
        $sql = "SELECT * FROM promociones ORDER BY prioridad ASC, fecha_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener una promoción por ID
     */
    public function obtenerPorId($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM promociones WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $promocion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($promocion) {
            // Decodificar JSON si existen
            $promocion['condicion'] = $promocion['condicion'] ? json_decode($promocion['condicion'], true) : [];
            $promocion['accion'] = $promocion['accion'] ? json_decode($promocion['accion'], true) : [];
        }

        return $promocion;
    }

    // 🔹 Generar el siguiente código automáticamente
    private function generarCodigo()
    {
        // Obtener el último código registrado
        $sql = "SELECT codigo FROM promociones ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ultimo && preg_match('/PRM(\d{6})/', $ultimo['codigo'], $matches)) {
            $num = (int)$matches[1] + 1;
        } else {
            $num = 1;
        }

        // Retornar en formato PRM000001
        return 'PRM' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    public function crear($data)
    {
        // Generar código automáticamente
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO promociones 
                (codigo, nombre, condicion, accion, acumulable, exclusivo, prioridad, activo, fecha_inicio, fecha_fin, tipo) 
                VALUES 
                (:codigo, :nombre, :condicion, :accion, :acumulable, :exclusivo, :prioridad, :activo, :fecha_inicio, :fecha_fin, :tipo)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':codigo'       => $codigo,
            ':nombre'       => $data['nombre'],
            ':condicion'    => $data['condicion'],
            ':accion'       => $data['accion'],
            ':acumulable'   => $data['acumulable'],
            ':exclusivo'    => $data['exclusivo'],
            ':prioridad'    => $data['prioridad'],
            ':activo'       => $data['activo'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin'    => $data['fecha_fin'],
            ':tipo'         => $data['tipo']
        ]);
    }

    /**
     * Actualizar una promoción existente
     */
    public function actualizar($id, $data)
    {
        $sql = "UPDATE promociones SET 
                    codigo = :codigo,
                    nombre = :nombre,
                    condicion = :condicion,
                    accion = :accion,
                    acumulable = :acumulable,
                    exclusivo = :exclusivo,
                    prioridad = :prioridad,
                    activo = :activo,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    tipo = :tipo
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':codigo'       => $data['codigo'],
            ':nombre'       => $data['nombre'],
            ':condicion'    => json_encode($data['condicion']),
            ':accion'       => json_encode($data['accion']),
            ':acumulable'   => $data['acumulable'],
            ':exclusivo'    => $data['exclusivo'],
            ':prioridad'    => $data['prioridad'],
            ':activo'       => $data['activo'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin'    => $data['fecha_fin'],
            ':tipo'         => $data['tipo'],
            ':id'           => $id
        ]);
    }

    /**
     * Eliminar una promoción
     */
    public function eliminar($id)
    {
        $sql = "DELETE FROM promociones WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0; // true si se eliminó al menos 1 fila
    }

    /**
     * Obtener estadísticas de promociones
     */
    public function obtenerEstadisticas()
    {
        $stats = [];

        // Total de promociones
        $sql = "SELECT COUNT(*) as total FROM promociones";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Promociones activas
        $sql = "SELECT COUNT(*) as activas FROM promociones WHERE activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['activas'];

        // Promociones vigentes
        $sql = "SELECT COUNT(*) as vigentes FROM promociones 
                WHERE activo = 1 AND CURDATE() BETWEEN fecha_inicio AND fecha_fin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['vigentes'] = $stmt->fetch(PDO::FETCH_ASSOC)['vigentes'];

        // Promociones vencidas
        $sql = "SELECT COUNT(*) as vencidas FROM promociones 
                WHERE fecha_fin < CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['vencidas'] = $stmt->fetch(PDO::FETCH_ASSOC)['vencidas'];

        return $stats;
    }

    /**
     * Cambiar estado activo/inactivo de una promoción
     */
    public function toggleEstado($id)
    {
        // Obtener el estado actual
        $sql = "SELECT activo FROM promociones WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $promocion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$promocion) return false;

        // Alternar el estado
        $nuevoEstado = $promocion['activo'] ? 0 : 1;

        $sql = "UPDATE promociones SET activo = :activo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':activo', $nuevoEstado, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Mapas para traducir condicion y accion a etiquetas legibles
     */
    private $condicionesMap = [
        'minimo_compra' => 'Monto mínimo de compra (S/)',
        'tipo_usuario'  => 'Tipo de Usuario',
        'todos'         => 'Todos los usuarios',
        'categoria'     => 'Categoría específica (ID)',
        'producto'      => 'Producto específico (ID)',
        'cantidad_total_productos' => 'Cantidad total de productos',
        'subtotal_minimo' => 'Monto mínimo del carrito',
        'primera_compra' => 'Primera compra del usuario',
        'cantidad_producto_identico' => 'Cantidad de un producto específico',
        'cantidad_producto_categoria' => 'Cantidad de productos de una categoría'

    ];

    private $accionesMap = [
        'descuento_porcentaje' => 'Descuento (%)',
        'descuento_monto'      => 'Descuento fijo (S/)',
        'envio_gratis'         => 'Envío Gratis',
        'producto_gratis'      => 'Producto Gratis',
        'compra_n_paga_m_general' => 'Promoción NxM General',
        'descuento_enesimo_producto' => 'Descuento en N-ésimo producto',
        'descuento_producto_mas_barato' => 'Descuento en el producto más barato',
        'descuento_menor_valor' => 'Descuento en el producto de menor valor de categoría',
        'descuento_enesima_unidad' => 'Descuento en la N-ésima unidad',
        'compra_n_paga_m' => 'Promoción NxM en producto específico'
    ];

    /**
     * Retorna la etiqueta legible de la condicion
     */
    public function getCondicionLabel($condicion)
    {
        // Si está guardado como JSON, lo decodificamos
        if ($this->esJson($condicion)) {
            $condicion = json_decode($condicion, true);
            $condicion = $condicion['tipo'] ?? $condicion;
        }

        return $this->condicionesMap[$condicion] ?? $condicion;
    }

    /**
     * Retorna la etiqueta legible de la acción
     */
    public function getAccionLabel($accion)
    {
        if ($this->esJson($accion)) {
            $accion = json_decode($accion, true);
            $accion = $accion['tipo'] ?? $accion;
        }

        return $this->accionesMap[$accion] ?? $accion;
    }

    /**
     * Verifica si un string es JSON válido
     */
    private function esJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Actualiza un campo específico de una promoción.
     * Es más eficiente que 'actualizar' para cambios puntuales.
     *
     * @param int $id ID de la promoción.
     * @param string $campo Nombre de la columna en la base de datos.
     * @param mixed $valor Nuevo valor para el campo.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizarCampo($id, $campo, $valor)
    {
        // Lista blanca de campos permitidos para evitar inyecciones SQL en los nombres de columna.
        $camposPermitidos = ['nombre', 'prioridad', 'activo', 'acumulable', 'exclusivo', 'fecha_inicio', 'fecha_fin'];

        if (!in_array($campo, $camposPermitidos)) {
            // Si el campo no está en la lista, no hacemos nada para proteger la BD.
            return false;
        }

        $sql = "UPDATE promociones SET {$campo} = :valor WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':valor' => $valor,
            ':id'    => $id
        ]);
    }
}
