
## Estado actual del proyecto (2026-11-04)

> **IA / desarrollador:** antes de modificar, lee `CLAUDE.md` (reglas que no se deben romper) y `SPEC.md` (comportamiento aprobado). Este README es solo un resumen operativo.

- **Tema `todo-acrilico`:** **2.3.6**. Incluye:
  - **Categorías del catálogo en Google:** todas en el sitemap de WordPress, que se actualiza solo, con canónica propia y redirección 301 de `?categoria=vidrios` a `?categoria=trabajos-especiales`.
  - Botón **"Configurar cookies"** en el footer de todas las páginas (Ley 21.719, Art. 12), además del enlace "Política de cookies".
  - Formulario de cotización por AJAX con validación en JS y PHP, modales de éxito y de límite de envíos, y borrador en `sessionStorage`.
  - Menú hamburguesa en móvil. El desplegable **Catálogo** lista todas las categorías del panel, también las vacías, y se actualiza solo.
  - Banner de cookies con Clarity, GA4 y GTM. No aparece en el login ni en el panel.
  - **Páginas legales versionadas dentro del tema** (`legal/*.html`): Política de Privacidad, Términos y Condiciones y **Política de cookies**. Al subir el ZIP se crean o actualizan solas, una vez por cada `TA_LEGAL_VERSION`, así que local y producción quedan iguales.
  - Tipografía Manrope local.
- **Plugin `jwt-gallery` (Todo Acrílico Panel):** **2.0.3**. Incluye:
  - Migración única del slug `vidrios` → `trabajos-especiales` (producción la conservaba).
  - `/galeria/` y `/galeria-panel/` **ocultos para Google**: fuera del sitemap y con `noindex`.
  - Login y panel con JWT (revocación por `jti`, un solo token válido por usuario).
  - Subida de imágenes locales (`wp-content/uploads/jg-gallery/`) y categorías dinámicas.
  - **Popovers de ayuda** con los límites de cada campo (Título 43, Descripción 120, Uso 43 y Medidas 20 caracteres; de 1 a 10 imágenes de hasta 8 MB, con .webp recomendado; categorías disponibles).
  - Modal **"Sesión cerrada"** cuando la sesión expira.
  - Sin archivos huérfanos al rechazar una pieza.
- **Producción:** `https://todoacrilico.cl/wp/` tiene el **tema 2.3.3** y el **plugin 2.0.1**, igual que local (verificado el 2026-11-04). Las tres páginas legales son idénticas a las de local (Privacidad 11 secciones, Términos 8, Cookies 10), el footer muestra "Política de cookies" y el menú Catálogo lista las categorías.
- **Analítica:** Microsoft Clarity (`z81r92medx`), Google Analytics 4 (`G-2KL8SPE9DY`, directo desde el tema) y Google Tag Manager (`GTM-K7FR5V9K`, para etiquetas adicionales). Solo se activan si el visitante acepta las cookies en el banner. Se cambian o desactivan en Apariencia → Personalizar → Analítica. **No agregar GA4 dentro de GTM** (duplicaría las visitas).
- **Correo del formulario:** `contacto@todoacrilico.cl`, vía WP Mail SMTP (Gmail OAuth). Si aparece "No se pudo enviar el mensaje", reconectar la cuenta de Google en WP Mail SMTP.
- **Pendiente del cliente para los textos legales:** nombre del representante legal, plazo de conservación (se puso 12 meses) y si se envía publicidad.
- **Rama de trabajo:** `feature/version8` (remoto `origin`).
- **Documentación** (todo en git; el repo es público, sin secretos): en la raíz `CLAUDE.md`, `SPEC.md`, `GEO.md`, `README.md` y `Docs_Seguridad.md`; `wp-content/themes/todo-acrilico/Docs_theme.md`; `wp-content/plugins/jwt-gallery/Docs_Plugin.md`. Credenciales: `CREDENCIALES.local` (no se sube).

## Panel
http://localhost/wordpress/proyecto3/index.php/galeria/

https://todoacrilico.cl/wp/galeria/


## usuario contraseña

Contraseña | *(no se escribe aquí: está en `CREDENCIALES.local`, archivo local que no se sube a git)*

Cuenta | Dónde

| Correto | Entorno | Contraseña |
| --- | --- | --- |
| `admin@todoacrilico.cl` | Local y producción | La de `CREDENCIALES.local` |
| `prn@todoacrilico.cl` | Producción | La del hosting, **no tocar** |

**Son dos cuentas distintas con contraseñas distintas**, por decisión del
cliente: la galería no debe compartir credencial con el acceso al sitio.

En **producción**, `admin@todoacrilico.cl` hay que crearla: Plugins → Todo
Acrílico-Panel → Ajustes → *Credenciales del panel*, con ese correo y esta
contraseña. La pantalla crea la cuenta y le asigna el rol que el panel exige.
`prn@todoacrilico.cl` es la cuenta real del sitio: **no se le cambia la
contraseña**, y su identificador puede no ser su correo, por lo que tampoco
sirve para entrar al login del panel.

Local y producción son bases de datos distintas: los usuarios no se comparten.

> **La contraseña ya no está en este archivo** (2026-11-06): README.md se respalda en el repositorio de GitHub, que es **público**. Está solo en `CREDENCIALES.local` (raíz de `proyecto3`, excluido de git) y conviene tenerla también en un gestor de contraseñas.
>
> **Si se olvida:** entra a WordPress con la cuenta de administrador del sitio → **Plugins → Todo Acrílico Panel → Ajustes → Credenciales del panel** → escribe `admin@todoacrilico.cl` y una contraseña nueva. La pantalla la cambia al instante (o crea la cuenta si no existe). Después actualiza `CREDENCIALES.local`.
>
> No subas `CREDENCIALES.local` ni `README.md` al servidor: en local los archivos de la raíz se pueden leer por HTTP; en producción no existen.


#instalar un SMTP 
/*
Así que el plugin recomendado es WP Mail SMTP (el más usado, gratuito, disponible en el repositorio oficial de WordPress). Se instala, se conecta a un proveedor real (Gmail, Outlook, SendGrid, Mailgun, etc.) con sus credenciales SMTP, y a partir de ahí wp_mail() empieza a entregar correos de verdad — sin tocar nada del códielelgo del formulario, ya que fue diseñado justo para eso.
*/


## Correo de destino del formulario
Ya está en producción: `ta_get_contact_email()` (functions.php) devuelve
`contacto@todoacrilico.cl` y la línea de pruebas con el Gmail personal quedó
comentada. **No hace falta editarla para publicar.** Para probar en local con
otra casilla, usa el filtro `ta_contact_email` en un plugin o mu-plugin, en
vez de cambiar la función.


## instalar 
### ninjaFirewall: Funciona como un plugin WAF para WordPress totalmente gratuito en su versión estándar desde el repositorio oficial.
### LiteSpeed Cache (LSCache): Es el plugin gratuito más potente y completo si tu proveedor de hosting utiliza un servidor LiteSpeed. Ofrece funciones avanzadas de caché, optimización de imágenes y minificación de código sin pagar nada
### WP Super Cache: Desarrollado por Automattic (los creadores de WordPress.com). Es completamente gratuito, extremadamente confiable y muy fácil de configurar para principiantes, ya que genera archivos HTML estáticos con un solo clic.W3 Total Cache: Ofrece un control absoluto y avanzado sobre la caché de páginas, bases de datos y objetos. Es totalmente gratuito en sus funciones principales, aunque requiere una curva de aprendizaje más alta debido a su gran cantidad de opciones.
### Wassap