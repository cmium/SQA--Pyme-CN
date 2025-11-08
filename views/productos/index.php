<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="productos-container">
    <div class="header-section">
        <h1>Gestión de Productos</h1>
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
            <a href="?page=productos&action=crear" class="btn btn-success">+ Nuevo Producto</a>
        <?php endif; ?>
    </div>
    
    <div class="search-section">
        <form method="GET" action="">
            <input type="hidden" name="page" value="productos">
            <input type="text" name="busqueda" placeholder="Buscar por nombre o SKU..." 
                   value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>
    
    <?php if (!empty($productosBajo)): ?>
        <div class="alert alert-warning">
            <strong>Alerta:</strong> <?php echo count($productosBajo); ?> producto(s) con stock bajo
        </div>
    <?php endif; ?>
    
    <table class="table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Mínimo</th>
                <th>Proveedor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos ?? [] as $producto): ?>
                <tr class="<?php echo ($producto['stock_actual'] <= $producto['stock_minimo']) ? 'stock-bajo' : ''; ?>">
                    <td><?php echo htmlspecialchars($producto['sku']); ?></td>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td>$<?php echo number_format($producto['precio_unitario'], 2); ?></td>
                    <td><?php echo $producto['stock_actual']; ?></td>
                    <td><?php echo $producto['stock_minimo']; ?></td>
                    <td><?php echo htmlspecialchars($producto['proveedor'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                            <a href="?page=productos&action=editar&id=<?php echo $producto['id']; ?>" 
                               class="btn btn-sm btn-info">Editar</a>
                            <form method="POST" action="?page=productos&action=eliminar" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este producto?');">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
