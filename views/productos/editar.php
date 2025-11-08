<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="form-container">
    <h1>Editar Producto</h1>
    
    <form method="POST" action="?page=productos&action=editar" class="form">
        <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
        
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="sku">SKU (No editable)</label>
            <input type="text" id="sku" name="sku" value="<?php echo htmlspecialchars($producto['sku']); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="precio">Precio Unitario</label>
                <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $producto['precio_unitario']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock_minimo">Stock Mínimo</label>
                <input type="number" id="stock_minimo" name="stock_minimo" value="<?php echo $producto['stock_minimo']; ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="proveedor">Proveedor</label>
                <input type="text" id="proveedor" name="proveedor" value="<?php echo htmlspecialchars($producto['proveedor'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoría</label>
                <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($producto['categoria'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="?page=productos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
