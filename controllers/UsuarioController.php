<?php
require_once dirname(__FILE__) . '/../models/Usuario.php';

class UsuarioController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Detectar si es petición AJAX
     */
    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
               !empty($_GET['ajax']) || 
               (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function sendJsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    public function listar() {
        try {
            $usuarios = $this->usuarioModel->obtenerTodos();
            
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(true, $usuarios, 'Usuarios obtenidos exitosamente');
            }
            
            return $usuarios;
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, null, $e->getMessage());
            }
            throw new Exception("Error al listar usuarios: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener usuario por ID (para AJAX)
     */
    public function obtener() {
        try {
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("ID de usuario inválido");
            }
            
            $usuario = $this->usuarioModel->obtenerPorId($id);
            
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }
            
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(true, $usuario, 'Usuario obtenido exitosamente');
            }
            
            return $usuario;
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, null, $e->getMessage());
            }
            throw $e;
        }
    }
    
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nombre = trim($_POST['nombre'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $rol = $_POST['rol'] ?? 'empleado';
                
                // Validaciones
                if (empty($nombre) || empty($email) || empty($password)) {
                    throw new Exception("Todos los campos son obligatorios");
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email inválido");
                }
                
                if (strlen($password) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres");
                }
                
                if (!in_array($rol, ['admin', 'empleado'])) {
                    throw new Exception("Rol inválido");
                }
                
                // Verificar si el email ya existe
                if ($this->usuarioModel->obtenerPorEmail($email)) {
                    throw new Exception("El email ya está registrado");
                }
                
                // Crear usuario (el modelo se encarga de hashear la contraseña)
                $id = $this->usuarioModel->crear($nombre, $email, $password, $rol);
                
                if ($this->isAjaxRequest()) {
                    $usuario = $this->usuarioModel->obtenerPorId($id);
                    $this->sendJsonResponse(true, $usuario, "Usuario creado exitosamente");
                }
                
                $_SESSION['mensaje'] = "Usuario creado exitosamente";
                header("Location: ?page=usuarios");
                exit;
            } catch (Exception $e) {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(false, null, $e->getMessage());
                }
                return $e->getMessage();
            }
        }
        return null;
    }
    
    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = intval($_POST['id'] ?? 0);
                $nombre = trim($_POST['nombre'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $rol = $_POST['rol'] ?? 'empleado';
                
                if ($id <= 0) {
                    throw new Exception("ID de usuario inválido");
                }
                
                if (empty($nombre) || empty($email)) {
                    throw new Exception("Todos los campos son obligatorios");
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email inválido");
                }
                
                // Verificar que el email no esté siendo usado por otro usuario
                $usuarioExistente = $this->usuarioModel->obtenerPorEmail($email);
                if ($usuarioExistente && $usuarioExistente['id'] != $id) {
                    throw new Exception("El email ya está siendo usado por otro usuario");
                }
                
                $this->usuarioModel->actualizar($id, $nombre, $email, $rol);
                
                if ($this->isAjaxRequest()) {
                    $usuario = $this->usuarioModel->obtenerPorId($id);
                    $this->sendJsonResponse(true, $usuario, "Usuario actualizado exitosamente");
                }
                
                $_SESSION['mensaje'] = "Usuario actualizado exitosamente";
                header("Location: ?page=usuarios");
                exit;
            } catch (Exception $e) {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonResponse(false, null, $e->getMessage());
                }
                return $e->getMessage();
            }
        }
        return null;
    }
    
    public function cambiarContrasena() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = intval($_POST['id'] ?? 0);
                $password_nueva = $_POST['password_nueva'] ?? '';
                $password_confirmar = $_POST['password_confirmar'] ?? '';
                
                if ($id <= 0) {
                    throw new Exception("ID de usuario inválido");
                }
                
                if (empty($password_nueva) || empty($password_confirmar)) {
                    throw new Exception("Las contraseñas son obligatorias");
                }
                
                if ($password_nueva !== $password_confirmar) {
                    throw new Exception("Las contraseñas no coinciden");
                }
                
                if (strlen($password_nueva) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres");
                }
                
                $hashedPassword = password_hash($password_nueva, PASSWORD_BCRYPT, ['cost' => 10]);
                $this->usuarioModel->cambiarContrasena($id, $hashedPassword);
                
                $_SESSION['mensaje'] = "Contraseña actualizada exitosamente";
                header("Location: ?page=usuarios");
                exit;
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
        return null;
    }
    
    public function eliminar() {
        try {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("ID de usuario inválido");
            }
            
            // No permitir eliminar el usuario admin actual
            $currentUserId = $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? null;
            if ($id == $currentUserId) {
                throw new Exception("No puedes eliminar tu propio usuario");
            }
            
            $this->usuarioModel->eliminar($id);
            
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(true, null, "Usuario eliminado exitosamente");
            }
            
            $_SESSION['mensaje'] = "Usuario eliminado exitosamente";
            header("Location: ?page=usuarios");
            exit;
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, null, $e->getMessage());
            }
            return $e->getMessage();
        }
    }
}
?>
