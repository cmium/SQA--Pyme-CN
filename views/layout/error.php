<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="error-container" style="padding: 2rem; text-align: center;">
    <h1>Error</h1>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($error ?? 'Ocurrió un error inesperado'); ?>
    </div>
    <a href="?page=dashboard" class="btn btn-primary">Volver al Dashboard</a>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
