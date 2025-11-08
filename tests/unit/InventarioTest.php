<?php
/**
 * Pruebas Unitarias - Modelo Inventario
 * SQA: Transacciones atómicas
 */

require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once BASE_PATH . '/models/Inventario.php';
require_once BASE_PATH . '/models/Producto.php';

class InventarioTest {
    private $inventario;
    private $producto;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->inventario = new Inventario();
        $this->producto = new Producto();
    }
    
    /**
     * Test: Registrar entrada
     */
    public function testRegistrarEntrada() {
        try {
            // Crear producto
            $prod_id = $this->producto->crear('Test Entrada', 'SKU-ENT-' . time(), 'Desc', 100, 10, 'Prov', 'Cat');
            
            // Stock inicial: 0
            $antes = $this->producto->obtenerPorId($prod_id);
            $stock_antes = $antes['stock_actual'];
            
            // Registrar entrada
            $this->inventario->registrarEntrada($prod_id, 1, 50, 'Test entrada', 'REF-001');
            
            // Verificar
            $despues = $this->producto->obtenerPorId($prod_id);
            
            if ($despues['stock_actual'] == ($stock_antes + 50)) {
                echo "✓ Test registrar entrada: PASÓ\n";
                return true;
            } else {
                echo "✗ Test registrar entrada: FALLÓ\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Test registrar entrada: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Test: Registrar salida
     */
    public function testRegistrarSalida() {
        try {
            $prod_id = $this->producto->crear('Test Salida', 'SKU-SAL-' . time(), 'Desc', 100, 10, 'Prov', 'Cat');
            
            // Registrar entrada primero
            $this->inventario->registrarEntrada($prod_id, 1, 100, 'Entrada test', 'REF-001');
            
            $antes = $this->producto->obtenerPorId($prod_id);
            $stock_antes = $antes['stock_actual'];
            
            // Registrar salida
            $this->inventario->registrarSalida($prod_id, 1, 30, 'Salida test', 'REF-002');
            
            $despues = $this->producto->obtenerPorId($prod_id);
            
            if ($despues['stock_actual'] == ($stock_antes - 30)) {
                echo "✓ Test registrar salida: PASÓ\n";
                return true;
            } else {
                echo "✗ Test registrar salida: FALLÓ\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Test registrar salida: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Test: Prevenir salida con stock insuficiente
     */
    public function testStockInsuficiente() {
        try {
            $prod_id = $this->producto->crear('Stock Bajo', 'SKU-BAJO-' . time(), 'Desc', 100, 10, 'Prov', 'Cat');
            
            // Intentar salida sin stock
            $this->inventario->registrarSalida($prod_id, 1, 50, 'Salida test', 'REF-003');
            
            echo "✗ Test stock insuficiente: FALLÓ - Debería lanzar excepción\n";
            return false;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Stock insuficiente') !== false) {
                echo "✓ Test stock insuficiente: PASÓ\n";
                return true;
            }
            echo "✗ Test stock insuficiente: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Ejecutar todos los tests
     */
    public function ejecutarTodos() {
        echo "\n=== EJECUTANDO PRUEBAS DE INVENTARIO ===\n";
        $resultados = [];
        $resultados[] = $this->testRegistrarEntrada();
        $resultados[] = $this->testRegistrarSalida();
        $resultados[] = $this->testStockInsuficiente();
        
        $pasadas = array_sum($resultados);
        $total = count($resultados);
        echo "\nRESULTADO: $pasadas/$total pruebas pasadas\n";
        echo "Cobertura: " . round(($pasadas / $total) * 100) . "%\n";
    }
}

// Ejecutar tests
$test = new InventarioTest();
$test->ejecutarTodos();
?>
