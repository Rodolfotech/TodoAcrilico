# SPEC.md — Especificación funcional (formulario de cotización, menú de navegación, analítica con Clarity, Google Analytics 4 y Google Tag Manager, páginas legales y panel del catálogo)

> **Para una IA o desarrollador que modifique el sitio:** este archivo describe el comportamiento **aprobado por el cliente** al 2026-11-04 (tema 2.3.3, plugin 2.0.1). Cada regla fue pedida explícitamente. Si un cambio contradice algo de aquí, **pregunta antes**; no "mejores" una regla por tu cuenta.
>
> Código involucrado:
> - `wp-content/themes/todo-acrilico/page-contacto.php`: HTML del formulario, modales y todo el JavaScript.
> - `wp-content/themes/todo-acrilico/functions.php`, función `ta_handle_contact_submit()`: validación en servidor y envío.
> - `wp-content/themes/todo-acrilico/style.css`: estilos `.ta-contact-*` y `.ta-modal*`.
> - Analítica, páginas legales y menú: ver sus secciones más abajo. Panel del catálogo: plugin `wp-content/plugins/jwt-gallery/`.
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
- Esto está declarado en la **Política de cookies** (sección 3, "Cookies necesarias"). Si cambias la clave, el mecanismo o el plazo, **actualiza ese texto**.

### R3. Los errores aparecen solo al presionar "Enviar mensaje"

- Mientras el usuario escribe, ningún campo muestra error. Si el usuario edita un campo que tenía error, el error se oculta. Solo el teléfono vuelve a verde, y únicamente cuando queda válido.
- El `<form>` tiene **`novalidate`**: los avisos nativos del navegador no aparecen y cada error se muestra **debajo de su campo**, en rojo y con borde rojo.
- **Única excepción:** el checkbox de términos usa el aviso nativo (`reportValidity()`) cuando no hay otros errores.
- Los errores que devuelve el **servidor** por AJAX se muestran en la alerta roja superior (`.ta-contact-alert--error`), no bajo el campo.

### R4. El frontend y el servidor validan lo mismo

Cada regla de la tabla de abajo existe **dos veces**: en JavaScript (`validateField()` en `page-contacto.php`) y en PHP (`ta_handle_contact_submit()`). **Si cambias una, cambia la otra.** El servidor es la barrera real, porque el JS se puede saltar.

### R5. La analítica (Microsoft Clarity, Google Analytics 4 y Google Tag Manager) no cambia el sitio ni el formulario

La integración de Microsoft Clarity, Google Analytics 4 y Google Tag Manager (detalle en la sección "Analítica") **no debe alterar en nada** el funcionamiento ni el aspecto del sitio ni del formulario:

- **En pantalla no se oculta nada del formulario, nunca.** Cada campo, valor escrito, error, modal y botón se ve exactamente igual con o sin analítica, haya aceptado o rechazado el visitante. El banner de cookies reserva su espacio abajo, así que no tapa el formulario ni el botón "Enviar mensaje".
- **El formulario está marcado para que Clarity nunca grabe datos personales:** la tarjeta `.ta-contact-form-card` lleva `data-clarity-mask="true"`. Clarity enmascara todo su contenido en las grabaciones y mapas de calor. Esta marca solo actúa dentro de Clarity: **no oculta nada al usuario**. No la quites ni la muevas a un elemento más chico.
- El envío AJAX, las validaciones (R1–R4), el borrador, los modales y el límite de envíos funcionan igual con Clarity o GTM cargados, sin cargar o bloqueados por el navegador.
- **Las etiquetas de GTM no pueden enviar datos del formulario.** En el contenedor está prohibido crear variables o etiquetas que lean los valores de los campos (nombre, teléfono, correo, descripción). Para medir cotizaciones se usa solo un evento sin datos personales.
- Un error de Clarity, de GTM o del banner no puede romper la página: todo su código está en `try/catch`, en un archivo aparte (`assets/js/ta-consent.js`).

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
- Esta clave está declarada en la **Política de cookies** (sección 3, "Cookies necesarias": tiempo de espera de sesenta segundos).

---

## Correo (wp_mail)

- **Destinatario:** `ta_get_contact_email()` en functions.php, filtrable con `ta_contact_email`.
  - Apunta a **`contacto@todoacrilico.cl`** (producción, desde el 2026-10-20). La casilla de pruebas `rodolfo.parada.gonzalez@gmail.com` quedó comentada debajo.
- **Remitente:** el código no fija `From`, a propósito, para que lo controle **WP Mail SMTP** (en local: mailer Gmail con OAuth). Si `wp_mail()` falla, revisa primero WP Mail SMTP → Herramientas → Registro de correos. El error típico `invalid_grant` o 401 se resuelve reconectando la cuenta de Google.
- **Cabeceras:** `Content-Type: text/html` y `Reply-To` con el nombre y el correo del cliente. El `Reply-To` solo se agrega si `is_email()` es válido.
- **Si falla:** `error_log()` registra el destinatario, el asunto, las cabeceras, el número de adjuntos y el correo del remitente.

---

## Analítica: Microsoft Clarity, Google Analytics 4 y Google Tag Manager (con consentimiento previo)

**Regla general:** Clarity, GA4 y Google Tag Manager **solo se cargan después de que el visitante presiona "Aceptar"** en el banner de cookies (Ley 21.719, consentimiento previo). Antes de eso no se pide nada a `clarity.ms` ni a `googletagmanager.com`, y no se instala ninguna cookie de analítica. Un único banner cubre ambas herramientas. Además se cumple la regla R5: no cambian el sitio ni el formulario.

- **IDs** (se cambian en **Apariencia → Personalizar → Analítica**; un campo **vacío** desactiva esa herramienta; si los tres están vacíos no hay banner ni scripts, ni el botón "Cambiar preferencias de cookies"):
  - **Clarity:** `z81r92medx`, constante `TA_CLARITY_DEFAULT_ID`.
  - **Google Analytics 4:** `G-2KL8SPE9DY`, constante `TA_GA4_DEFAULT_ID`. Se carga **directamente con gtag.js desde el tema**.
  - **Google Tag Manager:** `GTM-K7FR5V9K`, constante `TA_GTM_DEFAULT_ID`. Sirve para etiquetas **adicionales**. ⚠️ **No agregues GA4 (`G-2KL8SPE9DY`) también dentro de GTM**, o cada visita se contará dos veces.
- **Banner** (`#ta-consent`, impreso en `wp_footer` por `ta_render_consent_banner()`):
  - Fijo abajo, con texto, el enlace "Más información" (a la Política de cookies; si no existe, a la sección 9 de la Privacidad) y dos botones **del mismo tamaño**: "Rechazar" y "Aceptar". Ninguno se destaca para forzar la aceptación.
  - Aparece mientras el visitante no haya decidido. Si un navegador "no lo muestra", lo normal es que ya tenga una decisión guardada o una extensión que oculta avisos de cookies; se comprobó en Chrome y Firefox 157 con perfil limpio (2026-11-04).
  - Mientras está visible reserva su alto con `padding-bottom` en el body y `scroll-padding-bottom` en `html`, recalculado con `ResizeObserver`, para no tapar contenido.
  - Su `z-index` es 9000, menor que el de los modales (9999).
- **Decisión:** se guarda en `localStorage['ta_cookie_consent']` como `{analytics, date, v}`. Dura 12 meses y luego se vuelve a preguntar.
- **Aceptar:**
  - **Clarity:** se inyecta `https://www.clarity.ms/tag/{ID}` y se llama `clarity('consent')`.
  - **GA4 y GTM:** primero se fija **una sola vez** el modo de consentimiento de Google, `gtag('consent','default', {analytics_storage:'granted', ad_storage:'denied', ad_user_data:'denied', ad_personalization:'denied'})`, porque el visitante aceptó **solo analítica**. Luego GA4 hace `gtag('js')` y `gtag('config', ID)` e inyecta `gtag/js?id={ID}`, y GTM empuja el evento `gtm.js` e inyecta `gtm.js?id={ID}` (snippets oficiales). El orden en `dataLayer` es: `consent default` → `js` → `config` → `gtm.js`.
- **Rechazar:** no se carga nada.
- **Páginas privadas (login y panel del plugin):** no se carga la analítica ni aparece el banner de cookies. `ta_is_private_page()` las detecta por los shortcodes `[jwt_gallery]` y `[jwt_gallery_panel]` (no por slug), y `ta_should_load_analytics()` es la condición que usan `ta_assets()` y `ta_render_consent_banner()`. Se puede ampliar con el filtro `ta_is_private_page`. Motivo: son páginas internas de la empresa y no se debe grabar al personal.
- **Retirar el consentimiento:** el footer enlaza a la página **"Política de cookies"** (`ta_get_cookies_page()`), que se muestra en acordeón como las demás páginas legales y termina con el botón **"Cambiar preferencias de cookies"** (`[data-ta-consent-open]`, agregado por `page.php`), que reabre el banner. Si esa página no existe, el footer muestra en su lugar el botón "Preferencias de cookies". El texto de la política no va dentro del banner (pedido del cliente); el enlace "Más información" del banner lleva a esa página. Si el visitante había aceptado y ahora rechaza, se borran las cookies `_clck`, `_clsk`, `CLID`, `ANONCHK`, `MR`, `MUID`, `SM`, `_ga`, `_ga_*`, `_gid` y `_gat*`, y se recarga la página.
- **No hay `<noscript>` de GTM:** sin JavaScript no se puede dar consentimiento, así que no se carga el iframe de GTM.
- **Política de cookies** (página propia, 10 secciones, versión 1.0 del 4-11-2026): es el **único** lugar donde se describen las cookies, en lenguaje jurídico y sin nombres técnicos. La base de licitud de la analítica es el **consentimiento**, y explica cómo retirarlo. **Si cambia algo de la analítica, del banner o de los plazos, actualiza `legal/politica-de-cookies.html` y sube `TA_LEGAL_VERSION`** (llega sola a producción con el ZIP). **Si se agregan etiquetas nuevas en GTM** (por ejemplo, Google Ads o Meta Pixel), hay que declararlas en la Política de cookies y probablemente pedir un consentimiento separado para publicidad: no basta con publicarlas en GTM.
- **Código:** `functions.php` (`ta_get_clarity_id()`, `ta_get_ga4_id()`, `ta_get_gtm_id()`, sus `ta_sanitize_*`, `ta_analytics_enabled()`, `ta_is_private_page()`, `ta_should_load_analytics()`, `ta_render_consent_banner()`, Customizer y enqueue), `assets/js/ta-consent.js`, `footer.php` (enlace "Política de cookies" o, si falta la página, el botón "Preferencias de cookies"), `page.php` (bloque `.ta-cookie-prefs` con el botón) y `style.css` (`.ta-consent*`, `.ta-footer-consent`, `.ta-cookie-prefs`).
- **Prohibido:**
  - Cargar Clarity, GA4 o GTM directamente en `header.php` (incluidos los snippets `<script>` que entregan Google Analytics y Google Tag Manager) o sin consentimiento.
  - Quitar el botón "Rechazar" o hacerlo menos visible.
  - Quitar `data-clarity-mask` del formulario o activar en Clarity la opción que **desenmascara** el contenido.
  - Conceder `ad_storage` u otros consentimientos de publicidad con el botón "Aceptar" actual, que cubre solo analítica.

---

## Menú de navegación (todas las páginas)

- **Escritorio (más de 861px):** menú horizontal con Inicio, Catálogo (desplegable de categorías que se abre **solo con clic**, no con hover), Cuidados y el botón negro "Cotizar".
- **Desplegable "Catálogo":** "Todas las piezas" y luego **todas** las categorías del plugin en orden alfabético, **tengan o no piezas** (`ta_get_catalog_categories()`). Al crear, renombrar o borrar una categoría en el panel, el menú cambia solo en la siguiente carga, sin tocar el menú de WordPress. Cada enlace lleva a `?categoria={slug}` en el catálogo; una categoría vacía muestra "Todavía no hay productos publicados en esta categoría.". Mismo criterio que las pestañas del catálogo.
- **Móvil y tablet (861px o menos):**
  - Arriba van el logo a la izquierda y el botón **hamburguesa** a la derecha (`.ta-nav-toggle`, 44×44, borde suave). El menú está **cerrado** al cargar la página.
  - Al tocar el botón, el menú se despliega debajo en columna y el ícono cambia a **✕**.
  - Se cierra tocando ✕, con Escape o al agrandar la ventana a más de 861px.
  - Dentro del menú, Catálogo abre sus categorías como acordeón, con la flecha hacia arriba. "Cotizar" ocupa el ancho completo.
  - No debe haber scroll horizontal.
- **Accesibilidad:** el botón usa `aria-expanded`, `aria-controls="ta-primary-nav"` y una `aria-label` que alterna entre "Abrir menú" y "Cerrar menú".
- **Código:** botón en `header.php`, lógica en `assets/js/ta-nav.js` (`setNavOpen()`, clase `.is-nav-open` en `.ta-header`) y estilos en `style.css`, bloque `@media (max-width: 861px)`. El desplegable lo arman `TA_Nav_Walker` y `ta_nav_menu_fallback()` en functions.php.

---

## Páginas legales (Política de Privacidad, Términos y Condiciones y Política de Cookies)

- **Páginas:** en local, slugs `politica-de-privacidad` (ID 102), `terminos-y-condiciones` (ID 101) y `politica-de-cookies` (ID 179); en **producción**, `politica-privacidad` (ID 2190) y `termino-y-condiciones` (ID 2193). `ta_is_legal_page()` las detecta con `ta_find_page()` (búsqueda flexible), no por slug exacto. Se muestran con `page.php` como **acordeón con índice lateral** (`ta_render_legal_accordion()` en functions.php).
  - Cada `<h2>` del contenido, con el formato **"N. Título"**, se convierte en una tarjeta plegable con su número en verde.
  - **El texto vive en el tema** (`legal/politica-de-privacidad.html`, `legal/terminos-y-condiciones.html`, `legal/politica-de-cookies.html`) y `ta_sync_legal_pages()` lo copia a cada WordPress una vez por cada `TA_LEGAL_VERSION`: crea la página si falta y, si cambió, la actualiza guardando la versión anterior como revisión. Así local y producción se ven iguales. Para cambiar un texto: edita el archivo y sube `TA_LEGAL_VERSION`. **No escribas HTML de acordeón en los textos.**
- **Ancho:** las tres páginas usan `.ta-page-content--wide` (1280px). Bajo 860px el índice se oculta.
- **Redacción (pedido del cliente):** las tres páginas se redactan **como abogado**, sin vocabulario de desarrollo (nada de `localStorage`, nombres de cookies, "interfaz", etc.), y **cada tema se trata en un solo lugar**.
- **Política de Privacidad** (versión 2.0, 4-11-2026, 11 secciones): responsable, definiciones, datos del formulario y su origen, finalidades y bases de licitud, destinatarios y transferencia internacional (Google LLC como proveedor de correo, por WP Mail SMTP con Gmail), confidencialidad y seguridad, plazos de conservación, derechos, **9. Cookies (solo remite a la Política de Cookies)**, autoridad de control y modificaciones. **No vuelvas a detallar cookies aquí.**
- **Política de Cookies** (versión 1.0, 4-11-2026, 10 secciones): describe todas las cookies (necesarias y de analítica), con sus plazos, la transferencia internacional a Microsoft y Google, la base de licitud y la revocación. Si agregas, quitas o cambias una cookie o clave del navegador, **actualiza `legal/politica-de-cookies.html` y sube `TA_LEGAL_VERSION`** (Ley 21.719, deber de información).
- **Datos pendientes del cliente:** representante legal, plazo de conservación sin contrato (puesto: 12 meses) y envío de publicidad (puesto: no, salvo consentimiento).
- **Términos y Condiciones, sección 6:** remite a la Política de Privacidad y a la Política de Cookies, por nombre y sin número de sección.

---

## Panel del catálogo (plugin JWT Gallery, "Todo Acrílico Panel")

- **Páginas:** login `[jwt_gallery]` (slug `galeria`) y panel `[jwt_gallery_panel]` (slug `galeria-panel`). Son **privadas de la empresa**: sin banner de cookies ni analítica (ver "Analítica").
- **Sesión:** token JWT en una cookie HttpOnly `jg_session` (24 h) más `localStorage['jg_session']` con los datos visibles del usuario. **Un solo token válido por usuario** (`jti` en `jg_token_jti`): iniciar sesión en otro navegador o cerrar sesión revoca el anterior.
  - Si la sesión expiró o fue revocada, la API responde **401 con el motivo real** (`jg_token_revoked`, `jg_expired_token`, etc.) y el panel muestra el modal **"Sesión cerrada"** con un solo botón, "Iniciar sesión", que lleva al login. Nunca debe aparecer el genérico de WordPress "Lo siento, no tienes permisos para hacer eso.".
- **Subir imagen** (y el formulario de edición de cada pieza):

| Campo | Regla | Popover "i" |
|---|---|---|
| Título | Obligatorio, máximo **43** caracteres | "Campo obligatorio. Máximo 43 caracteres, incluidos los espacios." |
| Descripción | Opcional, máximo **120** | Igual, con 120 |
| Uso | Opcional, máximo **43** | Igual, con 43 |
| Medidas | Opcional, máximo **20** | Igual, con 20 |
| Imágenes | De **1 a 10** por pieza, máximo **8 MB** cada una; .jpg, .jpeg, .png, .gif, .webp y .avif | Cantidad, peso y formatos, más el recuadro verde **"Recomendado: sube en .webp…"** |
| Categoría | Opcional; sin elegir queda "Sin categoría" | Lista las **categorías actuales** (se lee al abrir) y cómo crear nuevas |

  - Los límites salen de las constantes de `JG_REST` (fuente única para el servidor, `maxlength` y popovers). Se valida en el navegador y en el servidor.
  - El popover se abre con el botón "i", hay uno abierto a la vez y se cierra con un clic fuera o con Escape (el foco vuelve al botón). El clic no activa el campo del `<label>`.
  - **Si una pieza se rechaza, no queda ningún archivo guardado** (se cuenta antes de guardar y se borran las ya guardadas si una falla).
- **Categorías:** crear, renombrar (sin duplicados) y eliminar (borra también sus piezas y archivos, con modal de confirmación). Cada cambio se refleja en el menú "Catálogo" del sitio.
- **Móvil:** el panel no puede ser más ancho que la pantalla (barra superior con `flex-wrap`, grilla `minmax(0, 1fr)`).
- **Código:** `includes/class-jg-rest.php` (API y validación), `includes/class-jg-shortcode.php` (HTML y `JGGallery.limits`), `assets/js/jwt-gallery.js` (`createHint()`, `HINTS`, `handleExpiredSession()`, `showConfirmModal()` con `hideCancel`) y `assets/css/jwt-gallery.css` (`.jg-hint*`).
