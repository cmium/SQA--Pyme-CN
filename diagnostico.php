<?php
/**
 * Script de diagnóstico - Verifica conexión, usuarios y contraseñas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DIAGNÓSTICO DE LOGIN - SISTEMA INVENTARIO</h1><hr>";

// 1. Verificar conexión a BD
echo "<h2>1. Verificando conexión a BD...</h2>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=inventario_pyme;charset=utf8mb4', 'root', '');
    echo "<span style='color:green'>✓ Conexión exitosa a BD inventario_pyme</span><br>";
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ Error de conexión: " . $e->getMessage() . "</span><br>";
    echo "Solución: Asegúrate que XAMPP está ejecutando MySQL y que creaste la BD 'inventario_pyme'<br>";
    exit;
}

// 2. Verificar tabla usuarios
echo "<h2>2. Verificando tabla usuarios...</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Total de usuarios: <strong>" . $result['total'] . "</strong><br>";
    
    if ($result['total'] > 0) {
        echo "<h3>Usuarios en BD:</h3>";
        $stmt = $pdo->query("SELECT id, nombre, email, rol, estado FROM usuarios");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></tr>";
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['nombre'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['rol'] . "</td>";
            echo "<td>" . $user['estado'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<span style='color:red'>✗ No hay usuarios en la BD. Necesitas insertar datos.</span>";
    }
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ Error: " . $e->getMessage() . "</span>";
    exit;
}

// 3. Verificar contraseñas hasheadas
echo "<h2>3. Verificando integridad de contraseñas...</h2>";
try {
    $stmt = $pdo->query("SELECT id, email, contrasena FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usuarios as $user) {
        echo "Usuario: <strong>" . $user['email'] . "</strong><br>";
        echo "Hash almacenado: <code style='font-size:11px'>" . $user['contrasena'] . "</code><br>";
        
        // Verificar que sea un hash bcrypt válido
        if (strpos($user['contrasena'], '$2y$') === 0) {
            echo "<span style='color:green'>✓ Hash bcrypt válido</span><br>";
        } else {
            echo "<span style='color:red'>✗ Hash NO válido (no es bcrypt)</span><br>";
        }
        echo "<hr>";
    }
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ Error: " . $e->getMessage() . "</span>";
}

// 4. Generar hashes de prueba
echo "<h2>4. Generando hashes para contraseñas de prueba...</h2>";
echo "Las siguientes son las contraseñas hasheadas correctas que debes usar:<br><br>";

$test_passwords = [
    'admin@pyme.com' => 'Admin@123',
    'empleado@pyme.com' => 'Emp@123'
];

foreach ($test_passwords as $email => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    echo "<strong>Email:</strong> $email<br>";
    echo "<strong>Contraseña:</strong> $password<br>";
    echo "<strong>Hash:</strong> <code style='font-size:11px'>$hash</code><br>";
    echo "<br>";
}

// 5. Instrucciones para corregir
echo "<h2>5. INSTRUCCIONES PARA CORREGIR EL PROBLEMA</h2>";
echo "<ol>";
echo "<li>Copia los hashes del paso 4 anterior</li>";
echo "<li>Ve a phpMyAdmin → BD inventario_pyme → Tabla usuarios</li>";
echo "<li>Edita cada usuario y reemplaza el campo 'contrasena' con su hash correspondiente</li>";
echo "<li>Guarda los cambios</li>";
echo "<li>Intenta login nuevamente</li>";
echo "</ol>";

// 6. Test de verificación de contraseña
echo "<h2>6. Test de password_verify() - Verificando que funciona correctamente</h2>";
$test_password = 'TestPassword123';
$test_hash = password_hash($test_password, PASSWORD_BCRYPT, ['cost' => 10]);
echo "Contraseña test: <strong>$test_password</strong><br>";
echo "Hash generado: <code style='font-size:11px'>$test_hash</code><br>";

if (password_verify($test_password, $test_hash)) {
    echo "<span style='color:green'>✓ password_verify() funciona correctamente</span><br>";
} else {
    echo "<span style='color:red'>✗ password_verify() NO funciona</span><br>";
}

echo "<hr>";
echo "<p>Última actualización: " . date('Y-m-d H:i:s') . "</p>";
?>
