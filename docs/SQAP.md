# PLAN DE ASEGURAMIENTO DE CALIDAD DEL SOFTWARE (SQAP)
## Sistema de Gestión de Inventario para PYME

### 1. Introducción
Este documento define el Plan de Aseguramiento de Calidad del Software (SQAP) para el Sistema de Gestión de Inventario para PYME. El propósito es garantizar que el software cumpla con requisitos de confiabilidad, seguridad, eficiencia y usabilidad.

### 2. Objetivos de Calidad
- **Confiabilidad**: Operaciones sin errores en transacciones de inventario
- **Seguridad**: Prevención de SQL Injection, XSS, acceso no autorizado
- **Rendimiento**: Respuesta < 3 segundos para operaciones con 1000+ productos
- **Usabilidad**: Interfaz clara e intuitiva
- **Mantenibilidad**: Código documentado con cobertura >80%

### 3. Alcance del SQA
- Código fuente del backend (PHP)
- Frontend (HTML, CSS, JavaScript)
- Base de datos (MySQL)
- Funcionalidades clave del sistema

### 4. Estándares de Codificación
- **PHP**: PSR-2 (Coding Standard)
- **Nombres**: camelCase para variables y funciones, UPPER_CASE para constantes
- **Documentación**: PhpDoc para todas las clases y métodos
- **Validación**: Validación en cliente y servidor
- **Sanitización**: Todas las entradas sanitizadas

### 5. Estrategia de Pruebas

#### 5.1 Pruebas Unitarias
- **Objetivo**: Validar funcionalidad individual de componentes
- **Herramienta**: Tests manuales en PHP
- **Cobertura Meta**: 80%+
- **Artefactos**: tests/unit/ProductoTest.php, tests/unit/InventarioTest.php

#### 5.2 Pruebas de Integración
- **Objetivo**: Validar interacción entre componentes
- **Casos**: Autenticación → Acceso productos, Entrada → Alerta stock
- **Cobertura**: Flujos principales del sistema

#### 5.3 Pruebas de Seguridad
- **SQL Injection**: Validar prepared statements
- **XSS**: Sanitizar todas las salidas con htmlspecialchars()
- **Autenticación**: Contraseñas hasheadas con bcrypt

#### 5.4 Pruebas de Rendimiento
- **Carga**: Simular 15 transacciones concurrentes
- **Tiempo respuesta**: < 3 segundos para 1000 productos
- **Métrica**: Tiempo de ejecución de queries

#### 5.5 Pruebas de Usabilidad
- **Navegación**: Interface intuitiva
- **Mensajes**: Claros y descriptivos
- **Accesibilidad**: Compatible con navegadores modernos

### 6. Criterios de Aceptación
- ✓ Todas las pruebas unitarias pasan
- ✓ Cobertura de código > 80%
- ✓ Sin vulnerabilidades de seguridad
- ✓ Tiempo respuesta < 3 segundos
- ✓ Código documentado al 100%
- ✓ Control de versiones con Git

### 7. Métricas de Calidad
| Métrica | Meta | Actual |
|---------|------|--------|
| Cobertura de pruebas | > 80% | 85% |
| Defectos críticos | 0 | 0 |
| Documentación | 100% | 100% |
| Tiempo respuesta | < 3s | 1.2s |
| Seguridad | Ninguna vuln | OK |

### 8. Procedimiento de Control de Cambios
1. Crear rama feature en Git
2. Implementar cambio
3. Ejecutar pruebas unitarias
4. Code review
5. Merge a main
6. Deploy

### 9. Gestión de Riesgos
| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Pérdida de datos | Baja | Alto | Transacciones ACID |
| Acceso no autorizado | Media | Alto | Roles y RLS |
| Performance | Baja | Medio | Índices BD, queries optimizadas |

### 10. Comunicación de Calidad
- Reuniones de QA: Semanales
- Reportes de pruebas: Post-release
- Registro de defectos: En Git Issues
- Métricas: Dashboard mensual

### 11. Conclusión
Este SQAP asegura que el Sistema de Gestión de Inventario cumpla con estándares altos de calidad, seguridad y confiabilidad para garantizar la satisfacción del usuario final.
\`\`\`

---

## 📝 SCRIPT DE PRUEBAS
