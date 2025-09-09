<?php
namespace Controllers;
use Core\Database;

class ReporteController
{
    public function resumen()
    {
        $db = Database::getInstance()->getConnection();

        $fechaInicio = $_GET['inicio'] ?? date('Y-m-01');
        $fechaFin    = $_GET['fin'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) $fechaInicio = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin))    $fechaFin    = date('Y-m-d');

        // Resumen general
        $stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT p.id) AS total_pedidos,
                COALESCE(SUM(pd.precio_unitario * pd.cantidad),0) AS total_vendido,
                COALESCE(AVG(sub.total_pedido),0) AS ticket_promedio
            FROM pedidos p
            JOIN detalle_pedido pd ON pd.pedido_id = p.id
            JOIN (
                SELECT pedido_id, SUM(precio_unitario * cantidad) AS total_pedido
                FROM detalle_pedido
                GROUP BY pedido_id
            ) sub ON sub.pedido_id = p.id
            WHERE DATE(p.creado_en) BETWEEN :inicio AND :fin
        ");
        $stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
        $resumen = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Lista de pedidos con fecha y total
        $stmt = $db->prepare("
            SELECT 
                p.id, 
                p.creado_en, 
                COALESCE(SUM(pd.precio_unitario * pd.cantidad),0) AS total
            FROM pedidos p
            LEFT JOIN detalle_pedido pd ON pd.pedido_id = p.id
            WHERE DATE(p.creado_en) BETWEEN :inicio AND :fin
            GROUP BY p.id, p.creado_en
            ORDER BY p.creado_en DESC
        ");
        $stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
        $pedidos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Detalle por producto
        $stmt = $db->prepare("
            SELECT 
                p.id AS pedido_id,
                p.creado_en,
                pr.nombre AS producto,
                pd.precio_unitario,
                pd.cantidad,
                (pd.precio_unitario * pd.cantidad) AS subtotal
            FROM detalle_pedido pd
            JOIN pedidos p ON pd.pedido_id = p.id
            JOIN productos pr ON pd.producto_id = pr.id
            WHERE DATE(p.creado_en) BETWEEN :inicio AND :fin
            ORDER BY p.creado_en DESC
        ");
        $stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
        $detalles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/reportes/resumen.php';
    }

    public function exportarCsv()
{
    $db = Database::getInstance()->getConnection();

    $fechaInicio = $_GET['inicio'] ?? date('Y-m-01');
    $fechaFin    = $_GET['fin'] ?? date('Y-m-d');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_ventas.csv"');

    echo "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
    $output = fopen('php://output', 'w');

    // Encabezado CSV
    fputcsv($output, ['Pedido ID', 'Fecha', 'Producto', 'Precio Unitario (S/)', 'Cantidad Vendida', 'Total (S/)']);

    // Datos
    $stmt = $db->prepare("
        SELECT 
            p.id AS pedido_id,
            p.creado_en,
            pr.nombre AS producto,
            pd.precio_unitario,
            pd.cantidad,
            (pd.precio_unitario * pd.cantidad) AS subtotal
        FROM detalle_pedido pd
        JOIN pedidos p ON pd.pedido_id = p.id
        JOIN productos pr ON pd.producto_id = pr.id
        WHERE DATE(p.creado_en) BETWEEN :inicio AND :fin
        ORDER BY p.creado_en DESC
    ");
    $stmt->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);

    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['pedido_id'],
            $row['creado_en'],
            $row['producto'],
            number_format($row['precio_unitario'], 2, '.', ''),
            $row['cantidad'],
            number_format($row['subtotal'], 2, '.', '')
        ]);
    }

    fclose($output);
    exit;
}

}
