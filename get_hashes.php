<?php
// Script simple para generar los hashes correctos
$hash_admin = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 10]);
$hash_empleado = password_hash('Emp@123', PASSWORD_BCRYPT, ['cost' => 10]);

echo "Hash Admin (Admin@123): " . $hash_admin . "\n";
echo "Hash Empleado (Emp@123): " . $hash_empleado . "\n";
?>
