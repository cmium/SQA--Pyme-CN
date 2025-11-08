<?php
if (!isset($_SESSION['user_email'])) {
    die('Debes iniciar sesión para acceder a esta página');
}

// Allow any logged-in user for now, check role later
$userRole = $_SESSION['user_role'] ?? 'empleado';
$userEmail = $_SESSION['user_email'] ?? '';

console.log("[v0] Usuario: " . $userEmail . ", Rol: " . $userRole);

// If not admin, redirect back
if ($userRole !== 'admin') {
    die('Sin permisos para acceder a esta página. Tu rol es: ' . $userRole);
}

require 'config/database.php';

$usuarios = [];
$error = '';
$success = '';

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'empleado';
    
    if ($nombre && $email && $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $hashed_password, $rol);
        
        if ($stmt->execute()) {
            $success = "Usuario creado exitosamente";
        } else {
            $error = "Error al crear usuario: " . $con->error;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'eliminar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Usuario eliminado exitosamente";
    } else {
        $error = "Error al eliminar usuario";
    }
}

$resultado = $con->query("SELECT id, nombre, email, rol FROM usuarios ORDER BY id DESC");
if ($resultado) {
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
} else {
    $error = "Error al cargar usuarios: " . $con->error;
}

// If AJAX request, return JSON
if (isset($_GET['action']) && $_GET['action'] === 'listar' && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios
    ]);
    exit;
}
?>

<div style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0; color: #667eea;">Gestión de Usuarios</h2>
        <button onclick="document.getElementById('modalUsuario').style.display='block'" style="
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        ">+ Nuevo Usuario</button>
    </div>

    <?php if (isset($error) && $error): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
    <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <!-- Modal para crear usuario -->
    <div id="modalUsuario" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
        <div style="background: white; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px;">
            <span onclick="document.getElementById('modalUsuario').style.display='none'" style="float: right; font-size: 28px; cursor: pointer; color: #aaa;">&times;</span>
            <h3 style="margin-bottom: 1.5rem; color: #667eea;">Crear Nuevo Usuario</h3>
            
            <form method="POST" action="?page=usuarios&action=crear">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nombre:</label>
                    <input type="text" name="nombre" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email:</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Contraseña:</label>
                    <input type="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Rol:</label>
                    <select name="rol" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="empleado">Empleado</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="flex: 1; background: #667eea; color: white; border: none; padding: 0.75rem; border-radius: 4px; cursor: pointer; font-weight: 600;">Crear</button>
                    <button type="button" onclick="document.getElementById('modalUsuario').style.display='none'" style="flex: 1; background: #6c757d; color: white; border: none; padding: 0.75rem; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div style="overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #667eea; color: white;">
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">ID</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Nombre</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Email</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Rol</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Acciones</th>
                </tr>
            </thead>
            <tbody id="usuariosTabla">
                <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; color: #999;">No hay usuarios registrados</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($usuario['id']); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td style="padding: 1rem;">
                            <span style="
                                padding: 0.35rem 0.75rem;
                                border-radius: 4px;
                                font-size: 0.85rem;
                                font-weight: 600;
                                background: <?php echo $usuario['rol'] === 'admin' ? '#dc3545' : '#28a745'; ?>;
                                color: white;
                            ">
                                <?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?>
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <button class="btn btn-sm btn-warning" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts para manejar usuarios -->
<script>
    // Cargar usuarios al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        cargarUsuarios();
    });

    function cargarUsuarios() {
        fetch('?page=usuarios&action=listar', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log("[v0] Usuarios cargados:", data);
            const tbody = document.getElementById('usuariosTabla');
            tbody.innerHTML = '';
            
            if (data.success && Array.isArray(data.usuarios)) {
                data.usuarios.forEach(usuario => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${usuario.id}</td>
                        <td>${usuario.nombre}</td>
                        <td>${usuario.email}</td>
                        <td><span class="badge badge-${usuario.rol}">${usuario.rol}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editarUsuario(${usuario.id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${usuario.id})">Eliminar</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: red;">Error al cargar los usuarios</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
            document.getElementById('usuariosTabla').innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: red;">Error al cargar los usuarios</td></tr>';
        });
    }

    function mostrarFormularioCrear() {
        document.getElementById('modalTitulo').textContent = 'Crear Usuario';
        document.getElementById('usuarioId').value = '';
        document.getElementById('formUsuario').reset();
        document.getElementById('modalUsuario').style.display = 'block';
    }

    function editarUsuario(id) {
        // Lógica para editar
        mostrarFormularioCrear();
    }

    function eliminarUsuario(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            window.location.href = `?page=usuarios&action=eliminar&id=${id}`;
        }
    }

    function cerrarModal() {
        document.getElementById('modalUsuario').style.display = 'none';
    }

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('modalUsuario');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Enviar formulario
    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const action = formData.get('id') ? 'editar' : 'crear';
        
        fetch(`?page=usuarios&action=${action}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cerrarModal();
                cargarUsuarios();
                alert('Operación realizada exitosamente');
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error en la operación');
        });
    });
</script>

<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    .table-container {
        overflow-x: auto;
        margin-top: 20px;
    }

    .badge-admin {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .badge-empleado {
        background-color: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-primary {
        background-color: #667eea;
        color: white;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-warning {
        background-color: #ffc107;
        color: black;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 0.85rem;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .table th {
        background-color: #667eea;
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }

    .table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }

    .table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
