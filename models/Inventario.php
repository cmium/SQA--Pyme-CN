<?php
/**
 * Modelo Inventario
 * SQA: Transacciones atómicas, precisión, auditoría
 */

class Inventario {
    private $db;
    private $table = 'movimientos_inventario';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registrar entrada de producto (ATÓMICO)
     */
    public function registrarEntrada($producto_id, $usuario_id, $cantidad, $motivo = '', $referencia = '') {
        try {
            $this->db->beginTransaction();
            
            // Obtener stock actual
            $stmt = $this->db->prepare("SELECT stock_actual FROM productos WHERE id = ? FOR UPDATE");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                throw new Exception('Producto no encontrado');
            }
            
            $stock_anterior = $producto['stock_actual'];
            $stock_nuevo = $stock_anterior + $cantidad;
            
            // Registrar movimiento
            $sql = "INSERT INTO {$this->table} 
                    (producto_id, usuario_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, referencia) 
                    VALUES (?, ?, 'entrada', ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$producto_id, $usuario_id, $cantidad, $stock_anterior, $stock_nuevo, $motivo, $referencia]);
            
            // Actualizar stock del producto
            $stmt = $this->db->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
            $stmt->execute([$stock_nuevo, $producto_id]);
            
            // Registrar en auditoría
            $this->registrarAuditoria($usuario_id, 'ENTRADA_INVENTARIO', 'productos', $producto_id, 
                                     ['stock_anterior' => $stock_anterior, 'stock_nuevo' => $stock_nuevo]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Registrar salida de producto (ATÓMICO)
     */
    public function registrarSalida($producto_id, $usuario_id, $cantidad, $motivo = '', $referencia = '') {
        try {
            $this->db->beginTransaction();
            
            // Obtener stock actual
            $stmt = $this->db->prepare("SELECT stock_actual FROM productos WHERE id = ? FOR UPDATE");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                throw new Exception('Producto no encontrado');
            }
            
            $stock_anterior = $producto['stock_actual'];
            
            if ($stock_anterior < $cantidad) {
                throw new Exception('Stock insuficiente');
            }
            
            $stock_nuevo = $stock_anterior - $cantidad;
            
            // Registrar movimiento
            $sql = "INSERT INTO {$this->table} 
                    (producto_id, usuario_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, referencia) 
                    VALUES (?, ?, 'salida', ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$producto_id, $usuario_id, $cantidad, $stock_anterior, $stock_nuevo, $motivo, $referencia]);
            
            // Actualizar stock del producto
            $stmt = $this->db->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
            $stmt->execute([$stock_nuevo, $producto_id]);
            
            // Verificar si está por debajo del stock mínimo
            $stmt = $this->db->prepare("SELECT stock_minimo FROM productos WHERE id = ?");
            $stmt->execute([$producto_id]);
            $producto_info = $stmt->fetch();
            
            if ($stock_nuevo <= $producto_info['stock_minimo']) {
                $this->crearAlerta($producto_id, $stock_nuevo, $producto_info['stock_minimo']);
            }
            
            // Registrar en auditoría
            $this->registrarAuditoria($usuario_id, 'SALIDA_INVENTARIO', 'productos', $producto_id,
                                     ['stock_anterior' => $stock_anterior, 'stock_nuevo' => $stock_nuevo]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Crear alerta de stock bajo
     */
    private function crearAlerta($producto_id, $stock_actual, $stock_minimo) {
        $sql = "INSERT INTO alertas_stock (producto_id, stock_actual, stock_minimo, estado) 
                VALUES (?, ?, ?, 'pendiente')
                ON DUPLICATE KEY UPDATE estado = 'pendiente'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$producto_id, $stock_actual, $stock_minimo]);
    }
    
    /**
     * Obtener movimientos
     */
    public function obtenerMovimientos($filtros = []) {
        $sql = "SELECT m.*, p.nombre as producto_nombre, p.sku, u.nombre as usuario_nombre 
                FROM {$this->table} m 
                JOIN productos p ON m.producto_id = p.id 
                JOIN usuarios u ON m.usuario_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['producto_id'])) {
            $sql .= " AND m.producto_id = ?";
            $params[] = $filtros['producto_id'];
        }
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo_movimiento = ?";
            $params[] = $filtros['tipo'];
        }
        
        $sql .= " ORDER BY m.fecha_movimiento DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Registrar en auditoría
     */
    private function registrarAuditoria($usuario_id, $accion, $entidad, $id_entidad, $cambios = []) {
        $sql = "INSERT INTO auditoria (usuario_id, accion, entidad, id_entidad, cambios, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $usuario_id,
            $accion,
            $entidad,
            $id_entidad,
            json_encode($cambios),
            $_SERVER['REMOTE_ADDR'] ?? 'N/A'
        ]);
    }
}
?>
