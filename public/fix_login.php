<?php
/**
 * SCRIPT DE CORRECCIÓN DE LOGIN
 * Este archivo corrige automáticamente las contraseñas en la BD
 * Acceder a: http://localhost/inventario_pyme/public/fix_login.php
 */

// Conectar a BD
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'inventario_pyme';

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Hashes correctos generados por password_hash()
    $hashes = [
        'admin@pyme.com' => '$2y$10$xnv5ahFvi/VhK27WNKHOeuRdV5574OpV8QA3.iCblooC.vjZ5RG52',
        'empleado@pyme.com' => '$2y$10$tGt2FYfFR2AdeoSDs7q8bO53QPnruX2gQxhpx0sHBh5zChq9QohGq'
    ];
    
    echo "<h2>CORRIGIENDO CONTRASEÑAS...</h2>";
    
    foreach ($hashes as $email => $hash) {
        $sql = "UPDATE usuarios SET contrasena = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $hash, $email);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Contraseña actualizada para: <strong>$email</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Error actualizando: <strong>$email</strong></p>";
        }
    }
    
    echo "<h3>USUARIOS ACTUALES EN BD:</h3>";
    $result = $conn->query("SELECT id, email, rol, estado FROM usuarios");
    
    echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Rol</th><th>Estado</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['rol']}</td>";
        echo "<td>{$row['estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>✓ CORRECCIÓN COMPLETADA</h3>";
    echo "<p><strong>Ahora intenta ingresar con:</strong></p>";
    echo "<pre>";
    echo "Email: admin@pyme.com\n";
    echo "Contraseña: Admin@123\n\n";
    echo "O\n\n";
    echo "Email: empleado@pyme.com\n";
    echo "Contraseña: Emp@123\n";
    echo "</pre>";
    echo "<a href='index.php'>Volver al Login</a>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
