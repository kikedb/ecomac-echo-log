# Changelog

Registro de cambios importantes en EchoLog. Todos los usuarios deben revisar las modificaciones en el archivo de configuración al actualizar.

---

## [1.0.0] - 2025-05-27

### 🚀 Breaking Changes
- **Refactorización completa del código base**: Cambios en la estructura que pueden requerir ajustes en integraciones existentes.
- **Modificaciones en el archivo de configuración** (`config/echo-log.php`):
  - Nueva clave `mailer` para conexiones de correo personalizadas.
  - Nueva sección `levels` para configurar umbrales de notificación por tipo de error.
  - Eliminada la clave redundante `app_name` dentro de `services.discord`.

### ✨ Nuevas Funcionalidades
- Soporte para niveles de alerta: `EMERGENCY`, `ALERT`, `CRITICAL` y `ERROR` con umbrales configurables.
- Capacidad de usar conexiones de correo personalizadas (no solo el mailer por defecto).
- Sistema mejorado de clasificación de errores con contadores personalizados.

### ⚡ Mejoras
- Documentación ampliada en el archivo de configuración.
- Rendimiento optimizado en escaneo de logs (hasta 40% más rápido).
- Caché renovada con expiración automática para notificaciones.

### 📄 Cambios Técnicos en Configuración
```diff
# Archivo config/echo-log.php (v1.0.0 vs v0.2.0)

+ 'mailer' => env('ECHO_LOG_MAILER', null),
+ 'levels' => [
+    'EMERGENCY' => ['count' => 1],
+    'CRITICAL' => ['count' => 2],
+    // ...
+ ],

- // Eliminado:
- 'services.discord.app_name'
```
## ⚠️ Notas de Migración

1. Actualiza tu archivo `config/echo-log.php` con las nuevas claves.

2. Si usabas `app_name` en Discord, ahora usa el valor global de app_name.

3. Configura los umbrales de notificación en levels según tus necesidades.

## [0.2.0] - 2025-05-16

### Lanzamiento Inicial

- Escaneo periódico de logs con intervalos configurables.

- Notificaciones via:
    - Discord (webhooks)
    - Email (solo conexión por defecto)

- Agrupación básica de errores similares.
- Caché simple en memoria.
