<?php
/**
 * Modelo Producto
 * SQA: CRUD con validaciones y transacciones
 */

class Producto {
    private $db;
    private $table = 'productos';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear producto
     */
    public function crear($nombre, $sku, $descripcion, $precio, $stock_minimo, $proveedor, $categoria) {
        // Validaciones
        if (empty($nombre) || empty($sku) || $precio <= 0) {
            throw new Exception('Datos inválidos');
        }
        
        // Verificar SKU único
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE sku = ?");
        $stmt->execute([$sku]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('El SKU ya existe');
        }
        
        $sql = "INSERT INTO {$this->table} (nombre, sku, descripcion, precio_unitario, stock_minimo, proveedor, categoria) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$nombre, $sku, $descripcion, $precio, $stock_minimo, $proveedor, $categoria])) {
            return $this->db->lastInsertId();
        }
        
        throw new Exception('Error al crear producto');
    }
    
    /**
     * Obtener producto por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener todos los productos
     */
    public function obtenerTodos() {
        $sql = "SELECT * FROM {$this->table} WHERE estado = 'activo' ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar productos (por nombre o SKU)
     */
    public function buscar($termino) {
        $termino = '%' . $termino . '%';
        $sql = "SELECT * FROM {$this->table} 
                WHERE estado = 'activo' AND (nombre LIKE ? OR sku LIKE ?) 
                ORDER BY nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$termino, $termino]);
        return $stmt->fetchAll();
    }
    
    /**
     * Actualizar producto
     */
    public function actualizar($id, $nombre, $descripcion, $precio, $stock_minimo, $proveedor, $categoria) {
        $sql = "UPDATE {$this->table} 
                SET nombre = ?, descripcion = ?, precio_unitario = ?, 
                    stock_minimo = ?, proveedor = ?, categoria = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$nombre, $descripcion, $precio, $stock_minimo, $proveedor, $categoria, $id])) {
            return true;
        }
        
        throw new Exception('Error al actualizar producto');
    }
    
    /**
     * Eliminar producto (soft delete)
     */
    public function eliminar($id) {
        $sql = "UPDATE {$this->table} SET estado = 'inactivo' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$id])) {
            return true;
        }
        
        throw new Exception('Error al eliminar producto');
    }
    
    /**
     * Obtener productos con stock bajo
     */
    public function obtenerStockBajo() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE estado = 'activo' AND stock_actual <= stock_minimo 
                ORDER BY stock_actual ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
