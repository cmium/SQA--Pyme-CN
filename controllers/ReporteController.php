<?php
require_once dirname(__FILE__) . '/../models/Producto.php';
require_once dirname(__FILE__) . '/../models/Inventario.php';

class ReporteController {
    private $productoModel;
    private $inventarioModel;
    
    public function __construct() {
        $this->productoModel = new Producto();
        $this->inventarioModel = new Inventario();
    }
    
    /**
     * Generar reporte PDF
     */
    public function generarPDF() {
        try {
            $stats = [];
            $stats['productos'] = $this->productoModel->obtenerEstadisticas();
            $stats['movimientos'] = $this->inventarioModel->obtenerEstadisticasMovimientos(30);
            $productos = $this->productoModel->obtenerTodos();
            $movimientos = $this->inventarioModel->obtenerMovimientos(['tipo' => '']);
            
            // Generar HTML con JavaScript para imprimir como PDF
            $html = $this->generarHTMLReporte($stats, $productos, $movimientos);
            
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit;
        } catch (Exception $e) {
            error_log('Error al generar PDF: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al generar el reporte PDF';
            header('Location: ?page=dashboard');
            exit;
        }
    }
    
    /**
     * Generar reporte Excel
     */
    public function generarExcel() {
        try {
            $stats = [];
            $stats['productos'] = $this->productoModel->obtenerEstadisticas();
            $stats['movimientos'] = $this->inventarioModel->obtenerEstadisticasMovimientos(30);
            $productos = $this->productoModel->obtenerTodos();
            $movimientos = $this->inventarioModel->obtenerMovimientos(['tipo' => '']);
            
            // Generar CSV (formato compatible con Excel)
            $filename = 'reporte_inventario_' . date('Y-m-d_His') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 (Excel)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($output, ['REPORTE DE INVENTARIO - ' . date('d/m/Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['ESTADÍSTICAS GENERALES']);
            fputcsv($output, ['Total Productos', $stats['productos']['total_productos']]);
            fputcsv($output, ['Productos con Stock Bajo', $stats['productos']['stock_bajo']]);
            fputcsv($output, ['Valor Total Inventario', 'Q' . number_format($stats['productos']['valor_inventario'], 2)]);
            fputcsv($output, ['Total Movimientos (30 días)', $stats['movimientos']['total_movimientos']]);
            fputcsv($output, []);
            
            // Productos
            fputcsv($output, ['PRODUCTOS']);
            fputcsv($output, ['SKU', 'Nombre', 'Precio Unitario', 'Stock Actual', 'Stock Mínimo', 'Proveedor', 'Categoría']);
            foreach ($productos as $producto) {
                fputcsv($output, [
                    $producto['sku'],
                    $producto['nombre'],
                    'Q' . number_format($producto['precio_unitario'], 2),
                    $producto['stock_actual'],
                    $producto['stock_minimo'],
                    $producto['proveedor'] ?? 'N/A',
                    $producto['categoria'] ?? 'N/A'
                ]);
            }
            fputcsv($output, []);
            
            // Movimientos (últimos 50)
            fputcsv($output, ['MOVIMIENTOS RECIENTES']);
            fputcsv($output, ['Fecha', 'Producto', 'Tipo', 'Cantidad', 'Stock Anterior', 'Stock Nuevo', 'Usuario']);
            $movimientosLimitados = array_slice($movimientos, 0, 50);
            foreach ($movimientosLimitados as $mov) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])),
                    $mov['producto_nombre'],
                    ucfirst($mov['tipo_movimiento']),
                    $mov['cantidad'],
                    $mov['cantidad_anterior'],
                    $mov['cantidad_nueva'],
                    $mov['usuario_nombre']
                ]);
            }
            
            fclose($output);
            exit;
        } catch (Exception $e) {
            error_log('Error al generar Excel: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al generar el reporte Excel';
            header('Location: ?page=dashboard');
            exit;
        }
    }
    
    /**
     * Generar HTML para reporte PDF
     */
    private function generarHTMLReporte($stats, $productos, $movimientos) {
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        @media screen {
            body { font-family: Arial, sans-serif; padding: 20px; }
            .no-print { display: block; }
        }
        @media print {
            body { font-family: Arial, sans-serif; padding: 10px; margin: 0; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: A4; }
            h1 { page-break-after: avoid; }
            h2 { page-break-after: avoid; }
            table { page-break-inside: avoid; }
        }
        h1 { color: #2c3e50; margin-top: 0; }
        h2 { color: #3498db; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #3498db; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; border: 1px solid #ddd; }
        .stat-value { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 14px; }
        .print-button { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; z-index: 1000; }
        .print-button:hover { background: #2980b9; }
    </style>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>
    <h1>Reporte de Inventario</h1>
    <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
    
    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">' . $stats['productos']['total_productos'] . '</div>
            <div class="stat-label">Total Productos</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">' . $stats['productos']['stock_bajo'] . '</div>
            <div class="stat-label">Stock Bajo</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q' . number_format($stats['productos']['valor_inventario'], 2) . '</div>
            <div class="stat-label">Valor Inventario</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">' . $stats['movimientos']['total_movimientos'] . '</div>
            <div class="stat-label">Movimientos (30 días)</div>
        </div>
    </div>
    
    <h2>Productos</h2>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Proveedor</th>
                <th>Categoría</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($productos as $producto) {
            $html .= '<tr>
                <td>' . htmlspecialchars($producto['sku']) . '</td>
                <td>' . htmlspecialchars($producto['nombre']) . '</td>
                <td>Q' . number_format($producto['precio_unitario'], 2) . '</td>
                <td>' . $producto['stock_actual'] . '</td>
                <td>' . $producto['stock_minimo'] . '</td>
                <td>' . htmlspecialchars($producto['proveedor'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($producto['categoria'] ?? 'N/A') . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <h2>Movimientos Recientes (Últimos 50)</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Stock Anterior</th>
                <th>Stock Nuevo</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>';
        
        $movimientosLimitados = array_slice($movimientos, 0, 50);
        foreach ($movimientosLimitados as $mov) {
            $html .= '<tr>
                <td>' . date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])) . '</td>
                <td>' . htmlspecialchars($mov['producto_nombre']) . '</td>
                <td>' . ucfirst($mov['tipo_movimiento']) . '</td>
                <td>' . $mov['cantidad'] . '</td>
                <td>' . $mov['cantidad_anterior'] . '</td>
                <td>' . $mov['cantidad_nueva'] . '</td>
                <td>' . htmlspecialchars($mov['usuario_nombre']) . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
</body>
</html>';
        
        return $html;
    }
}

