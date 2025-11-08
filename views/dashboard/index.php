<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="dashboard">
    <h1>Dashboard</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Gestión de Productos</h3>
            <p>Agregar, editar y buscar productos del inventario</p>
            <a href="?page=productos" class="btn btn-primary">Ir a Productos</a>
        </div>
        
        <div class="stat-card">
            <h3>Movimientos de Inventario</h3>
            <p>Registrar entradas y salidas de productos</p>
            <a href="?page=inventario" class="btn btn-primary">Registrar Movimiento</a>
        </div>
        
        <div class="stat-card">
            <h3>Historial</h3>
            <p>Ver el historial de movimientos</p>
            <a href="?page=movimientos" class="btn btn-primary">Ver Movimientos</a>
        </div>
    </div>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
