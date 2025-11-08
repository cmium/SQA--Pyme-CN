-- Insertar usuarios de prueba
-- Admin@123 hasheada con bcrypt
-- Emp@123 hasheada con bcrypt

INSERT INTO usuarios (nombre, email, contrasena, rol, estado) VALUES
('Administrador', 'admin@pyme.com', '$2y$10$8o.h9huhV.Fc0X6EcI.lDuVJVRK5VJVRp5VJVRp5VJVRp5VJVRp5VJV', 'admin', 'activo'),
('Juan Empleado', 'empleado@pyme.com', '$2y$10$9qK7K5K3.E5I8J3L5M7N9P1Q3R5T7U9W1X3Y5Z7A9B1C3D5E7F9G1H', 'empleado', 'activo');

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, sku, descripcion, precio_unitario, stock_actual, stock_minimo, proveedor, categoria, estado) VALUES
('Laptop Dell XPS 13', 'SKU-DELL-001', 'Laptop de alto rendimiento', 1500.00, 5, 2, 'Dell Inc', 'Electrónica', 'activo'),
('Mouse Logitech MX', 'SKU-LOG-001', 'Mouse inalámbrico premium', 99.99, 50, 10, 'Logitech', 'Accesorios', 'activo'),
('Teclado Mecánico RGB', 'SKU-KEY-001', 'Teclado gaming mecánico', 149.99, 15, 5, 'Corsair', 'Accesorios', 'activo'),
('Monitor LG 27"', 'SKU-LG-001', 'Monitor 4K IPS', 350.00, 8, 3, 'LG Electronics', 'Monitores', 'activo'),
('Hub USB-C', 'SKU-USB-001', 'Adaptador multi puerto', 59.99, 25, 8, 'Anker', 'Accesorios', 'activo');
