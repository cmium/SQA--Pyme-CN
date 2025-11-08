<?php
/**
 * Punto de entrada principal
 * SQA: Enrutador seguro
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/error.log');

if (!is_dir(dirname(__DIR__) . '/logs')) {
    mkdir(dirname(__DIR__) . '/logs', 0755, true);
}

// Cargar configuración y clases
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/Usuario.php';
require_once dirname(__DIR__) . '/models/Producto.php';
require_once dirname(__DIR__) . '/models/Inventario.php';

// Configurar sesión segura (solo una vez)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', SESSION_HTTPONLY);
    ini_set('session.cookie_samesite', SESSION_SAMESITE);
    session_start();
}

// Verificar autenticación (excepto en login)
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

if ($page !== 'login' && !isset($_SESSION['usuario_id'])) {
    header('Location: ?page=login');
    exit;
}

// Verificar timeout de sesión
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
    session_destroy();
    header('Location: ?page=login&expired=1');
    exit;
}

try {
    switch ($page) {
        case 'login':
            require CONTROLLERS_PATH . '/AuthController.php';
            break;
            
        case 'dashboard':
            require CONTROLLERS_PATH . '/DashboardController.php';
            $dashboardController = new DashboardController();
            
            // Si es petición AJAX para obtener datos de gráficas
            if (isset($_GET['action']) && $_GET['action'] === 'datos_graficas') {
                $dashboardController->obtenerDatosGraficas();
            }
            
            require VIEWS_PATH . '/dashboard/index.php';
            break;
            
        case 'productos':
            require CONTROLLERS_PATH . '/ProductoController.php';
            break;
            
        case 'inventario':
            require CONTROLLERS_PATH . '/InventarioController.php';
            break;
            
        case 'movimientos':
            require CONTROLLERS_PATH . '/InventarioController.php';
            break;
        
        case 'usuarios':
            // Verificar permisos - usar ambas variables de sesión por compatibilidad
            $userRole = $_SESSION['usuario_rol'] ?? $_SESSION['user_role'] ?? 'empleado';
            if ($userRole !== 'admin') {
                $error = 'No tienes permisos para acceder a esta sección';
                require VIEWS_PATH . '/layout/error.php';
            } else {
                require CONTROLLERS_PATH . '/UsuarioController.php';
                $usuarioController = new UsuarioController();
                
                // Manejar acciones GET (AJAX)
                if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($action)) {
                    try {
                        if ($action === 'obtener') {
                            $usuarioController->obtener();
                        } elseif ($action === 'listar') {
                            $usuarioController->listar();
                        } elseif ($action === 'eliminar') {
                            $usuarioController->eliminar();
                        }
                    } catch (Exception $e) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                        exit;
                    }
                }
                
                // Procesar acciones POST
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $accion = $_POST['accion'] ?? '';
                    try {
                        if ($accion === 'crear') {
                            $resultado = $usuarioController->crear();
                            if ($resultado && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                                $_SESSION['error'] = $resultado;
                            }
                        } elseif ($accion === 'editar') {
                            $resultado = $usuarioController->editar();
                            if ($resultado && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                                $_SESSION['error'] = $resultado;
                            }
                        } elseif ($accion === 'cambiar_contrasena') {
                            $resultado = $usuarioController->cambiarContrasena();
                            if ($resultado && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                                $_SESSION['error'] = $resultado;
                            }
                        } elseif ($accion === 'eliminar') {
                            $resultado = $usuarioController->eliminar();
                            if ($resultado && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                                $_SESSION['error'] = $resultado;
                            }
                        }
                    } catch (Exception $e) {
                        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                            $_SESSION['error'] = $e->getMessage();
                        }
                    }
                }
                
                require VIEWS_PATH . '/usuarios/index.php';
            }
            break;
            
        case 'reportes':
            require CONTROLLERS_PATH . '/ReporteController.php';
            $reporteController = new ReporteController();
            
            $action = $_GET['action'] ?? '';
            $tipo = $_GET['tipo'] ?? '';
            
            if ($action === 'generar') {
                if ($tipo === 'pdf') {
                    $reporteController->generarPDF();
                } elseif ($tipo === 'excel') {
                    $reporteController->generarExcel();
                } else {
                    $_SESSION['error'] = 'Tipo de reporte inválido';
                    header('Location: ?page=dashboard');
                    exit;
                }
            } else {
                $_SESSION['error'] = 'Acción no válida';
                header('Location: ?page=dashboard');
                exit;
            }
            break;
            
        default:
            require VIEWS_PATH . '/dashboard/index.php';
    }
} catch (Exception $e) {
    error_log('Error en enrutamiento: ' . $e->getMessage());
    $error = 'Ocurrió un error: ' . $e->getMessage();
    require VIEWS_PATH . '/layout/error.php';
}
?>
