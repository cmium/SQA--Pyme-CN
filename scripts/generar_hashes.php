<?php
/**
 * Script para generar hashes de contraseñas correctas
 * Ejecutar en terminal: php scripts/generar_hashes.php
 */

// Contraseñas de prueba
$contrasenas = [
    'admin@pyme.com' => 'Admin@123',
    'empleado@pyme.com' => 'Emp@123'
];

echo "=== HASHES DE CONTRASEÑAS CORRECTOS ===\n\n";

foreach ($contrasenas as $email => $contrasena) {
    $hash = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 10]);
    echo "Email: $email\n";
    echo "Contraseña: $contrasena\n";
    echo "Hash: $hash\n";
    echo "---\n";
}

echo "\nCopia estos hashes en el SQL 03_insert_data.sql\n";
?>
