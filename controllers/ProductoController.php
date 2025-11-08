<?php
/**
 * Controlador de Productos
 * SQA: Control de acceso, validaciones
 */

class ProductoController {
    private $productoModel;
    
    public function __construct() {
        $this->productoModel = new Producto();
    }
    
    /**
     * Verificar permisos Admin
     */
    private function verificarAdmin() {
        if ($_SESSION['usuario_rol'] !== 'admin') {
            throw new Exception('Acceso denegado: Se requieren permisos de administrador');
        }
    }
    
    /**
     * Listar productos
     */
    public function listar() {
        try {
            $busqueda = $_GET['busqueda'] ?? '';
            
            if (!empty($busqueda)) {
                $productos = $this->productoModel->buscar($busqueda);
            } else {
                $productos = $this->productoModel->obtenerTodos();
            }
            
            $productosBajo = $this->productoModel->obtenerStockBajo();
            
            require VIEWS_PATH . '/productos/index.php';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            require VIEWS_PATH . '/productos/index.php';
        }
    }
    
    /**
     * Mostrar formulario crear
     */
    public function mostrarCrear() {
        try {
            $this->verificarAdmin();
            require VIEWS_PATH . '/productos/crear.php';
        } catch (Exception $e) {
            $error = $e->getMessage();
            require VIEWS_PATH . '/layout/error.php';
        }
    }
    
    /**
     * Crear producto
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        try {
            $this->verificarAdmin();
            
            $nombre = trim($_POST['nombre'] ?? '');
            $sku = trim($_POST['sku'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $stock_minimo = intval($_POST['stock_minimo'] ?? 10);
            $proveedor = trim($_POST['proveedor'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            
            $id = $this->productoModel->crear($nombre, $sku, $descripcion, $precio, $stock_minimo, $proveedor, $categoria);
            
            $_SESSION['mensaje'] = 'Producto creado exitosamente';
            header('Location: ?page=productos');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            require VIEWS_PATH . '/productos/crear.php';
        }
    }
    
    /**
     * Mostrar formulario editar
     */
    public function mostrarEditar() {
        try {
            $this->verificarAdmin();
            $id = intval($_GET['id'] ?? 0);
            $producto = $this->productoModel->obtenerPorId($id);
            
            if (!$producto) {
                throw new Exception('Producto no encontrado');
            }
            
            require VIEWS_PATH . '/productos/editar.php';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            header('Location: ?page=productos');
            exit;
        }
    }
    
    /**
     * Actualizar producto
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        try {
            $this->verificarAdmin();
            
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = floatval($_POST['precio'] ?? 0);
            $stock_minimo = intval($_POST['stock_minimo'] ?? 10);
            $proveedor = trim($_POST['proveedor'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            
            $this->productoModel->actualizar($id, $nombre, $descripcion, $precio, $stock_minimo, $proveedor, $categoria);
            
            $_SESSION['mensaje'] = 'Producto actualizado exitosamente';
            header('Location: ?page=productos');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $id = intval($_POST['id'] ?? 0);
            $producto = $this->productoModel->obtenerPorId($id);
            require VIEWS_PATH . '/productos/editar.php';
        }
    }
    
    /**
     * Eliminar producto
     */
    public function eliminar() {
        try {
            $this->verificarAdmin();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = intval($_POST['id'] ?? 0);
                $this->productoModel->eliminar($id);
                $_SESSION['mensaje'] = 'Producto eliminado exitosamente';
            }
            
            header('Location: ?page=productos');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            header('Location: ?page=productos&error=' . urlencode($error));
            exit;
        }
    }
}

// Procesar request
$productoController = new ProductoController();
$action = $_GET['action'] ?? 'listar';

switch ($action) {
    case 'listar':
        $productoController->listar();
        break;
    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productoController->crear();
        } else {
            $productoController->mostrarCrear();
        }
        break;
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productoController->actualizar();
        } else {
            $productoController->mostrarEditar();
        }
        break;
    case 'eliminar':
        $productoController->eliminar();
        break;
    default:
        $productoController->listar();
}
?>
