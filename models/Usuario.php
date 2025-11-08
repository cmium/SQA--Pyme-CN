<?php
/**
 * Modelo Usuario
 * SQA: Validación, sanitización y seguridad
 */

class Usuario {
    private $db;
    private $table = 'usuarios';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function registrar($nombre, $email, $contrasena, $rol = 'empleado') {
        // Validaciones
        if (!$this->validarEmail($email)) {
            throw new Exception('Email inválido');
        }
        
        if (strlen($contrasena) < 8) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres');
        }
        
        // Verificar si email ya existe
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('El email ya está registrado');
        }
        
        // Hashear contraseña
        $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Insertar usuario
        $sql = "INSERT INTO {$this->table} (nombre, email, contrasena, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$nombre, $email, $contrasena_hash, $rol])) {
            return $this->db->lastInsertId();
        }
        
        throw new Exception('Error al registrar usuario');
    }
    
    /**
     * Autenticar usuario
     */
    public function autenticar($email, $contrasena) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Usuario o contraseña incorrectos');
        }
        
        $usuario = $stmt->fetch();
        
        if (!password_verify($contrasena, $usuario['contrasena'])) {
            throw new Exception('Usuario o contraseña incorrectos');
        }
        
        return $usuario;
    }
    
    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT id, nombre, email, rol, estado FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Validar email
     */
    private function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Obtener todos los usuarios (solo Admin)
     */
    public function obtenerTodos() {
        $sql = "SELECT id, nombre, email, rol, estado, fecha_creacion FROM {$this->table} ORDER BY fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener usuario por email
     */
    public function obtenerPorEmail($email) {
        $sql = "SELECT id, nombre, email, rol, estado FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nuevo usuario (alias de registrar)
     * Nota: La validación de longitud mínima se hace en el controlador
     */
    public function crear($nombre, $email, $password, $rol = 'empleado') {
        // Validaciones básicas
        if (!$this->validarEmail($email)) {
            throw new Exception('Email inválido');
        }
        
        // Verificar si email ya existe
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('El email ya está registrado');
        }
        
        // Hashear contraseña
        $contrasena_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Insertar usuario
        $sql = "INSERT INTO {$this->table} (nombre, email, contrasena, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$nombre, $email, $contrasena_hash, $rol])) {
            return $this->db->lastInsertId();
        }
        
        throw new Exception('Error al crear usuario');
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($id, $nombre, $email, $rol) {
        // Validaciones
        if (!$this->validarEmail($email)) {
            throw new Exception('Email inválido');
        }
        
        // Verificar que el email no esté siendo usado por otro usuario
        $usuarioExistente = $this->obtenerPorEmail($email);
        if ($usuarioExistente && $usuarioExistente['id'] != $id) {
            throw new Exception('El email ya está siendo usado por otro usuario');
        }
        
        $sql = "UPDATE {$this->table} SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$nombre, $email, $rol, $id])) {
            return true;
        }
        
        throw new Exception('Error al actualizar usuario');
    }
    
    /**
     * Cambiar contraseña
     */
    public function cambiarContrasena($id, $nuevaContrasena) {
        if (strlen($nuevaContrasena) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres');
        }
        
        $hashedPassword = password_hash($nuevaContrasena, PASSWORD_BCRYPT, ['cost' => 10]);
        $sql = "UPDATE {$this->table} SET contrasena = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$hashedPassword, $id])) {
            return true;
        }
        
        throw new Exception('Error al cambiar contraseña');
    }
    
    /**
     * Eliminar usuario
     */
    public function eliminar($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$id])) {
            return true;
        }
        
        throw new Exception('Error al eliminar usuario');
    }
}
?>
