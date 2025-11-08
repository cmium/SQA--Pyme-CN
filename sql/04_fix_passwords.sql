-- SCRIPT PARA CORREGIR LAS CONTRASEÑAS
-- Ejecutar en phpMyAdmin → SQL

-- Actualizar contraseña de admin@pyme.com (Admin@123)
UPDATE usuarios 
SET contrasena = '$2y$10$xnv5ahFvi/VhK27WNKHOeuRdV5574OpV8QA3.iCblooC.vjZ5RG52'
WHERE email = 'admin@pyme.com';

-- Actualizar contraseña de empleado@pyme.com (Emp@123)
UPDATE usuarios 
SET contrasena = '$2y$10$tGt2FYfFR2AdeoSDs7q8bO53QPnruX2gQxhpx0sHBh5zChq9QohGq'
WHERE email = 'empleado@pyme.com';

-- Verificar que se actualizaron
SELECT id, email, rol, estado FROM usuarios;
