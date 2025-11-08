<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="form-container">
    <h1><?php echo ucfirst($tipo ?? 'entrada'); ?> de Producto</h1>
    
    <form method="POST" action="?page=inventario&action=registrar_<?php echo $tipo ?? 'entrada'; ?>" class="form">
        <div class="form-group">
            <label for="producto_id">Producto *</label>
            <select id="producto_id" name="producto_id" required>
                <option value="">-- Seleccionar producto --</option>
                <?php foreach ($productos ?? [] as $prod): ?>
                    <option value="<?php echo $prod['id']; ?>">
                        <?php echo htmlspecialchars($prod['nombre']); ?> (Stock: <?php echo $prod['stock_actual']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="cantidad">Cantidad *</label>
            <input type="number" id="cantidad" name="cantidad" min="1" required>
        </div>
        
        <div class="form-group">
            <label for="motivo">Motivo</label>
            <textarea id="motivo" name="motivo" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label for="referencia">Referencia</label>
            <input type="text" id="referencia" name="referencia" placeholder="Ej: Factura, Número de orden">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Registrar <?php echo ucfirst($tipo ?? 'entrada'); ?></button>
            <a href="?page=dashboard" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
