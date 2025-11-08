<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener datos de sesión de forma segura
$usuario_nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$usuario_rol = htmlspecialchars($_SESSION['usuario_rol'] ?? 'empleado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Gestión Inventario - PYME</title>
    <link rel="stylesheet" href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="?page=dashboard" class="logo">📦 Inventario PYME</a>
            </div>
            <div class="navbar-menu">
                <a href="?page=dashboard" class="nav-link">Dashboard</a>
                <a href="?page=productos" class="nav-link">Productos</a>
                <a href="?page=inventario" class="nav-link">Movimientos</a>
                <?php if ($usuario_rol === 'admin'): ?>
                    <a href="?page=usuarios" class="nav-link">Usuarios</a>
                <?php endif; ?>
                <div class="nav-user">
                    <span><?php echo $usuario_nombre; ?></span>
                    <a href="?page=login&action=logout" class="btn-logout">Salir</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="container">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['mensaje']); 
                unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
