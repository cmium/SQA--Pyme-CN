<?php
/**
 * Controlador de Inventario
 * SQA: Transacciones, validaciones, registros atómicos
 */

class InventarioController {
    private $inventarioModel;
    private $productoModel;
    
    public function __construct() {
        $this->inventarioModel = new Inventario();
        $this->productoModel = new Producto();
    }
    
    /**
     * Mostrar formulario de entrada/salida
     */
    public function mostrarFormulario() {
        try {
            $productos = $this->productoModel->obtenerTodos();
            $tipo = $_GET['tipo'] ?? 'entrada';
            
            if (!in_array($tipo, ['entrada', 'salida'])) {
                throw new Exception('Tipo de movimiento inválido');
            }
            
            require VIEWS_PATH . '/inventario/entrada_salida.php';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            require VIEWS_PATH . '/inventario/entrada_salida.php';
        }
    }
    
    /**
     * Procesar entrada de producto
     */
    public function registrarEntrada() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        try {
            $producto_id = intval($_POST['producto_id'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 0);
            $motivo = trim($_POST['motivo'] ?? '');
            $referencia = trim($_POST['referencia'] ?? '');
            
            if ($producto_id <= 0 || $cantidad <= 0) {
                throw new Exception('Datos inválidos');
            }
            
            $this->inventarioModel->registrarEntrada($producto_id, $_SESSION['usuario_id'], $cantidad, $motivo, $referencia);
            
            $_SESSION['mensaje'] = 'Entrada registrada correctamente';
            header('Location: ?page=movimientos');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $productos = $this->productoModel->obtenerTodos();
            $tipo = 'entrada';
            require VIEWS_PATH . '/inventario/entrada_salida.php';
        }
    }
    
    /**
     * Procesar salida de producto
     */
    public function registrarSalida() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        try {
            $producto_id = intval($_POST['producto_id'] ?? 0);
            $cantidad = intval($_POST['cantidad'] ?? 0);
            $motivo = trim($_POST['motivo'] ?? '');
            $referencia = trim($_POST['referencia'] ?? '');
            
            if ($producto_id <= 0 || $cantidad <= 0) {
                throw new Exception('Datos inválidos');
            }
            
            $this->inventarioModel->registrarSalida($producto_id, $_SESSION['usuario_id'], $cantidad, $motivo, $referencia);
            
            $_SESSION['mensaje'] = 'Salida registrada correctamente';
            header('Location: ?page=movimientos');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $productos = $this->productoModel->obtenerTodos();
            $tipo = 'salida';
            require VIEWS_PATH . '/inventario/entrada_salida.php';
        }
    }
    
    /**
     * Listar movimientos
     */
    public function listarMovimientos() {
        try {
            $filtros = [
                'producto_id' => intval($_GET['producto_id'] ?? 0),
                'tipo' => $_GET['tipo'] ?? ''
            ];
            
            $movimientos = $this->inventarioModel->obtenerMovimientos($filtros);
            $productos = $this->productoModel->obtenerTodos();
            
            require VIEWS_PATH . '/inventario/movimientos.php';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            require VIEWS_PATH . '/inventario/movimientos.php';
        }
    }
}

// Procesar request
$inventarioController = new InventarioController();
$action = $_GET['action'] ?? 'formulario';

switch ($action) {
    case 'registrar_entrada':
        $inventarioController->registrarEntrada();
        break;
    case 'registrar_salida':
        $inventarioController->registrarSalida();
        break;
    case 'movimientos':
        $inventarioController->listarMovimientos();
        break;
    default:
        $inventarioController->mostrarFormulario();
}
?>
