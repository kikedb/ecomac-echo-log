# 📦 CHANGELOG

Todas las actualizaciones importantes del paquete EchoLog se documentan en este archivo.

## [0.2.0] - 2025-05-16
### Agregado
- Soporte para múltiples canales de notificación (Discord y Correo).
- Formato HTML básico para correos electrónicos.
- Notificaciones de Discord con menciones, colores y detalles enriquecidos.
- Clasificación automática de errores por tipo:
  - 📧 Mail
  - 🛢️ DB
  - 🔐 Auth
  - 📁 FS
  - 🧠 Cache
  - 🌐 Network
  - ❗ Unknown

### Cambios
- Se reorganizó la estructura del paquete para facilitar su integración y configuración.
- Mejora en la detección de errores y trazas.
- Documentación más clara y extensa.

## [0.2.0] - Versión inicial
- Reporte básico de errores a un canal de Discord mediante Webhook y correo electrónico.
