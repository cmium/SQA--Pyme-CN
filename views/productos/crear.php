<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="form-container">
    <h1>Crear Nuevo Producto</h1>
    
    <form method="POST" action="?page=productos&action=crear" class="form">
        <div class="form-group">
            <label for="nombre">Nombre del Producto *</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div class="form-group">
            <label for="sku">SKU (Código único) *</label>
            <input type="text" id="sku" name="sku" required>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="precio">Precio Unitario *</label>
                <input type="number" id="precio" name="precio" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="stock_minimo">Stock Mínimo *</label>
                <input type="number" id="stock_minimo" name="stock_minimo" value="10" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="proveedor">Proveedor</label>
                <input type="text" id="proveedor" name="proveedor">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoría</label>
                <input type="text" id="categoria" name="categoria">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Crear Producto</button>
            <a href="?page=productos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
