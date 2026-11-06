##Estilos generales
##Para todo el sitio la tipografía es Manrope
###Colores utilizados
Verde #03854A
Negro #1A1C1C

Tamaños Men ú principal Nav bar
Títulos Menú Monrope Regular 16px
Tamaños textos banner
E T I Q U E TAS D E CAT EG O R Í AS t i p o g r a f í a M a n r o p e 1 2 ex t r a b o l d C O LO R 444 74 8
TÍTULO Manrope light 80px
Párrafo Manrope Regular 18px
Tamaños textos Bloque Categorías
E T I Q U E TAS D E CAT EG O R Í AS t i p o g r a f í a M a n r o p e 1 2 ex t r a b o l d C O LO R 444 74 8
Título 32 Monrope regular
Título Card 24px Monrope Medium
Párrafo Manrope Regular 15px
Vínculo Extra bold 15px
Tamaños Bloque Destacados
E T I Q U E TAS D E CAT EG O R Í AS t i p o g r a f í a M a n r o p e 1 2 ex t r a b o l d C O LO R 444 74 8
Título 32 Monrope regular
Título Card 24px Monrope Medium
Párrafo Manrope Regular 15px
Vínculo Extra bold 15px
Tamaños Bloque Nuestra historia
E T I Q U E TAS D E CAT EG O R Í AS t i p o g r a f í a M a n r o p e 1 2 ex t r a b o l d C O LO R 444 74 8
Título 48 Monrope Light
Párrafo Manrope Regular 18px
Cifras Monrope Medium 24px
Párrafos Extra bold 15px
Bloque Tienes una idea en mente
Título 48 Monrope Light
Párrafo Manrope Regular 18px
Botón fill #000000

Texto boton Manrope #FFFFFF Extra bold 6px

Tamaño boton 20px alto
Footer
Párrafos Monrope Regular 15px
Menú tamaño títulos Extrabold 15
Menú tamaño subtítulos Regular 15

---

# Guía para IA / desarrolladores — leer antes de modificar

> Estado aprobado al **2026-11-04**: tema `todo-acrilico` **2.3.6**, plugin `jwt-gallery` **2.0.3**, WordPress 7.1, rama `feature/version8`. Las reglas de abajo existen porque el cliente las pidió o porque romperlas ya causó errores. Si una petición nueva contradice alguna, **pregunta antes de cambiarla**.

## Mapa de documentación (orden de lectura)

| # | Archivo | Qué contiene | En git |
|---|---|---|---|
| 1 | `CLAUDE.md` (este) | Guía de estilo visual del cliente y reglas para no romper nada | **Sí** |
| 2 | `SPEC.md` | **Comportamiento aprobado:** formulario de cotización, modales, límite de envíos, menú (móvil y desplegable Catálogo), diseño de Cotizar, **analítica (Clarity + GA4 + GTM) con banner de cookies**, páginas legales versionadas en el tema y **panel del plugin** | **Sí** |
| 3 | `wp-content/themes/todo-acrilico/Docs_theme.md` | Tema: estado actual, archivos, funciones y bitácora §4.x (lo más reciente: §4.127–§4.133) | **Sí** |
| 4 | `wp-content/plugins/jwt-gallery/Docs_Plugin.md` | Plugin JWT Gallery: REST, panel y bitácora §12.x (lo más reciente: §12.51–§12.55) | **Sí** |
| 5 | `Docs_Seguridad.md` | Fixes de seguridad: plugin 1–12, tema y formulario 13–16 (16 = Clarity, GTM y consentimiento), 17 = cambios del 2026-11-04 (panel privado, errores de sesión, archivos huérfanos) | **Sí** |
| 6 | `GEO.md`, `.well-known/ai.json` | Optimización para IA generativa | Sí |
| 7 | `README.md` | Resumen operativo: URLs, cuentas del panel y pasos de producción. **Ya no contiene la contraseña**: está en `CREDENCIALES.local` (raíz, excluido de git) | **Sí** |
| — | `wp-content/plugins/jwt-gallery/theme.md` | Bitácora interna **antigua** del plugin (1.4.8). Solo histórica; no va en el ZIP | Sí |

**El repositorio de GitHub es PÚBLICO y todos los `.md` del proyecto están en git** (desde 2026-11-06). Por eso, **nunca escribas en un `.md` contraseñas, la clave `JG_JWT_SECRET`, credenciales de base de datos ni tokens**. Las credenciales van solo en **`CREDENCIALES.local`** (raíz de `proyecto3`, excluido por `.gitignore`) y en `wp-config.php`. Antes de cada commit, busca en lo preparado la contraseña de `CREDENCIALES.local` y el secreto de `wp-config.php`. Si se olvida la contraseña del panel: wp-admin → Plugins → Todo Acrílico Panel → Ajustes → "Credenciales del panel".
- **Dónde va cada `.md`:** en la raíz, los generales (`CLAUDE.md`, `SPEC.md`, `GEO.md`, `README.md` y `Docs_Seguridad.md`, que cubre tema y plugin). La documentación técnica va dentro de cada uno: `Docs_theme.md` en el tema y `Docs_Plugin.md` (más `theme.md`, histórico) en el plugin. `.gitignore` ignora la raíz (`/*`) salvo excepciones: para un `.md` nuevo en la raíz, agrega su `!/archivo.md`. Los ZIP excluyen `*.md`, así que no llegan a producción.

Los `Docs_*.md` contienen **copias antiguas de código** en algunas secciones. **El código fuente real manda**; si una copia no coincide, créele al archivo `.php`/`.css`/`.js`.

## Reglas que no se deben romper

1. **Formulario de cotización** (`page-contacto.php` + `ta_handle_contact_submit()`): sigue `SPEC.md` al pie de la letra. En resumen:
   - Nunca limpia los datos ante un error (envío AJAX; el servidor responde JSON a AJAX y redirige sin JS: mantener ambos caminos).
   - Conserva lo escrito al actualizar la página (`sessionStorage` `ta_contact_draft`).
   - Los errores aparecen **solo al presionar Enviar**, debajo de cada campo (el form tiene `novalidate`).
   - **Cada validación existe en JS y en PHP: si cambias una, cambia la otra** (nombre y apellido; teléfono `^[3-9]\d{7}$`; correos permitidos + `.cl`/`.org`/`.net`; descripción ≥ 20; adjuntos ≤ 5 × 5 MB).
   - Los mensajes de error están duplicados en `functions.php` (`$error_messages`) y `page-contacto.php` (`$ta_contact_error_messages`).
2. **Límite de envíos:** transient por IP de 60 s, creado **solo tras un envío exitoso**. El modal con cuenta regresiva usa `localStorage` `ta_rate_limit_timestamp`, sobrevive a F5 y limpia `reason=rate_limit` de la URL al terminar.
3. **Modal de éxito:** diseño cerrado (check SVG en círculo verde, "¡Mensaje enviado!", texto de 24–48 h, botón "Cerrar"). Ver SPEC.md. No agregar WhatsApp, emojis ni otros botones.
4. **Versión del tema = caché:** `.htaccess` sirve CSS/JS con `Cache-Control: immutable` por 1 año. **Cualquier cambio en `style.css` o en `assets/js/*` exige subir `TA_VERSION` (functions.php) y `Version:` (cabecera de style.css) a la misma versión**, o los visitantes seguirán viendo la versión antigua.
5. **Páginas legales (Privacidad, Términos y Política de cookies):** el texto **vive en el tema**, en `legal/*.html` (secciones `<h2>N. Título</h2>`, redactado como abogado), y `ta_sync_legal_pages()` lo aplica en cada WordPress **una vez por cada `TA_LEGAL_VERSION`**: crea la página si falta y reemplaza el contenido si cambió (la versión anterior queda como revisión de WordPress). Así local y producción quedan iguales con solo subir el ZIP (pedido del cliente). **Para cambiar un texto legal: edita `legal/<archivo>.html` y sube `TA_LEGAL_VERSION`**; no lo edites solo en WordPress, porque se pierde en la siguiente versión. El tema los muestra como acordeón (`ta_render_legal_accordion()`); no metas HTML de acordeón en los textos. Las páginas se buscan con `ta_find_page()`, porque en producción los slugs son otros: `politica-privacidad` (ID 2190) y `termino-y-condiciones` (ID 2193). **No detectes páginas por slug exacto.** Resultado de la última sincronización: opción `ta_legal_sync_log`.
6. **Cookies / almacenamiento:** si agregas, quitas o renombras una cookie o clave de `localStorage`/`sessionStorage` (tema o plugin, p. ej. `jg_session`), **actualiza la Política de cookies** (`legal/politica-de-cookies.html` y sube `TA_LEGAL_VERSION`, regla 5; Ley 21.719, deber de información). Las cookies se describen **solo** ahí, en lenguaje jurídico, sin nombres técnicos (`localStorage`, `_ga`, etc.): **la Política de Privacidad no debe volver a detallar cookies**. Su sección 9 solo remite a la Política de cookies (pedido del cliente: sin redundancia y redactado como abogado). Agregar analítica o publicidad exige además un banner de consentimiento previo.
7. **Plugin JWT Gallery (Todo Acrílico Panel):**
   - El token lleva el claim `jti`, guardado en el usermeta `jg_token_jti`; el logout lo rota para revocar. **Hay un solo token válido por usuario**: iniciar sesión o emitir un token en otro lado cierra la sesión anterior. No quites esa validación. La categoría por defecto `vidrios` pasó a llamarse `trabajos-especiales`: en producción conservaba el **slug** `vidrios` (renombrar en el panel solo cambia el nombre), y `JG_CPT::migrate_legacy_slugs()` lo corrige una vez (`LEGACY_SLUGS`, opción `jg_slug_migrations`; mismo ID, las piezas no cambian). El tema redirige `?categoria=vidrios` con 301.
   - **Límites en un solo lugar:** constantes de `JG_REST` (`MAX_TITLE_CHARS` 43, `MAX_DESCRIPTION_CHARS` 120, `MAX_USAGE_CHARS` 43, `MAX_MEASUREMENTS_CHARS` 20, `MAX_IMAGES_PER_POST` 10, `MAX_UPLOAD_BYTES` 8 MB, `ALLOWED_IMAGE_EXTENSIONS`). La validación del servidor, los `maxlength` y los popovers de ayuda las leen de ahí (`JGGallery.limits`). Para cambiar un límite, cambia solo la constante.
   - **Popovers de ayuda** (botón "i" junto a cada campo de "Subir imagen" y del formulario de edición): `createHint()` y `HINTS` en `jwt-gallery.js`. El de Imágenes recomienda subir en **.webp** y el de Categoría lista las categorías actuales. No los quites (pedido del cliente).
   - **Sesión expirada o revocada:** `require_auth()` devuelve el `WP_Error` real (401), no `false`; el panel muestra el modal "Sesión cerrada" y lleva al login (`handleExpiredSession()`). No vuelvas a devolver `false`: WordPress lo convierte en "Lo siento, no tienes permisos para hacer eso.".
   - **Sin archivos huérfanos:** `collect_image_sources()` cuenta las imágenes antes de guardar y, si una falla, borra las ya guardadas en esa petición.
   - **Login y panel ocultos para buscadores** (desde 2.0.2, pedido del cliente): `JG_Template` los saca del sitemap de WordPress (`wp_sitemaps_posts_query_args`) y les pone `noindex, nofollow, noarchive` en la meta robots y en la cabecera `X-Robots-Tag`. Detecta las páginas por slug y por shortcode (`private_page_ids()`). **No los bloquees en robots.txt**: Google necesita entrar para ver el `noindex`. Tampoco los agregues al `sitemap.xml` del tema.
   - **Pruebas automáticas:** nunca emitas tokens para `admin@todoacrilico.cl` (ID 3), porque cierra la sesión real del cliente. Usa una cuenta `prueba.panel.*` (por ejemplo, ID 4) y borra lo que crees.
8. **Menú móvil:** a 861px o menos, el nav se oculta y se abre con el botón hamburguesa `.ta-nav-toggle` (header.php), que agrega `.is-nav-open` a `.ta-header` (ta-nav.js). En escritorio el botón no se ve. No vuelvas a dejar el menú siempre desplegado en móvil.
9. **Contacto, alineación:** la tarjeta del formulario empieza a la misma altura que la descripción "Escríbenos…". Lo resuelve la grilla de `.ta-contact-inner`: `.ta-contact-info` usa `display: contents` y la tarjeta va en `grid-row: 4 / -1`. **No volver a usar un `margin-top` fijo** (antes eran 137px y se desalineaba según cuántas líneas ocupara el título).
10. **Nombre Completo:** al escribir o pegar se borra todo lo que no sea letra (con tildes y ñ), espacio, `-` o `'`, y se convierte a MAYÚSCULAS sin mover el cursor (regex `/[^\p{L}\s'’-]/gu`, en page-contacto.php). El correo se convierte a minúsculas al escribir.
11. **No quites funcionalidades sin preguntar, y no reintroduzcas lo que el cliente pidió quitar.** Todo lo de SPEC.md y de estas reglas fue pedido explícitamente. El cliente pidió quitar: la alerta verde de éxito sobre el formulario, el emoji ✅ y el bloque de WhatsApp del modal, el enlace "Enviar otro mensaje" y el apartado "Lo que no utilizamos" de la sección de cookies. No vuelvas a agregarlos.
12. **Analítica: Microsoft Clarity, Google Analytics 4 y Google Tag Manager (SPEC.md, R5 y sección "Analítica"):**
    - Las tres se cargan **solo tras "Aceptar"** en el banner `#ta-consent` (`assets/js/ta-consent.js`). Nunca en `header.php` ni sin consentimiento. **No pegues en el `<head>` los snippets que entregan Google Analytics o Google Tag Manager**: el tema ya los carga después del consentimiento.
    - IDs: Clarity `z81r92medx` (`TA_CLARITY_DEFAULT_ID`), GA4 `G-2KL8SPE9DY` (`TA_GA4_DEFAULT_ID`) y GTM `GTM-K7FR5V9K` (`TA_GTM_DEFAULT_ID`). Se cambian en Personalizar → Analítica; un campo vacío desactiva esa herramienta.
    - GA4 se carga **directo desde el tema** (gtag.js). **No lo agregues también en GTM**, o duplica las visitas. Antes de cargar GA4 o GTM se fija una sola vez el modo de consentimiento de Google: analítica concedida y publicidad denegada. No concedas publicidad con el botón "Aceptar" actual.
    - Si agregas etiquetas en GTM, declara sus cookies en la Política de cookies. Ninguna etiqueta puede leer los valores del formulario.
    - La tarjeta del formulario lleva `data-clarity-mask="true"`, para que Clarity no grabe datos personales. Solo afecta a Clarity, nunca oculta nada al usuario: no la quites.
    - **Páginas privadas del panel** (login y panel de JWT Gallery, detectadas por los shortcodes `[jwt_gallery]` y `[jwt_gallery_panel]` en `ta_is_private_page()`): **no** se carga la analítica **ni** se muestra el banner. Son de uso interno de la empresa (pedido del cliente). Usa `ta_should_load_analytics()`, no `ta_analytics_enabled()`, para decidir si se carga algo de analítica.
    - "Aceptar" y "Rechazar" deben tener el mismo tamaño.
    - **Política de cookies (desde tema 2.3.1, pedido del cliente):** el footer muestra el enlace **"Política de cookies"** (reemplazó a "Preferencias de cookies"). Es una página legal más (texto en `legal/politica-de-cookies.html`, la crea `ta_sync_legal_pages()` si falta) y se muestra con acordeón. Se busca con `ta_get_cookies_page()` (slugs `politica-de-cookies`, `politica-cookies`, `cookies`; local ID 179). El botón **"Configurar cookies"** (`[data-ta-consent-open]`, reabre el banner de Aceptar/Rechazar) va **en el footer de todas las páginas públicas** (desde tema 2.3.4, pedido del cliente) **y** al final de la Política de cookies (`page.php`). Es el medio de revocación que exige la Ley 21.719, Art. 12: "en cualquier momento", por un medio "similar o equivalente" al usado para dar el consentimiento y "permanentemente disponible". **No lo quites ni lo dejes solo dentro de la página.** El footer muestra el enlace "Política de cookies" si la página existe, y el botón siempre (con `ta_should_load_analytics()`). El texto legal **no va dentro del banner** ni en una ventana: lo pidió el cliente. El enlace "Más información" del banner lleva a esta página.
    - El banner reserva su alto (`padding-bottom` y `scroll-padding-bottom`) para no tapar contenido. Por eso **ninguna fila del footer ni ningún componente puede desbordar el ancho en móvil**: si la página es más ancha que la pantalla, el banner queda fuera de vista. Pasó con `.ta-footer-legal` y con la paginación del catálogo (arreglados con `flex-wrap`) y con el panel del plugin (barra superior con `flex-wrap` y grilla `minmax(0, 1fr)`).
    - Si el banner "no aparece" en un navegador, casi siempre es porque ese navegador ya guardó una decisión (12 meses) o porque una extensión oculta avisos de cookies. Verifícalo con un perfil limpio antes de tocar el código: el 2026-11-04 se comprobó en Chrome y en Firefox 157.
    - Cualquier cambio en las cookies obliga a actualizar la Política de cookies (regla 6).
13. **Colores y tipografía:** usar las variables de `style.css` (`--ta-accent` = #03854A, `--ta-ink` = #1A1C1C, `--ta-muted`, `--ta-border`, `--ta-bg`) y Manrope (servida localmente desde `fonts/manrope/`; no usar Google Fonts).
14. **Menú "Catálogo":** el desplegable lista "Todas las piezas" y **todas** las categorías del plugin, tengan o no piezas (`ta_get_catalog_categories()`, usada por `TA_Nav_Walker` y `ta_nav_menu_fallback()`). Al crear, renombrar o borrar una categoría en el panel, el menú cambia solo en la siguiente carga. No vuelvas a filtrar las categorías vacías (pedido del cliente).
15. **Catálogo en Google (desde tema 2.3.6, pedido del cliente):** cada categoría `/catalogo/?categoria={slug}` debe indexarse.
    - **Sitemap:** el proveedor `TA_Sitemaps_Catalog` agrega `wp-sitemap-catalogo-categorias-1.xml` al índice `wp-sitemap.xml`, con **todas** las categorías (`ta_get_catalog_categories()`). Una categoría nueva aparece sola, y una borrada desaparece. El `lastmod` es la fecha de su pieza más reciente.
    - **Canónica propia** para cada categoría válida (filtro `get_canonical_url`). Antes todas apuntaban a `/catalogo/` y Google las trataba como duplicadas. No la quites.
    - **Slugs antiguos:** `ta_catalog_legacy_slugs()` (`vidrios` → `trabajos-especiales`) redirige con 301 si el slug antiguo ya no existe.
    - El `sitemap.xml` estático del tema **no** se usa en producción (`/wp/sitemap.xml` → `wp-sitemap.xml`).

## Pendientes antes de producción (no "arreglar" sin confirmar con el cliente)

- ✅ Resuelto el 2026-10-20: `ta_get_contact_email()` devuelve `contacto@todoacrilico.cl`. La casilla de pruebas quedó comentada. Para probar en local, usa el filtro `ta_contact_email` en vez de editar la función.
- `.htaccess` es de **local**: `RewriteBase /wordpress/proyecto3/` y redirecciones 301 con rutas `/wordpress/proyecto3/...`. En producción ajustar la base y los destinos.
- `sitemap.xml` y `robots.txt` viven en el tema (`wp-content/themes/todo-acrilico/`) y se sirven en la raíz por reglas de `.htaccess` (GEO.md ya lo refleja).
- `header.php` enlaza `/.well-known/ai.json` con ruta absoluta desde la raíz del dominio; en un WordPress instalado en subcarpeta (`/wp/`) esa URL no existe. `ai.json` usa `info@todoacrilico.cl` y el resto del sitio `contacto@todoacrilico.cl`.
- WP Mail SMTP (Gmail OAuth) debe estar conectado en cada entorno; si no, el formulario responde "No se pudo enviar el mensaje".
- **Textos legales con datos pendientes del cliente:** nombre del representante legal (Art. 14 ter b), plazo de conservación si no hay contrato (se puso 12 meses) y si se envía publicidad (se puso que no, salvo consentimiento). Cuando los confirme: edita `legal/*.html` y sube `TA_LEGAL_VERSION`.

## Cómo entregar cambios a producción

1. **Versión:**
   - **Tema:** si cambiaste `style.css` o `assets/js/*`, sube `TA_VERSION` y `Version:` (regla 4). Si solo cambiaste PHP, no es obligatorio, pero súbela igual si vas a entregar un ZIP nuevo, para no confundir dos ZIP con el mismo número.
   - **Textos legales:** si cambiaste `legal/*.html`, sube `TA_LEGAL_VERSION` (regla 5).
   - **Plugin:** si cambiaste `assets/js` o `assets/css` del plugin, sube `JG_VERSION` y la cabecera `Version:` de `jwt-gallery.php`.
2. **Verifica** antes de dar algo por terminado:
   - `php -l` de cada archivo PHP tocado y `node --check` de cada JS.
   - Una prueba real en Chrome headless a 390px y 1280px. Para clics y mediciones se usa el DevTools Protocol con el `WebSocket` nativo de Node 22, sin dependencias.
   - Si tocaste el plugin: la regresión de API y panel descrita en Docs_Plugin.md §12.53 (crear, editar y borrar pieza y categoría, cada límite +1, sin archivos huérfanos), con la cuenta de prueba y sin dejar restos.
3. **ZIP del tema** (en la raíz de `proyecto3`; los `*.zip` están en `.gitignore`):
   ```bash
   cd wp-content/themes && zip -rq -X ../../todo-acrilico-X.Y.Z.zip todo-acrilico -x "*/.*" "*.md"
   ```
   **Plugin:** igual, desde `wp-content/plugins` con `jwt-gallery`. Excluye siempre `*.md`, porque `theme.md` es interno. El ZIP del tema debe incluir `legal/`.
   El usuario suele renombrar los ZIP (`TodoAcrilico-X.Y.Z.zip`, `TodoAcrilicoPanel-X.Y.Z.zip`); no borres los que él renombró.
   Borra el ZIP de `proyecto3` después de entregarlo: en local la carpeta es pública por HTTP.
4. **Contenido de la base de datos** (menús, otras páginas): no viaja en el ZIP y hay que replicarlo a mano en producción. **Excepción: los textos legales** viajan en `legal/*.html` y se aplican solos al subir `TA_LEGAL_VERSION` (regla 5).
5. **Commits:** el usuario a veces hace commits propios en medio. Antes de hacer `git add` de un archivo con cambios ajenos sin commitear, revisa el `git diff` y no los mezcles sin avisar.

## Entorno local

- URL: `http://localhost/wordpress/proyecto3/` (permalinks con `index.php/`).
- El `php` por defecto de la consola es **7.3** y WordPress 7.1 pide ≥ 7.4: para scripts con `wp-load.php` usar **`/usr/bin/php8.3`**. `php -l` con 7.3 sirve solo para revisar sintaxis.
- No hay WP-CLI instalado. Hay Google Chrome para capturas headless (`google-chrome --headless=new --screenshot=...`) y **Firefox 157 + geckodriver** (`geckodriver --port 4445`, WebDriver por HTTP) para comprobar en Firefox.
- `php8.3` con GD sirve para generar imágenes `.webp` de prueba (`imagewebp`).
- En local, todos los `.md` de la raíz responden **200** por HTTP. En producción (`https://todoacrilico.cl/wp/`) responden **404** (verificado el 2026-10-22). **No subas `.md` a producción.**
