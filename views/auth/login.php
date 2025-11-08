<?php
session_start();

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'procesar_login') {
    header('Content-Type: application/json');
    
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['contrasena'] ?? '');
    
    // Validar datos
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email y contraseña son requeridos.'
        ]);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'El formato del email no es válido.'
        ]);
        exit();
    }
    
    // Usuarios válidos
    $validUsers = [
        'admin@pyme.com' => ['password' => 'Admin@123', 'role' => 'admin'],
        'empleado@pyme.com' => ['password' => 'Emp@123', 'role' => 'empleado']
    ];
    
    // Validar credenciales
    if (isset($validUsers[$email]) && $validUsers[$email]['password'] === $password) {
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $validUsers[$email]['role'];
        $_SESSION['login_time'] = time();
        
        echo json_encode([
            'success' => true,
            'redirect' => 'dashboard.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Las credenciales proporcionadas son incorrectas. Verifica tu email y contraseña.'
        ]);
    }
    exit();
}
?>

<?php require VIEWS_PATH . '/layout/header.php'; ?>

<div class="login-wrapper">
    <div class="login-container">
        <!-- Lado izquierdo: Información -->
        <div class="login-info-section">
            <div class="login-info-content">
                <div class="login-icon">📦</div>
                <h1>Sistema de Gestión de Inventario</h1>
                <p>Administra tu inventario de forma eficiente y segura</p>
                
                <div class="features-list">
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Control total de inventario</span>
                    </div>
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Alertas de stock mínimo</span>
                    </div>
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Auditoría completa</span>
                    </div>
                    <div class="feature-item">
                        <span class="check-icon">✓</span>
                        <span>Acceso seguro por roles</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lado derecho: Formulario -->
        <div class="login-form-section">
            <div class="login-form-content">
                <h2>Iniciar Sesión</h2>
                <p class="subtitle">Bienvenido de vuelta</p>
                
                <!-- Formulario sin mostrar errores en encabezado, solo envía a procesar_login -->
                <form method="POST" action="?page=login&action=procesar_login" id="loginForm" novalidate>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                            placeholder="tu@email.com"
                            class="form-input"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input 
                            type="password" 
                            id="contrasena" 
                            name="contrasena" 
                            required
                            placeholder="••••••••"
                            class="form-input"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="submitBtn">
                        <span class="btn-text">Iniciar Sesión</span>
                        <span class="spinner" id="spinner" style="display: none;"></span>
                    </button>
                </form>
                
                <div class="credentials-info">
                    <p class="credentials-title">Credenciales de Prueba</p>
                    <div class="credential-box">
                        <strong>Admin:</strong> admin@pyme.com / Admin@123
                    </div>
                    <div class="credential-box">
                        <strong>Empleado:</strong> empleado@pyme.com / Emp@123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal flotante para errores de login -->
<div class="error-modal-overlay" id="errorModal" role="alertdialog" aria-modal="true" aria-hidden="true">
    <div class="error-modal-content">
        <div class="error-modal-header">
            <h3 class="error-modal-title">
                <span class="error-icon">⚠️</span>
                Error de Autenticación
            </h3>
            <button type="button" class="error-modal-close" id="closeModal" aria-label="Cerrar">×</button>
        </div>
        <div class="error-modal-body">
            <p id="errorMessage"></p>
        </div>
        <div class="error-modal-footer">
            <button type="button" class="btn-accept" id="acceptBtn">Aceptar</button>
        </div>
    </div>
</div>

<?php require VIEWS_PATH . '/layout/footer.php'; ?>

<style>
    /* Modal Flotante */
    .error-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .error-modal-overlay.active {
        display: flex;
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .error-modal-content {
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 420px;
        width: 90%;
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .error-modal-header {
        background-color: #fee2e2;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #fecaca;
        border-radius: 8px 8px 0 0;
    }

    .error-modal-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #991b1b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .error-icon {
        font-size: 20px;
    }

    .error-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        color: #991b1b;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .error-modal-close:hover {
        background-color: rgba(153, 27, 27, 0.1);
    }

    .error-modal-body {
        padding: 20px;
        color: #374151;
        font-size: 14px;
        line-height: 1.6;
    }

    .error-modal-footer {
        padding: 12px 20px 20px;
        display: flex;
        justify-content: flex-end;
    }

    .btn-accept {
        background-color: #dc2626;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .btn-accept:hover {
        background-color: #991b1b;
    }

    .btn-accept:focus {
        outline: 2px solid #991b1b;
        outline-offset: 2px;
    }

    /* Spinner en botón */
    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-left: 8px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .btn-login:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .error-modal-content {
            width: 95%;
        }

        .error-modal-title {
            font-size: 15px;
        }

        .error-modal-body {
            font-size: 13px;
        }
    }
</style>

<script>
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const spinner = document.getElementById('spinner');
    const btnText = document.querySelector('.btn-text');
    const errorModal = document.getElementById('errorModal');
    const errorMessage = document.getElementById('errorMessage');
    const closeModal = document.getElementById('closeModal');
    const acceptBtn = document.getElementById('acceptBtn');

    function showError(message) {
        errorMessage.textContent = message;
        errorModal.classList.add('active');
        errorModal.setAttribute('aria-hidden', 'false');
        closeModal.focus();
    }

    function hideError() {
        errorModal.classList.remove('active');
        errorModal.setAttribute('aria-hidden', 'true');
        submitBtn.focus();
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && errorModal.classList.contains('active')) {
            hideError();
        }
    });

    errorModal.addEventListener('click', (e) => {
        if (e.target === errorModal) {
            hideError();
        }
    });

    closeModal.addEventListener('click', hideError);
    acceptBtn.addEventListener('click', hideError);

    // Enviar formulario con AJAX
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validación básica en cliente
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('contrasena').value.trim();

        if (!email || !password) {
            showError('Por favor completa todos los campos.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('Ingresa un email válido.');
            return;
        }

        // Mostrar spinner
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';

        try {
            const formData = new FormData(loginForm);
            const response = await fetch('?page=login&action=procesar_login', {
                method: 'POST',
                body: formData
            });

            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (data.success) {
                    window.location.href = data.redirect || 'dashboard.php';
                } else {
                    showError(data.message || 'Credenciales incorrectas.');
                }
            } else {
                // Si la respuesta no es JSON, asumimos que fue redirigido exitosamente
                const html = await response.text();
                if (response.ok && !html.includes('class="toast')) {
                    window.location.href = 'dashboard.php';
                } else if (html.includes('error') || html.includes('incorrecto')) {
                    showError('Las credenciales proporcionadas son incorrectas.');
                } else {
                    window.location.href = 'dashboard.php';
                }
            }
        } catch (error) {
            showError('No se pudo procesar tu solicitud. Intenta más tarde.');
        } finally {
            // Ocultar spinner
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            spinner.style.display = 'none';
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        showError(decodeURIComponent(urlParams.get('error')));
    }
</script>
