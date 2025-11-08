<?php
require_once dirname(__FILE__) . '/../models/Producto.php';
require_once dirname(__FILE__) . '/../models/Inventario.php';

class DashboardController {
    private $productoModel;
    private $inventarioModel;
    
    public function __construct() {
        $this->productoModel = new Producto();
        $this->inventarioModel = new Inventario();
    }
    
    /**
     * Obtener todas las estadísticas para el dashboard
     */
    public function obtenerEstadisticas() {
        try {
            $stats = [];
            
            // Estadísticas de productos
            $stats['productos'] = $this->productoModel->obtenerEstadisticas();
            
            // Estadísticas de movimientos
            $stats['movimientos'] = $this->inventarioModel->obtenerEstadisticasMovimientos(30);
            
            return $stats;
        } catch (Exception $e) {
            error_log('Error al obtener estadísticas: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener datos para gráficas (AJAX)
     */
    public function obtenerDatosGraficas() {
        header('Content-Type: application/json');
        
        try {
            $stats = $this->obtenerEstadisticas();
            
            if (!$stats) {
                echo json_encode(['success' => false, 'message' => 'Error al obtener datos']);
                exit;
            }
            
            // Preparar datos para gráficas
            $datos = [
                'success' => true,
                'productos' => [
                    'total' => $stats['productos']['total_productos'],
                    'stock_bajo' => $stats['productos']['stock_bajo'],
                    'valor_inventario' => round($stats['productos']['valor_inventario'], 2),
                    'por_categoria' => $stats['productos']['por_categoria'],
                    'top_stock' => $stats['productos']['top_stock']
                ],
                'movimientos' => [
                    'total' => $stats['movimientos']['total_movimientos'],
                    'entradas_salidas' => $stats['movimientos']['entradas_salidas'],
                    'por_dia' => $stats['movimientos']['movimientos_por_dia'],
                    'top_productos' => $stats['movimientos']['top_productos']
                ]
            ];
            
            echo json_encode($datos);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}

