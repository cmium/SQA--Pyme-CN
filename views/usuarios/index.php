<?php 
// Verificar autenticación y permisos
if (!isset($_SESSION['user_email']) && !isset($_SESSION['usuario_id'])) {
    die('Debes iniciar sesión para acceder a esta página');
}

// Verificar rol de admin (compatibilidad con ambos sistemas de sesión)
$userRole = $_SESSION['user_role'] ?? $_SESSION['usuario_rol'] ?? 'empleado';
$userEmail = $_SESSION['user_email'] ?? $_SESSION['usuario_email'] ?? '';

if ($userRole !== 'admin') {
    die('Sin permisos para acceder a esta página. Tu rol es: ' . $userRole);
}

require VIEWS_PATH . '/layout/header.php'; 
?>

<div style="padding: 2rem; max-width: 1400px; margin: 0 auto;">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0; color: #667eea; font-size: 1.75rem;">Gestión de Usuarios</h2>
        <button id="btnNuevoUsuario" onclick="abrirModalCrear()" style="
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.3s;
        " onmouseover="this.style.background='#5568d3'" onmouseout="this.style.background='#667eea'">
            + Nuevo Usuario
        </button>
    </div>

    <!-- Mensajes de éxito/error -->
    <div id="mensajeContainer" style="display: none; margin-bottom: 1rem;">
        <div id="mensaje" style="padding: 1rem; border-radius: 6px; font-weight: 500;"></div>
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
                    <th style="padding: 1rem; text-align: left; font-weight: 600;">Estado</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600;">Acciones</th>
                </tr>
            </thead>
            <tbody id="usuariosTabla">
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: #999;">
                        <div style="display: inline-block; padding: 1rem;">Cargando usuarios...</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para crear/editar usuario -->
<div id="modalUsuario" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); overflow: auto;">
    <div style="background: white; margin: 5% auto; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 id="modalTitulo" style="margin: 0; color: #667eea; font-size: 1.5rem;">Crear Usuario</h3>
            <span onclick="cerrarModal()" style="float: right; font-size: 28px; cursor: pointer; color: #aaa; font-weight: bold; line-height: 1;">&times;</span>
        </div>
        
        <form id="formUsuario" onsubmit="guardarUsuario(event)">
            <input type="hidden" id="usuarioId" name="id" value="">
            <input type="hidden" name="accion" id="formAccion" value="crear">
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Nombre:</label>
                <input type="text" id="usuarioNombre" name="nombre" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Email:</label>
                <input type="email" id="usuarioEmail" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 1rem;" id="passwordContainer">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Contraseña:</label>
                <input type="password" id="usuarioPassword" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.95rem; box-sizing: border-box;">
                <small style="color: #666; font-size: 0.85rem;">Dejar en blanco para mantener la contraseña actual (solo al editar)</small>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Rol:</label>
                <select id="usuarioRol" name="rol" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.95rem; box-sizing: border-box;">
                    <option value="empleado">Empleado</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" id="btnGuardar" style="flex: 1; background: #667eea; color: white; border: none; padding: 0.75rem; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.95rem;">Guardar</button>
                <button type="button" onclick="cerrarModal()" style="flex: 1; background: #6c757d; color: white; border: none; padding: 0.75rem; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.95rem;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
let modoEdicion = false;

    // Cargar usuarios al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        cargarUsuarios();
    });

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'success') {
    const container = document.getElementById('mensajeContainer');
    const mensajeDiv = document.getElementById('mensaje');
    
    container.style.display = 'block';
    mensajeDiv.textContent = mensaje;
    mensajeDiv.style.background = tipo === 'success' ? '#d4edda' : '#f8d7da';
    mensajeDiv.style.color = tipo === 'success' ? '#155724' : '#721c24';
    mensajeDiv.style.border = `1px solid ${tipo === 'success' ? '#c3e6cb' : '#f5c6cb'}`;
    
    // Ocultar después de 5 segundos
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}

// Cargar lista de usuarios
    function cargarUsuarios() {
        fetch('?page=usuarios&action=listar', {
            method: 'GET',
            headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('usuariosTabla');
            tbody.innerHTML = '';
            
        if (data.success && Array.isArray(data.data)) {
            if (data.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="padding: 2rem; text-align: center; color: #999;">No hay usuarios registrados</td></tr>';
            } else {
                data.data.forEach(usuario => {
                    const row = document.createElement('tr');
                    row.style.borderBottom = '1px solid #eee';
                    row.innerHTML = `
                        <td style="padding: 1rem;">${usuario.id}</td>
                        <td style="padding: 1rem;">${escapeHtml(usuario.nombre)}</td>
                        <td style="padding: 1rem;">${escapeHtml(usuario.email)}</td>
                        <td style="padding: 1rem;">
                            <span style="
                                padding: 0.35rem 0.75rem;
                                border-radius: 4px;
                                font-size: 0.85rem;
                                font-weight: 600;
                                background: ${usuario.rol === 'admin' ? '#dc3545' : '#28a745'};
                                color: white;
                            ">
                                ${usuario.rol === 'admin' ? 'Admin' : 'Empleado'}
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="
                                padding: 0.35rem 0.75rem;
                                border-radius: 4px;
                                font-size: 0.85rem;
                                font-weight: 600;
                                background: ${(usuario.estado === 'activo' || !usuario.estado) ? '#28a745' : '#6c757d'};
                                color: white;
                            ">
                                ${(usuario.estado === 'activo' || !usuario.estado) ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <button onclick="editarUsuario(${usuario.id})" style="
                                background: #ffc107;
                                color: black;
                                border: none;
                                padding: 0.5rem 1rem;
                                border-radius: 4px;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.85rem;
                                margin-right: 0.5rem;
                            ">Editar</button>
                            <button onclick="eliminarUsuario(${usuario.id})" style="
                                background: #dc3545;
                                color: white;
                                border: none;
                                padding: 0.5rem 1rem;
                                border-radius: 4px;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.85rem;
                            ">Eliminar</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: red;">Error al cargar los usuarios</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
        document.getElementById('usuariosTabla').innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: red;">Error al cargar los usuarios</td></tr>';
    });
}

// Abrir modal para crear
function abrirModalCrear() {
    modoEdicion = false;
    document.getElementById('modalTitulo').textContent = 'Crear Nuevo Usuario';
    document.getElementById('formAccion').value = 'crear';
        document.getElementById('usuarioId').value = '';
        document.getElementById('formUsuario').reset();
    document.getElementById('usuarioPassword').required = true;
    document.getElementById('passwordContainer').style.display = 'block';
        document.getElementById('modalUsuario').style.display = 'block';
    }

// Abrir modal para editar
    function editarUsuario(id) {
    modoEdicion = true;
    document.getElementById('modalTitulo').textContent = 'Editar Usuario';
    document.getElementById('formAccion').value = 'editar';
    document.getElementById('usuarioId').value = id;
    document.getElementById('usuarioPassword').required = false;
    document.getElementById('passwordContainer').style.display = 'block';
    
    // Cargar datos del usuario
    fetch(`?page=usuarios&action=obtener&id=${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const usuario = data.data;
            document.getElementById('usuarioNombre').value = usuario.nombre || '';
            document.getElementById('usuarioEmail').value = usuario.email || '';
            document.getElementById('usuarioRol').value = usuario.rol || 'empleado';
            document.getElementById('usuarioPassword').value = '';
            document.getElementById('modalUsuario').style.display = 'block';
        } else {
            mostrarMensaje(data.message || 'Error al cargar el usuario', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al cargar el usuario', 'error');
    });
}

// Guardar usuario (crear o editar)
function guardarUsuario(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formUsuario'));
    const accion = formData.get('accion');
    const password = formData.get('password');
    
    // Si es edición y no hay contraseña, no enviarla
    if (accion === 'editar' && !password) {
        formData.delete('password');
    }
    
    // Agregar header para AJAX
    const headers = {
        'X-Requested-With': 'XMLHttpRequest'
    };
    
    fetch('?page=usuarios', {
            method: 'POST',
        headers: headers,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            mostrarMensaje(data.message || 'Operación realizada exitosamente', 'success');
                cerrarModal();
                cargarUsuarios();
            } else {
            mostrarMensaje(data.message || 'Error en la operación', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        mostrarMensaje('Error en la operación', 'error');
    });
}

// Eliminar usuario
function eliminarUsuario(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        return;
    }
    
    fetch(`?page=usuarios&action=eliminar&id=${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarMensaje(data.message || 'Usuario eliminado exitosamente', 'success');
            cargarUsuarios();
        } else {
            mostrarMensaje(data.message || 'Error al eliminar usuario', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensaje('Error al eliminar usuario', 'error');
    });
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modalUsuario').style.display = 'none';
    document.getElementById('formUsuario').reset();
    modoEdicion = false;
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('modalUsuario');
    if (event.target === modal) {
        cerrarModal();
    }
}

// Función para escapar HTML (prevenir XSS)
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}
</script>

<style>
    /* Estilos adicionales para mejorar la apariencia */
    #usuariosTabla tr:hover {
        background-color: #f5f5f5;
    }
    
    #modalUsuario {
        animation: fadeIn 0.3s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    #formUsuario input:focus,
    #formUsuario select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    #btnGuardar:hover {
        background: #5568d3 !important;
    }
</style>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>
