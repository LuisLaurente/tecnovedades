<?php
namespace Controllers;
use Models\Reporte;

class ReporteController
{
    public function resumen()
    {
        $fechaInicio = $_GET['inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fin'] ?? date('Y-m-d');

        $reporteModel = new \Models\Reporte();
        $resumen = $reporteModel->obtenerResumenVentas($fechaInicio, $fechaFin);
        $detalles = $reporteModel->obtenerDetallePorProducto($fechaInicio, $fechaFin);

        require_once __DIR__ . '/../views/admin/reportes/resumen.php';
    }
    public function exportar_csv()
    {
        $fechaInicio = $_GET['inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fin'] ?? date('Y-m-d');
        $tipo = $_GET['tipo'] ?? 'general';

        $reporteModel = new \Models\Reporte();

        if ($tipo === 'general') {
            $resumen = $reporteModel->obtenerResumenVentas($fechaInicio, $fechaFin);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="reporte_general.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Total Vendido (S/)', 'Total de Pedidos', 'Ticket Promedio (S/)']);
            fputcsv($output, [
                number_format($resumen['total_vendido'], 2),
                $resumen['total_pedidos'],
                number_format($resumen['ticket_promedio'], 2)
            ]);
            fclose($output);
            exit;
        }

        if ($tipo === 'detalle') {
            $detalles = $reporteModel->obtenerDetallePorProducto($fechaInicio, $fechaFin);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="reporte_detallado.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Producto', 'Precio Unitario (S/)', 'Cantidad Vendida', 'Subtotal (S/)']);
            foreach ($detalles as $d) {
                fputcsv($output, [
                    $d['nombre'],
                    number_format($d['precio_unitario'], 2),
                    $d['cantidad_total'],
                    number_format($d['precio_unitario'] * $d['cantidad_total'], 2)
                ]);
            }
            fclose($output);
            exit;
        }

        echo "❌ Tipo de reporte no válido.";
    }

    
}
