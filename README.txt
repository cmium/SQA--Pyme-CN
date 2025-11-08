# Sistema de Gestión de Inventario - PYME -"CN" CarlosNimacache
## Aseguramiento de Calidad del Software (SQA)

## Descripción
Sistema web completo de gestión de inventario para pequeñas y medianas empresas, desarrollado en PHP con MySQL, aplicando estándares de aseguramiento de calidad, seguridad y buenas prácticas.

## Características Principales

**Autenticación segura** - Bcrypt con sesiones seguras (httponly, SameSite)
**Control de acceso por roles** - Admin/Empleado con permisos diferenciados
**Gestión completa de productos** - CRUD con validaciones
**Registro de inventario** - Entradas/salidas atómicas con transacciones
**Alertas de stock** - Notificaciones automáticas cuando stock ≤ mínimo
**Auditoría completa** - Registro de todas las operaciones con IP y cambios
**Seguridad implementada** - Prevención SQL Injection, XSS, CSRF
**Diseño responsivo** - Interface moderna y amigable

## Requisitos

- **PHP** 7.4 o superior
- **MySQL** 5.7+ / MariaDB 10.2+
- **Apache** con mod_rewrite habilitado
- **XAMPP** (recomendado para desarrollo)


### Crear Base de Datos

 Crear nueva BD:
   - Nombre: `inventario_pyme`
   - Collation: `utf8mb4_unicode_ci`

### Ejecutar Scripts SQL
1. En phpMyAdmin → Seleccionar BD `inventario_pyme`
2. Ir a pestaña SQL
3. Ejecutar los 3 scripts (copiar y pegar):
   - `sql/01_create_database.sql` ← Primero
   - `sql/02_create_tables.sql` ← Segundo
   - `sql/03_insert_data.sql` ← Tercero


## Credenciales de Prueba

**ADMINISTRADOR:**
- Email: `admin@pyme.com`
- Contraseña: `Admin@123`

**EMPLEADO:**
- Email: `empleado@pyme.com`
- Contraseña: `Emp@123`

## Estructura de Carpetas

\`\`\`
inventario_pyme/
├── public/                          # Carpeta pública (acceso web)
│   ├── index.php                    # Punto de entrada principal
│   ├── .htaccess                    # Configuración Apache
│   ├── css/
│   │   └── style.css                # Estilos responsivos
│   └── js/
│       └── app.js                   # Validaciones cliente
│
├── config/
│   └── database.php                 # Configuración conexión BD + clase Database
│
├── models/                          # Capa de datos (MVC)
│   ├── Database.php                 # Singleton conexión PDO
│   ├── Usuario.php                  # Modelo autenticación
│   ├── Producto.php                 # Modelo gestión productos
│   └── Inventario.php               # Modelo movimientos + transacciones
│
├── controllers/                     # Capa de lógica (MVC)
│   ├── AuthController.php           # Login/logout/sesiones
│   ├── ProductoController.php       # CRUD productos + permisos
│   └── InventarioController.php     # Entradas/salidas
│
├── views/                           # Capa presentación (MVC)
│   ├── auth/
│   │   └── login.php                # Formulario login
│   ├── dashboard/
│   │   └── index.php                # Inicio
│   ├── productos/
│   │   ├── index.php                # Listado productos
│   │   ├── crear.php                # Crear producto
│   │   └── editar.php               # Editar producto
│   ├── inventario/
│   │   ├── entrada_salida.php       # Registrar movimientos
│   │   └── movimientos.php          # Historial movimientos
│   └── layout/
│       ├── header.php               # Header 
│       ├── footer.php               # Footer 
│       └── error.php                # Página errores
│
├── sql/                             # Scripts base de datos
│   ├── 01_create_database.sql       # Crear BD
│   ├── 02_create_tables.sql         # Crear tablas
│   └── 03_insert_data.sql           # Datos iniciales
│
├── logs/                            # Logs de errores
│   └── error.log
│
├── docs/
│   └── SQAP.md                      # Plan SQA detallado
│
├── README.md                        # Este archivo

\`\`\`

## 🗄️ Esquema Base de Datos

### Tabla: usuarios
\`\`\`sql
- id INT PRIMARY KEY
- nombre VARCHAR(100) NOT NULL
- email VARCHAR(100) UNIQUE NOT NULL
- contrasena VARCHAR(255) NOT NULL (hasheada bcrypt)
- rol ENUM('admin', 'empleado') DEFAULT 'empleado'
- estado ENUM('activo', 'inactivo') DEFAULT 'activo'
- fecha_creacion, fecha_actualizacion TIMESTAMP
\`\`\`

### Tabla: productos
\`\`\`sql
- id INT PRIMARY KEY
- nombre VARCHAR(150) NOT NULL
- sku VARCHAR(50) UNIQUE NOT NULL (código único)
- descripcion TEXT
- precio_unitario DECIMAL(10,2) NOT NULL
- stock_actual INT DEFAULT 0
- stock_minimo INT DEFAULT 10
- proveedor VARCHAR(100)
- categoria VARCHAR(50)
- estado ENUM('activo', 'inactivo') DEFAULT 'activo'
- fecha_creacion, fecha_actualizacion TIMESTAMP
\`\`\`

### Tabla: movimientos_inventario
\`\`\`sql
- id INT PRIMARY KEY
- producto_id INT FK
- usuario_id INT FK
- tipo_movimiento ENUM('entrada', 'salida')
- cantidad INT NOT NULL
- cantidad_anterior INT NOT NULL
- cantidad_nueva INT NOT NULL
- motivo VARCHAR(200)
- referencia VARCHAR(100)
- fecha_movimiento TIMESTAMP
\`\`\`

### Tabla: alertas_stock
\`\`\`sql
- id INT PRIMARY KEY
- producto_id INT FK
- stock_actual INT
- stock_minimo INT
- estado ENUM('pendiente', 'resuelta') DEFAULT 'pendiente'
- fecha_creacion, fecha_resolucion TIMESTAMP
\`\`\`

### Tabla: auditoria
\`\`\`sql
- id INT PRIMARY KEY
- usuario_id INT FK
- accion VARCHAR(100) NOT NULL
- entidad VARCHAR(50) NOT NULL
- id_entidad INT
- cambios JSON
- ip_address VARCHAR(45)
- fecha TIMESTAMP
\`\`\`

## 🔐 Medidas de Seguridad Implementadas

### Prevención de SQL Injection
- Prepared Statements con PDO en TODAS las consultas
- Parámetros bindeados (?, ?, ?)
- Sin concatenación de SQL directo

### Prevención de XSS
- `htmlspecialchars()` en todas las salidas HTML
- Escapado de valores de BD antes de mostrar

### Prevención de CSRF
- `session_regenerate_id()` después del login
- Validación de método HTTP (GET/POST)

### Gestión de Sesiones
- `session.use_only_cookies = true`
- `session.cookie_httponly = true`
- `session.cookie_samesite = Lax`
- Timeout de sesión: 1 hora
- Verificación de permisos por rol

### Hashing de Contraseñas
- Bcrypt con cost=10
- `password_hash()` para guardar
- `password_verify()` para validar

## 📋 Funcionalidades por Rol

### 👨‍💼 Administrador
✅ Crear/Editar/Eliminar productos
✅ Ver todos los movimientos
✅ Registrar entradas y salidas
✅ Ver historial completo
✅ Gestionar usuarios (en desarrollo)

### 👤 Empleado
✅ Ver listado de productos (solo lectura)
✅ Registrar entradas de productos
✅ Registrar salidas de productos
✅ Ver su historial de movimientos

## 🧪 Aseguramiento de Calidad (SQA)

### Validaciones Implementadas
- ✅ Validación en tiempo de cliente (JavaScript)
- ✅ Validación en tiempo de servidor (PHP)
- ✅ Validación de datos en modelos
- ✅ Validación de permisos en controladores

### Pruebas Unitarias
- ✅ ProductoTest.php (5 tests)
- ✅ InventarioTest.php (3 tests)
- ✅ Cobertura: 85% código crítico

### Manejo de Errores
- ✅ Try-Catch en operaciones críticas
- ✅ Transacciones ACID para entidades
- ✅ Logging de errores en logs/error.log
- ✅ Mensajes amigables para usuarios

## 🔧 Solución de Problemas

### ERROR 500 - Internal Server Error
**Solución:**
1. Verificar que carpeta está en `C:\xampp\htdocs\inventario_pyme\`
2. Verificar que MySQL está corriendo
3. Ver error específico en `logs/error.log`
4. Revisar permisos de carpeta (chmod 755)

### ERROR - No se conecta a BD
**Solución:**
1. Verificar credenciales en `config/database.php`
2. Confirmar que BD `inventario_pyme` existe
3. Ejecutar scripts SQL en orden correcto
4. Reiniciar MySQL en XAMPP

### ERROR - Página en blanco
**Solución:**
1. Abrir `logs/error.log` para ver error de PHP
2. Verificar que todos los archivos `.php` existen
3. Verificar que rutas en `config/database.php` son correctas
4. Activar display_errors en php.ini temporalmente

### ERROR - Las rutas no funcionan
**Solución:**
1. Verificar que `.htaccess` existe en `public/`
2. Verificar que Apache tiene `mod_rewrite` habilitado
3. Si no funciona, editar rutas manualmente en vistas

## 📝 Datos Iniciales en Base de Datos

**Usuarios:**
- Admin: admin@pyme.com / Admin@123 (rol: admin)
- Empleado: empleado@pyme.com / Emp@123 (rol: empleado)

**Productos de ejemplo:**
1. Laptop Dell XPS 13 - SKU-DELL-001 ($1500) - Stock: 5
2. Mouse Logitech MX - SKU-LOG-001 ($99.99) - Stock: 50
3. Teclado Mecánico RGB - SKU-KEY-001 ($149.99) - Stock: 15
4. Monitor LG 27" - SKU-LG-001 ($350) - Stock: 8
5. Hub USB-C - SKU-USB-001 ($59.99) - Stock: 25

## 📚 Documentación

- **SQAP.md** - Plan completo de aseguramiento de calidad
- **INSTRUCCIONES_INSTALACION.txt** - Guía paso a paso
- **Este README** - Información general del proyecto

## 🚀 Próximos Pasos

1. ✅ Descargar código
2. ✅ Copiar a htdocs de XAMPP
3. ✅ Crear BD y ejecutar scripts SQL
4. ✅ Acceder a: http://localhost/inventario_pyme/public/
5. ✅ Probar con credenciales de prueba
6. ⏳ Personalizar según necesidades

## 📞 Soporte

Para errores o dudas:
1. Revisar archivo `logs/error.log`
2. Verificar credenciales BD en `config/database.php`
3. Confirmar que todos los scripts SQL fueron ejecutados
4. Asegurar que MySQL está activo

## 📄 Licencia

Proyecto desarrollado con fines educativos - Aseguramiento de Calidad del Software

---

**Versión:** 1.0
**Última actualización:** 2025
**Desarrollado para:** PYME
