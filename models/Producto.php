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
    
    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total de productos
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'activo'");
        $stmt->execute();
        $stats['total_productos'] = $stmt->fetch()['total'];
        
        // Productos con stock bajo
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'activo' AND stock_actual <= stock_minimo");
        $stmt->execute();
        $stats['stock_bajo'] = $stmt->fetch()['total'];
        
        // Valor total del inventario
        $stmt = $this->db->prepare("SELECT SUM(stock_actual * precio_unitario) as valor_total FROM {$this->table} WHERE estado = 'activo'");
        $stmt->execute();
        $stats['valor_inventario'] = $stmt->fetch()['valor_total'] ?? 0;
        
        // Productos por categoría
        $stmt = $this->db->prepare("SELECT categoria, COUNT(*) as cantidad FROM {$this->table} WHERE estado = 'activo' AND categoria IS NOT NULL AND categoria != '' GROUP BY categoria");
        $stmt->execute();
        $stats['por_categoria'] = $stmt->fetchAll();
        
        // Top 5 productos con más stock
        $stmt = $this->db->prepare("SELECT nombre, stock_actual FROM {$this->table} WHERE estado = 'activo' ORDER BY stock_actual DESC LIMIT 5");
        $stmt->execute();
        $stats['top_stock'] = $stmt->fetchAll();
        
        return $stats;
    }
}
?>
