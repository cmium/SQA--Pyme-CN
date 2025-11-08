<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="movimientos-container">
    <h1>Historial de Movimientos</h1>
    
    <div class="filters">
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="page" value="movimientos">
            <select name="tipo">
                <option value="">-- Todos los tipos --</option>
                <option value="entrada" <?php echo ($_GET['tipo'] ?? '') === 'entrada' ? 'selected' : ''; ?>>Entradas</option>
                <option value="salida" <?php echo ($_GET['tipo'] ?? '') === 'salida' ? 'selected' : ''; ?>>Salidas</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
        </form>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Producto</th>
                <th>SKU</th>
                <th>Cantidad</th>
                <th>Stock Anterior</th>
                <th>Stock Nuevo</th>
                <th>Usuario</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos ?? [] as $mov): ?>
                <tr class="movimiento-<?php echo $mov['tipo_movimiento']; ?>">
                    <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $mov['tipo_movimiento']; ?>">
                            <?php echo ucfirst($mov['tipo_movimiento']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($mov['producto_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($mov['sku']); ?></td>
                    <td><?php echo $mov['cantidad']; ?></td>
                    <td><?php echo $mov['cantidad_anterior']; ?></td>
                    <td><?php echo $mov['cantidad_nueva']; ?></td>
                    <td><?php echo htmlspecialchars($mov['usuario_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($mov['motivo'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
