<?php
/**
 * Controlador de Autenticación
 * SQA: Validación de seguridad, sesiones seguras
 */

class AuthController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Mostrar formulario de login
     */
    public function mostrarLogin() {
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ?page=dashboard');
            exit;
        }
        $error = $_GET['error'] ?? '';
        $email = '';
        require VIEWS_PATH . '/auth/login.php';
    }
    
    /**
     * Procesar login
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        try {
            // Sanitizar entrada
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $contrasena = $_POST['contrasena'] ?? '';
            
            if (empty($email) || empty($contrasena)) {
                throw new Exception('Email y contraseña requeridos');
            }
            
            // Autenticar
            $usuario = $this->usuarioModel->autenticar($email, $contrasena);
            
            // Crear sesión segura
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['login_time'] = time();
            
            // Registrar en auditoría
            $this->registrarAcceso($usuario['id'], 'LOGIN_EXITOSO', true);
            
            header('Location: ?page=dashboard');
            exit;
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $email = $_POST['email'] ?? '';
            $this->registrarAcceso(null, 'LOGIN_FALLIDO', false, $email);
            require VIEWS_PATH . '/auth/login.php';
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (isset($_SESSION['usuario_id'])) {
            $this->registrarAcceso($_SESSION['usuario_id'], 'LOGOUT', true);
        }
        session_destroy();
        header('Location: ?page=login');
        exit;
    }
    
    /**
     * Registrar acceso
     */
    private function registrarAcceso($usuario_id, $accion, $exitoso, $email = '') {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "INSERT INTO auditoria (usuario_id, accion, entidad, cambios, ip_address) 
                    VALUES (?, ?, 'autenticacion', ?, ?)";
            
            $cambios = json_encode(['exitoso' => $exitoso, 'email' => $email]);
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id, $accion, $cambios, $_SERVER['REMOTE_ADDR'] ?? 'N/A']);
        } catch (Exception $e) {
            // Si falla la auditoría, no afecta el login
            error_log('Error en auditoría: ' . $e->getMessage());
        }
    }
}

// Procesar request
$auth = new AuthController();

$action = $_GET['action'] ?? 'mostrar_login';

switch ($action) {
    case 'mostrar_login':
        $auth->mostrarLogin();
        break;
    case 'procesar_login':
        $auth->procesarLogin();
        break;
    case 'logout':
        $auth->logout();
        break;
    default:
        $auth->mostrarLogin();
}
?>
