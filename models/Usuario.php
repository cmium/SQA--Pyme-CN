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
}
?>
