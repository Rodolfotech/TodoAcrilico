# SPEC.md — Especificación funcional (formulario de cotización y páginas legales)

> **Para una IA o desarrollador que modifique el sitio:** este archivo describe el comportamiento **aprobado por el cliente** al 2026-10-20 (tema 2.0.2, plugin 2.0.0). Cada regla fue pedida explícitamente. Si un cambio contradice algo de aquí, **pregunta antes**; no "mejores" una regla por tu cuenta.
>
> Código involucrado:
> - `wp-content/themes/todo-acrilico/page-contacto.php`: HTML del formulario, modales y todo el JavaScript.
> - `wp-content/themes/todo-acrilico/functions.php`, función `ta_handle_contact_submit()`: validación en servidor y envío.
> - `wp-content/themes/todo-acrilico/style.css`: estilos `.ta-contact-*` y `.ta-modal*`.
>
> Página: slug `cotizar` (título "Contacto"). Se ubica con `ta_find_page( array( 'cotizar', 'contacto', 'contact' ) )`.

---

## Reglas críticas (no romper)

### R1. El formulario NUNCA se limpia si hay un error

Si el usuario presiona **"Enviar mensaje"** y ocurre cualquier error, el formulario conserva **todo** lo escrito, incluidos los adjuntos. Esto vale para errores de validación del navegador o del servidor, archivos rechazados, límite de 60 segundos, nonce vencido y fallo de `wp_mail()`.

- Solo se limpia cuando el envío es **exitoso** (`form.reset()`).
- Por eso el envío es **AJAX** (`fetch` con la cabecera `X-Requested-With: XMLHttpRequest`) y no un POST que recarga la página.

### R2. Los datos sobreviven a una actualización de página (F5)

- Se guardan en `sessionStorage`, clave **`ta_contact_draft`**: `nombre`, `celular`, `email`, `descripcion`, `acepto_terminos` y `acepta_ofertas`.
- Se guardan en cada evento `input` o `change` y se restauran al cargar la página.
- Se borran al enviar con éxito o al cerrar la pestaña.
- Los **adjuntos no se pueden restaurar**: el navegador no lo permite. Es una limitación conocida y aceptada.
- Esto está declarado en la Política de Privacidad, sección 8. Si cambias la clave o el mecanismo, **actualiza esa tabla**.

### R3. Los errores aparecen solo al presionar "Enviar mensaje"

- Mientras el usuario escribe, ningún campo muestra error. Si el usuario edita un campo que tenía error, el error se oculta. Solo el teléfono vuelve a verde, y únicamente cuando queda válido.
- El `<form>` tiene **`novalidate`**: los avisos nativos del navegador no aparecen y cada error se muestra **debajo de su campo**, en rojo y con borde rojo.
- **Única excepción:** el checkbox de términos usa el aviso nativo (`reportValidity()`) cuando no hay otros errores.
- Los errores que devuelve el **servidor** por AJAX se muestran en la alerta roja superior (`.ta-contact-alert--error`), no bajo el campo.

### R4. El frontend y el servidor validan lo mismo

Cada regla de la tabla de abajo existe **dos veces**: en JavaScript (`validateField()` en `page-contacto.php`) y en PHP (`ta_handle_contact_submit()`). **Si cambias una, cambia la otra.** El servidor es la barrera real, porque el JS se puede saltar.

---

## Diseño de la página

- **Escritorio (más de 860px):** dos columnas. Arriba a la izquierda van el eyebrow "Contacto", el título y la línea. Debajo van la descripción y los datos de contacto. La **tarjeta del formulario empieza exactamente a la altura de la descripción** (borde superior alineado con su primera línea), sin importar en cuántas líneas se parta el título.
- **Móvil (860px o menos):** una columna. La tarjeta va debajo de los datos de contacto.
- El contenido del formulario (campos, textos y orden) no cambia con esta regla.

---

## Campos y validación

| Campo | Regla (JS y PHP) | Mensaje bajo el campo | Comportamiento al escribir |
|---|---|---|---|
| **Nombre Completo** (`nombre`) | Nombre y apellido: 2 o más palabras, cada una de 2 o más letras. Solo letras (con tildes y ñ), espacios, `-` y `'`. Máximo 100. Regex: `^\p{L}[\p{L}'’-]*\p{L}(\s+\p{L}[\p{L}'’-]*\p{L})+$` (flag `u`). | "Ingresa al menos un nombre y un apellido." (para cualquier error del nombre) | Borra al instante números y caracteres especiales (`@ # $ ! ? % & / ( ) = ; <` …) al escribir o pegar, conserva la posición del cursor y convierte a **MAYÚSCULAS** |
| **Teléfono** (`celular`) | Prefijo visual fijo `+56 9`. El usuario escribe **8 dígitos** que comienzan con **3, 4, 5, 6, 7, 8 o 9**. Regex: `^[3-9]\d{7}$`. El servidor también acepta el número con `9` o `569` delante y lo normaliza. | "Ingrese un teléfono válido de 8 dígitos." | Solo dígitos, con formato `XXXX XXXX` |
| **Correo** (`email`) | `gmail.com`, `outlook.com`, `hotmail.com`, `live.cl`, `yahoo.com`, `icloud.com`, o cualquier dominio que termine en **`.cl`**, **`.org`** o **`.net`**. No puede empezar con un carácter especial ni contener `@.`. | "Ingrese un correo electrónico válido" (vacío: "El correo es requerido") | Convierte a **minúsculas** |
| **Descripción** (`descripcion`) | Mínimo **20** caracteres (sin contar espacios al inicio o al final) y máximo 2000 (`maxlength`). | "La descripción del proyecto debe tener al menos 20 caracteres." Va a la izquierda de "Límite 2000 caracteres", que siempre está a la derecha. | — |
| **Adjuntos** (`adjunto[]`, opcional) | Hasta **5** archivos de máximo **5 MB** cada uno, en formato JPG, PNG, GIF, WEBP o PDF. El servidor revisa además el contenido real (`getimagesize()` o la firma `%PDF`). | Error bajo la lista de archivos, por ejemplo: «El archivo "x.jpg" debe pesar menos de 5 MB. Pesa 11.0 MB.» | Se valida al elegir el archivo y el archivo inválido no se agrega |
| **Términos** (`acepto_terminos`) | Obligatorio. | Aviso nativo del navegador | — |
| **Ofertas** (`acepta_ofertas`) | Opcional. | — | — |

**Anti-spam:**

- **Honeypot:** campo oculto `ta_contact_website`. Si viene lleno, el servidor responde éxito falso y no envía nada.
- **Nonce:** campo `ta_contact_nonce` (acción `ta_contact_submit`).

---

## Envío (AJAX) y respuestas del servidor

- **URL:** atributo `data-ajax-url` del form, que vale `home_url( '/wp-admin/admin-post.php' )`, con `action=ta_contact_submit`.
- **Cómo distingue el servidor:** `ta_handle_contact_submit()` detecta AJAX por la cabecera `X-Requested-With`.
  - **AJAX:** responde `wp_send_json( { success, reason, message } )`. En éxito incluye además `redirect_url`.
  - **Sin JS (fallback):** redirige con `?ta_contact=error&reason=…` o `?ta_contact=success`. **Mantener este camino.**
- **Códigos de `reason`:** `campos`, `nombre_corto`, `nombre_largo`, `nombre_numerico`, `nombre_invalido`, `desc_corta`, `email_vacio`, `email_invalido`, `email_caracteres`, `email_patron`, `email_dominio`, `celular`, `archivo`, `archivo_grande`, `archivo_tipo`, `archivos_muchos`, `terminos`, `nonce`, `rate_limit`, `envio`.
  - Los textos están **duplicados**: en `$error_messages` (functions.php) y en `$ta_contact_error_messages` (page-contacto.php). Mantén ambos iguales.

---

## Modales

Los dos modales usan la estructura `.ta-modal`, `.ta-modal-overlay` y `.ta-modal-content`, con tipografía Manrope. Se cierran con la **X arriba a la derecha** (`.ta-modal-close.ta-modal-close--right`), con el botón del modal o con un clic en el fondo oscuro.

### Modal de éxito (`#ta-contact-success-modal`)

Diseño aprobado, en este orden y **sin agregar nada más** (el cliente rechazó un bloque de WhatsApp y el enlace "Enviar otro mensaje"):

1. Círculo verde `#03854A` con un check SVG blanco y un halo verde claro alrededor. **No usar el emoji ✅.**
2. Título **"¡Mensaje enviado!"**, en negrita (800) y 20px.
3. Texto: "Nos pondremos en contacto en un plazo de 24 a 48 horas hábiles."
4. Botón negro de ancho completo **"Cerrar"**, con el texto centrado y bordes redondeados de 12px.

Después del éxito se limpia el formulario y el borrador, y a los **3 s** la página se recarga con `?ta_contact=success`, que **ya no muestra** la alerta verde (se eliminó a pedido del cliente).

### Modal de límite de envíos (`#ta-contact-rate-limit-modal`)

Muestra "Espera un momento", una cuenta regresiva de **60** segundos y el botón Enviar deshabilitado. Detalles en la sección siguiente.

---

## Límite de envíos (60 segundos)

- **Servidor:** guarda un transient `ta_contact_rate_{md5(IP)}` por `TA_CONTACT_RATE_LIMIT` (60 s) **solo después de un envío exitoso**. Los intentos fallidos no renuevan la espera. Si el transient existe, responde `reason=rate_limit`.
- **Navegador:** al recibir `rate_limit`, guarda la hora en `localStorage`, clave **`ta_rate_limit_timestamp`**, y abre el modal con la cuenta regresiva.
  - Si el usuario actualiza la página, la cuenta **continúa desde donde iba**: no reinicia en 60.
  - Al llegar a 0, cierra el modal, habilita el botón, borra la clave y quita `reason=rate_limit` de la URL (`history.replaceState`). Así no reaparece al actualizar.
- Esta clave también figura en la tabla de cookies de la Política de Privacidad.

---

## Correo (wp_mail)

- **Destinatario:** `ta_get_contact_email()` en functions.php, filtrable con `ta_contact_email`.
  - Apunta a **`contacto@todoacrilico.cl`** (producción, desde el 2026-10-20). La casilla de pruebas `rodolfo.parada.gonzalez@gmail.com` quedó comentada debajo.
- **Remitente:** el código no fija `From`, a propósito, para que lo controle **WP Mail SMTP** (en local: mailer Gmail con OAuth). Si `wp_mail()` falla, revisa primero WP Mail SMTP → Herramientas → Registro de correos. El error típico `invalid_grant` o 401 se resuelve reconectando la cuenta de Google.
- **Cabeceras:** `Content-Type: text/html` y `Reply-To` con el nombre y el correo del cliente. El `Reply-To` solo se agrega si `is_email()` es válido.
- **Si falla:** `error_log()` registra el destinatario, el asunto, las cabeceras, el número de adjuntos y el correo del remitente.

---

## Páginas legales (Política de Privacidad y Términos y Condiciones)

- **Páginas:** en local, slugs `politica-de-privacidad` (ID 102) y `terminos-y-condiciones` (ID 101); en **producción**, `politica-privacidad` (ID 2190) y `termino-y-condiciones` (ID 2193). `ta_is_legal_page()` las detecta con `ta_find_page()` (búsqueda flexible), no por slug exacto. Se muestran con `page.php` como **acordeón con índice lateral** (`ta_render_legal_accordion()` en functions.php).
  - Cada `<h2>` del contenido, con el formato **"N. Título"**, se convierte en una tarjeta plegable con su número en verde.
  - El texto se edita en WordPress como contenido normal. **No escribas HTML de acordeón en la base de datos.**
- **Ancho:** ambas páginas usan `.ta-page-content--wide` (1280px) para que la tabla de cookies quepa sin scroll en escritorio. Bajo 860px el índice se oculta y la tabla se desplaza horizontalmente.
- **Cookies:** la sección **8** de la Política de Privacidad, "Uso de Cookies y Almacenamiento Local", lista en una tabla cada cookie o clave del navegador: `ta_contact_draft`, `ta_rate_limit_timestamp`, `jg_session`, `wordpress_*` y `wp-settings-*`.
  - Si agregas, quitas o renombras una cookie o una clave de `localStorage`/`sessionStorage`, **actualiza esa tabla** (Ley 21.719, deber de información).
  - Si agregas analítica o publicidad, además hace falta un banner de consentimiento previo.
- **Términos y Condiciones, sección 6:** remite a "la sección 8 de dicha Política". Si se renumera la Política, actualiza esa referencia.
