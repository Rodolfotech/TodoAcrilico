# SPEC.md - Especificación del Formulario de Contacto

## Reglas Críticas

### 1. El Formulario NUNCA Debe Limpiarse en Caso de Error ⚠️

**Regla:** Si el usuario presiona "Enviar Mensaje" y ocurre CUALQUIER error (validación, archivo, rate limit, envío), el formulario debe mantener TODOS los datos que el usuario ingresó.

**Errores considerados:**
- Validación en frontend (campo vacío, correo inválido, teléfono incorrecto, etc.)
- Validación en backend
- Archivos rechazados (archivo muy grande, formato no permitido, demasiados archivos)
- Rate limiting (máximo 1 envío por IP cada 60 segundos)
- Error al enviar email
- Erro de CSRF token

**Comportamiento esperado:**
```
Usuario llena formulario con datos inválidos
         ↓
Usuario presiona "Enviar Mensaje"
         ↓
ERROR: Campo de correo inválido (ej: gmial.com)
         ↓
✅ Formulario SIGUE MOSTRANDO todos los datos que escribió
✅ El campo correo muestra borde ROJO + mensaje de error debajo
✅ El usuario puede corregir el error sin perder los datos
```

**Comportamiento INCORRECTO** (PROHIBIDO):
```
Usuario llena formulario
         ↓
Usuario presiona "Enviar Mensaje"
         ↓
ERROR: validación fallida
         ↓
❌ Formulario se limpia / se pierden todos los datos
❌ El usuario debe reescribir todo
```

**Excepción:** El formulario SÍ se limpia solo cuando el envío es EXITOSO (no hay error).

---

### 2. Los Datos se Conservan al Actualizar la Página

**Regla:** Si el usuario actualiza el navegador (F5) con datos escritos, el formulario debe mostrar los mismos datos (nombre, teléfono, correo, descripción y checkboxes).

- Se guardan en `sessionStorage` (solo esa pestaña; se borran al cerrarla).
- Se borran al enviar el mensaje con éxito.
- Excepción: los archivos adjuntos no se pueden restaurar (restricción del navegador).

---

## Implementación Técnica

### Frontend (JavaScript)
- Usar **AJAX (fetch)** en lugar de POST tradicional
- En caso de error:
  - NO recargar página
  - Mostrar mensaje de error debajo del campo problemático (o en modal para rate limit)
  - MANTENER todos los valores del formulario
- En caso de éxito:
  - Mostrar mensaje "¡Gracias! Recibimos tu mensaje..."
  - LIMPIAR el formulario
  - Opcionalmente redirigir a página de éxito

### Backend (PHP)
- Validar todos los campos
- Si hay error, responder con JSON: `{ "success": false, "reason": "archivo_grande", "message": "..." }`
- Si es exitoso, responder con JSON: `{ "success": true, "message": "..." }`
- NO redirigir en caso de error (dejar que JS maneje la respuesta)

---

## Estados del Formulario

| Estado | Descripción | Acción |
|--------|-------------|--------|
| **Inicial** | Formulario vacío, listo para completar | - |
| **Completando** | Usuario escribiendo datos | Validar en tiempo real |
| **Validando** | User hace submit, se validan campos | Mostrar errores si los hay |
| **Error** | Hay error de validación/envío | Mostrar error + MANTENER datos |
| **Enviando** | Email siendo enviado al servidor | Deshabilitar botón, mostrar spinner |
| **Éxito** | Email enviado correctamente | Limpiar formulario + Mostrar mensaje éxito |

---

## Mensajes de Error

Los mensajes de error deben aparecer:
- **Debajo del campo problemático** (si es validación de campo específico)
- **En modal emergente** (si es rate limit de IP)
- Con **borde ROJO** en el campo
- Con ícono visual (⚠️ o 🔴)

Ejemplo:
```
┌─────────────────────────────────┐
│ Correo Electrónico              │
│ ┌─────────────────────────────┐ │
│ │ juan@gmial.com              │ │ ← Borde ROJO
│ └─────────────────────────────┘ │
│ ⚠️ Usa un correo permitido:     │ ← Error debajo
│    gmail.com, outlook.com, ...  │
└─────────────────────────────────┘
```

---

## Rate Limiting (60 segundos)

- **Cuando se activa:** Después de un envío EXITOSO (no en intentos fallidos)
- **Duración:** 60 segundos por IP
- **UI:** Modal con countdown visual
- **Comportamiento:**
  - Botón "Enviar Mensaje" deshabilitado
  - Modal muestra: "Espera un momento ⏱️ 60"
  - Al llegar a 0, se cierra modal y se habilita botón
  - Modal NO reaparece al actualizar página (URL se limpia)

---

## Validación de Campos

### Nombre Completo
- ✅ **Nombre y apellido:** mínimo 2 palabras, cada una de 2+ letras (máx. 100 caracteres)
- ✅ Solo letras (con tildes y ñ), espacios, guion y apóstrofe (ej: "PÉREZ-SOTO", "O'BRIEN")
- ❌ "AA", "JUAN", "JUAN P" → error "Ingresa al menos un nombre y un apellido." (visible solo al presionar Enviar)
- ❌ NO números
- ❌ NO caracteres especiales

### Teléfono
- ✅ Formato chileno: `+56 9 XXXX XXXX` o `+56 9 8765 4321`
- ✅ Exactamente 8 dígitos después de `+56 9`
- ✅ Deben comenzar con 4, 5, 6, 7, 8 o 9 (móviles vigentes en Chile)
- ❌ Comienzan con 0, 1, 2 o 3, o no son 8 dígitos → "Ingrese un teléfono válido de 8 dígitos." (visible solo al presionar Enviar)

### Correo Electrónico
- ✅ Dominios permitidos: `gmail.com`, `outlook.com`, `hotmail.com`, `live.cl`, `yahoo.com`, `icloud.com`
- ✅ Cualquier dominio `.org`
- ✅ Cualquier dominio `.cl`
- ❌ Otro dominio rechazado
- ❌ NO caracteres especiales al inicio

### Descripción
- ✅ **20-2000 caracteres**
- ✅ Cualquier texto

### Archivos (opcional)
- ✅ Máximo 5 archivos
- ✅ Máximo 5 MB por archivo
- ✅ Formatos: JPG, PNG, GIF, WEBP, PDF
- ❌ Otros formatos rechazados
- ❌ Archivos > 5 MB rechazados

### Términos y Condiciones
- ✅ OBLIGATORIO aceptar (checkbox marcado)
- ❌ NO enviar si no está marcado

---

## Honeypot (Anti-spam)

Campo oculto `ta_contact_website` debe estar:
- ✅ Vacío antes del submit
- Si tiene contenido → ❌ Rechazar silenciosamente (bot)

---

## CSRF Token (Seguridad)

- WordPress `nonce` field: `ta_contact_nonce`
- **Validar en backend antes de procesar**
- Si es inválido: ❌ Rechazar con mensaje "Tu sesión expiró"

---

## Email (Envío)

### Configuración Requerida

Para que `wp_mail()` funcione correctamente en producción, se necesita:

- **Plugin SMTP:** WP Mail SMTP, Mailgun, SendGrid, etc.
- **O configuración de servidor:** Postfix, Sendmail, etc.
- **Email válido:** El email del destinatario debe ser válido

### Email del Destinatario

Actualmente configurado en `functions.php`:
```php
function ta_get_contact_email() {
    return apply_filters( 'ta_contact_email', 'rodolfo.parada.gonzalez@gmail.com' );
}
```

**Cambiar a:** El email corporativo de Todo Acrílico (ej: `contacto@todoacrilico.cl`)

### Debugging

Si `wp_mail()` falla, se registra en:
- `wp-content/debug.log` (si `WP_DEBUG` está habilitado)
- Logs del sistema (via `error_log()`)

Para ver errores, habilitar en `wp-config.php`:
```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```
