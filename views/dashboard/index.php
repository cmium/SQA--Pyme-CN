<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div style="display: flex; gap: 1.5rem; padding: 2rem; max-width: 1600px; margin: 0 auto;">
    <!-- Barra lateral izquierda con accesos rápidos -->
    <aside style="width: 250px; flex-shrink: 0; position: sticky; top: 2rem; height: fit-content;">
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: #2c3e50; font-size: 1.1rem; font-weight: 600; border-bottom: 2px solid #3498db; padding-bottom: 0.5rem;">Accesos Rápidos</h3>
            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="?page=productos" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; text-decoration: none; color: #2c3e50; border-radius: 6px; transition: all 0.2s; font-size: 0.95rem;" 
                   onmouseover="this.style.background='#f8f9fa'; this.style.color='#3498db';" 
                   onmouseout="this.style.background='transparent'; this.style.color='#2c3e50';">
                    <span style="font-size: 1.2rem;">📦</span>
                    <span>Gestión de Productos</span>
                </a>
                <a href="?page=inventario&tipo=entrada" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; text-decoration: none; color: #2c3e50; border-radius: 6px; transition: all 0.2s; font-size: 0.95rem;" 
                   onmouseover="this.style.background='#f8f9fa'; this.style.color='#27ae60';" 
                   onmouseout="this.style.background='transparent'; this.style.color='#2c3e50';">
                    <span style="font-size: 1.2rem;">➕</span>
                    <span>Registrar Entrada</span>
                </a>
                <a href="?page=inventario&tipo=salida" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; text-decoration: none; color: #2c3e50; border-radius: 6px; transition: all 0.2s; font-size: 0.95rem;" 
                   onmouseover="this.style.background='#f8f9fa'; this.style.color='#e74c3c';" 
                   onmouseout="this.style.background='transparent'; this.style.color='#2c3e50';">
                    <span style="font-size: 1.2rem;">➖</span>
                    <span>Registrar Salida</span>
                </a>
                <a href="?page=movimientos" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; text-decoration: none; color: #2c3e50; border-radius: 6px; transition: all 0.2s; font-size: 0.95rem;" 
                   onmouseover="this.style.background='#f8f9fa'; this.style.color='#3498db';" 
                   onmouseout="this.style.background='transparent'; this.style.color='#2c3e50';">
                    <span style="font-size: 1.2rem;">📋</span>
                    <span>Ver Movimientos</span>
                </a>
                <?php if (($_SESSION['usuario_rol'] ?? $_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="?page=usuarios" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; text-decoration: none; color: #2c3e50; border-radius: 6px; transition: all 0.2s; font-size: 0.95rem;" 
                   onmouseover="this.style.background='#f8f9fa'; this.style.color='#9b59b6';" 
                   onmouseout="this.style.background='transparent'; this.style.color='#2c3e50';">
                    <span style="font-size: 1.2rem;">👥</span>
                    <span>Gestión de Usuarios</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </aside>

    <!-- Contenido principal del dashboard -->
    <div class="dashboard-container" style="flex: 1; min-width: 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0; color: #2c3e50; font-size: 2rem;">Dashboard</h1>
            <p style="margin: 0.5rem 0 0 0; color: #7f8c8d;">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></p>
        </div>
        <div>
            <button onclick="generarReporte('pdf')" class="btn btn-primary" style="margin-right: 0.5rem;">
                📄 Generar Reporte PDF
            </button>
            <button onclick="generarReporte('excel')" class="btn btn-success">
                📊 Generar Reporte Excel
            </button>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem; font-weight: 500;">Total Productos</p>
                    <h2 id="stat-total-productos" style="margin: 0.5rem 0 0 0; color: #2c3e50; font-size: 2rem; font-weight: 600;">-</h2>
                </div>
                <div style="width: 60px; height: 60px; background: #3498db; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    📦
                </div>
            </div>
        </div>

        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem; font-weight: 500;">Stock Bajo</p>
                    <h2 id="stat-stock-bajo" style="margin: 0.5rem 0 0 0; color: #e74c3c; font-size: 2rem; font-weight: 600;">-</h2>
                </div>
                <div style="width: 60px; height: 60px; background: #e74c3c; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    ⚠️
                </div>
            </div>
        </div>

        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem; font-weight: 500;">Valor Inventario</p>
                    <h2 id="stat-valor-inventario" style="margin: 0.5rem 0 0 0; color: #27ae60; font-size: 2rem; font-weight: 600;">-</h2>
                </div>
                <div style="width: 60px; height: 60px; background: #27ae60; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    💰
                </div>
            </div>
        </div>

        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem; font-weight: 500;">Movimientos (30 días)</p>
                    <h2 id="stat-movimientos" style="margin: 0.5rem 0 0 0; color: #f39c12; font-size: 2rem; font-weight: 600;">-</h2>
                </div>
                <div style="width: 60px; height: 60px; background: #f39c12; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    📊
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Gráfica de productos por categoría -->
        <div class="chart-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: #2c3e50; font-size: 1.2rem;">Productos por Categoría</h3>
            <canvas id="chartCategorias" style="max-height: 300px;"></canvas>
        </div>

        <!-- Gráfica de entradas vs salidas -->
        <div class="chart-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: #2c3e50; font-size: 1.2rem;">Entradas vs Salidas (30 días)</h3>
            <canvas id="chartEntradasSalidas" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Gráfica de movimientos por día -->
        <div class="chart-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: #2c3e50; font-size: 1.2rem;">Movimientos por Día (Últimos 7 días)</h3>
            <canvas id="chartMovimientosDia" style="max-height: 300px;"></canvas>
        </div>

        <!-- Top productos con más stock -->
        <div class="chart-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1.5rem 0; color: #2c3e50; font-size: 1.2rem;">Top 5 Productos con Más Stock</h3>
            <canvas id="chartTopStock" style="max-height: 300px;"></canvas>
        </div>
    </div>

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
let chartCategorias, chartEntradasSalidas, chartMovimientosDia, chartTopStock;

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosDashboard();
});

function cargarDatosDashboard() {
    fetch('?page=dashboard&action=datos_graficas', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            actualizarEstadisticas(data);
            crearGraficas(data);
        } else {
            console.error('Error al cargar datos:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function actualizarEstadisticas(data) {
    document.getElementById('stat-total-productos').textContent = data.productos.total || 0;
    document.getElementById('stat-stock-bajo').textContent = data.productos.stock_bajo || 0;
    document.getElementById('stat-valor-inventario').textContent = 'Q' + (data.productos.valor_inventario || 0).toLocaleString('es-GT', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('stat-movimientos').textContent = data.movimientos.total || 0;
}

function crearGraficas(data) {
    // Gráfica de productos por categoría
    const categoriasData = data.productos.por_categoria || [];
    const ctxCategorias = document.getElementById('chartCategorias').getContext('2d');
    chartCategorias = new Chart(ctxCategorias, {
        type: 'doughnut',
        data: {
            labels: categoriasData.map(item => item.categoria || 'Sin categoría'),
            datasets: [{
                data: categoriasData.map(item => item.cantidad),
                backgroundColor: [
                    '#3498db', '#27ae60', '#e74c3c', '#f39c12', '#9b59b6',
                    '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#16a085'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfica de entradas vs salidas
    const entradasSalidas = data.movimientos.entradas_salidas || [];
    const entrada = entradasSalidas.find(item => item.tipo_movimiento === 'entrada')?.cantidad || 0;
    const salida = entradasSalidas.find(item => item.tipo_movimiento === 'salida')?.cantidad || 0;
    const ctxEntradasSalidas = document.getElementById('chartEntradasSalidas').getContext('2d');
    chartEntradasSalidas = new Chart(ctxEntradasSalidas, {
        type: 'bar',
        data: {
            labels: ['Entradas', 'Salidas'],
            datasets: [{
                label: 'Cantidad',
                data: [entrada, salida],
                backgroundColor: ['#27ae60', '#e74c3c']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfica de movimientos por día
    const movimientosPorDia = data.movimientos.por_dia || [];
    const ctxMovimientosDia = document.getElementById('chartMovimientosDia').getContext('2d');
    chartMovimientosDia = new Chart(ctxMovimientosDia, {
        type: 'line',
        data: {
            labels: movimientosPorDia.map(item => {
                const fecha = new Date(item.fecha);
                return fecha.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit' });
            }),
            datasets: [{
                label: 'Movimientos',
                data: movimientosPorDia.map(item => item.cantidad),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfica de top productos con más stock
    const topStock = data.productos.top_stock || [];
    const ctxTopStock = document.getElementById('chartTopStock').getContext('2d');
    chartTopStock = new Chart(ctxTopStock, {
        type: 'bar',
        data: {
            labels: topStock.map(item => item.nombre.length > 15 ? item.nombre.substring(0, 15) + '...' : item.nombre),
            datasets: [{
                label: 'Stock',
                data: topStock.map(item => item.stock_actual),
                backgroundColor: '#3498db'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

function generarReporte(tipo) {
    window.location.href = `?page=reportes&action=generar&tipo=${tipo}`;
}
</script>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
