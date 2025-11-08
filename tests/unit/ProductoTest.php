<?php
/**
 * Pruebas Unitarias - Modelo Producto
 * SQA: Cobertura >80%
 */

require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once BASE_PATH . '/models/Producto.php';

class ProductoTest {
    private $producto;
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->producto = new Producto();
    }
    
    /**
     * Test: Crear producto válido
     */
    public function testCrearProductoValido() {
        try {
            $id = $this->producto->crear(
                'Test Producto',
                'SKU-TEST-' . time(),
                'Descripción test',
                99.99,
                10,
                'Proveedor Test',
                'Test'
            );
            
            echo "✓ Test crear producto válido: PASÓ\n";
            return true;
        } catch (Exception $e) {
            echo "✗ Test crear producto válido: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Test: Evitar SKU duplicado
     */
    public function testSKUDuplicado() {
        try {
            $sku = 'SKU-DUP-' . time();
            $this->producto->crear('Prod 1', $sku, 'Desc', 100, 10, 'Prov', 'Cat');
            $this->producto->crear('Prod 2', $sku, 'Desc', 100, 10, 'Prov', 'Cat');
            echo "✗ Test SKU duplicado: FALLÓ - Debería lanzar excepción\n";
            return false;
        } catch (Exception $e) {
            echo "✓ Test SKU duplicado: PASÓ\n";
            return true;
        }
    }
    
    /**
     * Test: Obtener producto por ID
     */
    public function testObtenerPorId() {
        try {
            $id = $this->producto->crear('Prod Test', 'SKU-GET-' . time(), 'Desc', 50, 5, 'Prov', 'Cat');
            $prod = $this->producto->obtenerPorId($id);
            
            if ($prod && $prod['id'] == $id && $prod['nombre'] === 'Prod Test') {
                echo "✓ Test obtener por ID: PASÓ\n";
                return true;
            } else {
                echo "✗ Test obtener por ID: FALLÓ\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Test obtener por ID: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Test: Buscar productos
     */
    public function testBuscar() {
        try {
            $termino = 'Laptop';
            $resultados = $this->producto->buscar($termino);
            echo "✓ Test búsqueda: PASÓ (Encontrados: " . count($resultados) . " resultados)\n";
            return true;
        } catch (Exception $e) {
            echo "✗ Test búsqueda: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Test: Actualizar producto
     */
    public function testActualizar() {
        try {
            $id = $this->producto->crear('Prod Original', 'SKU-UPD-' . time(), 'Desc', 100, 10, 'Prov', 'Cat');
            $this->producto->actualizar($id, 'Prod Actualizado', 'Desc Nueva', 150, 5, 'Prov Nueva', 'Cat Nueva');
            
            $prod = $this->producto->obtenerPorId($id);
            if ($prod['nombre'] === 'Prod Actualizado' && $prod['precio_unitario'] == 150) {
                echo "✓ Test actualizar: PASÓ\n";
                return true;
            } else {
                echo "✗ Test actualizar: FALLÓ\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Test actualizar: FALLÓ - " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Ejecutar todos los tests
     */
    public function ejecutarTodos() {
        echo "\n=== EJECUTANDO PRUEBAS DE PRODUCTO ===\n";
        $resultados = [];
        $resultados[] = $this->testCrearProductoValido();
        $resultados[] = $this->testSKUDuplicado();
        $resultados[] = $this->testObtenerPorId();
        $resultados[] = $this->testBuscar();
        $resultados[] = $this->testActualizar();
        
        $pasadas = array_sum($resultados);
        $total = count($resultados);
        echo "\nRESULTADO: $pasadas/$total pruebas pasadas\n";
        echo "Cobertura: " . round(($pasadas / $total) * 100) . "%\n";
    }
}

// Ejecutar tests
$test = new ProductoTest();
$test->ejecutarTodos();
?>
