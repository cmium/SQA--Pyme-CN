<?php
require_once dirname(__FILE__) . '/../models/Usuario.php';

class UsuarioController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    public function listar() {
        try {
            $usuarios = $this->usuarioModel->obtenerTodos();
            return $usuarios;
        } catch (Exception $e) {
            throw new Exception("Error al listar usuarios: " . $e->getMessage());
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
                
                // Crear usuario
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
                $this->usuarioModel->crear($nombre, $email, $hashedPassword, $rol);
                
                $_SESSION['mensaje'] = "Usuario creado exitosamente";
                header("Location: ?page=usuarios");
                exit;
            } catch (Exception $e) {
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
                
                $_SESSION['mensaje'] = "Usuario actualizado exitosamente";
                header("Location: ?page=usuarios");
                exit;
            } catch (Exception $e) {
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
            $id = intval($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("ID de usuario inválido");
            }
            
            // No permitir eliminar el usuario admin actual
            if ($id === $_SESSION['usuario_id'] ?? null) {
                throw new Exception("No puedes eliminar tu propio usuario");
            }
            
            $this->usuarioModel->eliminar($id);
            
            $_SESSION['mensaje'] = "Usuario eliminado exitosamente";
            header("Location: ?page=usuarios");
            exit;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
?>
