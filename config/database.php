<?php
/**
 * Configuración de Base de Datos
 * Aseguramiento de Calidad: Conexión segura con manejo de errores
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventario_pyme');
define('DB_CHARSET', 'utf8mb4');

// Opciones de seguridad para PDO
define('PDO_OPTIONS', array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // Previene SQL Injection
));

// Configuración de sesión segura
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_SECURE', false); // Cambiar a true en producción con HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Ruta base de la aplicación
define('BASE_PATH', dirname(dirname(__FILE__)));
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('MODELS_PATH', BASE_PATH . '/models');
define('VIEWS_PATH', BASE_PATH . '/views');

/**
 * Clase Database - Gestión de conexión a BD
 * SQA: Implementa patrón Singleton y prepared statements
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            error_log('Error de conexión: ' . $e->getMessage());
            die('<h2>Error de Conexión a Base de Datos</h2><p>No se pudo conectar a la BD. Por favor:</p><ol><li>Asegúrate que XAMPP está ejecutando MySQL</li><li>Verifica que creaste la BD "inventario_pyme" en phpMyAdmin</li><li>Ejecuta los scripts SQL en orden: 01, 02, 03</li></ol>');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    // Prevenir clonación
    private function __clone() {}
    private function __wakeup() {}
}
?>
