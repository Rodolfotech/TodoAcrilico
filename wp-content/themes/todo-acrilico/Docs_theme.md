# Tema "Todo Acrílico" — Documentación

> **Ubicación (desde 2026-11-06):** este archivo vive en `wp-content/themes/todo-acrilico/` y está en git. El repositorio es **público**: no escribas aquí contraseñas ni claves (van en `CREDENCIALES.local` y `wp-config.php`). Los ZIP excluyen `*.md`.
>
> **IA / desarrollador: lee esto primero.**
> 1. Las reglas que no se deben romper están en **`CLAUDE.md`** (sección "Guía para IA").
> 2. El comportamiento aprobado del formulario, los modales, el menú (móvil y desplegable Catálogo), la analítica, las páginas legales y el panel está en **`SPEC.md`**.
> 3. Este archivo es una **bitácora histórica**: varias secciones de §1 tienen copias de código **anteriores** a la versión actual. El código real del tema siempre manda.
> 4. Lo más reciente está en **§4.110–§4.133**, el estado al 2026-11-06, tema **2.3.6**.

Tema de WordPress a medida para Todo Acrílico: header con logo propio, navegación (Inicio, Catálogo, Cuidados) y botón "Cotizar" (hamburguesa en móvil), footer, y una página "Catálogo" (slug `catalogo`, plantilla `page-nuestras-soluciones.php`) que muestra las categorías e imágenes publicadas en el plugin **JWT Gallery** (ver [Docs_Plugin.md](Docs_Plugin.md)).

- **Versión:** `TA_VERSION` **2.3.3** (`functions.php`); `style.css` Version: **2.3.3** (ambas coinciden). WordPress usa `TA_VERSION` para el cache-busting de CSS/JS. Textos legales: `TA_LEGAL_VERSION` **2026-11-04.1** (§4.129).
- **Text Domain:** `todo-acrilico`
- **Ubicación:** `wp-content/themes/todo-acrilico/`
- **Depende de:** el plugin `jwt-gallery` para el contenido de "Nuestras Soluciones" (CPT `jg_gallery_image` + taxonomía `jg_gallery_category`). Si el plugin no está activo, la página sigue funcionando pero muestra el estado vacío (ver §3).

## Estado actual en este entorno (proyecto3) — verificado 2026-11-04

| | |
|---|---|
| Tema | **Activo**, `todo-acrilico` **2.3.3** (`TA_VERSION` y la cabecera de `style.css` coinciden). Producción tiene la misma versión |
| Analítica | **Microsoft Clarity** (`z81r92medx`), **Google Analytics 4** (`G-2KL8SPE9DY`, directo con gtag.js) y **Google Tag Manager** (`GTM-K7FR5V9K`, para etiquetas adicionales, sin GA4), cargados solo con consentimiento desde el banner `#ta-consent` (§4.124–§4.126) |
| Plugin del catálogo | `jwt-gallery` (nombre visible "Todo Acrílico Panel") **2.0.1**, activo. Producción tiene la misma versión |
| Inicio | `http://localhost/wordpress/proyecto3/`, plantilla `front-page.php` |
| Catálogo | Página ID 14, slug **`catalogo`**. Usa la plantilla `page-nuestras-soluciones.php`, asignada por filtro y no por slug (§4.102) |
| Cuidados | Página ID 15, slug **`cuidados`**. Usa la plantilla `page-cuidados-del-acrilico.php`, asignada por filtro |
| Cotizar / Contacto | Página ID 16, slug **`cotizar`**. Usa la plantilla `page-contacto.php`. Su comportamiento está en **SPEC.md** |
| Páginas legales | Local: ID 102 `politica-de-privacidad`, ID 101 `terminos-y-condiciones` e ID 179 `politica-de-cookies`. **Producción:** ID 2190 `politica-privacidad`, ID 2193 `termino-y-condiciones` y `politica-de-cookies` (creada por el tema). El texto vive en `legal/*.html` y lo aplica `ta_sync_legal_pages()` (§4.129). Acordeón con índice (§4.116) |
| Menú `primary` | Inicio, Catálogo (desplegable con **todas** las categorías del plugin, también las vacías, `ta_get_catalog_categories()`, §4.130), Cuidados y Cotizar (botón negro). A 861px o menos se muestra como **hamburguesa** (§4.121) |
| Páginas del plugin (`/galeria/`, `/galeria-panel/`) | Usan su propia plantilla en blanco, independiente del tema (Docs_Plugin.md §1.8). Son privadas: sin banner de cookies ni analítica (`ta_is_private_page()`, §4.128) |
| Correo del formulario | `ta_get_contact_email()` → `contacto@todoacrilico.cl`; envío por WP Mail SMTP |
| Producción | `https://todoacrilico.cl/wp/` (WordPress en la subcarpeta `/wp/`) |

---|---|
| Tema | **Activo** (`wp_get_theme()` → `todo-acrilico`) |
| Título del sitio | "Todo Acrílico" (antes era "proyecto3", genérico — se actualizó junto con esta entrega) |
| Inicio | `http://localhost/wordpress/proyecto3/` |
| Catálogo (etiqueta del menú; slug de la página sigue siendo `nuestras-soluciones`) | `http://localhost/wordpress/proyecto3/index.php/nuestras-soluciones/` |
| Cuidados (etiqueta del menú; slug de la página sigue siendo `cuidados-del-acrilico`) | `http://localhost/wordpress/proyecto3/index.php/cuidados-del-acrilico/` |
| Contacto / Cotizar (página ID 16, **slug `cotizar`**, plantilla `page-contacto.php`) | `http://localhost/wordpress/proyecto3/index.php/cotizar/` — comportamiento del formulario en **SPEC.md** |
| Política de Privacidad (ID 102) / Términos y Condiciones (ID 101) | `.../index.php/politica-de-privacidad/` y `.../index.php/terminos-y-condiciones/` — acordeón con índice (§4.116) |
| Cotizar (botón del menú, sin página propia) | Enlaza a la página Contacto |
| Menú de navegación | "Menú principal", asignado a la ubicación `primary`; 5 ítems: Inicio, Catálogo, Cuidados, Contacto, Cotizar |
| Páginas del plugin (`/galeria/`, `/galeria-panel/`) | Sin cambios — siguen usando su propia plantilla en blanco, independiente del tema activo (ver Docs_Plugin.md §1.8) |

---

## 1. Estructura de archivos

```
todo-acrilico/
├── style.css                       # Cabecera del tema (metadatos) + todo el CSS
├── functions.php                   # Setup del tema, menú, helpers de la galería, formulario de contacto, walker del nav
├── header.php                      # <head>, apertura de <body>, header + nav
├── footer.php                      # footer + cierre de <body>
├── index.php                       # Fallback genérico (listado de entradas)
├── front-page.php                  # Plantilla de Inicio (home)
├── page.php                        # Plantilla genérica de página (ninguna página la usa ahora mismo)
├── page-nuestras-soluciones.php    # Plantilla de "Nuestras Soluciones" (categorías + imágenes)
├── page-contacto.php               # Plantilla de "Contacto" (info + formulario, §1.10, §4.41)
├── page-cuidados-del-acrilico.php  # Plantilla de "Cuidados del Acrílico" (guía + consejo + CTA, §1.11, §4.31)
└── assets/
    ├── js/
    │   ├── ta-carousel.js          # Mini-carrusel reutilizable (publicaciones con más de una foto, §1.9)
    │   └── ta-nav.js               # Botón del desplegable de "Catálogo" en pantallas táctiles (§1.12)
    └── img/
        └── logo.webp               # Logo del header y footer (descargado una vez, servido localmente)
```

13 archivos. WordPress selecciona automáticamente `page-{slug}.php` (por ejemplo `page-nuestras-soluciones.php`, `page-contacto.php` o `page-cuidados-del-acrilico.php`) antes que `page.php` para la página cuyo slug coincida — no hace falta asignar la plantilla manualmente desde "Atributos de página". Ya ninguna de las 4 páginas de navegación usa `page.php`; queda como fallback genérico para páginas futuras sin plantilla propia.

---

## 1.1 `style.css`

> **Nota (1.20.0):** la copia de código de esta sección puede ser anterior a los cambios del 2026-10-20 (§4.110–§4.117). El archivo real del tema es la referencia; los cambios recientes están descritos en esa bitácora y en `SPEC.md`.

Cabecera de metadatos obligatoria para que WordPress reconozca el tema, más todo el CSS del sitio (sin preprocesadores ni build step). Paleta: fondo gris muy claro, tarjetas blancas, acento teal (`--ta-accent: #03854a`, verde de marca — ver §4.35; antes era `#1f7a8c`, revertido). Tipografía: pila `system-ui` (sin fuentes externas, carga instantánea).

```css
/*
Theme Name:   TodoAcrílico
Description:  Tema a medida para Todo Acrílico — inicio, nuestras soluciones (categorías e imágenes de la galería), cuidados del acrílico y contacto.
Author:       dev@reparadores.cl
Version:      1.14.15
Requires at least: 6.0
Requires PHP: 7.2.24
License:      GPL-2.0-or-later
Text Domain:  todo-acrilico
*/

/* ---- reset & tokens ---- */

:root {
	--ta-ink: #1a1c1c;
	--ta-muted: #5b6469;
	--ta-border: #e0e4e3;
	--ta-surface: #ffffff;
	--ta-bg: #f5f7f6;
	--ta-accent: #03854a;
	--ta-accent-dark: #03854a;
	--ta-accent-ink: #ffffff;
	/* Ancho compartido por el navbar y las 2 franjas del footer, para que
	   siempre queden alineados — cambiar solo este valor los mueve a los 3. */
	--ta-shell-width: 1850px; // se modifica el ancho del navbar y footer
}

*,
*::before,
*::after {
	box-sizing: border-box;
}

/* ---- Manrope (tipografía del sitio, self-hosted en /fonts/manrope/) ---- */

@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 200;
	font-display: swap;
	src: url(fonts/manrope/Manrope-200.ttf) format("truetype");
}
@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 300;
	font-display: swap;
	src: url(fonts/manrope/Manrope-300.ttf) format("truetype");
}
@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 400;
	font-display: swap;
	src: url(fonts/manrope/Manrope-400.ttf) format("truetype");
}
@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 500;
	font-display: swap;
	src: url(fonts/manrope/Manrope-500.ttf) format("truetype");
}
@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 600;
	font-display: swap;
	src: url(fonts/manrope/Manrope-600.ttf) format("truetype");
}
@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 700;
	font-display: swap;
	src: url(fonts/manrope/Manrope-700.ttf) format("truetype");
}
@font-face {
	font-family: "Manrope";
	font-style: normal;
	font-weight: 800;
	font-display: swap;
	src: url(fonts/manrope/Manrope-800.ttf) format("truetype");
}

html {
	-webkit-text-size-adjust: 100%;
}

body {
	margin: 0;
	background: var(--ta-bg);
	color: var(--ta-ink);
	font-family: "Manrope", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
	font-size: 16px;
	line-height: 1.6;
}

/* Ocultar barra en todo el sitio (Chrome, Safari, Edge, Opera) */
html::-webkit-scrollbar,
body::-webkit-scrollbar {
    display: none !important;
}

/* Ocultar barra en todo el sitio (Firefox e Internet Explorer) */
html, body {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}




img {
	max-width: 100%;
	display: block;
}

a {
	color: var(--ta-accent);
}

h1,
h2,
h3,
h4 {
	line-height: 1.2;
	margin: 0 0 0.5em;
	text-wrap: balance;
}

.ta-container {
	max-width: var(--ta-shell-width);
	margin: 0 auto;
	padding: 0 24px;
	
}

/* ---- header / nav ---- */

.ta-header {
	background: var(--ta-surface);
	border-bottom: 1px solid var(--ta-border);
	position: sticky;
	top: 0;
	z-index: 10;
	/* Promueve el header a su propia capa de composición: en mobile, un
	   position:sticky que comparte capa con el contenido que scrollea
	   detrás puede repintarse un frame más tarde que ese contenido y
	   dejarlo asomar/parpadear encima del nav durante el scroll. Con esto
	   el navegador lo compone aparte y deja de "tearear". */
	will-change: transform;
	transform: translateZ(0);
	-webkit-transform: translateZ(0);
	backface-visibility: hidden;
	-webkit-backface-visibility: hidden;
}

/* Con sesión iniciada, la barra de administración de WordPress (#wpadminbar) es
   fija arriba y tiene z-index más alto que cualquier cosa del tema — si el navbar
   sigue pegado a top:0, queda tapado/cortado detrás de ella al hacer scroll. */
body.admin-bar .ta-header {
	top: 32px;
}

@media (max-width: 782px) {
	body.admin-bar .ta-header {
		top: 46px;
	}
}

/* WP core (wp-includes/css/admin-bar.css) cambia #wpadminbar de fixed a
   absolute por debajo de 600px: en ese ancho la barra deja de estar fija
   y se va con el scroll normal de la página. Si nuestro navbar sigue
   reservando 46px para ella, ese espacio queda vacío en cuanto se pasa
   la primera pantalla y deja asomar contenido ya scrolleado por encima
   del nav. Por eso acá se vuelve a top:0, igual que sin sesión iniciada. */
@media (max-width: 600px) {
	body.admin-bar .ta-header {
		top: 0;
	}
}

.ta-header-inner {
	max-width: var(--ta-shell-width);
	margin: 0 auto;
	padding: 36px 24px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 24px;
	flex-wrap: wrap;
}

.ta-logo {
	display: flex;
	align-items: center;
}

.ta-logo-img {
	height: 90px;
	width: auto;
	display: block;
}


.ta-nav-list {
	list-style: none;
	display: flex;
	gap: 6px;
	margin: 0;
	padding: 0;
	flex-wrap: wrap;
}

.ta-nav-list li {
	position: relative;
}

/* Desplegable de categorías bajo "Catálogo", armado en cada carga de página
   desde las categorías reales del plugin (functions.php: TA_Nav_Walker).
   Solo se abre con la clase .is-open (click en "Catálogo"/caret, ta-nav.js) —
   ni hover ni foco lo despliegan (ver ta-nav.js / Docs §1.12, §4.57). */
/* Estado cerrado por defecto */
.ta-nav-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 30;
    min-width: 220px;
    margin: 0;
    padding: 8px;
    list-style: none;
    background: var(--ta-surface);
    border: 1px solid var(--ta-border);
    border-radius: 8px;
    box-shadow: 0 12px 32px rgba(20, 22, 20, 0.12);
    opacity: 0;
    visibility: hidden;
    pointer-events: none; /* Evita clics invisibles mientras está cerrado */
    transform: translateY(-4px);
    transition: opacity 0.15s ease, transform 0.15s ease;
}

/* Estado abierto: únicamente con la clase .is-open (click) */
.ta-nav-list li.is-open > .ta-nav-dropdown {
    opacity: 1;
    visibility: visible;
    pointer-events: auto; /* Habilita los clics al abrir */
    transform: translateY(0);
}

.ta-nav-dropdown a {
	display: block;
	padding: 8px 12px;
	border-radius: 6px;
	color: var(--ta-ink);
	text-decoration: none;
	font-size: 0.9rem;
	font-weight: 500;
	white-space: nowrap;
}

.ta-nav-dropdown a:hover {
	background: var(--ta-bg);
	color: var(--ta-accent);
}

/* ">li >a" (no descendiente genérico "a"): así apunta solo a los links de
   primer nivel (Inicio, Catálogo, Cuidados…) y no pisa el estilo de los
   links del desplegable, que viven más anidados dentro de .ta-nav-dropdown
   pero igual calzaban con un simple ".ta-nav-list a". Con la misma
   especificidad y viniendo después en el archivo, esa colisión los dejaba
   con display:inline-block en vez de block, achicando el área de hover de
   cada categoría al ancho del texto en vez de ocupar toda la fila. */
.ta-nav-list > li > a {
	display: inline-block;
	padding: 8px 14px;
	border-radius: 6px;
	color: var(--ta-ink);
	text-decoration: none;
	font-size: 16px;
	font-weight: 400;
}

.ta-nav-list > li > a:hover,
.ta-nav-list > li.current-menu-item > a {
	background: var(--ta-bg);
	color: var(--ta-accent);
	font-weight: 700;
}

/* El link "Catálogo" reserva espacio a la derecha para el botón del caret
   (que es position:absolute y por lo tanto no empuja el ancho del <li> por
   su cuenta) — sin este padding el botón queda flotando ENCIMA de las
   últimas letras de "Catálogo" en vez de al lado, que es justo el bug
   reportado. */
.ta-nav-list .ta-nav-has-caret > a {
	padding-right: 34px;
}


/* Botón del caret: reemplaza la flecha decorativa en TODAS las pantallas
   (no solo touch) para que el comportamiento sea idéntico en cualquier
   ancho/dispositivo — nada de detección por media query "hover", que en
   DevTools y ciertos híbridos táctil+mouse da resultados inconsistentes.
   El botón y el link "Catálogo" abren/cierran el desplegable con un click
   (ta-nav.js) — el listado NO se muestra con hover. */
.ta-nav-caret-toggle {
	display: block;
	position: absolute;
	top: 0;
	right: 0;
	width: 34px;
	height: 100%;
	min-height: 40px;
	padding: 0;
	background: transparent;
	border: none;
	cursor: pointer;
}

.ta-nav-caret-toggle::after {
	content: "";
	position: absolute;
	top: 50%;
	left: 50%;
	width: 7px;
	height: 7px;
	margin: -6px 0 0 -5px;
	/* Verde de marca siempre, en los dos estados — no currentColor: el
	   botón ya no es parte del <a>, así que no hereda su color de hover. */
	border-right: 2px solid var(--ta-accent);
	border-bottom: 2px solid var(--ta-accent);
	transform: rotate(45deg);
	transition: transform 0.15s ease;
}

.ta-nav-has-caret.is-open .ta-nav-caret-toggle::after {
	transform: rotate(225deg);
	margin-top: -3px;
}

.ta-nav-list .ta-nav-cta {
	margin-left: 6px;
}

.ta-nav-list li.ta-nav-cta > a,
.ta-nav-list li.ta-nav-cta.current-menu-item > a {
	background: var(--ta-ink);
	color: #fff;
	border-radius: 6px;
}

.ta-nav-list li.ta-nav-cta > a:hover,
.ta-nav-list li.ta-nav-cta.current-menu-item > a:hover {
	background: var(--ta-accent-dark);
	color: #fff;
}

/* ---- Media Queries: Navegación Responsive ---- */

/* Tablet y Pantallas Medianas (<= 860px) */
@media (max-width: 860px) {
	.ta-header-inner {
		padding: 16px 20px;
		flex-direction: column;
		align-items: flex-start;
		gap: 16px;
	}

	.ta-logo-img {
		height: 60px; /* Reducción de escala del logo */
		width: auto;
	}

	.ta-nav {
		width: 100%;
	}

	.ta-nav-list {
		width: 100%;
		gap: 4px;
		justify-content: flex-start;
	}

	.ta-nav-list > li > a {
		padding: 8px 10px;
		font-size: 0.88rem;
	}

	/* Limita el submenú para que no se salga del margen de la pantalla */
	.ta-nav-dropdown {
		left: 0;
		right: auto;
		min-width: 200px;
		max-width: calc(100vw - 40px);
	}
}

/* Teléfonos Moviles (<= 480px) */
@media (max-width: 480px) {
	.ta-nav-list {
		flex-direction: column;
		align-items: stretch;
		gap: 2px;
	}

	.ta-nav-list > li {
		width: 100%;
	}

	.ta-nav-list > li > a {
		display: block;
		width: 100%;
		padding: 10px 12px;
	}

	.ta-nav-list .ta-nav-has-caret > a {
		padding-right: 44px; /* Otorga más espacio al botón caret en móviles */
	}

	/* Convierte el desplegable en un acordeón vertical dentro del menú */
	.ta-nav-dropdown {
		position: static;
		width: 100%;
		box-shadow: none;
		border: none;
		border-left: 2px solid var(--ta-accent);
		border-radius: 0;
		background: var(--ta-bg);
		margin: 4px 0 8px 12px;
		display: none;
		opacity: 1;
		visibility: visible;
		pointer-events: auto;
		transform: none;
	}

	/* Muestra el desplegable en modo acordeón únicamente cuando está activo */
	.ta-nav-list li.is-open > .ta-nav-dropdown {
		display: block;
	}
}

/* ---- main ---- */

.ta-main {
	min-height: 60vh;
}

/* ---- hero (front page) ---- */
.ta-hero {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    min-height: 60vh;
    background-size: cover;
    /* En pantallas grandes el sujeto de la foto queda a la derecha; en
       pantallas chicas se reencuadra en la media query de abajo para que
       no se recorte todo el motivo detrás del texto. */
    background-position: 100% center;
    background-repeat: no-repeat;
    box-sizing: border-box;
    padding: 64px 24px;
}

.ta-hero-inner {
	width: 100%;
	/* Mismo ancho/centrado que el resto de las secciones (header, footer,
	   features…) para que el bloque de texto quede alineado con ellas en
	   cualquier resolución, en vez de un offset fijo en px. */
	max-width: var(--ta-shell-width);
	margin: 0 auto;
}

.ta-hero-inner > * {
	/* Ocupa todo el ancho disponible del contenedor hasta un tope de 760px
	   (para dejar la foto visible a la derecha en pantallas grandes). Sin
	   piso fijo: en anchos intermedios (p. ej. 665px) se achica junto con
	   el contenedor en vez de quedar forzado a un mínimo de 620px. */
	max-width: min(100%, 760px);
}

.ta-hero-eyebrow {
	margin: 0 0 16px;
	font-size: 12px;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--ta-muted);
}

.ta-hero h1 {
    font-size: 80px; /* Tamaño fijo del título del banner */
    font-weight: 300; /* Hace la letra delgada y elegante como en la imagen */
    line-height: 100px; /* Interlineado fijo de 100px entre las frases */
    letter-spacing: -0.02em; /* Junta sutilmente las letras para mejorar el impacto visual */
    white-space: nowrap; /* "Soluciones en acrílico," queda en una sola línea */
    color: #1d1d1f; /* Un tono oscuro pero suave */
    margin-bottom: 34px;
}

.ta-hero-title-accent {
    display: block;
    color: var(--ta-accent); /* Tu variable para el verde */
    font-weight: 400; /* Le da un toque apenas más firme al verde si lo deseas, o déjalo en 300 para mantenerlo igual de delgado */
    margin-top: 4px; /* Separa milimétricamente la línea verde de la superior */
}

.ta-hero p {
    font-size: 18px; /* Tamaño fijo del párrafo del banner */
    font-weight: 400;         /* Peso regular */
    line-height: 1.5;         /* Altura de línea para que respire entre renglones */
    color: #4a5154;           /* Color gris oscuro suave, no negro puro */
    max-width: 100%;
    margin: 0 0 34px;         /* Margen inferior para separarlo limpiamente de los botones */
    letter-spacing: -0.01em;  /* Ajuste milimétrico opcional para mejorar la lectura */
}

.ta-hero-actions {
	display: flex;
	gap: 12px;
	flex-wrap: wrap;
}

.ta-hero-image-hint {
	margin: 20px 0 0;
	max-width: 46ch;
	font-size: 0.8rem;
	color: var(--ta-muted);
	border: 1px dashed var(--ta-border);
	border-radius: 8px;
	padding: 10px 14px;
	background: rgba(255, 255, 255, 0.6);
}

/* Tablet: el offset del texto se reduce gradualmente, el fondo se
   reencuadra un poco más hacia el centro para no perder el motivo. */
@media (max-width: 960px) {
	.ta-hero {
		background-position: 80% center;
	}
}

/* Mobile: layout de una sola columna a todo el ancho disponible, altura
   fluida (no fija en vh, para no dejar aire de más ni cortar el texto)
   y el recuadro de la foto centrado para que siga siendo reconocible
   detrás del degradado. */
@media (max-width: 640px) {
	.ta-hero {
		min-height: auto;
		padding: 48px 20px;
		background-position: 65% center;
	}

	.ta-hero-inner > * {
		max-width: 100%;
	}

	.ta-hero-actions {
		flex-direction: column;
		align-items: stretch;
	}

	.ta-hero-actions .ta-button {
		justify-content: center;
	}
}

/* .ta-hero--has-image{
	height: 800px;
} */

.ta-button {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 12px 22px;
	border-radius: 6px;
	border: 1px solid transparent;
	text-decoration: none;
	font-size: 12px;
	font-weight: 800;
}

.ta-button--accent {
	background: var(--ta-accent);
	color: var(--ta-accent-ink);
}

.ta-button--accent:hover {
	background: var(--ta-accent-dark);
}

.ta-button--dark {
	background: var(--ta-ink);
	color: #fff;

}

.ta-button--dark:hover {
	background: var(--ta-accent-dark);
}

.ta-button--outline {
	background: transparent;
	border-color: var(--ta-ink);
	color: var(--ta-ink);
}

.ta-button--outline:hover {
	background: var(--ta-accent-dark);
	color: #fff;
}

.ta-button--text {
	padding: 0;
	background: transparent;
	color: var(--ta-ink);
}

.ta-button--text:hover {
	color: var(--ta-accent);
}

.ta-button-icon {
	font-size: 1rem;
}

/* ---- feature grid (categorías, o cualquier grupo ícono+texto+link) ---- */

.ta-features {
	padding: 64px 24px;
}

/* Variante blanca (como el nav): usada en page-cuidados-del-acrilico.php para
   la "Guía de mantenimiento" (ta_render_feature_grid, parámetro $class). */
.ta-features--white {
	background: var(--ta-surface);
}

.ta-features-eyebrow {
	margin: 0 0 12px;
	font-size: 12px;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--ta-muted);
}

/* En el catálogo el eyebrow "Nuestro Catálogo" va semibold (pedido del usuario). */
.ta-features--catalog .ta-features-eyebrow {
	font-weight: 600;
}

/* En el catálogo el título va 48px light (pedido del usuario). */
.ta-features--catalog .ta-features-title {
	font-size: 48px;
	font-weight: 300; /* light */
}

.ta-features-title {
	font-size: 32px;
	font-weight: 400;
	margin: 0 0 16px;
	max-width: 34ch;
}

/* En la página de Cuidados el título de la guía es más grande: 48px regular
   (pedido del usuario). Se activa con el modifier del <section>. */
.ta-features--hero-title .ta-features-title {
	font-size: 48px;
}

/* En Cuidados los textos bajo los títulos de las cards van a 16px regular
   (la home mantiene 15px de §4.69). */
.ta-features--hero-title .ta-feature-card p {
	font-size: 16px;
	font-weight: 400;
}

.ta-features-description {
	margin: 0 0 40px;
	max-width: 100%;
	color: var(--ta-muted);
	font-size: 18px;
	font-weight: 400;
	line-height: 1.6;
}

.ta-features-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
	gap: 20px;
	margin-top: 40px;
}

.ta-feature-card {
	background: var(--ta-surface);
	border: 1px solid var(--ta-border);
	border-radius: 0px;
	padding: 28px 24px;
}

.ta-feature-icon {
	display: block;
	color: var(--ta-accent);
	margin-bottom: 20px;
}

.ta-feature-icon svg {
	width: 40px;
	height: 40px;
}

.ta-feature-card h3 {
	font-size: 24px;
	font-weight: 500; /* medium */
	margin: 0 0 10px;
}

.ta-feature-card p {
	margin: 0 0 18px;
	color: var(--ta-muted);
	font-size: 15px;
	line-height: 1.5;
    max-width: 28ch; /* Esto fuerza un ancho máximo de ~28 caracteres, haciendo que salte solo elegantemente */
}

/* ---- showcase grid (destacados, o cualquier grilla de fotos + texto) ---- */

.ta-showcase {
	padding: 64px 24px;
   background: var(--ta-surface); /* blanco, igual que el nav (.ta-header) */
}

.ta-showcase-header {
	display: flex;
	align-items: flex-end;
	justify-content: space-between;
	gap: 24px;
	flex-wrap: wrap;
	margin-bottom: 40px;
}

/* "Ver todo el catálogo" (botón text) del encabezado de Destacados: 12px
   extrabold y en verde de marca, como pide el usuario. Selector compuesto
   para ganarle la especificidad a .ta-button--text (color ink). */
.ta-button--text.ta-showcase-cta {
	font-size: 12px;
	font-weight: 800;
	color: var(--ta-accent);
}

.ta-showcase-eyebrow {
	margin: 0 0 12px;
	font-size: 12px;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--ta-muted);
}

.ta-showcase-title {
	font-size: 32px;
	font-weight: 400;
	margin: 0;
}

.ta-showcase-cta {
	font-size: 0.82rem;
	font-weight: 700;
	color: var(--ta-accent);
	text-decoration: underline;
	text-underline-offset: 3px;
}

.ta-showcase-cta:hover {
	color: var(--ta-accent-dark);
}

.ta-showcase-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
	gap: 28px;
}

.ta-showcase-figure {
	aspect-ratio: 4 / 3;
	border-radius: 0px;
	overflow: hidden;
	background: var(--ta-bg);
	margin-bottom: 18px;
}

.ta-showcase-figure img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}

.ta-showcase-figure-empty {
	width: 100%;
	height: 100%;
	background-image: repeating-linear-gradient(45deg, var(--ta-bg), var(--ta-bg) 10px, #e8ebea 10px, #e8ebea 20px);
}

.ta-showcase-card h3 {
	font-size: 20px;
	margin: 0 0 6px;
}

.ta-showcase-card p {
	margin: 0;
	color: var(--ta-muted);
	font-size: 15px;
	line-height: 1.5;
}

/* ---- story section (nuestra historia, o cualquier bloque foto + texto + stats) ---- */

.ta-story {
	padding: 64px 24px;
	background: var(--ta-surface); /* Esto usa la variable blanca (#ffffff)*/
}


.ta-story-inner {
	display: grid;
	grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
	gap: 56px;
	align-items: center;
}

.ta-story-inner--media-right .ta-story-media {
	order: 2;
}

.ta-story--panel {
	position: relative;
	padding: 64px 24px;
	background: var(--ta-surface); /* blanco: el gris es el bloque centrado de abajo */
}

/* Franja gris centrada con el MISMO ancho del contenido de .ta-container
   (la franja de las tarjetas de la guía): shell menos 24px por lado,
   calculado en % para que sea responsive en todos los anchos */
.ta-story--panel::before {
	content: "";
	position: absolute;
	top: 0;
	bottom: 0;
	left: 50%;
	transform: translateX(-50%);
	width: calc(100% - 48px);
	max-width: calc(var(--ta-shell-width) - 48px);
	background: var(--ta-bg);
}

/* El contenido va directo sobre la franja gris (sin tarjeta): ancho cómodo,
   centrado dentro del bloque gris */
.ta-story--panel .ta-story-inner {
	position: relative;
	margin-inline: auto;
	max-width: 1100px;
}

.ta-story--panel .ta-story-inner {
	max-width: 1100px;
	background: #ececea;
	border-radius: 0px;
	padding: 48px;
	gap: 40px;
}

.ta-story--panel .ta-story-media {
	border-radius: 0px;
}

/* 1. Forzamos a que TODO el bloque de la historia permita elementos flotantes */
.ta-story-inner,
.ta-story-media {
    position: relative !important;
    overflow: visible !important; /* Rompe cualquier candado de desborde */
}

.ta-story-media {
	position: relative;
	width: 100%;
	max-width: 100%;
	/* La altura se deriva de aspect-ratio en vez de un valor fijo en px:
	   así la foto guarda la misma proporción (no un recorte distinto) en
	   cualquier ancho de pantalla, de PC a mobile. */
	aspect-ratio: 4 / 3;
	border-radius: 14px;
	overflow: hidden;
	background: var(--ta-bg);
}

.ta-story-media img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	border-radius: 0px; /* Mantiene la foto con los bordes redondeados */

}

.ta-story-media-empty {
	width: 100%;
	height: 100%;
	background-image: repeating-linear-gradient(45deg, var(--ta-bg), var(--ta-bg) 10px, #e8ebea 10px, #e8ebea 20px);
}

.ta-story-badge {
	position: absolute;
	right: -10px;
	bottom: -30px;
	width: 150px;
	height: 170px;
	padding: 10px;
	background: var(--ta-surface);
	border-radius: 0px;
	box-shadow: 0 8px 24px rgba(20, 22, 20, 0.18);
	z-index: 10;
color: var(--ta-muted); /*fondo del logo que esta a pie de la imagen*/
}
.ta-story-badge img {
	width: 100%;
	height: 100%;
	object-fit: contain;
	border-radius: 0;    /* El logo no necesita bordes redondeados internos */
	
}

.ta-story-content .ta-story-eyebrow {
	margin: 0 0 12px;
	font-size: 12px;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--ta-muted);
}

/* En el panel "Consejo final del experto" (Cuidados) el eyebrow va semibold:
   igual especificidad, definido después para ganarle a la regla de arriba. */
.ta-story--panel .ta-story-eyebrow {
	font-weight: 600;
	font-size: 12px;
}

.ta-story-title {
	font-size: 48px;
	line-height: 1.2;
	font-weight: 400;
	margin: 0 0 24px;
	max-width: 22ch;
}

.ta-story-content p {
	margin: 0 0 18px;
	color: var(--ta-muted);
	font-size: 18px;
	font-weight: 400;
	line-height: 1.6;
	max-width: 56ch;
}

.ta-story-divider {
	margin: 28px 0 24px;
	border: none;
	border-top: 1px solid var(--ta-border);
}

.ta-story-stats {
	display: flex;
	gap: 48px;
	flex-wrap: wrap;
}

.ta-story-stat {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.ta-story-stat-value {
	font-size: 24px;
	font-weight: 500;
	color: var(--ta-accent);
}

.ta-story-stat-label {
	font-size: 12px;
	color: var(--ta-accent);
	font-weight: 800;
}

@media (max-width: 860px) {
	.ta-story-inner {
		grid-template-columns: 1fr;
	}

	/* En desktop media_position:'right' pone la foto después del texto
	   (order:2) para que quede visualmente a la derecha. En mobile, con
	   una sola columna, eso dejaría el texto arriba y la foto abajo —
	   distinto de las demás secciones tipo "historia" del sitio, donde la
	   foto siempre va primero. Se resetea para que todas se apilen igual. */
	.ta-story-inner--media-right .ta-story-media {
		order: initial;
	}

	.ta-story-badge {
		right: 16px;
		bottom: -16px;
	}
}

/* Mobile: la insignia con el logo se achica para no tapar media foto de
   ancho angosto — en desktop mide 150x170 sobre una foto mucho más ancha,
   pero a 375-640px eso es casi la mitad del recuadro. */
@media (max-width: 640px) {
	.ta-story-badge {
		width: 96px;
		height: 108px;
		right: 12px;
		bottom: -12px;
		padding: 6px;
	}
}

.ta-story--panel .ta-story-inner {
    max-width: 1100px; /* ancho cómodo centrado; el ancho de la franja lo da el ::before */
    background: transparent !important; /* sin tarjeta: el contenido va directo sobre el gris (§4.67) */
    border-radius: 0 !important;
    padding: 64px 48px;
    gap: 40px;
}

/* La caja de la imagen usa aspect-ratio (no un height fijo) y un ancho
   fluido con tope: así se comporta igual que la sección "historia" del
   home en cualquier pantalla, en vez de desbordar cuando la columna es
   más angosta que los 500px fijos que tenía antes. */
.ta-story--panel .ta-story-media {
    width: 100%;
    max-width: 500px;
    aspect-ratio: 4 / 3;
    border-radius: 0 !important;    /* Quita los bordes redondeados de la caja contenedora */
}

/* 3. Aseguramos que la imagen interna se adapte perfectamente sin deformarse */
.ta-story--panel .ta-story-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;              /* Hace que la foto llene el espacio de forma limpia */
    border-radius: 0 !important;    /* Quita cualquier borde redondeado*/

}

/* ---- cta section (llamado a la acción de cierre) ---- */

.ta-cta {
	padding: 72px 24px;
	background: var(--ta-surface); /* blanco, igual que el nav (.ta-header) */
}

.ta-cta-inner {
	max-width: 640px;
	margin: 0 auto;
	text-align: center;
}

.ta-cta-title {
	font-size: 48px;
	font-weight: 300; /* light */
	margin: 0 0 16px;
	white-space: nowrap; /* "¿Tienes una idea en mente?" en una sola línea */
}

.ta-cta-description {
	margin: 0 0 32px;
	color: var(--ta-muted);
	font-size: 18px;
	line-height: 1.6;
}

.ta-section {
	padding: 56px 24px;
}

.ta-section h2 {
	font-size: 1.6rem;
	margin-bottom: 8px;
}

.ta-section-intro {
	color: var(--ta-muted);
	max-width: 60ch;
	margin: 0 0 32px;
}

/* ---- generic page content ---- */

.ta-page-content {
	max-width: 720px;
	margin: 0 auto;
	padding: 56px 24px;
}

.ta-page-content h1 {
	font-size: 2rem;
	margin-bottom: 24px;
}

.ta-page-content .entry-content > * + * {
	margin-top: 1em;
}

/* ---- contenido de páginas legales / textos largos (page.php) ---- */

.ta-page-content .entry-content h2 {
	font-size: 1.25rem;
	font-weight: 600;
	margin: 28px 0 8px;
	color: var(--ta-ink);
}

.ta-page-content .entry-content h2:first-child {
	margin-top: 0;
}

.ta-page-content .entry-content p {
	font-size: 0.9375rem;
	line-height: 1.7;
	color: var(--ta-muted);
	margin: 0;
}

.ta-page-content .entry-content strong {
	color: var(--ta-ink);
	font-weight: 700;
}

.ta-page-content .entry-content table {
	width: 100%;
	border-collapse: collapse;
	margin: 16px 0;
	font-size: 0.875rem;
	
}

.ta-page-content .entry-content th {
	text-align: left;
	font-weight: 700;
	color: var(--ta-ink);
	background: var(--ta-bg);
	padding: 10px 12px;
	border: 1px solid var(--ta-border);
}

.ta-page-content .entry-content td {
	padding: 10px 12px;
	border: 1px solid var(--ta-border);
	color: var(--ta-muted);
}

.ta-page-content .entry-content ul {
	margin: 8px 0 0;
	padding-left: 24px;
	color: var(--ta-muted);
	font-size: 0.9375rem;
	line-height: 1.7;
}

.ta-page-content .entry-content li + li {
	margin-top: 6px;
}

/* ---- contacto: info + formulario ---- */

.ta-contact {
	padding: 64px 24px;
}

.ta-contact-inner {
	max-width: var(--ta-shell-width);
	margin: 0 auto;
	display: grid;
	grid-template-columns: minmax(0, 0.85fr) minmax(0, 1fr);
	gap: 64px;
	align-items: start;
}

.ta-contact-eyebrow {
	margin: 0 0 8px;
	font-size: 0.8rem;
	font-weight: 700;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--ta-muted);
}

.ta-contact-title {
	position: relative;
	font-size: clamp(2rem, 3.6vw, 2.6rem);
	margin: 0 0 18px;
}

/* Línea subrayada debajo de "Cuéntanos": una barra gruesa que llega hasta las
   letras "Cu". Usa `ch` relativo al tamaño del propio título (hereda su font),
   así el ancho escala con el clamp() en vez de quedar fijo. El `bottom`
   (~16px) deja un salto de línea: la barra queda despegada del texto (~8px
   debajo del descensor de la "u"), no pegada a las letras. */
.ta-contact-title::after {
	content: "";
	position: absolute;
	left: 0;
	bottom: -16px;
	width: 2.2ch;
	height: 4px;
	background: var(--ta-ink);
}

.ta-contact-divider {
	display: block;
	width: 40px;
	height: 2px;
	background: transparent;
	margin: 0 0 24px;
}

.ta-contact-description {
	margin: 0 0 36px;
	max-width: 42ch;
	color: var(--ta-muted);
	font-size: 0.98rem;
	line-height: 1.6;
}

.ta-contact-list {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 24px;
}

.ta-contact-list li {
	display: flex;
	align-items: flex-start;
	gap: 14px;
}

.ta-contact-icon {
	display: inline-flex;
	flex: none;
	margin-top: 2px;
	color: var(--ta-accent);
}

.ta-contact-label {
	margin: 0 0 4px;
	font-size: 0.75rem;
	font-weight: 700;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	color: var(--ta-muted);
}

.ta-contact-value {
	margin: 0;
	font-weight: 600;
	color: var(--ta-ink);
}

.ta-contact-value-sub {
	margin: 2px 0 0;
	color: var(--ta-muted);
	font-size: 0.92rem;
}

.ta-contact-form-card {
	background: var(--ta-surface);
	border: 1px solid var(--ta-border);
	border-radius: 0px;
	padding: 32px;
	max-width: 680px;
	/* Baja la tarjeta para que el formulario empiece a la misma altura que la
	   descripción de la columna izquierda ("Escríbenos con la mayor cantidad
	   de detalles…"): eyebrow + título + divisora de la columna izquierda. */
	margin-top: 137px;
}

.ta-contact-quote {
	position: relative;
	display: flex;
	gap: 14px;
	align-items: flex-start;
	padding: 12px 40px 14px 16px;
	margin-bottom: 20px;
	background: var(--ta-bg);
	border: 1px solid var(--ta-border);
}

.ta-contact-quote-remove {
	position: absolute;
	top: 8px;
	right: 8px;
	width: 24px;
	height: 24px;
	line-height: 1;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0;
	border: none;
	border-radius: 50%;
	background: transparent;
	color: var(--ta-muted);
	font-size: 1.25rem;
	cursor: pointer;
	transition: background 0.2s ease, color 0.2s ease;
}

.ta-contact-quote-remove:hover {
	background: rgba(0, 0, 0, 0.06);
	color: var(--ta-ink);
}

.ta-contact-quote-thumb {
	flex-shrink: 0;
	width: 72px;
	height: 54px;
	overflow: hidden;
	background: #eee;
	border: 1px solid var(--ta-border);
}

.ta-contact-quote-thumb img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}

.ta-contact-quote-text {
	margin: 0;
	font-size: 0.85rem;
	color: var(--ta-muted);
}

.ta-contact-quote-text strong {
	display: block;
	margin-bottom: 2px;
	color: var(--ta-ink);
}

.ta-contact-quote-text span {
	display: block;
}

.ta-contact-form {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.ta-contact-form-row {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
}

.ta-contact-form label {
	display: flex;
	flex-direction: column;
	gap: 8px;
	font-size: 0.85rem;
	font-weight: 600;
	color: var(--ta-ink);
	/* Sin esto, el label (item del grid de 2 columnas) no se achica más
	   allá del ancho mínimo intrínseco del <input> (~190px por defecto) y
	   el campo se desborda de la tarjeta en anchos intermedios en vez de
	   encogerse junto con la columna. */
	min-width: 0;
}

.ta-contact-form input[type="text"],
.ta-contact-form input[type="email"],
.ta-contact-form input[type="tel"],
.ta-contact-form textarea {
	width: 100%;
	box-sizing: border-box;
	font: inherit;
	font-weight: 400;
	padding: 10px 12px;
	border: 1px solid var(--ta-border);
	border-radius: 6px;
	color: var(--ta-ink);
	background: var(--ta-surface);
}

.ta-contact-form input[type="text"]:focus,
.ta-contact-form input[type="email"]:focus,
.ta-contact-form input[type="tel"]:focus,
.ta-contact-form textarea:focus {
	outline: 2px solid var(--ta-accent);
	outline-offset: 1px;
}

.ta-contact-form textarea {
	resize: vertical;
}

.ta-contact-char-limit {
	align-self: flex-end;
	font-size: 0.78rem;
	font-weight: 400;
	color: var(--ta-muted);
}

.ta-contact-files-note {
	margin: 0;
	font-size: 0.78rem;
	font-weight: 400;
	color: var(--ta-muted);
	line-height: 1.4;
}

.ta-contact-tel-wrap {
	display: flex;
	gap: 8px;
}

.ta-contact-tel-wrap input[type="tel"] {
	flex: 1;
	min-width: 0;
}

.ta-contact-tel-prefix {
	display: flex;
	align-items: center;
	gap: 6px;
	flex-shrink: 0;
	padding: 10px 12px;
	border: 1px solid var(--ta-border);
	border-radius: 6px;
	background: var(--ta-surface);
	color: var(--ta-ink);
	font-weight: 400;
	font-size: 0.85rem;
}

.ta-contact-flag {
	font-size: 1.1rem;
	line-height: 1;
}

.ta-contact-files input[type="file"] {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}

.ta-contact-file-upload {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 12px 14px;
	border: 1px dashed var(--ta-border);
	border-radius: 6px;
	background: var(--ta-bg);
	color: var(--ta-muted);
	font-weight: 600;
	font-size: 0.85rem;
	cursor: pointer;
	text-align: left;
	transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
}

.ta-contact-file-upload:hover {
	border-color: var(--ta-accent);
	color: var(--ta-ink);
	background: var(--ta-surface);
}

.ta-contact-file-status {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-top: 10px;
	padding: 8px 12px;
	border: 1px solid var(--ta-border);
	border-radius: 6px;
	background: var(--ta-bg);
	color: var(--ta-ink);
	font-size: 0.85rem;
}

.ta-contact-file-status[hidden] {
	display: none;
}

.ta-contact-file-actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.ta-contact-file-add {
	display: inline-flex;
	align-items: center;
	padding: 6px 14px;
	border: none;
	border-radius: 6px;
	background: var(--ta-ink);
	color: #fff;
	font-size: 0.8rem;
	font-weight: 600;
	line-height: 1;
	cursor: pointer;
	text-decoration: none;
	white-space: nowrap;
}

.ta-contact-file-add:hover {
	background: var(--ta-accent-dark);
	color: #fff;
}

.ta-contact-file-add:disabled {
	background: var(--ta-muted);
	color: #fff;
	cursor: not-allowed;
	opacity: 0.8;
}

.ta-contact-file-remove {
	display: inline-flex;
	align-items: center;
	padding: 6px 14px;
	border: none;
	border-radius: 6px;
	background: #c0392b;
	color: #fff;
	font-size: 0.8rem;
	font-weight: 600;
	line-height: 1;
	cursor: pointer;
	text-decoration: none;
	white-space: nowrap;
}

.ta-contact-file-remove:hover {
	background: #922b21;
	color: #fff;
}

.ta-contact-form .ta-contact-optin {
	display: flex;
	flex-direction: row;
	align-items: flex-start;
	gap: 8px;
	font-weight: 400;
	cursor: pointer;
}

.ta-contact-optin input[type="checkbox"] {
	width: auto;
	margin-top: 2px;
	accent-color: var(--ta-accent);
}

.ta-contact-optin span {
	line-height: 1.4;
}

.ta-contact-optin a {
	color: var(--ta-accent);
	text-decoration: underline;
	text-underline-offset: 2px;
}

.ta-contact-optin a:hover {
	color: var(--ta-accent-dark);
}

.ta-contact-form .ta-contact-accept {
	font-weight: 600;
}

.ta-contact-submit {
	justify-content: center;
	width: 100%;
}

.ta-contact-form-note {
	margin: 0;
	text-align: center;
	color: var(--ta-muted);
	font-size: 0.85rem;
}

.ta-contact-honeypot {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}

.ta-contact-alert {
	margin: 0 0 20px;
	padding: 12px 14px;
	border-radius: 6px;
	font-size: 0.9rem;
	font-weight: 600;
}

.ta-contact-alert--success {
	background: #e6f4ea;
	color: #1e7a34;
}

.ta-contact-alert--error {
	background: #fdecea;
	color: #b3261e;
}

.ta-contact-alert--info {
	display: flex;
	gap: 10px;
	align-items: flex-start;
	background: #f0f6f3;
	border: 1px solid #d6e6dc;
	color: var(--ta-ink);
	font-weight: 400;
}

.ta-contact-alert--info .ta-alert-icon {
	flex: 0 0 auto;
	display: inline-flex;
	margin-top: 2px;
	color: var(--ta-accent);
}

.ta-contact-alert--info .ta-alert-text {
	margin: 0;
	line-height: 1.5;
}

@media (max-width: 860px) {
	.ta-contact-inner {
		grid-template-columns: 1fr;
	}
	.ta-contact-form-card {
		margin-top: 0;
	}
}

@media (max-width: 560px) {
	.ta-contact-form-row {
		grid-template-columns: 1fr;
	}
}

/* ---- nuestras soluciones: categorías + imágenes ---- */

.ta-solutions-group {
	margin-bottom: 48px;
}

.ta-solutions-group h2 {
	font-size: 1.3rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	color: var(--ta-accent);
	border-bottom: 1px solid var(--ta-border);
	padding-bottom: 10px;
	margin-bottom: 20px;
}

.ta-filter-tabs {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin: 0 0 32px;
	border-bottom: 1px solid var(--ta-border);
	padding-bottom: 16px;
}

.ta-filter-tabs__tab {
	padding: 9px 14px;
	border: 1px solid var(--ta-border);
	border-radius: 999px;
	color: var(--ta-ink);
	font-size: 12px;
	font-weight: 400;
	text-decoration: none;
	transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.ta-filter-tabs__tab:hover,
.ta-filter-tabs__tab:focus-visible {
	border-color: var(--ta-accent);
	color: var(--ta-accent);
}

.ta-filter-tabs__tab.is-active {
	/* Mismo negro que el botón Cotizar (.ta-button--dark). */
	background: var(--ta-ink);
	border-color: var(--ta-ink);
	color: #fff;
}

.ta-solutions-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
	gap: 20px;
}

/* El CSS y la jerarquía .jg-pagination se cargan directamente desde el plugin.
   Solo definimos aquí sus tokens para no incluir el layout .jg-gallery completo. */
.ta-catalog-pagination {
	--jg-border: #dcdcd6;
	--jg-ink: #1a1c1c;
	--jg-muted: #6b6b64;
	--jg-accent: #03854a;
	--jg-surface-2: #f1f1ee;
}

.ta-catalog-pagination .jg-pagination-nav {
	margin-left: auto;
}

/* Estado activo explícito para que la página actual conserve el verde del panel. */
.ta-catalog-pagination .jg-pagination-page.is-active,
.ta-catalog-pagination .jg-pagination-page.is-active:hover,
.ta-catalog-pagination .jg-pagination-page.is-active:focus-visible {
	background: var(--jg-accent);
	color: #fff;
}

.ta-solution-card {
	background: var(--ta-surface);
	border: 1px solid var(--ta-border);
	border-radius: 0;
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.ta-solution-figure {
	position: relative;
	aspect-ratio: 4 / 3;
	background: #eee;
	overflow: hidden;
}

.ta-solution-figure img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.ta-solution-badge {
	position: absolute;
	top: 8px;
	left: 8px;
	padding: 3px 10px;
	background: var(--ta-bg);
	color: var(--ta-ink);
	font-size: 0.72rem;
	font-weight: 600;
	text-transform: uppercase;
	z-index: 1;
}

/* ---- mini-carrusel: publicaciones con más de una foto ---- */

.ta-carousel-slide[hidden] {
	display: none !important;
}

.ta-carousel-nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	display: flex;
	align-items: center;
	justify-content: center;
	width: 28px;
	height: 28px;
	padding: 0;
	border: none;
	border-radius: 50%;
	background: rgba(20, 22, 20, 0.55);
	color: #fff;
	cursor: pointer;
	opacity: 0;
	transition: opacity 0.15s ease;
}

.ta-solution-figure:hover .ta-carousel-nav,
.ta-solution-figure:focus-within .ta-carousel-nav {
	opacity: 1;
}

.ta-carousel-nav:hover {
	background: rgba(20, 22, 20, 0.8);
}

.ta-carousel-nav--prev {
	left: 6px;
}

.ta-carousel-nav--next {
	right: 6px;
}

.ta-carousel-counter {
	position: absolute;
	bottom: 8px;
	right: 8px;
	padding: 2px 8px;
	border-radius: 999px;
	background: rgba(20, 22, 20, 0.72);
	color: #fff;
	font-size: 0.7rem;
	font-weight: 600;
	font-variant-numeric: tabular-nums;
}

.ta-solution-body {
	padding: 14px 16px 18px;
	background: var(--ta-bg);
	flex-grow: 1;
}

.ta-solution-body h3 {
	font-size: 1rem;
	margin-bottom: 6px;
}

.ta-solution-body p {
	margin: 0;
	font-size: 0.9rem;
	color: var(--ta-muted);
}

.ta-solution-body .ta-solution-measurements {
	margin-top: 8px;
	color: var(--ta-ink);
}

.ta-solution-quote {
	margin-top: 14px;
	justify-content: center;
	width: 100%;
	padding: 10px 16px;
	font-size: 0.85rem;
	border: 1px solid var(--ta-muted);
	border-radius: 0;
	background: var(--ta-bg);
	color: var(--ta-ink);
}

.ta-solution-quote:hover {
	background: var(--ta-accent);
	color: #fff;
	border-color: var(--ta-accent-dark);
}

.ta-solution-quote:hover .ta-solution-quote-icon {
	background: var(--ta-accent);
	color: #fff;
}

.ta-solution-quote-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 18px;
	height: 18px;
	border-radius: 50%;
	background: #fff;
	color: var(--ta-ink);
	font-size: 0.85rem;
	font-weight: 700;
	line-height: 1;
}

.ta-empty-state {
	color: var(--ta-muted);
	background: var(--ta-surface);
	border: 1px dashed var(--ta-border);
	border-radius: 10px;
	padding: 32px;
	text-align: center;
}

/* ---- footer ---- */

.ta-footer {
	background: var(--ta-bg);
	color: var(--ta-ink);
	border-top: 1px solid var(--ta-border);
}

.ta-footer-top {
	max-width: var(--ta-shell-width);
	margin: 0 auto;
	padding: 56px 24px 40px;
	display: grid;
	grid-template-columns: 1.4fr 1fr 1fr 1fr;
	gap: 40px;
}

.ta-footer-logo {
	height: 90px;
	width: auto;
	display: block;
	margin-bottom: 16px;
}

.ta-footer-brand p {
	margin: 0;
	max-width: 32ch;
	color: var(--ta-muted);
	font-size: 15px;
	line-height: 1.6;
}

.ta-footer-heading {
	margin: 0 0 20px;
	font-size: 12px;
	font-weight: 800;
	letter-spacing: 0.06em;
	text-transform: uppercase;
}

.ta-footer-links,
.ta-footer-contact-list {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 14px;
}

.ta-footer-links a {
	color: var(--ta-muted);
	text-decoration: none;
	font-size: 15px;
}

.ta-footer-links a:hover {
	color: var(--ta-ink);
}

.ta-footer-contact-list li {
	display: flex;
	align-items: center;
	gap: 10px;
	color: var(--ta-muted);
	font-size: 15px;
}

.ta-footer-contact-list a {
	color: var(--ta-muted);
	text-decoration: none;
}

.ta-footer-contact-list a:hover {
	color: var(--ta-ink);
}

.ta-footer-icon {
	display: inline-flex;
	flex: none;
	color: var(--ta-accent);
}

.ta-footer-social {
	list-style: none;
	margin: 0;
	padding: 0;
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
}

.ta-footer-social a {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 44px;
	height: 44px;
	border: 1px solid var(--ta-border);
	border-radius: 8px;
	color: var(--ta-muted);
	text-decoration: none;
	transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.ta-footer-social a:hover {
	color: var(--ta-accent);
	border-color: var(--ta-accent);
	background: rgba(3, 133, 74, 0.06);
}

.ta-footer-social .ta-footer-icon {
	color: inherit;
}

.ta-footer-bottom {
	max-width: var(--ta-shell-width);
	margin: 0 auto;
	padding: 24px 24px 32px;
	border-top: 1px solid var(--ta-border);
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 16px;
}

.ta-footer-copy {
	margin: 0;
	font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
	font-size: 0.8rem;
	color: var(--ta-muted);
}

.ta-footer-legal {
	display: flex;
	align-items: center;
	gap: 24px;
	font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
	font-size: 0.8rem;
	color: var(--ta-muted);
}

.ta-footer-legal a {
	color: var(--ta-muted);
	text-decoration: none;
}

.ta-footer-legal a:hover {
	color: var(--ta-ink);
}

@media (max-width: 860px) {
	.ta-footer-top {
		grid-template-columns: 1fr;
	}
}

@media (max-width: 640px) {
	/* .ta-header-inner ya se resuelve arriba (bloque "Media Queries:
	   Navegación Responsive", @media max-width:860px) — se saca de acá
	   para no repetir el mismo selector en dos breakpoints distintos. */
	.ta-footer-bottom {
		flex-direction: column;
		align-items: flex-start;
	}
}

```

---

## 1.2 `functions.php`

> **Nota (1.20.0):** la copia de código de esta sección puede ser anterior a los cambios del 2026-10-20 (§4.110–§4.117). El archivo real del tema es la referencia; los cambios recientes están descritos en esa bitácora y en `SPEC.md`.

**Responsabilidad:** registra el soporte de `title-tag`, `post-thumbnails` y HTML5; registra la ubicación de menú `primary`; encola `style.css`, `ta-carousel.js` y `ta-nav.js` (y el estilo `jg-gallery` del plugin en el Catálogo); expone `ta_get_solutions_by_category()` (conecta el tema con el plugin JWT Gallery, ahora también con el `slug` de cada categoría); expone los componentes reutilizables `ta_button()`, `ta_icon()`, `ta_render_feature_grid()`, `ta_render_showcase_grid()`, `ta_render_story_section()` y `ta_render_cta_section()` (§4.11, §4.12, §4.13, §4.14, extendidos en §4.31); expone `ta_render_filter_tabs()` y `ta_get_pagination_pages()` (pestañas y paginador del Catálogo, §4.41); procesa el formulario de Contacto (`ta_handle_contact_submit()`, §1.10/§4.41/§4.42) — con celular chileno, adjuntos múltiples de imagen/PDF, la "pieza a cotizar" traída desde el catálogo, la **aceptación obligatoria de la Política y los Términos** (checkbox `acepto_terminos` con validación server-side, §4.50), el consentimiento de comunicaciones comerciales (en el correo indica si el cliente aceptó o no recibir ofertas, §4.42) y el correo HTML con plantilla de tabla estilo email (bloques DATOS DEL CLIENTE / DESCRIPCIÓN DEL PROYECTO / PIEZA A COTIZAR / adjuntos, §4.46); y registra en el Personalizador el control `ta_hero_image_url` (imagen del hero de Inicio, editable sin tocar código) junto con su getter `ta_get_hero_image_url()`. También define la clase `TA_Nav_Walker` (walker del menú principal) que inyecta el desplegable dinámico de categorías bajo "Catálogo" (§4.38, §4.39) y el botón `ta-nav-caret-toggle` que `ta-nav.js` controla.

**`ta_button( $label, $url, $args )`** — botón reutilizable: imprime un `<a>` con las clases `ta-button ta-button--{variant}`. `$args['variant']` acepta `'accent'` (teal, el botón original), `'dark'` (relleno oscuro), `'outline'` (contorno) o `'text'` (sin fondo ni borde, solo texto — usado en las tarjetas de categoría y en el link "Ver todo el catálogo"). `$args['icon']` agrega una flecha "→" al final; `$args['class']` permite una clase extra. Usado en el hero de `front-page.php` (§1.6, §4.10) para "Ver catálogo" (`dark` + flecha) y "Cotizar un proyecto" (`outline`), en cada tarjeta de `ta_render_feature_grid()` para "Ver piezas" (`text` + flecha), y en el encabezado de `ta_render_showcase_grid()` para "Ver todo el catálogo" (`text`, con la clase extra `ta-showcase-cta` para el subrayado verde).

**`ta_icon( $name, $size = 28 )`** — devuelve el SVG en línea (solo trazo, sin relleno, `currentColor`) de uno de 13 íconos predefinidos: `'grid'`, `'eye'`, `'shield'`, `'info'`, `'pane'` (tarjetas de categoría en Inicio, 28px por defecto); `'pin'`, `'mail'`, `'clock'` (columna "Contacto" del footer y página de Contacto, usados a 18–20px vía `$size`); y `'paperclip'`, `'thermometer'`, `'prohibited'`, `'spray'`, `'circular'` (agregados en §4.31 para la página "Cuidados del Acrílico" — adjuntar, evitar calor, no usar, limpieza, rayones). Cualquier nombre no reconocido devuelve un ícono genérico (círculo), para que nunca falte un ícono. `$size` reemplaza el `width`/`height="28"` del SVG por el valor pedido — por eso todos los íconos del banco están escritos a 28px de base.

**`ta_render_showcase_grid( $eyebrow, $title, $items, $cta = null )`** (§4.12) — componente reutilizable distinto de `ta_render_feature_grid()`: tarjetas grandes de **foto** de producto (sin ícono, sin borde) en vez de tarjetas con ícono. `$items` es un array de `array{title, description, image_url}`; `$cta` (opcional) es `array{label, url}` para un link alineado a la derecha del título, junto al encabezado (no dentro de cada tarjeta). Si `$item['image_url']` viene vacío, se muestra un placeholder rayado (`.ta-showcase-figure-empty`) en vez de un `<img>` roto — pensado para armar la sección antes de tener las fotos reales y completarlas después editando solo el array, sin tocar el markup.

**`ta_render_feature_grid( $eyebrow, $title, $items, $description = '' )`** (§4.11, extendido en §4.31) — componente reutilizable: imprime una sección con etiqueta pequeña + título + un párrafo opcional (`$description`, agregado en §4.31) + una grilla de tarjetas (ícono, título, bajada, botón "Ver piezas →" opcional en variante `text`). `$items` es un array de `array{icon, title, description, url?}` — `url` es opcional desde §4.31: si una tarjeta no lo trae, no se imprime el botón (tarjetas puramente informativas). Se usa en `front-page.php` para las 4 categorías destacadas (con `url` y enlaces a anclas en la página Catálogo, `#categoria-{slug}`, §1.8) y en `page-cuidados-del-acrilico.php` (§1.11) para la guía de mantenimiento (sin `url` en ninguna tarjeta, con `$description`) — no depende de nada específico de categorías: sirve para cualquier otra grilla de ícono+texto(+link) que se necesite después.

> **Cambio directo del usuario (§4.32):** `$description` y `$item['description']` pasaron de `esc_html()` a `wp_kses( $texto, array( 'br' => array() ) )` — permite `<br>` literal en el texto (para forzar saltos de línea manuales en las bajadas) sin abrir la puerta a HTML arbitrario: `wp_kses()` con esa lista blanca elimina cualquier otra etiqueta o atributo, así que sigue siendo seguro contra XSS aunque el contenido viniera de un origen no confiable.

**`ta_render_story_section( $args )`** (§4.13, extendido en §4.31) — componente reutilizable de bloque foto + texto: foto grande (con una insignia opcional del logo del tema superpuesta en la esquina, sin necesitar una URL nueva — reutiliza `assets/img/logo.webp`, header.php §1.3) y, al otro lado, eyebrow + título + párrafos + una rayita divisoria (siempre visible desde §4.31, antes solo aparecía si había estadísticas) + una fila opcional de estadísticas. `$args` acepta `eyebrow`, `title`, `paragraphs` (array de strings), `stats` (array de `array{value, label}`, puede venir vacío), `image_url`, `show_logo_badge` (bool, default `true`), y 2 parámetros nuevos de §4.31: `media_position` (`'left'` default o `'right'`, de qué lado va la foto) y `panel` (bool, default `false` — envuelve el contenido en una tarjeta con fondo gris y esquinas redondeadas en vez del layout a página completa). Igual que en `ta_render_showcase_grid()`, si `image_url` viene vacío se muestra un placeholder rayado (`.ta-story-media-empty`) en vez de un `<img>` roto. Usado en Inicio (`media_position` izquierda por defecto, `panel` desactivado) y en Cuidados (`media_position: 'right'`, `panel: true`, `show_logo_badge: false`, `stats: array()`). Cada párrafo también pasó de `esc_html()` a `wp_kses( $paragraph, array( 'br' => array() ) )` (mismo cambio de §4.32, misma justificación de seguridad).

**`ta_render_cta_section( $title, $description, $button_label, $button_url )`** (§4.14) — el componente reutilizable más simple de los cinco: título + bajada opcional + un botón `dark`, todo centrado. No tiene nada de "contacto" quemado — título, bajada, texto del botón y link son parámetros, así que sirve para cualquier llamado a la acción de cierre, no solo el de Inicio.

**`ta_customize_register()` / `ta_get_hero_image_url()`** — agregan un control de imagen (`WP_Customize_Image_Control`) a la sección "Identidad del sitio" del Personalizador, para que la imagen del hero de Inicio se cargue desde **Apariencia → Personalizar** sin editar ningún archivo. `ta_get_hero_image_url()` devuelve `''` si todavía no se cargó ninguna, y `front-page.php` muestra un estado vacío con instrucciones en ese caso, en vez de un `<img>` roto (mismo patrón defensivo que el estado vacío del plugin).

`ta_get_solutions_by_category()` primero comprueba que el CPT `jg_gallery_image` y la taxonomía `jg_gallery_category` existan (`post_type_exists()` / `taxonomy_exists()`) — si el plugin está desactivado, devuelve un array vacío en vez de fallar. Si existen, trae las categorías con `get_terms( ..., 'hide_empty' => true )` (así no se muestran categorías sin imágenes) y, por cada una, hace un `WP_Query` filtrado por esa categoría (`tax_query`), leyendo **todas** las URLs de imagen de cada publicación del mismo post meta que usa el plugin (`_jg_image_url`, meta repetible). Categorías que terminan sin ninguna publicación con al menos una imagen válida se omiten del resultado.

> **Nota (historial):** cuando el plugin pasó a permitir hasta 10 fotos por publicación (Docs_Plugin.md §12.19), esta función siguió leyendo `_jg_image_url` con `get_post_meta( $post->ID, '_jg_image_url', true )` — el `true` final ("dame un solo valor") hacía que devolviera solo la primera foto, y "Nuestras Soluciones" mostraba únicamente esa foto principal por tarjeta, sin carrusel. Al pedirse un carrusel reutilizable también en el tema (Docs_Plugin.md/bitácora §4.6 de este documento), se cambió `true` por `false` (`get_post_meta( $post->ID, '_jg_image_url', false )`, devuelve un array con todas las fotos) y se adaptó el markup de la tarjeta en `page-nuestras-soluciones.php` (§1.8) para renderizar el carrusel cuando hay más de una.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TA_VERSION', '1.18.21' );

function ta_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'primary' => 'Menú principal',
		)
	);
}
add_action( 'after_setup_theme', 'ta_setup' );

function ta_assets() {
	wp_enqueue_style( 'ta-style', get_stylesheet_uri(), array(), TA_VERSION );
	wp_enqueue_script( 'ta-carousel', get_template_directory_uri() . '/assets/js/ta-carousel.js', array(), TA_VERSION, true );
	wp_enqueue_script( 'ta-nav', get_template_directory_uri() . '/assets/js/ta-nav.js', array(), TA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ta_assets' );

/**
 * Botón reutilizable (CTA). Variantes: 'accent' (teal, la de siempre), 'dark' (relleno oscuro)
 * y 'outline' (contorno). $args['icon'] agrega una flecha "→" al final.
 */
function ta_button( $label, $url, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'variant' => 'accent',
			'icon'    => false,
			'class'   => '',
		)
	);

	$classes = array( 'ta-button', 'ta-button--' . $args['variant'] );
	if ( $args['class'] ) {
		$classes[] = $args['class'];
	}

	printf(
		'<a class="%1$s" href="%2$s">%3$s%4$s</a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $url ),
		esc_html( $label ),
		$args['icon'] ? '<span class="ta-button-icon" aria-hidden="true">→</span>' : ''
	);
}

/**
 * Imagen del hero de Inicio, editable desde Personalizar → Identidad del sitio
 * (sin tocar código). Devuelve '' si todavía no se ha cargado ninguna —
 * front-page.php muestra un estado vacío en ese caso, en vez de un <img> roto.
 */
function ta_customize_register( $wp_customize ) {
	$wp_customize->add_setting(
		'ta_hero_image_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'ta_hero_image_url',
			array(
				'label'    => 'Imagen del hero (Inicio)',
				'section'  => 'title_tagline',
				'settings' => 'ta_hero_image_url',
			)
		)
	);
}
add_action( 'customize_register', 'ta_customize_register' );

function ta_get_hero_image_url() {
    // Debe leer 'ta_hero_image_url' — es la key que registra ta_customize_register()
    // y bajo la que WP realmente guarda el valor (ver theme_mods_todo-acrilico en
    // wp_options). Antes leía 'ta_hero_image' (sin "_url"), una key que nunca se
    // escribe, así que cambiar la imagen desde Personalizar nunca tenía efecto.
    return get_theme_mod( 'ta_hero_image_url', 'https://i.postimg.cc/TYX3CTrK/home.webp' );
}
/**
 * Ícono SVG en línea (trazo, sin relleno) para las tarjetas de ta_render_feature_grid(),
 * el footer y la página de Contacto/Cuidados. Nombres disponibles: 'grid', 'eye', 'shield',
 * 'info', 'pane', 'pin', 'mail', 'clock', 'paperclip', 'thermometer', 'prohibited', 'spray',
 * 'circular', 'instagram', 'linkedin'. Cualquier otro nombre devuelve un ícono genérico
 * (círculo) para que nunca se quede algo sin ícono. `$size` (default 28) controla el
 * `width`/`height` del SVG devuelto.
 */
function ta_icon( $name, $size = 28 ) {
	$icons = array(
		'grid'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
		'eye'    => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
		'shield' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V6l7-3Z"/></svg>',
		'info'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="10" x2="12" y2="16"/><circle cx="12" cy="7" r="1" fill="currentColor" stroke="none"/></svg>',
		'pane'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 15l5-5 4 4 3-3 4 4"/></svg>',
		'pin'    => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.5-7-11a7 7 0 0 1 14 0c0 4.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
		'mail'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
		'clock'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>',
		'paperclip'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a5 5 0 0 1-7.07-7.07l9.19-9.19a3.5 3.5 0 0 1 4.95 4.95l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>',
		'thermometer' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4a2 2 0 0 0-4 0v10.5a4 4 0 1 0 4 0V4Z"/><line x1="12" y1="8" x2="12" y2="14"/></svg>',
		'prohibited' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="5.5" y1="5.5" x2="18.5" y2="18.5"/></svg>',
		'spray'      => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="9" width="7" height="12" rx="1"/><path d="M10 9V6a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v3"/><path d="M15 5h3"/><path d="M16 8h3"/><path d="M17 3h2"/></svg>',
		'circular'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 3.5 7.1"/><path d="M3 17v-5h5"/></svg>',
		'instagram'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37Z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
		'linkedin'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6Z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
	);

	$svg = isset( $icons[ $name ] ) ? $icons[ $name ] : '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>';

	if ( 28 !== (int) $size ) {
		$svg = str_replace( 'width="28" height="28"', 'width="' . (int) $size . '" height="' . (int) $size . '"', $svg );
	}

	return $svg;
}

/**
 * Grilla de tarjetas reutilizable (ícono + título + bajada + link opcional "Ver piezas →").
 * $items: array de array{icon: string (SVG en línea), title: string, description: string, url?: string}.
 * `url` es opcional — si una tarjeta no lo trae, no se imprime el botón (tarjetas puramente
 * informativas, ej. la guía de cuidados). $description (opcional) agrega un párrafo entre
 * el título de la sección y la grilla. Usado en front-page.php para las 4 categorías
 * destacadas y en page-cuidados-del-acrilico.php para la guía de mantenimiento.
 * $class (opcional) agrega una clase al <section> (p. ej. 'ta-features--white').
 */
function ta_render_feature_grid( $eyebrow, $title, $items, $description = '', $class = '' ) {
    $section_class = 'ta-features' . ( $class ? ' ' . esc_attr( $class ) : '' );
    ?>
    <section class="<?php echo $section_class; ?>">
        <div class="ta-container">
            <p class="ta-features-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
            <h2 class="ta-features-title"><?php echo esc_html( $title ); ?></h2>
            <?php if ( $description ) : ?>
                <!-- CAMBIO: Habilitado wp_kses para permitir <br> en la descripción general de la grilla -->
                <p class="ta-features-description"><?php echo wp_kses( $description, array( 'br' => array() ) ); ?></p>
            <?php endif; ?>
            <div class="ta-features-grid">
                <?php foreach ( $items as $item ) : ?>
                    <article class="ta-feature-card">
                        <span class="ta-feature-icon" aria-hidden="true"><?php echo $item['icon']; ?></span>
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                        
                        <!-- CAMBIO: Habilitado wp_kses para permitir <br> en la descripción de cada tarjeta -->
                        <p><?php echo wp_kses( $item['description'], array( 'br' => array() ) ); ?></p>
                        
                        <?php if ( ! empty( $item['url'] ) ) : ?>
                            <?php
                            ta_button(
                                'Ver piezas',
                                $item['url'],
                                array(
                                    'variant' => 'text',
                                    'icon'    => true,
                                )
                            );
                            ?>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Grilla de "destacados" reutilizable: encabezado (eyebrow + título + link opcional
 * "Ver todo el catálogo" alineado a la derecha) y tarjetas grandes de foto + título +
 * bajada, sin ícono ni borde — pensada para fotos de producto en vez de íconos.
 *
 * $items: array de array{title: string, description: string, image_url: string}.
 * $cta (opcional): array{label: string, url: string} para el link del encabezado.
 *
 * Si `image_url` viene vacío, se muestra un placeholder rayado en vez de un <img> roto —
 * así se puede armar la sección antes de tener las fotos reales y completarlas después
 * editando solo el array de `$items`, sin tocar el markup.
 */
function ta_render_showcase_grid( $eyebrow, $title, $items, $cta = null ) {
    ?>
    <section class="ta-showcase">
        <div class="ta-container">
            <div class="ta-showcase-header">
                <div>
                    <p class="ta-showcase-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
                    <h2 class="ta-showcase-title"><?php echo esc_html( $title ); ?></h2>
                </div>
                <?php if ( $cta ) : ?>
                    <?php
                    ta_button(
                        $cta['label'],
                        $cta['url'],
                        array(
                            'variant' => 'text',
                            'class'   => 'ta-showcase-cta',
                        )
                    );
                    ?>
                <?php endif; ?>
            </div>
            <div class="ta-showcase-grid">
                <?php foreach ( $items as $item ) : ?>
                    <article class="ta-showcase-card">
                        <div class="ta-showcase-figure">
                            <?php if ( ! empty( $item['image_url'] ) ) : ?>
                                <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="ta-showcase-figure-empty" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                        <p><?php echo esc_html( $item['description'] ); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}
/**
 * Sección "nuestra historia" reutilizable: foto grande (con una insignia opcional del
 * logo superpuesta en la esquina) y, al otro lado, eyebrow + título + párrafos + una
 * fila opcional de estadísticas (ej. "5k+ Proyectos realizados").
 *
 * $args:
 * - eyebrow (string), title (string), paragraphs (array de string)
 * - stats (array de array{value: string, label: string}) — si viene vacío, no se
 *   imprime la fila de estadísticas (la rayita divisoria sí se imprime siempre,
 *   después de los párrafos).
 * - image_url (string) — si viene vacío, se muestra un placeholder rayado en vez de
 *   un <img> roto, para poder armar la sección antes de tener la foto real.
 * - show_logo_badge (bool, default true) — usa el logo del tema (assets/img/logo.webp,
 *   header.php §1.3), no una URL nueva.
 * - media_position ('left'|'right', default 'left') — de qué lado va la foto.
 * - panel (bool, default false) — envuelve el contenido en una tarjeta con fondo e
 *   inset, para secciones tipo "consejo destacado" en vez del layout a página completa.
 */

/**
 * Busca esta función en tu functions.php o inc/template-tags.php
 */
function ta_render_story_section( $args ) {
    $args = wp_parse_args(
        $args,
        array(
            'eyebrow'         => '',
            'title'           => '',
            'paragraphs'      => array(),
            'stats'           => array(),
            'image_url'       => '',
            'show_logo_badge' => true,
            'media_position'  => 'left',
            'panel'           => false,
        )
    );

    $section_classes = array( 'ta-story' );
    if ( $args['panel'] ) {
        $section_classes[] = 'ta-story--panel';
    }

    $inner_classes = array( 'ta-container', 'ta-story-inner' );
    if ( 'right' === $args['media_position'] ) {
        $inner_classes[] = 'ta-story-inner--media-right';
    }
    ?>
    <section class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>">
        <div class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>">
            <div class="ta-story-media">
                <?php if ( $args['image_url'] ) : ?>
                    <img src="<?php echo esc_url( $args['image_url'] ); ?>" alt="<?php echo esc_attr( $args['title'] ); ?>" loading="lazy">
                <?php else : ?>
                    <div class="ta-story-media-empty" aria-hidden="true"></div>
                <?php endif; ?>
                <?php if ( $args['show_logo_badge'] ) : ?>
                    <div class="ta-story-badge">
                        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ta-story-content">
                <p class="ta-story-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
                <h2 class="ta-story-title"><?php echo esc_html( $args['title'] ); ?></h2>
                
                <!-- ======================================================= -->
                <!-- ¡AQUÍ VA EL CÓDIGO! Reemplaza el foreach anterior por este: -->
                <?php foreach ( $args['paragraphs'] as $paragraph ) : ?>
                    <p><?php echo wp_kses( $paragraph, array( 'br' => array() ) ); ?></p>
                <?php endforeach; ?>
                <!-- ======================================================= -->
                
                <hr class="ta-story-divider">
                <?php if ( $args['stats'] ) : ?>
                    <div class="ta-story-stats">
                        <?php foreach ( $args['stats'] as $stat ) : ?>
                            <div class="ta-story-stat">
                                <span class="ta-story-stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
                                <span class="ta-story-stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Sección de cierre reutilizable (llamado a la acción): título + bajada opcional +
 * un botón, todo centrado. Pensada para invitar a contactar/cotizar al final de
 * una página, pero sin nada específico de "contacto" — el título, la bajada, el
 * texto del botón y el link son parámetros.
 */
function ta_render_cta_section( $title, $description, $button_label, $button_url ) {
	?>
	<section class="ta-cta">
		<div class="ta-container ta-cta-inner">
			<h2 class="ta-cta-title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $description ) : ?>
				<p class="ta-cta-description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<?php ta_button( $button_label, $button_url, array( 'variant' => 'dark' ) ); ?>
		</div>
	</section>
	<?php
}

/**
 * Correo donde llegan los mensajes del formulario de Contacto (page-contacto.php,
 * §1.10). Filtrable con `ta_contact_email` para no tener que tocar código si cambia.
 */
function ta_get_contact_email() {
	// return apply_filters( 'ta_contact_email', 'contacto@todoacrilico.cl' );
	return apply_filters( 'ta_contact_email', 'rodolfo.parada.gonzalez@gmail.com' );
}


define( 'TA_CONTACT_MAX_FILE_SIZE', 5 * 1024 * 1024 ); // 5 MB por adjunto.
define( 'TA_CONTACT_MAX_FILES', 5 ); // Hasta 5 archivos adjuntos.

/**
 * Manejador del formulario de Contacto. Registrado tanto para visitantes sin sesión
 * (`_nopriv_`) como con sesión — es un formulario público, tiene que aceptar los dos.
 * Valida/sanitiza cada campo, revisa nonce + honeypot, maneja el adjunto con cuidado
 * (extensión + contenido real de imagen + tamaño máximo) y redirige de vuelta a la
 * página de Contacto con un parámetro en la URL (éxito/error) en vez de recargar en
 * blanco — así funciona sin JavaScript y sin reenvíos accidentales al refrescar.
 */
add_action( 'admin_post_nopriv_ta_contact_submit', 'ta_handle_contact_submit' );
add_action( 'admin_post_ta_contact_submit', 'ta_handle_contact_submit' );

function ta_handle_contact_submit() {
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/contacto/' );

	$adjunto_paths = array();
	$attachments    = array();

	$fail = function ( $reason ) use ( $redirect_to, &$adjunto_paths ) {
		foreach ( $adjunto_paths as $adjunto_path ) {
			if ( $adjunto_path && file_exists( $adjunto_path ) ) {
				wp_delete_file( $adjunto_path );
			}
		}
		wp_safe_redirect(
			add_query_arg(
				array(
					'ta_contact' => 'error',
					'reason'     => $reason,
				),
				$redirect_to
			)
		);
		exit;
	};

	if ( ! isset( $_POST['ta_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ta_contact_nonce'] ) ), 'ta_contact_submit' ) ) {
		$fail( 'nonce' );
	}

	// Honeypot: un campo oculto que solo un bot completaría. Si viene lleno, se
	// descarta en silencio simulando éxito — no le da pistas a quien lo esté probando.
	if ( ! empty( $_POST['ta_contact_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'ta_contact', 'success', $redirect_to ) );
		exit;
	}

	// Aceptación obligatoria de la Política de Privacidad y los Términos y
	// condiciones: sin el checkbox marcado no se envía la cotización.
	if ( empty( $_POST['acepto_terminos'] ) ) {
		$fail( 'terminos' );
	}

	$nombre      = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$celular     = isset( $_POST['celular'] ) ? sanitize_text_field( wp_unslash( $_POST['celular'] ) ) : '';
	$descripcion = isset( $_POST['descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ) ) : '';
	$acepta_ofertas = empty( $_POST['acepta_ofertas'] ) ? '0' : '1';
	$adjunto_ref = isset( $_POST['adjunto_referencia'] ) ? esc_url_raw( wp_unslash( $_POST['adjunto_referencia'] ) ) : '';
	$adjunto_ref = ( $adjunto_ref && wp_http_validate_url( $adjunto_ref ) ) ? $adjunto_ref : '';
	$adjunto_tit  = isset( $_POST['adjunto_titulo'] ) ? sanitize_text_field( wp_unslash( $_POST['adjunto_titulo'] ) ) : '';
	$adjunto_tit  = $adjunto_tit ? wp_strip_all_tags( trim( $adjunto_tit ) ) : '';
	$adjunto_desc = isset( $_POST['adjunto_descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_POST['adjunto_descripcion'] ) ) : '';
	$adjunto_desc = $adjunto_desc ? wp_strip_all_tags( trim( $adjunto_desc ) ) : '';
	$adjunto_med  = isset( $_POST['adjunto_medidas'] ) ? sanitize_text_field( wp_unslash( $_POST['adjunto_medidas'] ) ) : '';
	$adjunto_med  = $adjunto_med ? trim( $adjunto_med ) : '';

	if ( '' === $nombre || '' === $descripcion ) {
		$fail( 'campos' );
	}

	if ( '' === $email || ! is_email( $email ) ) {
		$fail( 'email' );
	}

	// El dominio del correo debe estar dentro de los permitidos: .cl, .com, .net u .org.
	$email_domain = strtolower( substr( strrchr( $email, '@' ), 1 ) );
	if ( ! preg_match( '/\.(cl|com|net|org)$/', $email_domain ) ) {
		$fail( 'email' );
	}

	// Celular chileno obligatorio: ocho dígitos móviles tras el prefijo +56 9.
	// El campo del formulario entrega 8 dígitos (sin el 9, que va en el prefijo);
	// por compatibilidad se tolera también el formato con 9 inicial.
	$celular_digits = preg_replace( '/[^\d]/', '', $celular );
	$celular_digits = preg_replace( '/^56/', '', $celular_digits );
	if ( preg_match( '/^9\d{8}$/', $celular_digits ) ) {
		$celular_digits = substr( $celular_digits, 1 );
	}
	if ( ! preg_match( '/^[2-9]\d{7}$/', $celular_digits ) ) {
		$fail( 'celular' );
	}
	$celular = '+56 9 ' . $celular_digits;

	// Adjuntos opcionales (varias imágenes o PDFs). Con el input `multiple`, PHP
	// recibe $_FILES['adjunto'] como arrays. Se normaliza a una lista de archivos
	// y se valida cada uno (extensión + contenido real: getimagesize para
	// imágenes, MIME %PDF para los PDF).
	$submit_files = array();
	if ( isset( $_FILES['adjunto'] ) && is_array( $_FILES['adjunto']['name'] ) ) {
		for ( $i = 0, $n = count( $_FILES['adjunto']['name'] ); $i < $n; $i++ ) {
			if ( UPLOAD_ERR_NO_FILE === $_FILES['adjunto']['error'][ $i ] ) {
				continue;
			}
			$submit_files[] = array(
				'name'  => $_FILES['adjunto']['name'][ $i ],
				'tmp'   => $_FILES['adjunto']['tmp_name'][ $i ],
				'error' => $_FILES['adjunto']['error'][ $i ],
				'size'  => $_FILES['adjunto']['size'][ $i ],
			);
		}
	}

	if ( count( $submit_files ) > TA_CONTACT_MAX_FILES ) {
		$fail( 'archivos_muchos' );
	}

	$allowed_ext = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf' );

	foreach ( $submit_files as $f ) {
		if ( UPLOAD_ERR_OK !== $f['error'] ) {
			$fail( 'archivo' );
		}

		if ( $f['size'] > TA_CONTACT_MAX_FILE_SIZE ) {
			$fail( 'archivo_grande' );
		}

		$ext = strtolower( pathinfo( sanitize_file_name( $f['name'] ), PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, $allowed_ext, true ) ) {
			$fail( 'archivo_tipo' );
		}

		// Contenido real: una imagen con getimagesize; un PDF por su cabecera %PDF.
		if ( 'pdf' === $ext ) {
			$mime = function_exists( 'mime_content_type' ) ? mime_content_type( $f['tmp'] ) : '';
			if ( stripos( (string) $mime, 'application/pdf' ) === false ) {
				$fail( 'archivo_tipo' );
			}
		} elseif ( ! @getimagesize( $f['tmp'] ) ) {
			$fail( 'archivo_tipo' );
		}

		$upload_dir = wp_upload_dir();
		$target_dir = trailingslashit( $upload_dir['basedir'] ) . 'ta-contacto-tmp/';
		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		$adjunto_path = $target_dir . wp_unique_filename( $target_dir, sanitize_file_name( $f['name'] ) );

		// move_uploaded_file() verifica por su cuenta que el archivo venga de una
		// subida HTTP real — no acepta rutas arbitrarias aunque alguien falsifique $_FILES.
		if ( ! move_uploaded_file( $f['tmp'], $adjunto_path ) ) {
			$fail( 'archivo' );
		}

		$attachments[]  = $adjunto_path;
		$adjunto_paths[] = $adjunto_path;
	}

	$subject = sprintf( 'Nuevo mensaje de contacto — %s', $nombre );

	// Cuerpo HTML con el estilo de email definido (tablas de 600px, tonos
	// neutros). Todos los valores dinámicos van escapados (esc_html / esc_url /
	// esc_attr); la imagen de la pieza a cotizar se incrusta por su URL pública
	// ya validada con wp_http_validate_url() — más seguro y compatible con
	// webmail que un CID, y sin datos personales incrustados.
	$html_body = '<!DOCTYPE html>'
		. '<html lang="es"><head><meta charset="UTF-8">'
		. '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
		. '<title>Nuevo mensaje de contacto</title></head>'
		. '<body style="margin: 0; padding: 20px; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #333333;">'
		// Contenedor principal.
		. '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">'
		// Encabezado / título.
		. '<tr><td style="padding: 24px 24px 16px 24px;">'
		. '<h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;">Nuevo mensaje de contacto — <span style="font-weight: 400;">' . esc_html( $nombre ) . '</span></h2>'
		. '</td></tr>'
		// DATOS DEL CLIENTE.
		. '<tr><td style="padding: 0 24px 20px 24px;">'
		. '<p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">DATOS DEL CLIENTE</p>'
		. '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border-top: 1px solid #e2e8f0;">'
		. '<tr style="border-bottom: 1px solid #f1f5f9;"><td width="28%" style="padding: 10px 0; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">NOMBRE</td>'
		. '<td style="padding: 10px 0; font-size: 14px; color: #1e293b;">' . esc_html( $nombre ) . '</td></tr>'
		. '<tr style="border-bottom: 1px solid #f1f5f9;"><td width="28%" style="padding: 10px 0; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">CELULAR</td>'
		. '<td style="padding: 10px 0; font-size: 14px; color: #1e293b;">' . esc_html( $celular ) . '</td></tr>'
		. '<tr style="border-bottom: 1px solid #f1f5f9;"><td width="28%" style="padding: 10px 0; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">CORREO</td>'
		. '<td style="padding: 10px 0; font-size: 14px; color: #1e293b;">' . esc_html( $email ) . '</td></tr>'
		. '</table>'
		// Consentimiento: la línea siempre se muestra, con check (aceptó) o cruz
		// (no aceptó). Verde si aceptó, gris neutro si no.
		. '<p style="margin: 8px 0 0 0; font-size: 12px; color: ' . ( '1' === $acepta_ofertas ? '#15903e' : '#64748b' ) . ';">'
		. ( '1' === $acepta_ofertas ? '✓ Aceptó recibir ofertas y comunicaciones comerciales.' : '✗ No aceptó recibir ofertas y comunicaciones comerciales.' )
		. '</p>'
		. '</td></tr>'
		// DESCRIPCIÓN DEL PROYECTO.
		. '<tr><td style="padding: 0 24px 24px 24px;">'
		. '<p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">DESCRIPCIÓN DEL PROYECTO</p>'
		. '<div style="font-size: 14px; line-height: 1.5; color: #334155;">' . nl2br( esc_html( $descripcion ) ) . '</div>'
		. '</td></tr>';

	// Información de la pieza a cotizar, pasada automáticamente al hacer clic en
	// "Cotizar esta pieza" en el catálogo (page-nuestras-soluciones.php).
	if ( $adjunto_ref || $adjunto_tit || $adjunto_desc || $adjunto_med ) {
		$html_body .= '<tr><td style="padding: 0 24px 24px 24px;">'
			. '<p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">PIEZA A COTIZAR</p>';
		if ( $adjunto_tit ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Título:</strong> ' . esc_html( $adjunto_tit ) . '</p>';
		}
		if ( $adjunto_med ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Medidas:</strong> ' . esc_html( $adjunto_med ) . '</p>';
		}
		if ( $adjunto_desc ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Descripción:</strong> ' . nl2br( esc_html( $adjunto_desc ) ) . '</p>';
		}
		if ( $adjunto_ref ) {
			$html_body .= '<div style="margin-top: 8px; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; background-color: #000000; text-align: center;">'
				. '<img src="' . esc_url( $adjunto_ref ) . '" alt="' . esc_attr( $adjunto_tit ? $adjunto_tit : 'Imagen de la pieza a cotizar' ) . '" style="display: block; width: 100%; max-width: 100%; height: auto;">'
				. '<div style="padding: 8px 12px; background-color: #ffffff; text-align: left; font-size: 12px; color: #475569; border-top: 1px solid #e2e8f0;">' . esc_html( $adjunto_tit ? $adjunto_tit : 'Imagen de la pieza a cotizar' ) . '</div>'
				. '</div>'
				. '<p style="margin: 8px 0 0; font-size: 12px; color: #64748b;">Ver imagen: <a href="' . esc_url( $adjunto_ref ) . '" style="color: #15903e;">' . esc_html( $adjunto_ref ) . '</a></p>';
		}
		$html_body .= '</td></tr>';
	}

	// Archivos adjuntos del formulario: resumen estilizado (los archivos reales
	// viajan adjuntos al correo con wp_mail(); no se incrustan en línea).
	if ( $adjunto_paths ) {
		$html_body .= '<tr><td style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background-color: #fafbfc;">'
			. '<p style="margin: 0; font-size: 12px; color: #64748b;">📎 <strong>' . count( $adjunto_paths ) . ( count( $adjunto_paths ) === 1 ? ' archivo adjunto' : ' archivos adjuntos' ) . '</strong> &nbsp;·&nbsp; Analizados automáticamente</p>'
			. '<p style="margin: 6px 0 0; font-size: 12px; color: #475569;">' . esc_html( implode( ' · ', array_map( 'basename', $adjunto_paths ) ) ) . '</p>'
			. '</td></tr>';
	}

	// Cierre del contenedor.
	$html_body .= '</table></body></html>';

	// Sin "From" propio a propósito: dejarlo así es lo que hace que un plugin SMTP
	// (WP Mail SMTP, etc.) controle el remitente sin que este código le compita.
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $nombre . ' <' . $email . '>',
	);

	$sent = wp_mail( ta_get_contact_email(), $subject, $html_body, $headers, $attachments );

	foreach ( $adjunto_paths as $adjunto_path ) {
		if ( $adjunto_path && file_exists( $adjunto_path ) ) {
			wp_delete_file( $adjunto_path );
		}
	}

	if ( ! $sent ) {
		$fail( 'envio' );
	}

	wp_safe_redirect( add_query_arg( 'ta_contact', 'success', $redirect_to ) );
	exit;
}

/**
 * Categorías de la galería (plugin JWT Gallery) con sus imágenes publicadas,
 * para usarlas en page-nuestras-soluciones.php. Devuelve un array vacío si el
 * plugin no está activo, en vez de fallar.
 *
 * Cada publicación puede tener varias fotos (`_jg_image_url` es meta repetible,
 * ver Docs_Plugin.md §12.19); por eso `images` es un array de URLs, no una sola.
 *
 * @return array<int, array{name: string, slug: string, images: array<int, array{title: string, description: string, images: array<int, string>}>}>
 */
function ta_get_solutions_by_category() {
	if ( ! post_type_exists( 'jg_gallery_image' ) || ! taxonomy_exists( 'jg_gallery_category' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'jg_gallery_category',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$groups = array();

	foreach ( $terms as $term ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'jg_gallery_image',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'tax_query'      => array(
					array(
						'taxonomy' => 'jg_gallery_category',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			)
		);

		if ( ! $query->have_posts() ) {
			continue;
		}

		$images = array();
		foreach ( $query->posts as $post ) {
			$urls = get_post_meta( $post->ID, '_jg_image_url', false );
			$urls = is_array( $urls ) ? array_values( array_filter( $urls ) ) : array();
			if ( empty( $urls ) ) {
				continue;
			}
			$images[] = array(
				'title'       => get_the_title( $post ),
				'description' => $post->post_content,
				'images'      => $urls,
			);
		}

		if ( empty( $images ) ) {
			continue;
		}

		$groups[] = array(
			'name'   => $term->name,
			'slug'   => $term->slug,
			'images' => $images,
		);
	}

	return $groups;
}

/**
 * Walker del menú principal: le agrega al ítem "Catálogo" (el que apunta a
 * "Nuestras Soluciones") un desplegable con un link por cada categoría de
 * `ta_get_solutions_by_category()` — las mismas categorías con imágenes
 * publicadas que arma las secciones de esa página (con id="categoria-{slug}",
 * page-nuestras-soluciones.php §1.8). Al reusar esa función, el desplegable
 * queda siempre sincronizado con el plugin sin tocar el menú a mano: una
 * categoría nueva con al menos una imagen publicada aparece sola, y una que
 * se vacía o se borra desaparece sola, en la siguiente carga de página.
 */
/**
 * Walker del menú principal: le agrega al ítem "Catálogo" (el que apunta a
 * "Nuestras Soluciones") un desplegable con un link por cada categoría de
 * `ta_get_solutions_by_category()`.
 */
class TA_Nav_Walker extends Walker_Nav_Menu {
    private ?int $solutions_page_id = null;

    private function get_solutions_page_id() {
        if ( null === $this->solutions_page_id ) {
            $page                    = ta_find_page( array( 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ) );
            $this->solutions_page_id = $page ? $page->ID : 0;
        }
        return $this->solutions_page_id;
    }

    // 1. Agregamos la clase 'ta-nav-has-caret' al <li> de Catálogo si tiene categorías
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        if ( 0 === $depth && 'page' === $item->object && (int) $item->object_id === $this->get_solutions_page_id() ) {
            $groups = ta_get_solutions_by_category();
            if ( ! empty( $groups ) ) {
                $item->classes[] = 'ta-nav-has-caret';
            }
        }
        parent::start_el( $output, $item, $depth, $args, $id );
    }

    // 2. Inyectamos el botón flecha y el desplegable <ul class="ta-nav-dropdown">
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( 0 === $depth && 'page' === $item->object && (int) $item->object_id === $this->get_solutions_page_id() ) {
            $groups = ta_get_solutions_by_category();

            if ( ! empty( $groups ) ) {
                $catalog_url = get_permalink( $this->get_solutions_page_id() );

                $output .= '<button type="button" class="ta-nav-caret-toggle" aria-expanded="false" aria-label="Mostrar categorías de catálogo"></button>';
                $output .= '<ul class="ta-nav-dropdown">';
                $output .= sprintf(
                    '<li><a href="%1$s">%2$s</a></li>',
                    esc_url( $catalog_url ),
                    esc_html__( 'Todas las piezas', 'todo-acrilico' )
                );
                foreach ( $groups as $group ) {
                    $output .= sprintf(
                        '<li><a href="%1$s">%2$s</a></li>',
                        esc_url( add_query_arg( 'categoria', $group['slug'], $catalog_url ) ),
                        esc_html( $group['name'] )
                    );
                }
                $output .= '</ul>';
            }
        }

        parent::end_el( $output, $item, $depth, $args );
    }
}
```

> Nota técnica: `jg_gallery_image` se registra con `public => false` en el plugin, pero eso no le impide a `WP_Query` encontrarlo desde el tema — ese flag solo afecta permalinks propios, el admin UI y la exposición en REST, no las consultas directas por `post_type` desde PHP.

---

## 1.3 `header.php`

> **Nota (2.0.1):** desde §4.121 el header incluye el botón hamburguesa `.ta-nav-toggle` antes del `<nav id="ta-primary-nav">`, y desde §4.110 las metas GEO. Si la copia de abajo no los muestra, es anterior: créele al archivo real.

Abre el documento HTML, llama a `wp_head()`, y arma el header visual: logo (enlaza a `home_url()`, imagen `assets/img/logo.webp`) + `wp_nav_menu()` apuntando a la ubicación `primary`. `fallback_cb => 'ta_nav_menu_fallback'` (§4.100): si no hay ningún menú asignado a la ubicación —lo típico tras una migración, porque `nav_menu_locations` no siempre se copia— WordPress no dibuja nada y el nav quedaría vacío. Con ese fallback el header dibuja siempre los cuatro destinos del sitio (Inicio, Catálogo, Cuidados, Cotizar) con las mismas clases que el menú real, incluido el desplegable de categorías en "Catálogo".

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="ta-header">
	<div class="ta-header-inner">
		<a class="ta-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="ta-logo-img"
				width="90"
				height="89"
			>
		</a>
		<nav class="ta-nav" aria-label="Menú principal">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ta-nav-list',
					'fallback_cb'    => 'ta_nav_menu_fallback',
					'walker'         => new TA_Nav_Walker(),
				)
			);
			?>
		</nav>
	</div>
</header>

<main class="ta-main">
```

---

## 1.4 `footer.php`

Cierra `<main>`, imprime el footer, y cierra el documento con `wp_footer()`.

**Rediseño (§4.15):** el footer ya no reutiliza el menú `primary` (`wp_nav_menu()`) ni el fondo oscuro que tenía antes. Ahora es un footer de 3 columnas sobre fondo claro (`var(--ta-bg)`, consistente con el resto del sitio, no `var(--ta-ink)`):
- **Marca:** logo del tema (mismo `assets/img/logo.webp` del header, §1.3) + una bajada corta.
- **Explorar:** 4 links armados a mano con `ta_find_page()` (§4.101) — Inicio, Catálogo, Cuidados, Contacto — a propósito **no** usa `wp_nav_menu()` del menú `primary`, porque ese menú ya tiene 5 ítems (incluye "Cotizar", §4.9) y el footer solo debía mostrar 4, según la captura de referencia. Desde 1.18.63 no depende de que la página conserve su slug canónico.
- **Contacto:** 3 líneas con ícono (`ta_icon('pin'|'mail'|'clock', 18)`) — ubicación, correo (`mailto:`) y horario. Los datos (`Santiago, Chile`, `contacto@todoacrilico.cl`, `Lun a Vie · 9:00 — 18:00`) son texto de ejemplo tomado de la captura del usuario, **no verificados como los datos reales del negocio** — hay que confirmarlos antes de publicar.
- **Síguenos:** columna agregada a la derecha de Contacto (§4.54), con heading "Síguenos" y dos íconos de redes sociales lado a lado en la fila `.ta-footer-social`: Instagram (`ta_icon('instagram', 20)` → `https://www.instagram.com/todoacrilico_cl/`) y LinkedIn (`ta_icon('linkedin', 20)` → `https://www.linkedin.com/company/todo-acrílico/`), ambos `target="_blank" rel="noopener noreferrer"` con `aria-label` y `aria-hidden` en el ícono.

Abajo, una barra inferior con: copyright (`&copy; {año actual} TODO Acrílico SpA` — el nombre legal con "SpA" viene de la captura, es distinto del `bloginfo('name')` que usa el resto del sitio, "Todo Acrílico") y tres textos finales: "Política de privacidad" (enlaza a la página legal `politica-de-privacidad`, §4.48), "Términos y condiciones" (enlaza a la página legal `terminos-y-condiciones`, §4.47) y "Diseñado con foco en usabilidad" (texto plano, no un link).

> **Nota (historial):** al construir el footer (§4.15) se habían agregado también 2 íconos circulares decorativos (`undo`/`redo`) junto al copyright, porque la captura de referencia los mostraba pero no dejaba clara su función — se replicaron solo visualmente, sin comportamiento. El usuario pidió quitarlos (§4.16) y se eliminaron por completo: el markup, la CSS (`.ta-footer-deco`/`.ta-footer-deco-btn`) y los íconos `'undo'`/`'redo'` del banco de `ta_icon()` (§1.2), que no se usaban en ningún otro lugar del tema.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ta_footer_solutions = ta_find_page( array( 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ) );
$ta_footer_cuidados  = ta_find_page( array( 'cuidados-del-acrilico', 'cuidados', 'cuidado' ) );
$ta_footer_contacto  = ta_find_page( array( 'contacto', 'contact' ) );
$ta_footer_terminos  = ta_find_page( array( 'terminos-y-condiciones', 'termino' ) );
$ta_footer_privacidad = ta_find_page( array( 'politica-de-privacidad', 'privacidad' ) );
?>
</main>

<footer class="ta-footer">
	<div class="ta-footer-top">
		<div class="ta-footer-col ta-footer-brand">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="ta-footer-logo"
				width="90"
				height="89"
			>
			<p>Más de 5 años diseñando y fabricando soluciones en acrílico a medida en Santiago, Chile.</p>
		</div>

		<div class="ta-footer-col">
			<h3 class="ta-footer-heading">Explorar</h3>
			<ul class="ta-footer-links">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
				<li><a href="<?php echo esc_url( $ta_footer_solutions ? get_permalink( $ta_footer_solutions ) : home_url( '/' ) ); ?>">Catálogo</a></li>
				<li><a href="<?php echo esc_url( $ta_footer_cuidados ? get_permalink( $ta_footer_cuidados ) : home_url( '/' ) ); ?>">Cuidados</a></li>
				<li><a href="<?php echo esc_url( $ta_footer_contacto ? get_permalink( $ta_footer_contacto ) : home_url( '/' ) ); ?>">Contacto</a></li>
			</ul>
		</div>

		<div class="ta-footer-col">
			<h3 class="ta-footer-heading">Contacto</h3>
			<ul class="ta-footer-contact-list">
				<li>
					<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'pin', 18 ); ?></span>
					Santiago, Chile
				</li>
				<li>
					<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'mail', 18 ); ?></span>
					<a href="mailto:contacto@todoacrilico.cl">contacto@todoacrilico.cl</a>
				</li>
				<li>
					<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'clock', 18 ); ?></span>
					Lun a Vie · 9:00 — 18:00
				</li>
			</ul>
		</div>

		<div class="ta-footer-col">
			<h3 class="ta-footer-heading">Síguenos</h3>
			<ul class="ta-footer-social">
				<li>
					<a href="https://www.instagram.com/todoacrilico_cl/" target="_blank" rel="noopener noreferrer" aria-label="Instagram de Todo Acrílico">
						<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'instagram', 20 ); ?></span>
					</a>
				</li>
				<li>
					<a href="https://www.linkedin.com/company/todo-acrílico/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn de Todo Acrílico">
						<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'linkedin', 20 ); ?></span>
					</a>
				</li>
			</ul>
		</div>
	</div>

	<div class="ta-footer-bottom">
		<p class="ta-footer-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> TODO Acrílico SpA</p>
		<div class="ta-footer-legal">
			<a href="<?php echo esc_url( $ta_footer_privacidad ? get_permalink( $ta_footer_privacidad ) : home_url( '/' ) ); ?>">Política de privacidad</a>
			<a href="<?php echo esc_url( $ta_footer_terminos ? get_permalink( $ta_footer_terminos ) : home_url( '/' ) ); ?>">Términos y condiciones</a>
			<span>Diseñado con foco en usabilidad</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
```

---

## 1.5 `index.php`

Plantilla de respaldo genérica (obligatoria en cualquier tema clásico de WordPress) — un listado simple de entradas con título enlazado y extracto. No es el foco de este tema (no hay sección de blog en la navegación), pero evita errores si WordPress necesita renderizar algo que no calza con ninguna otra plantilla (por ejemplo, una búsqueda o un archivo de entradas).

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="ta-page-content">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<div class="entry-content"><?php the_excerpt(); ?></div>
			</article>
			<?php
		endwhile;
		?>
	<?php else : ?>
		<p>No hay contenido para mostrar.</p>
	<?php endif; ?>
</div>

<?php
get_footer();
```

---

## 1.6 `front-page.php` (Inicio)

WordPress usa automáticamente `front-page.php` para la portada del sitio (`/`) sin necesidad de una página "Inicio" propia ni de configurar "Ajustes → Lectura" — por eso el ítem "Inicio" del menú es un enlace directo a `home_url('/')` en vez de apuntar a una página (ver §2).

Muestra un hero a todo el ancho de la sección, pero con el **contenido de texto en una columna angosta y alineada a la izquierda** (`.ta-hero-inner`, `max-width: 620px`, §4.24 — a propósito no usa `--ta-shell-width` como el resto de los componentes, §4.22: en un hero con foto de fondo, estirar el texto a todo el ancho lo deja viéndose perdido/pequeño) — eyebrow, título de dos líneas, bajada y dos botones (`ta_button()`, §1.2) enlazados dinámicamente a las páginas `nuestras-soluciones` y `contacto` vía `ta_find_page()` (§4.101 — slug canónico o título, sin depender del slug de apariencia). La imagen del hero (`ta_get_hero_image_url()`, §1.2) se aplica como **`background-image` de la propia `<section class="ta-hero">`**, no como un `<img>` aparte al lado del texto, con `background-size: contain; background-position: right center;` (§4.24 — no `cover`, que hacía ver la foto recortada/muy agrandada) y un degradado gris superpuesto (más opaco a la izquierda, donde está el texto, más transparente hacia la derecha) para que el texto siga siendo legible encima de cualquier foto. Si todavía no se cargó ninguna imagen, no se agrega el atributo `style` (la sección usa el degradado gris de respaldo, más oscuro que el `--ta-bg` general del sitio) y se muestra un aviso corto (`.ta-hero-image-hint`) con instrucciones para cargarla.

Debajo del hero, `ta_render_feature_grid()` (§1.2, §4.11) imprime la sección "Categorías" con 4 tarjetas fijas (Organizar/Exhibir/Proteger/Informar — copy y orden tomados de una captura de referencia del usuario), cada una con ícono (`ta_icon()`), título, bajada y un botón "Ver piezas →" que enlaza a `nuestras-soluciones` con un ancla por categoría (`#categoria-{slug}`, agregada en `page-nuestras-soluciones.php`, §1.8). Las 4 categorías están fijas en el código (no las 5 que existen en la taxonomía — la quinta, "vidrios", no aparece en la referencia) para que coincidan con el título "Cuatro maneras de trabajar el acrílico."; si en el futuro se agregan o quitan categorías destacadas, hay que editar el array de `$items` en `front-page.php` a mano.

> **Nota (historial):** antes de este cambio, debajo del hero había una vitrina automática con las primeras 4 imágenes de la primera categoría no vacía (`$groups[0]['images']`). Se reemplazó por completo por `ta_render_feature_grid()`. De paso se encontró y corrigió un bug: esa vitrina vieja leía `$image['url']`, una clave que ya no existe desde que `ta_get_solutions_by_category()` devuelve `$image['images']` (array) en vez de una URL única (§4.6/Docs_Plugin.md §12.19) — el `<img>` se renderizaba con `src=""` (ver §4.10).

Luego, `ta_render_showcase_grid()` (§1.2, §4.12) imprime la sección "Destacados" — "Piezas más solicitadas.", con link "Ver todo el catálogo" enlazado a `nuestras-soluciones`, y 3 tarjetas grandes (Buzón / Organizador de escritorio / Bandeja modular, copy tomado de una segunda captura de referencia del usuario), cada una con su foto real (URLs agregadas en §4.27).

Luego, `ta_render_story_section()` (§1.2, §4.13) imprime "Nuestra historia": foto grande a la izquierda (con la insignia del logo del tema superpuesta en la esquina), eyebrow, título ("Más de 20 años trabajando el acrílico a medida."), 2 párrafos y 2 estadísticas (5k+ Proyectos realizados, 20+ Años de experiencia) — copy tomado de una tercera captura de referencia del usuario, con la foto real del equipo agregada en §4.28.

Al final, `ta_render_cta_section()` (§1.2, §4.14) imprime el llamado a la acción de cierre: "¿Tienes una idea en mente?", una bajada, y el botón "Hablemos de tu proyecto" (variante `dark`) enlazado a la página `contacto` — copy tomado de una cuarta captura de referencia del usuario. Es el componente más simple de los cinco de Inicio: sin imagen, sin estado vacío que manejar.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$solutions_page  = ta_find_page( array( 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ) );
$solutions_url   = $solutions_page ? get_permalink( $solutions_page ) : home_url( '/' );
$contacto_page   = ta_find_page( array( 'contacto', 'contact' ) );
$contacto_url    = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/' );
$hero_image_url  = ta_get_hero_image_url();
?>

<section class="ta-hero<?php echo $hero_image_url ? ' ta-hero--has-image' : ''; ?>" <?php if ( $hero_image_url ) : ?>style="background-image: linear-gradient(90deg, rgba(221,225,224,0.97) 0%, rgba(221,225,224,0.9) 35%, rgba(221,225,224,0.5) 100%), url('<?php echo esc_url( $hero_image_url ); ?>');"<?php endif; ?>>
	<div class="ta-hero-inner">
		<p class="ta-hero-eyebrow">Taller de acrílico · Chile · Desde 2019</p>
		<h1>
			Soluciones en acrílico,
			<span class="ta-hero-title-accent">pensadas para durar.</span>
		</h1>
		<p>Diseñamos y fabricamos piezas para organizar, exhibir,proteger e
		   informar. Cada solución se adapta a tu espacio y a tu marca con la precisión
		   técnica que nos caracteriza.
		</p>
		<div class="ta-hero-actions">
			<?php
			ta_button(
				'Ver catálogo',
				$solutions_url,
				array(
					'variant' => 'dark',
					'icon'    => true,
				)
			);
			ta_button(
				'Cotizar un proyecto',
				$contacto_url,
				array( 'variant' => 'outline' )
			);
			?>
		</div>
		<?php if ( ! $hero_image_url ) : ?>
			<p class="ta-hero-image-hint">Agrega la imagen de fondo desde <strong>Personalizar → Identidad del sitio → Imagen del hero (Inicio)</strong>.</p>
		<?php endif; ?>
	</div>
</section>

<?php
ta_render_feature_grid(
	'Categorías',
	'Cuatro maneras de trabajar el acrílico.',
	array(
		array(
			'icon'        => ta_icon( 'grid' ),
			'title'       => 'Organizar',
			'description' => 'Orden claro, a la vista. <br>
			                  Piezas que ayudan a  <br>
							  ordenar espacios de <br>
							  trabajo, tiendas y <br>
							  eventos.', 
			'url'         => $solutions_url . '#categoria-organizador',
		),
		array(
			'icon'        => ta_icon( 'eye' ),
			'title'       => 'Exhibir',
			'description' => 'Presenta lo que importa. <br>
			                  Soportes y vitrinas que  <br>
							  ponen tus productos u  <br>
							  objetos en primer plano.',
			'url'         => $solutions_url . '#categoria-exhibir',
		),
		array(
			'icon'        => ta_icon( 'shield' ),
			'title'       => 'Proteger',
			'description' => 'Cuida sin ocultar. <br>
			                  Cubiertas y protecciones <br>
							  transparentes para <br>
							  maquetas, obras y equipos.',
			'url'         => $solutions_url . '#categoria-proteger',
		),
		array(
			'icon'        => ta_icon( 'info' ),
			'title'       => 'Informar',
			'description' => 'Comunica con claridad. <br>
			                  Señalética, tótems y <br>
							  soportes con códigos QR <br>
							  para guiar a las personas.',
			'url'         => $solutions_url . '#categoria-informar',
		),
	)
);

ta_render_showcase_grid(
	'Destacados',
	'Piezas más solicitadas.',
	array(
		array(
			'title'       => 'Buzón',
			'description' => 'Buzón transparente para sugerencias, votaciones o sorteos.',
			'image_url'   => 'https://i.postimg.cc/fbkX7YdT/buzon-acrilico-transparente.webp',
		),
		array(
			'title'       => 'Organizador de escritorio',
			'description' => 'Compartimentos abiertos para papelería y accesorios.',
			'image_url'   => 'https://i.postimg.cc/qqcCBwf4/organizador-de-escritorio.webp',
		),
		array(
			'title'       => 'Bandeja modular',
			'description' => 'Bandejas apilables para separar productos o insumos.',
			'image_url'   => 'https://i.postimg.cc/jjGWzmtK/bandeja-modular.webp',
		),
	),
	array(
		'label' => 'Ver todo el catálogo',
		'url'   => $solutions_url,
	)
);

ta_render_story_section(
	array(
		'eyebrow'    => 'Nuestra historia',
		'title'      => 'Más de 20 años trabajando el acrílico a medida.',
		'paragraphs' => array(
			'Nacimos como un taller pequeño con una idea simple: el acrílico bien trabajado ordena, muestra y protege sin estorbar. Hoy seguimos fabricando cada pieza a mano en Santiago, combinando tecnología de corte láser con el acabado manual que solo los años de experiencia permiten.',
			'Trabajamos con marcas, arquitectos, museos y personas que buscan una solución concreta. Si no está en el catálogo, lo diseñamos para ti desde cero, asegurando calidad en todo lo que realizamos.',
		),
		'stats'      => array(
			array(
				'value' => '5k+',
				'label' => 'Proyectos realizados',
			),
			array(
				'value' => '20+',
				'label' => 'Años de experiencia',
			),
		),
		'image_url'  => 'https://i.postimg.cc/t4c7jw1W/equipo-todo-acrilico.webp',
	)
);

ta_render_cta_section(
	'¿Tienes una idea en mente?',
	'Estamos listos para materializar tu proyecto en acrílico. Desde piezas unitarias hasta grandes producciones.',
	'Hablemos de tu proyecto',
	$contacto_url
);
?>

<?php
get_footer();
```

---

## 1.7 `page.php` (plantilla genérica)

> **Actualizado en 1.20.0 (§4.116):** para `politica-de-privacidad` y `terminos-y-condiciones` (`ta_is_legal_page()`), `page.php` agrega la clase `ta-page-content--wide` y pasa el contenido por `ta_render_legal_accordion()` en vez de `the_content()`. El resto de las páginas sigue igual. La copia de código de abajo es anterior a ese cambio: créele al archivo real.

Plantilla de página estándar (título + contenido del editor de WordPress) — queda como fallback genérico para cualquier página nueva sin plantilla propia. Ya no la usa ninguna de las 4 páginas del menú: "Cuidados del Acrílico" y "Contacto" tenían contenido editorial simple al principio, pero ambas pasaron a tener plantilla dedicada (`page-contacto.php`, §1.10, §4.30; `page-cuidados-del-acrilico.php`, §1.11, §4.31).

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ta-page-content">
		<h1><?php the_title(); ?></h1>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
```

---

## 1.8 `page-nuestras-soluciones.php`

El núcleo de lo pedido: agrupa las imágenes de la galería por categoría. WordPress la selecciona automáticamente para la página de slug `nuestras-soluciones` (jerarquía de plantillas `page-{slug}.php`).

Toma el contenido editorial de la página (si el usuario escribió algo en el editor de WordPress, se muestra como bajada; si no, usa un texto por defecto) y luego recorre `ta_get_solutions_by_category()`: por cada categoría con imágenes, imprime un `<div class="ta-solutions-group" id="categoria-{slug}">` (el `id` se agregó para que los botones "Ver piezas →" de `ta_render_feature_grid()` en Inicio, §1.6, §4.11, puedan enlazar directo a la categoría con un ancla) con un encabezado con su nombre y una grilla de tarjetas (imagen, título, descripción). Si no hay ninguna categoría con imágenes (galería vacía, o plugin inactivo), muestra un estado vacío en vez de una sección en blanco.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$groups = ta_get_solutions_by_category();

if ( have_posts() ) {
	the_post();
}

$page_content = get_the_content();
?>

<div class="ta-container">
	<section class="ta-section">
		<h2><?php the_title(); ?></h2>
		<?php if ( $page_content ) : ?>
			<div class="ta-section-intro entry-content"><?php the_content(); ?></div>
		<?php else : ?>
			<p class="ta-section-intro">Estas son nuestras soluciones en acrílico, organizadas por categoría.</p>
		<?php endif; ?>

		<?php if ( empty( $groups ) ) : ?>
			<div class="ta-empty-state">
				<p>Todavía no hay imágenes publicadas en la galería.</p>
			</div>
		<?php else : ?>
			<?php foreach ( $groups as $group ) : ?>
				<div class="ta-solutions-group" id="categoria-<?php echo esc_attr( $group['slug'] ); ?>">
					<h2><?php echo esc_html( $group['name'] ); ?></h2>
					<div class="ta-solutions-grid">
						<?php foreach ( $group['images'] as $image ) : ?>
							<?php $has_carousel = count( $image['images'] ) > 1; ?>
							<article class="ta-solution-card">
								<div class="ta-solution-figure" <?php echo $has_carousel ? 'data-ta-carousel' : ''; ?>>
									<?php foreach ( $image['images'] as $index => $url ) : ?>
										<img
											src="<?php echo esc_url( $url ); ?>"
											alt="<?php echo esc_attr( $image['title'] ); ?>"
											loading="lazy"
											class="ta-carousel-slide"
											<?php echo 0 === $index ? '' : 'hidden'; ?>
										>
									<?php endforeach; ?>
									<?php if ( $has_carousel ) : ?>
										<button type="button" class="ta-carousel-nav ta-carousel-nav--prev" aria-label="Imagen anterior">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
										</button>
										<button type="button" class="ta-carousel-nav ta-carousel-nav--next" aria-label="Imagen siguiente">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
										</button>
										<span class="ta-carousel-counter">1/<?php echo count( $image['images'] ); ?></span>
									<?php endif; ?>
								</div>
								<div class="ta-solution-body">
									<h3><?php echo esc_html( $image['title'] ); ?></h3>
									<?php if ( $image['description'] ) : ?>
										<p><?php echo esc_html( $image['description'] ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>
</div>

<?php
get_footer();
```

---

## 1.9 `assets/js/ta-carousel.js`

Mini-carrusel reutilizable, vanilla JS sin dependencias, para publicaciones con más de una foto en "Nuestras Soluciones" (mismo patrón visual — flechas ‹/›, contador — que el carrusel del panel del plugin, ver Docs_Plugin.md §1.6.4, pero implementado por separado porque son dos aplicaciones independientes: el panel construye tarjetas dinámicamente en JS a partir de `fetch()`, mientras que el tema renderiza HTML del lado del servidor en PHP). Busca todos los `[data-ta-carousel]` del documento y les conecta los botones "anterior"/"siguiente" y el contador.

```js
(function () {
	'use strict';

	function initCarousel(root) {
		var slides = root.querySelectorAll('.ta-carousel-slide');
		var counter = root.querySelector('.ta-carousel-counter');
		var prevBtn = root.querySelector('.ta-carousel-nav--prev');
		var nextBtn = root.querySelector('.ta-carousel-nav--next');
		var current = 0;

		function show(index) {
			slides[current].hidden = true;
			current = (index + slides.length) % slides.length;
			slides[current].hidden = false;
			if (counter) {
				counter.textContent = (current + 1) + '/' + slides.length;
			}
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				show(current - 1);
			});
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				show(current + 1);
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-ta-carousel]').forEach(initCarousel);
	});
})();
```

**Markup que espera** (generado por `page-nuestras-soluciones.php`, §1.8): un contenedor con `data-ta-carousel` que envuelve varias `<img class="ta-carousel-slide">` (todas menos la primera con el atributo `hidden`), dos `<button class="ta-carousel-nav ta-carousel-nav--prev|--next">` y un `<span class="ta-carousel-counter">`. Si la publicación tiene una sola foto, el contenedor no lleva `data-ta-carousel` y no se renderizan ni los botones ni el contador — `initCarousel()` nunca se ejecuta sobre esa tarjeta.

Se enqueua desde `functions.php::ta_assets()` junto con `style.css`, versionado con la misma constante `TA_VERSION` (cache-busting — ver la lección documentada en Docs_Plugin.md §12.20, aplicada aquí también).

---

## 1.10 `page-contacto.php` (Contacto — versión actual 1.18.14, §4.50)

> ⚠️ **Esta sección está DESACTUALIZADA desde 1.20.0.** El formulario se reescribió el 2026-10-20 (§4.110–§4.115): envío AJAX, errores bajo cada campo solo al enviar, `novalidate`, borrador en `sessionStorage`, modales de éxito y de límite de envíos, y nuevas reglas de nombre, teléfono y correo. **La especificación vigente está en `SPEC.md`**, y el código real en `page-contacto.php` y `ta_handle_contact_submit()`. La descripción y la copia de código de abajo son históricas (1.18.14): no las uses como referencia para modificar.

Plantilla dedicada para la página "Contacto" (WordPress la selecciona automáticamente por slug, jerarquía `page-{slug}.php` antes que `page.php` — mismo mecanismo que `page-nuestras-soluciones.php`, §1.8). Reemplaza por completo la plantilla genérica que usaba antes: 2 columnas — a la izquierda, eyebrow + título + una rayita divisoria + bajada + una lista de 3 datos de contacto con ícono (`ta_icon('mail'|'pin'|'clock', 20)`, §1.2); a la derecha, una tarjeta blanca con el formulario.

> **Datos de contacto sin verificar:** el correo de recepción real es `rodolfo.parada.gonzalez@gmail.com` (lo definía `ta_get_contact_email()`, §1.2 — confirmado por el usuario). La dirección (`Av. Las Condes 1234, Oficina 502`) y el horario (`Lunes a Viernes, 09:00 — 18:30 hrs`) se tomaron **literalmente** de una captura de referencia que mandó el usuario — no son datos confirmados como reales del negocio, y siguen sin confirmarse si deben unificarse con los del footer (`hola@todoacrilico.cl`, §4.15).

### Campos, validación y comportamiento del formulario

El `<form>` apunta a `admin_url('admin-post.php')` con `enctype="multipart/form-data"` (imprescindible para que los archivos viajen en el `POST`), un campo oculto `action=ta_contact_submit`, `redirect_to` (la URL real de vuelta, no hardcodeada), nonce (`wp_nonce_field()`) y un honeypot (`ta_contact_website`, §4.33). Campos:

- **Nombre Completo + Teléfono** en una fila. `celular` usa un prefijo fijo chileno `+56 9` visual (bandera 🇨🇱), y el JS (bloque 2 del `<script>`) formatea la entrada a 8 dígitos con espacio tras el cuarto (`XXXX XXXX`). El servidor exige celular chileno real (§1.2): quita el `56`/`9` inicial y valida 8 dígitos empezando en 2–9.
- **Correo Electrónico** (`type="email"`). El servidor exige además que el dominio termine en `.cl`, `.com`, `.net` u `.org` (§1.2).
- **Descripción del proyecto** (`textarea required maxlength="2000"`) con un aviso "Límite 2000 caracteres" debajo.
- **Adjuntos opcionales** (`adjunto[]`, `multiple`, `accept="image/*,.pdf"`) — hasta **5** archivos de máx. 5 MB cada uno: imágenes (extensión **y** contenido real con `getimagesize()`) o PDF (`%PDF`/MIME). El `<input>` real está oculto de forma accesible (técnica `position:absolute; width:1px; height:1px; clip:rect(0,0,0,0);`, no `display:none`) — un `<label class="ta-contact-file-upload">` estilizado con ícono `paperclip` dispara el selector. El JS (bloque 1 del `<script>`) mantiene un `DataTransfer` con los archivos elegidos y renderiza la lista `#ta-adjunto-list` (nombre + tamaño por fila, botones "Agregar"/"Eliminar"), ocultando el botón de adjuntar al llegar a 5. Debajo, una nota `.ta-contact-files-note` (texto chico gris, mismo estilo que el aviso de caracteres): "Nota: Por favor no adjunte documentos con datos personales sensibles o cédula de identidad." (§4.44).
- **Consentimiento de comunicaciones comerciales** (`.ta-contact-optin`, §4.42) — debajo del check de aceptación de términos, un checkbox **opcional** (no obligatorio) con el texto "(Opcional) Acepto recibir ofertas y comunicaciones comerciales." El manejador lo lee como `acepta_ofertas` y agrega al correo la línea "El cliente aceptó/no aceptó recibir ofertas y comunicaciones comerciales." (§1.2).
- **Aceptación de Política y Términos** (`.ta-contact-accept`, §4.50) — **sobre** el check opcional, un checkbox **obligatorio** (`required` + validación server-side con `$fail( 'terminos' )`, §1.2): "He leído y acepto **la Política de Privacidad** y los **Términos y condiciones**." — los dos textos son enlaces reales (`$ta_privacy_url` → `politica-de-privacidad`, `$ta_terms_url` → `terminos-y-condiciones`, con `get_page_by_path()`) que abren las páginas legales. Estilo: mismo layout del optin pero `font-weight:600` (para distinguir el obligatorio del opcional) y los enlaces en verde `--ta-accent` subrayados.
- **Alert informativo de privacidad** (`.ta-contact-alert--info`, §4.45) — debajo del check, un componente Alert variante *info* estilo Bootstrap/Tailwind/shadcn: ícono de escudo a la izquierda (SVG `ta_icon('shield', 18)`, §1.2, coloreado con `--ta-accent`) y el texto a su derecha: "Sus datos serán tratados conforme a la ley 21.719 para gestionar su cotización. Consulte nuestra Política de Privacidad o ejerza sus derechos en contacto@todoacrilico.cl". **Ojo:** el correo público de privacidad (`contacto@todoacrilico.cl`) es distinto del correo que recibe las cotizaciones (`ta_get_contact_email()` → `rodolfo.parada.gonzalez@gmail.com`, §1.2) — así lo pidió el texto de referencia.
- **Tarjeta de pieza a cotizar** (`.ta-contact-quote`, opcional): si la página llega con `?imagen=&pieza=&descripcion=&medidas=` — los parámetros que arma el botón "Cotizar esta pieza" de cada tarjeta del Catálogo (`page-nuestras-soluciones.php`, §1.8/§4.41) — se muestra una miniatura + nombre + descripción + medidas de esa pieza con el aviso "Esta información se adjunta automáticamente junto a tu mensaje." Acompaña campos ocultos `adjunto_referencia`/`adjunto_titulo`/`adjunto_descripcion`/`adjunto_medidas` que el manejador incrusta en el cuerpo del correo (§1.2). El botón `×` (JS, bloque 3) quita la tarjeta y borra esos campos ocultos, para enviar sin la pieza.
- **Enviar mensaje** (`ta-button--dark`, ancho completo) + nota "Respondemos entre 24 y 48 horas hábiles."

Arriba del formulario, `page-contacto.php` lee `?ta_contact=success|error&reason={motivo}` (patrón Post/Redirect/Get del manejador, §4.33) y muestra el aviso verde/rojo correspondiente (`.ta-contact-alert`). Motivos de error cubiertos: `campos`, `email`, `celular`, `archivo`, `archivo_grande`, `archivo_tipo`, `archivos_muchos`, `nonce`, `envio`.

El procesamiento completo vive en `ta_handle_contact_submit()` (`functions.php` §1.2): sanitiza cada campo, valida nonce + honeypot, valida teléfono y dominio de correo, mueve y limpia cada adjunto, y arma un cuerpo **HTML** con la "pieza a cotizar". Ver el detalle en §1.2, la compatibilidad con plugins SMTP en §4.33 y la verificación punta a punta en §4.33.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contacto_page = ta_find_page( array( 'contacto', 'contact' ) );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/contacto/' );

$ta_privacy_page = ta_find_page( array( 'politica-de-privacidad', 'privacidad' ) );
$ta_terms_page   = ta_find_page( array( 'terminos-y-condiciones', 'termino' ) );
$ta_privacy_url  = $ta_privacy_page ? get_permalink( $ta_privacy_page ) : home_url( '/' );
$ta_terms_url    = $ta_terms_page ? get_permalink( $ta_terms_page ) : home_url( '/' );

$ta_contact_status = isset( $_GET['ta_contact'] ) ? sanitize_key( $_GET['ta_contact'] ) : '';
$ta_contact_reason = isset( $_GET['reason'] ) ? sanitize_key( $_GET['reason'] ) : '';

$ta_quote_image   = isset( $_GET['imagen'] ) ? esc_url_raw( wp_unslash( $_GET['imagen'] ) ) : '';
$ta_quote_piece   = isset( $_GET['pieza'] ) ? sanitize_text_field( wp_unslash( $_GET['pieza'] ) ) : '';
$ta_quote_piece   = wp_strip_all_tags( $ta_quote_piece );
$ta_quote_image   = ( $ta_quote_image && wp_http_validate_url( $ta_quote_image ) ) ? $ta_quote_image : '';
$ta_quote_desc    = isset( $_GET['descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_GET['descripcion'] ) ) : '';
$ta_quote_desc    = $ta_quote_desc ? wp_strip_all_tags( trim( $ta_quote_desc ) ) : '';
$ta_quote_meas    = isset( $_GET['medidas'] ) ? sanitize_text_field( wp_unslash( $_GET['medidas'] ) ) : '';
$ta_quote_meas    = $ta_quote_meas ? trim( $ta_quote_meas ) : '';
$ta_has_quote     = (bool) $ta_quote_image;

$ta_contact_error_messages = array(
	'campos'         => 'Completa tu nombre y la descripción del proyecto.',
	'email'          => 'Ingresa un correo electrónico válido.',
	'celular'        => 'Ingresa un celular chileno válido (9 dígitos que empiecen en 9).',
	'archivo'        => 'No se pudo procesar el archivo adjunto. Intenta de nuevo.',
	'archivo_grande' => 'El archivo adjunto no puede superar los 5 MB.',
	'archivo_tipo'   => 'El archivo adjunto debe ser una imagen (JPG, PNG, GIF o WEBP) o un PDF.',
	'archivos_muchos'=> 'Puedes adjuntar hasta 5 archivos.',
	'terminos'       => 'Debes aceptar la Política de Privacidad y los Términos y condiciones para continuar.',
	'nonce'          => 'Tu sesión expiró, vuelve a intentarlo.',
	'envio'          => 'No se pudo enviar el mensaje. Intenta de nuevo más tarde.',
);
?>

<section class="ta-contact">
	<div class="ta-container ta-contact-inner">

		<div class="ta-contact-info">
			<p class="ta-contact-eyebrow">Contacto</p>
			<h1 class="ta-contact-title">Cuéntanos tu proyecto</h1>
			<span class="ta-contact-divider" aria-hidden="true"></span>
			<p class="ta-contact-description">Escríbenos con la mayor cantidad de detalles: medidas, uso y contexto. Si tienes bocetos o imágenes, adjúntalas para cotizar más rápido.</p>

			<ul class="ta-contact-list">
				<li>
					<span class="ta-contact-icon" aria-hidden="true"><?php echo ta_icon( 'mail', 20 ); ?></span>
					<div>
						<p class="ta-contact-label">Email Corporativo</p>
						<p class="ta-contact-value"><?php echo esc_html( ta_get_contact_email() ); ?></p>
					</div>
				</li>
				<li>
					<span class="ta-contact-icon" aria-hidden="true"><?php echo ta_icon( 'pin', 20 ); ?></span>
					<div>
						<p class="ta-contact-label">Ubicación</p>
						<p class="ta-contact-value">Santiago, Chile</p>
						<p class="ta-contact-value-sub">Av. Las Condes 1234, Oficina 502</p>
					</div>
				</li>
				<li>
					<span class="ta-contact-icon" aria-hidden="true"><?php echo ta_icon( 'clock', 20 ); ?></span>
					<div>
						<p class="ta-contact-label">Horario de Atención</p>
						<p class="ta-contact-value">Lunes a Viernes</p>
						<p class="ta-contact-value-sub">09:00 — 18:30 hrs</p>
					</div>
				</li>
			</ul>
		</div>

		<div class="ta-contact-form-card">
			<?php if ( 'success' === $ta_contact_status ) : ?>
				<p class="ta-contact-alert ta-contact-alert--success">¡Gracias! Recibimos tu mensaje y te responderemos entre 24 y 48 horas hábiles.</p>
			<?php elseif ( 'error' === $ta_contact_status ) : ?>
				<p class="ta-contact-alert ta-contact-alert--error"><?php echo esc_html( $ta_contact_error_messages[ $ta_contact_reason ] ?? 'Ocurrió un error. Intenta de nuevo.' ); ?></p>
			<?php endif; ?>

			<?php if ( $ta_has_quote ) : ?>
				<div class="ta-contact-quote" id="ta-contact-quote">
					<button type="button" class="ta-contact-quote-remove" id="ta-contact-quote-remove" aria-label="Quitar pieza a cotizar del mensaje">×</button>
					<?php if ( $ta_quote_image ) : ?>
						<div class="ta-contact-quote-thumb">
							<img src="<?php echo esc_url( $ta_quote_image ); ?>" alt="<?php echo esc_attr( $ta_quote_piece ? $ta_quote_piece : 'Imagen de la pieza a cotizar' ); ?>" loading="lazy">
						</div>
					<?php endif; ?>
					<div class="ta-contact-quote-text">
						<?php if ( $ta_quote_piece ) : ?>
							<strong><?php echo esc_html( $ta_quote_piece ); ?></strong>
						<?php endif; ?>
						<?php if ( $ta_quote_desc ) : ?>
							<span class="ta-contact-quote-desc"><?php echo esc_html( $ta_quote_desc ); ?></span>
						<?php endif; ?>
						<?php if ( $ta_quote_meas ) : ?>
							<span class="ta-contact-quote-medi"><strong>Medidas:</strong> <?php echo esc_html( $ta_quote_meas ); ?></span>
						<?php endif; ?>
						<span class="ta-contact-quote-note">Esta información se adjunta automáticamente junto a tu mensaje.</span>
					</div>
				</div>
			<?php endif; ?>

			<form class="ta-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="ta_contact_submit">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $contacto_url ); ?>">
				<?php wp_nonce_field( 'ta_contact_submit', 'ta_contact_nonce' ); ?>
				<?php if ( $ta_has_quote ) : ?>
					<input type="hidden" name="adjunto_referencia" value="<?php echo esc_url( $ta_quote_image ); ?>">
					<?php if ( $ta_quote_piece ) : ?>
						<input type="hidden" name="adjunto_titulo" value="<?php echo esc_attr( $ta_quote_piece ); ?>">
					<?php endif; ?>
					<?php if ( $ta_quote_desc ) : ?>
						<input type="hidden" name="adjunto_descripcion" value="<?php echo esc_attr( $ta_quote_desc ); ?>">
					<?php endif; ?>
					<?php if ( $ta_quote_meas ) : ?>
						<input type="hidden" name="adjunto_medidas" value="<?php echo esc_attr( $ta_quote_meas ); ?>">
					<?php endif; ?>
				<?php endif; ?>
				<p class="ta-contact-honeypot" aria-hidden="true">
					<label>
						Sitio web (dejar vacío)
						<input type="text" name="ta_contact_website" tabindex="-1" autocomplete="off">
					</label>
				</p>
				<div class="ta-contact-form-row">
					<label>
						Nombre Completo
						<input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>
					</label>
					<label>
						Teléfono
						<div class="ta-contact-tel-wrap">
							<div class="ta-contact-tel-prefix">
								<span class="ta-contact-flag" aria-hidden="true">🇨🇱</span>
								<span>+56 9</span>
							</div>
							<input type="tel" name="celular" placeholder="8765 4321" inputmode="tel" required>
						</div>
					</label>
				</div>
				<label>
					Correo Electrónico
					<input type="email" name="email" placeholder="juan@ejemplo.cl" required>
				</label>
				<label>
					Descripción del proyecto
					<textarea name="descripcion" rows="5" maxlength="2000" placeholder="Cuéntanos detalles de medidas, materiales y plazos…" required></textarea>
					<span class="ta-contact-char-limit">Límite 2000 caracteres</span>
				</label>
				<div class="ta-contact-files">
					<input type="file" name="adjunto[]" id="ta-adjunto-input" accept="image/*,.pdf,application/pdf" multiple>
					<label for="ta-adjunto-input" class="ta-contact-file-upload">
						<?php echo ta_icon( 'paperclip', 16 ); ?>Adjuntar imagen, PDF del boceto o producto (opcional)
					</label>
					<div id="ta-adjunto-list"></div>
				</div>
				<p class="ta-contact-files-note">Nota: Por favor no adjunte documentos con datos personales sensibles o cédula de identidad.</p>
				<label class="ta-contact-optin ta-contact-accept">
					<input type="checkbox" name="acepto_terminos" value="1" required>
					<span>He leído y acepto <a href="<?php echo esc_url( $ta_privacy_url ); ?>">la Política de Privacidad</a> y los <a href="<?php echo esc_url( $ta_terms_url ); ?>">Términos y condiciones</a>.</span>
				</label>
				<label class="ta-contact-optin">
					<input type="checkbox" name="acepta_ofertas" value="1">
					<span>(Opcional) Acepto recibir ofertas y comunicaciones comerciales.</span>
				</label>
				<div class="ta-contact-alert ta-contact-alert--info" role="note">
					<span class="ta-alert-icon" aria-hidden="true"><?php echo ta_icon( 'shield', 18 ); ?></span>
					<p class="ta-alert-text">Sus datos serán tratados conforme a la ley 21.719 para gestionar su cotización. Consulte nuestra Política de Privacidad o ejerza sus derechos en contacto@todoacrilico.cl</p>
				</div>
				<button type="submit" class="ta-button ta-button--dark ta-contact-submit">
					Enviar mensaje
					<span class="ta-button-icon" aria-hidden="true">→</span>
				</button>
				<p class="ta-contact-form-note">Respondemos entre 24 y 48 horas hábiles.</p>
			</form>
		</div>

	</div>
</section>

<script>
(function () {
	var MAX = 5;
	var input = document.getElementById('ta-adjunto-input');
	var list = document.getElementById('ta-adjunto-list');
	var upload = document.querySelector('.ta-contact-file-upload');
	if (!input || !list || !upload) return;

	var dt = new DataTransfer();

	function formatSize(bytes) {
		if (!bytes) return '';
		if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
		return Math.ceil(bytes / 1024) + ' KB';
	}

	function syncFiles() {
		input.files = dt.files;
	}

	function render() {
		list.innerHTML = '';
		var full = dt.files.length >= MAX;
		Array.prototype.forEach.call(dt.files, function (file, index) {
			var row = document.createElement('div');
			row.className = 'ta-contact-file-status';

			var name = document.createElement('span');
			name.textContent = file.name + (formatSize(file.size) ? ' (' + formatSize(file.size) + ')' : '');

			var actions = document.createElement('div');
			actions.className = 'ta-contact-file-actions';

			var add = document.createElement('button');
			add.type = 'button';
			add.className = 'ta-contact-file-add';
			add.textContent = 'Agregar';
			if (full) {
				add.disabled = true;
			}
			add.addEventListener('click', function () {
				input.click();
			});

			var remove = document.createElement('button');
			remove.type = 'button';
			remove.className = 'ta-contact-file-remove';
			remove.textContent = 'Eliminar';
			remove.setAttribute('aria-label', 'Eliminar ' + file.name);
			remove.addEventListener('click', function () {
				dt.items.remove(index);
				syncFiles();
				render();
			});

			actions.appendChild(add);
			actions.appendChild(remove);
			row.appendChild(name);
			row.appendChild(actions);
			list.appendChild(row);
		});

		upload.style.display = full ? 'none' : '';
	}

	input.addEventListener('change', function () {
		var accepted = [];
		for (var i = 0; i < input.files.length && dt.files.length + accepted.length < MAX; i++) {
			accepted.push(input.files[i]);
		}
		for (var j = 0; j < accepted.length; j++) {
			dt.items.add(accepted[j]);
		}
		input.value = '';
		syncFiles();
		render();
	});

	render();
})();

(function () {
	var tel = document.querySelector('.ta-contact-tel-wrap input[type="tel"][name="celular"]');
	if (!tel) return;

	tel.addEventListener('input', function () {
		var digits = tel.value.replace(/\D/g, '').slice(0, 8);
		tel.value = digits.length > 4 ? digits.slice(0, 4) + ' ' + digits.slice(4) : digits;
	});
})();

(function () {
	var quote = document.getElementById('ta-contact-quote');
	var remove = document.getElementById('ta-contact-quote-remove');
	if (!quote || !remove) return;

	remove.addEventListener('click', function () {
		var hiddens = document.querySelectorAll('input[name^="adjunto_"]');
		Array.prototype.forEach.call(hiddens, function (h) { h.remove(); });
		quote.remove();
	});
})();
</script>

<?php
get_footer();
```

---

## 1.11 `page-cuidados-del-acrilico.php` (versión 1.11.0, §4.31)

Plantilla dedicada para "Cuidados del Acrílico" (mismo mecanismo de jerarquía `page-{slug}.php`, §1.8/§1.10 — ya no usa `page.php`, que ahora solo sirve páginas genéricas sin plantilla propia, si las hubiera). Reutiliza **3 de los 4 componentes de Inicio** en vez de construir algo nuevo desde cero:

1. **`ta_render_feature_grid()`** (§1.2, §4.11) con las 4 tarjetas de la guía (Evitar el calor / No uses estos productos / Limpieza suave / ¿Rayones?, con íconos `thermometer`/`prohibited`/`spray`/`circular` nuevos en `ta_icon()`) — sin `url` en ninguna, así que no se imprime el botón "Ver piezas →" (la función ahora lo hace opcional, §4.31). Usa también el 4º parámetro nuevo, `$description`, para el párrafo introductorio entre el título y la grilla.
2. **`ta_render_story_section()`** (§1.2, §4.13) para "Consejo final del experto", con 3 parámetros nuevos: `'media_position' => 'right'` (la foto va a la derecha, al revés que en Inicio), `'panel' => true` (envuelve el contenido en una tarjeta con fondo gris `#ececea` y esquinas redondeadas, en vez del layout a página completa de Inicio) y `'show_logo_badge' => false` (sin la insignia del logo). `'stats' => array()` vacío — como la rayita divisoria ahora se imprime siempre después de los párrafos (ya no solo cuando hay estadísticas, §4.31), sigue apareciendo aunque no haya estadísticas, igual que en la referencia.
3. **`ta_render_cta_section()`** (§1.2, §4.14) — llamado **exactamente igual** que en Inicio, mismo texto, mismo botón hacia `contacto`. Cero código nuevo para esta sección.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$contacto_page = ta_find_page( array( 'contacto', 'contact' ) );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/' );
?>

<?php
ta_render_feature_grid(
    'Guía de mantenimiento',
    'Cómo cuidar tus piezas',
    array(
        array(
            'icon'        => ta_icon( 'thermometer' ),
            'title'       => 'Evitar el calor',
            'description' => 'No expongas el acrílico a <br>fuentes de calor: se deforma <br>sobre 90°C.',
        ),
        array(
            'icon'        => ta_icon( 'prohibited' ),
            'title'       => 'No uses estos productos',
            'description' => 'Limpiadores para vidrio, <br>alcohol, acetona, bencina, <br>solventes ni productos <br>abrasivos.',
        ),
        array(
            'icon'        => ta_icon( 'spray' ),
            'title'       => 'Limpieza suave',
            'description' => 'Agua tibia con detergente <br>suave (por ejemplo, shampoo <br>de bebé).',
        ),
        array(
            'icon'        => ta_icon( 'circular' ),
            'title'       => '¿Rayones?',
            'description' => 'Aplica cera de pulir y frota <br>suavemente en movimientos <br>circulares.',
        ),
    ),
    'El acrílico es un material noble y duradero que requiere de atenciones específicas para <br> 
	 conservar su transparencia y brillo original por décadas. Sigue estas recomendaciones <br>
	 arquitectónicas para su correcta preservación.'
);

ta_render_story_section(
    array(
        'eyebrow'         => 'Consejo final del experto',
        'title'           => 'El secreto reside en la limpieza.',
        'paragraphs'      => array(
            'Aunque el acrílico sea transparente, no es lo mismo <br>
			 que el vidrio: usa siempre productos diseñados para <br>
			 acrílico.',
            'Evita el uso de cepillos, esponjas abrasivas o <br>
			 estropajos, ya que pueden rayar y dañar <br>
			 permanentemente la superficie.',
        ),
        'stats'           => array(),
        'image_url'       => 'https://i.postimg.cc/Kj93gzYt/cuidados.webp',
        'show_logo_badge' => false,
        'media_position'  => 'right',
        'panel'           => true,
    )
);

ta_render_cta_section(
    '¿Tienes una idea en mente?',
    'Estamos listos para materializar tu proyecto en acrílico. Desde piezas unitarias hasta grandes producciones.',
    'Hablemos de tu proyecto',
    $contacto_url
);
?>

<?php
get_footer();
```

---

## 1.12 `assets/js/ta-nav.js`

> **Nota (2.0.1):** además del desplegable de Catálogo, ahora controla el menú hamburguesa: `setNavOpen()` alterna `.is-nav-open` en `.ta-header`, Escape lo cierra y se cierra solo al pasar a más de 861px (§4.121). La copia de abajo puede no incluirlo.

Mini-script vanilla JS (sin dependencias) que controla el desplegable de "Catálogo" en el navbar. Se encola desde `functions.php::ta_assets()` junto con `ta-carousel.js`, versionado con la misma constante `TA_VERSION`.

> **Por qué existe este archivo:** con §4.57-§4.58 el desplegable de "Catálogo" se controla por **click**: el link "Catálogo" (o el botón `.ta-nav-caret-toggle` que inyecta `TA_Nav_Walker::end_el()`, §1.2/§4.38) alterna la clase `is-open` y despliega la lista; como los CSS abiertos por hover/foco se eliminaron (§4.58), el panel **solo** se muestra tras un click real. Seleccionar un ítem navega a su contenido (`?categoria=` en el Catálogo, §4.41). El script actúa en cualquier pantalla (no se filtra por `matchMedia(hover)`).

```js
(function () {
	// El desplegable de "Catálogo" se abre SOLO con click: sobre el link
	// "Catálogo" y también sobre el botón flecha (.ta-nav-caret-toggle). El
	// link "Catálogo" ya no navega en el primer click (se preventDefault): su
	// función es desplegar la lista; para llegar al catálogo completo está el
	// primer ítem del desplegable ("Todas las piezas"). El mouse NO abre el
	// panel (el CSS solo responde a .is-open, §1.1/§4.57).
	document.addEventListener('DOMContentLoaded', function () {
		var items = document.querySelectorAll('.ta-nav-has-caret');

		function close(li) {
			li.classList.remove('is-open');
			var toggle = li.querySelector(':scope > .ta-nav-caret-toggle');
			if (toggle) {
				toggle.setAttribute('aria-expanded', 'false');
			}
		}

		function closeAll(except) {
			items.forEach(function (li) {
				if (li !== except) {
					close(li);
				}
			});
		}

		function toggle(li) {
			var isOpen = li.classList.contains('is-open');
			closeAll(li);
			if (isOpen) {
				close(li);
			} else {
				li.classList.add('is-open');
				var toggle = li.querySelector(':scope > .ta-nav-caret-toggle');
				if (toggle) {
					toggle.setAttribute('aria-expanded', 'true');
				}
			}
		}

		items.forEach(function (li) {
			var link = li.querySelector(':scope > a');
			if (link) {
				link.addEventListener('click', function (e) {
					e.preventDefault();
					toggle(li);
				});
			}

			var toggleBtn = li.querySelector(':scope > .ta-nav-caret-toggle');
			if (toggleBtn) {
				toggleBtn.addEventListener('click', function () {
					toggle(li);
				});
			}
		});

		document.addEventListener('click', function (e) {
			if (!e.target.closest('.ta-nav')) {
				document.querySelectorAll('.ta-nav-list li.is-open').forEach(function (li) {
					li.classList.remove('is-open');
					var btn = li.querySelector('.ta-nav-caret-toggle');
					if (btn) {
						btn.setAttribute('aria-expanded', 'false');
					}
				});
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				document.querySelectorAll('.ta-nav-list li.is-open').forEach(function (li) {
					li.classList.remove('is-open');
					var btn = li.querySelector('.ta-nav-caret-toggle');
					if (btn) {
						btn.setAttribute('aria-expanded', 'false');
					}
				});
			}
		});
	});
})();
```

**Comportamiento:**

- Busca todos los `<li class="ta-nav-has-caret">` (clase que `TA_Nav_Walker::start_el()` les agrega al ítem "Catálogo" cuando hay categorías con imágenes, §1.2/§4.38).
- **Clic en el link "Catálogo"**: `preventDefault()` (ya no navega en el primer clic) y alterna `is-open` — la flecha rota y el listado se despliega.
- El clic en el botón `.ta-nav-caret-toggle` hace exactamente lo mismo (misma función `toggle()`).
- `closeAll()` cierra cualquier otro desplegable abierto antes de abrir el nuevo — solo uno abierto a la vez.
- Un listener global en `document` cierra todos los desplegables si se hace clic fuera de `.ta-nav` (click-away), y la tecla `Escape` hace lo mismo.

**Markup que espera** (generado por `TA_Nav_Walker` en `functions.php`, §1.2): un `<li class="... ta-nav-has-caret">` que contiene el `<a>` del link "Catálogo", un `<button class="ta-nav-caret-toggle" aria-expanded="false">`, y un `<ul class="ta-nav-dropdown">` con "Todas las piezas" y un link por categoría (`?categoria={slug}`, §4.57). Si el plugin está desactivado o no hay categorías con imágenes, el `<li>` no recibe `ta-nav-has-caret`, el botón no se inyecta y este script no tiene nada que controlar.

---

## 2. Páginas y menú creados

Además de los archivos del tema, se crearon en la base de datos:

| Página | Slug | Plantilla que la sirve | Contenido |
|---|---|---|---|
| (Inicio, sin página propia) | — | `front-page.php` | Enlace directo `home_url('/')` en el menú |
| Catálogo (etiqueta del menú "Catálogo", la página en sí sigue llamándose "Nuestras Soluciones") | `nuestras-soluciones` | `page-nuestras-soluciones.php` | Vacío (usa el texto por defecto de la plantilla) |
| Cuidados del Acrílico (etiqueta del menú "Cuidados") | `cuidados-del-acrilico` | `page-cuidados-del-acrilico.php` (§1.11, §4.31 — antes usaba `page.php`) | Guía de mantenimiento (4 tarjetas) + consejo del experto con foto + CTA de cierre |
| Contacto | `contacto` | `page-contacto.php` (§1.10, §4.30 — antes usaba `page.php` con texto placeholder) | Info de contacto + formulario, con datos tomados de una captura de referencia — **sin confirmar como reales del negocio** (§1.10) |

Y un menú de navegación **"Menú principal"** con 5 ítems en orden (Inicio, Catálogo, Cuidados, Contacto, Cotizar), asignado a la ubicación `primary` que registra `functions.php`. "Cotizar" no tiene página propia: enlaza a Contacto y se muestra como botón (clase `ta-nav-cta`, §4.9). El mismo menú se reutiliza en el footer (§1.4), donde las clases `ta-nav-has-caret`/`ta-nav-cta` no producen ningún efecto visual porque el footer usa su propia clase de lista (`ta-footer-nav-list`), no `ta-nav-list`.

> El contenido de "Cuidados del Acrílico" y "Contacto" es un punto de partida razonable, no texto final del negocio — conviene revisarlo y ajustarlo desde **Escritorio → Páginas**.

---

## 3. Dependencia del plugin JWT Gallery

"Nuestras Soluciones" (y la vitrina de Inicio) leen directamente de las tablas que administra el plugin `jwt-gallery` (CPT `jg_gallery_image` + taxonomía `jg_gallery_category`), usando `WP_Query`/`get_terms()` en PHP — **no** llaman a la API REST del plugin, así que no dependen de que la API esté disponible ni de tokens JWT; es una lectura directa a la base de datos, más simple y rápida al ser todo parte del mismo WordPress.

Consecuencias:
- Si el plugin se desactiva, `ta_get_solutions_by_category()` devuelve un array vacío (no un error) y "Nuestras Soluciones" muestra el estado vacío en vez de romperse.
- Las imágenes que se suben, editan o eliminan desde el panel del plugin (`/galeria-panel/`) se reflejan automáticamente en el tema — no hay caché ni paso de sincronización intermedio.
- Las pestañas de "Nuestras Soluciones" **sí** muestran categorías vacías: desde 1.18.58 (`hide_empty => false`, ver §4.95) se listan todas las categorías de la taxonomía, así una categoría recién creada en el panel aparece como pestaña (con su pieza `0`) aunque todavía no tenga imágenes. El tab "Todos" incluye además las publicaciones sin categoría.
- Las imágenes se sirven desde archivos **locales** (`wp-content/uploads/jg-gallery/`), no desde hostings externos: el tema usa directamente la ruta guardada en `_jg_image_url`.

---

## 4. Bitácora de despliegue en este entorno (proyecto3)

> **Resumen de cambios recientes (formulario de Contacto, páginas legales y correo; `TA_VERSION` 1.18.5 → 1.18.14):** esta sesión agregó, en orden:
> 1. **1.18.6 — Checkbox de consentimiento** (§4.42): `acepta_ofertas` opcional bajo los adjuntos; el correo indica siempre si el cliente aceptó o no ofertas.
> 2. **1.18.7 — Fix CSS** (§4.43): el texto se apilaba bajo el check por especificidad; el contenedor pasó a `.ta-contact-form .ta-contact-optin` (`flex-direction: row`).
> 3. **1.18.8 — Nota de privacidad en adjuntos** (§4.44): `.ta-contact-files-note` entre "Adjuntar imagen" y el check.
> 4. **1.18.9 — Alert informativo (Ley 21.719)** (§4.45): `.ta-contact-alert--info` con ícono escudo y texto a la derecha, bajo el check.
> 5. **1.18.10 — Email con plantilla HTML estilo newsletter** (§4.46): el cuerpo del correo deja de ser texto plano simple y usa tabla de 600px (encabezado, DATOS DEL CLIENTE con el consentimiento, DESCRIPCIÓN DEL PROYECTO, PIEZA A COTIZAR con imagen y resumen de adjuntos).
> 6. **1.18.11 — Página legal "Términos y Condiciones"** (§4.47): se creó la página con el texto legal completo (8 cláusulas) y el link del footer dejó de ser `#` y enlaza a la página real, con estilos propios para el texto legal.
> 7. **1.18.12 — Página legal "Política de Privacidad"** (§4.48): nueva página (id=102) enlazada a la izquierda de "Términos y condiciones" en el footer; luego se le agregó el contenido íntegro del cliente (§4.49).
> 8. **1.18.14 — Aceptación obligatoria de Política/Términos en el formulario** (§4.50): nuevo check requerido sobre el opcional, con enlaces a las páginas legales y validación server-side (`acepto_terminos`).
> 9. **1.18.15 — Formulario de Contacto más angosto** (§4.51): `.ta-contact-form-card` con `max-width: 680px` (los inputs son `width:100%`, bajan junto con la tarjeta).
> 10. **1.18.16 — Formulario alineado con la descripción** (§4.52): la tarjeta baja (`margin-top: 137px`) para que el primer campo empiece a la misma altura que "Escríbenos con la mayor cantidad de detalles…"; en móvil (≤860px) el margen se resetea a 0.
> 11. **1.18.17 — Subrayado grueso bajo "Cuéntanos"** (§4.53): barra `::after` en el título de Contacto que llega hasta las letras "Cu" (`2.2ch`, escala con el `clamp()`), 6px de alto, en verde; la divisora gris pasó a transparente.
> 12. **1.18.18 — Footer: columna "Síguenos"** (§4.54): a la derecha de Contacto, con Instagram y LinkedIn lado a lado (íconos nuevos `'instagram'`/`'linkedin'` en `ta_icon`); el footer pasa de 3 a 4 columnas.
> 13. **1.18.19 — Subrayado de "Cu" negro y pegado al texto** (§4.55): el `::after` pasó de verde a `var(--ta-ink)` y de `bottom:4px` a `bottom:6px` (≈8px entre la base del texto y la barra, la misma distancia que separa el eyebrow "Contacto" del título).
> 14. **1.18.20 — Subrayado de "Cu": salto de línea** (§4.56): la barra queda despegada del texto (era ~3px y se topaba con el descensor de la "u"); `bottom` subió a 16px → ~8px de aire antes de la línea.
> 15. **1.18.21 — Desplegable de "Catálogo" por clic** (§4.57): un clic sobre "Catálogo" despliega el listado (la flecha rota); cada ítem navega a su contenido vía `?categoria=`; se agregó "Todas las piezas" al tope y se cerró el bug de los anchors `#categoria-` obsoletos.
> 16. **1.18.22 — Desplegable de "Catálogo": ni hover ni foco lo abren** (§4.58): corrección del usuario — el listado solo se muestra al hacer click; se quitaron `li:hover`/`:focus-within` del CSS (y del modo acordeón móvil).
> 17. **1.18.23 — Destacados "Piezas más solicitadas" con fondo blanco** (§4.59): habilitada `background: var(--ta-surface)` en `.ta-showcase` (la misma variable que usa el navbar).
> 18. **1.18.24 — CTA "¿Tienes una idea en mente?" con fondo blanco** (§4.60): `background: var(--ta-surface)` en `.ta-cta`, igual que el nav.
> 19. **1.18.25 — Footer sin franja de color sobre la línea** (§4.61): quitado el `margin-top: 64px` que mostraba el gris del fondo por encima del `border-top` — la línea arranca directo tras la sección blanca.
> 20. **1.18.26 — Guía de mantenimiento (Cuidados) con fondo blanco** (§4.62): variante `.ta-features--white` vía nuevo parámetro `$class` de `ta_render_feature_grid()` — solo aplicada en Cuidados, la home queda intacta.
> 21. **1.18.27 — Consejo final del experto (Cuidados) con fondo gris** (§4.63): `.ta-story--panel` pasa a `background: var(--ta-bg)` (el gris que tenía la guía antes de §4.62); la tarjeta interior se mantiene.
> 22. **1.18.28 — Consejo final del experto: gris del ancho de las tarjetas** (§4.64): el gris queda como bloque centrado (pseudo `::before`, `max-width: var(--ta-shell-width)`, igual que `.ta-container` de la guía), con blanco a los costados.
> 23. **1.18.29 — Consejo final del experto: interior del mismo gris** (§4.65): la tarjeta interior pasa de blanca a `var(--ta-bg)`, igual que la franja.
> 24. **1.18.30 — Consejo final del experto: misma anchura que la guía (responsive)** (§4.66): franja `::before` a `calc(100% - 48px)` / `calc(shell - 48px)` y tarjeta sin `max-width` fijo — igual ancho que las tarjetas "Cómo cuidar tus piezas".
> 25. **1.18.31 — Consejo final del experto: un solo gris, sin tarjeta** (§4.67): se elimina el fondo de la tarjeta interior (`background: transparent`) — el contenido va directo sobre la única franja gris del ancho de la guía, en columna cómoda (1100px) centrada.
> 26. **1.18.32 — Tipografía del banner y botones** (§4.68): eyebrow 12px extrabold, título 80px con interlineado 100px, párrafo 18px regular, y todos los botones a 12px extra bold.
> 27. **1.18.33 — Tipografía del bloque Categorías** (§4.69): eyebrow 12px, título 32px, títulos de card 24px, párrafos 15px; "Ver piezas" en 12px (ya lo daba `.ta-button`).
> 28. **1.18.34 — Tipografía del bloque Destacados** (§4.70): eyebrow 12px, título 32px, títulos de card 20px, párrafos 15px.
> 29. **1.18.35 — Tipografía del bloque Nuestra Historia** (§4.71): eyebrow 12px, título 48px regular, párrafos 18px regular, cifras 24px medium, etiquetas 12px extrabold.
> 30. **1.18.36 — Manrope instalada + tipografía del footer** (§4.72): Manrope self-hosted (`fonts/manrope/`, 7 pesos vía `@font-face`) y aplicada a `body` (todos los textos); títulos de footer 12px extrabold y resto de textos 15px.
> 31. **1.18.37 — Navbar 16px normal / bold al seleccionar** (§4.73): `.ta-nav-list > li > a` a 16px y weight 400; el estado hover/seleccionado pasa a bold (700).
> 32. **1.18.38 — Hero "Soluciones en acrílico," en una sola línea** (§4.74): `white-space: nowrap` en `.ta-hero h1`; ojo al desborde en mobile (#4.74).
> 33. **1.18.39 — Título de Categorías en regular** (§4.75): `font-weight: 400` en `.ta-features-title` (era bold por ser `<h2>`).
> 34. **1.18.40 — Título de Destacados en regular** (§4.76): `font-weight: 400` en `.ta-showcase-title`; los tres títulos de bloque ya están en regular.
> 35. **1.18.41 — CTA "¿Tienes una idea en mente?" 48px light** (§4.77): `.ta-cta-title` a 48px y weight 300.
> 36. **1.18.42 — CTA: párrafo a 18px** (§4.78): `.ta-cta-description` pasa de 1rem a 18px.
> 37. **1.18.43 — CTA: título en una sola línea** (§4.79): `white-space: nowrap` en `.ta-cta-title`; ojo al desborde en mobile (#4.79).
> 38. **1.18.44 — Nuestra Historia: párrafos regular + fix eyebrow** (§4.80): los párrafos ya eran 400 (18px); se corrige el eyebrow "NUESTRA HISTORIA" que `.ta-story-content p` pisaba (selector → `.ta-story-content .ta-story-eyebrow`).
> 39. **1.18.45 — "Ver todo el catálogo" 12px extrabold verde** (§4.81): `.ta-button--text.ta-showcase-cta` en 12px/800 y `var(--ta-accent)`.
> 40. **1.18.46 — Cuidados: título de guía 48px regular** (§4.82): modifier `ta-features--hero-title` (solo Cuidados) con `.ta-features-title` a 48px; la home queda en 32px.
> 41. **1.18.47 — Cuidados: descripción de guía 18px regular** (§4.83): `.ta-features-description` a 18px/400.
> 42. **1.18.48 — Títulos de cards medium** (§4.84): `.ta-feature-card h3` con `font-weight: 500` (24px ya lo tenía); aplica también a las cards de home y Nuestras Soluciones (#4.84).
> 43. **1.18.49 — Cuidados: textos de cards 16px regular** (§4.85): `.ta-features--hero-title .ta-feature-card p` a 16px/400; la home sigue en 15px.
> 44. **1.18.50 — Consejo: eyebrow semibold/12** (§4.86): `.ta-story--panel .ta-story-eyebrow` a 600; home mantiene 800. Título del panel ya era 48/400 (§4.87, sin cambios).
> 45. **1.18.51 — Todos los botones extrabold** (§4.88): `.ta-filter-tabs__tab` de 600 a 800; el resto ya era 800.
> 46. **1.18.52 — Íconos de cards 40×40** (§4.89): `.ta-feature-icon svg` a 40px (era 28px vía atributo).
> 47. **1.18.53 — Catálogo: eyebrow 12px semibold** (§4.90): modifier `ta-features--catalog` con `.ta-features-eyebrow` en 600.
> 48. **1.18.54 — Catálogo: título 48px light sin punto** (§4.91): `.ta-features--catalog .ta-features-title` a 48px/300; se quitó el "." del texto (HTML).
> 49. **1.18.55 — Tabs del catálogo 12px regular** (§4.92): `.ta-filter-tabs__tab` a 12px/400 (override del extrabold de §4.88 para estos tabs).
> 50. **1.18.56 — Imágenes de la home locales** (§4.93): las 5 imágenes (`i.postimg.cc`) bajadas a `assets/img/home/` y referenciadas localmente; el hero ignora el default externo legado.
> 51. **1.18.57 — Imagen de Cuidados local** (§4.94): `cuidados.webp` bajada a `assets/img/cuidados/`; ya no queda ninguna imagen del tema externa.
> 52. **1.18.58 — Pestañas del catálogo dinámicas** (§4.95): `page-nuestras-soluciones.php` arma las pestañas desde la taxonomía `jg_gallery_category`, así las categorías creadas en `/galeria-panel/` aparecen automáticamente en el catálogo.
> 53. **1.18.59 — Campo "Uso" en panel y catálogo** (§4.96): nuevo input "Uso" (entre Descripción y Medidas) en subir/editar del panel; se guarda en `_jg_usage`, se muestra en las tarjetas del catálogo y viaja a la cotización (`adjunto_uso` en el correo). Plugin 1.4.1 → 1.4.2.
> 54. **1.18.60 — Imágenes del catálogo como las del inicio** (§4.97): las tarjetas de "Nuestras Soluciones" adoptan la línea visual de las "Piezas más solicitadas" (`.ta-showcase-card`): sin borde ni fondo, figura 4/3 con fondo claro y 18px bajo la imagen, títulos 20px y párrafos 15px, grilla `minmax(260px,1fr)` con gap 28px.
> 55. **1.18.61 — Tipografía de catálogo y panel alineada a "Cuatro maneras de trabajar"** (§4.98): el título de las tarjetas del catálogo pasa a 24px Medium y los párrafos a 15px/1.5 (igual que `.ta-feature-card`); el panel (`/galeria-panel/`, plugin 1.4.6) abandona `system-ui` y usa Manrope (body + modal), así inputs y labels quedan con la misma tipografía del sitio.
> La doc de la plantilla vive en §1.10 (bloque completo embebido) y el `functions.php` en §1.2. La entrega real del correo sigue bloqueada por el SMTP OAuth vencido (§4.40, §6.2).

### 4.1 Construcción

Se crearon los archivos del tema directamente en `wp-content/themes/todo-acrilico/` (a diferencia del plugin, esta carpeta sí tenía permisos de escritura para el usuario del sistema con el que se trabaja, así que no hizo falta el paso intermedio por `sudo cp`). Se validó sintaxis de los 6 archivos PHP con `php -l`: sin errores.

### 4.2 Páginas y menú

Se crearon las 3 páginas (`nuestras-soluciones`, `cuidados-del-acrilico`, `contacto`) con `wp_insert_post()` y el menú "Menú principal" con `wp_create_nav_menu()` + `wp_update_nav_menu_item()` (el ítem "Inicio" como enlace personalizado a `home_url('/')`, los otros 3 como enlaces a las páginas creadas).

### 4.3 Activación del tema

Se confirmó explícitamente con el usuario antes de activar el tema, porque cambia la apariencia de todo el sitio en vivo (incluidas páginas preexistentes como "Sample Page", que ahora también se sirven con `page.php` de este tema). Con la confirmación, se activó con `switch_theme('todo-acrilico')` y se asignó el menú a la ubicación `primary` con `set_theme_mod('nav_menu_locations', ...)`.

### 4.4 Verificación y hallazgos corregidos en el camino

Se probaron por `curl` las 6 URLs relevantes (Inicio, las 3 páginas nuevas, y las 2 páginas del plugin) — todas responden `200`. En el camino se encontraron y corrigieron dos detalles:

- **"Nuestras Soluciones" mostraba el estado vacío** aunque el plugin tenía datos: la única imagen que quedaba en la galería (de pruebas anteriores) no tenía ninguna categoría asignada. Se le asignó la categoría `vidrios` (`wp_set_object_terms()`) para verificar el renderizado agrupado real, y quedó así como contenido de muestra.
- **El logo y el copyright del footer mostraban "proyecto3"** en vez de "Todo Acrílico", porque el título del sitio en WordPress (`Ajustes → General → Título del sitio`) seguía con el valor genérico de la instalación. Se actualizó con `update_option('blogname', 'Todo Acrílico')`.

Se confirmó también que `/galeria/` y `/galeria-panel/` (páginas del plugin) siguen sirviéndose con su propia plantilla en blanco sin nav/footer del tema — el cambio de tema activo no las afecta, porque su plantilla se resuelve por un filtro (`template_include`) independiente del tema (Docs_Plugin.md §1.8).

### 4.5 Compatibilidad con el modelo multi-imagen del plugin (sin cambios de código)

El plugin JWT Gallery pasó a permitir hasta 10 fotos por publicación (Docs_Plugin.md §12.19), guardadas como filas repetidas del meta `_jg_image_url` en vez de un valor único. Se verificó que este tema **no necesitó ningún cambio** para seguir funcionando: se subió una publicación de prueba con 4 fotos vía la API del plugin, y "Nuestras Soluciones" la mostró correctamente usando la primera foto como imagen de la tarjeta — exactamente igual que con las publicaciones de una sola foto. Detalle técnico y el porqué en la nota de §1.2.

### 4.6 Carrusel reutilizable en "Nuestras Soluciones" para publicaciones con más de una foto (versión 1.1.0)

A pedido explícito ("se puede reutilizar el carrusel en theme para que se visualice las imagenes cuando existan mas de 1 imagen"), se llevó al tema el mismo patrón visual que el mini-carrusel del panel del plugin (flechas ‹/› + contador, Docs_Plugin.md §1.6.4), adaptado a que este tema renderiza HTML del lado del servidor en vez de construir tarjetas por JS:

- **`functions.php`** (§1.2): `ta_get_solutions_by_category()` ahora lee `_jg_image_url` con `get_post_meta( ..., false )` (todas las fotos, no solo la primera) y devuelve `images` como un array de URLs por publicación en vez de un string `url` único.
- **`page-nuestras-soluciones.php`** (§1.8): por cada publicación con más de una foto, renderiza todas las `<img class="ta-carousel-slide">` dentro de un contenedor `data-ta-carousel` (todas ocultas con el atributo `hidden` salvo la primera), más los botones de navegación y el contador. Si solo hay una foto, el markup queda igual que antes (sin controles).
- **`assets/js/ta-carousel.js`** (§1.9, archivo nuevo): script reutilizable que conecta los botones y el contador de cualquier `[data-ta-carousel]` de la página — mismo patrón de "buscar todos los `[data-...]` del DOM y conectarlos" que usan los componentes reutilizables del plugin (paginación, modal, repetidor de imágenes), pero implementado de cero para el tema porque el JS del panel del plugin no es reutilizable directamente: está encapsulado en una IIFE distinta, atado a `fetch()`/sesión JWT y sin build step ni módulo exportable entre plugin y tema.
- **CSS** (§1.1): se agregó `.ta-carousel-nav`/`.ta-carousel-counter` a `style.css`, con los mismos valores de opacidad-al-hover, tamaño y colores que `.jg-card-nav`/`.jg-card-counter` del plugin, para que el carrusel se vea igual en panel y en el sitio público. Se agregó `position: relative` a `.ta-solution-figure`, que faltaba (necesario para posicionar los controles en absoluto).
- `TA_VERSION` subido de `1.0.0` a `1.1.0` — se enqueua el nuevo `ta-carousel.js` con la misma constante que `style.css`, y ambos deben refrescarse en el navegador (lección de cache-busting documentada en Docs_Plugin.md §12.20, aplicada aquí también desde el primer commit del script).

**Verificación:** se pidió por `curl` la página "Nuestras Soluciones" en vivo y se confirmó que las dos publicaciones de prueba con más de una foto ("auto", 2 fotos, y otra con 3) traen el `data-ta-carousel`, las `<img class="ta-carousel-slide">` (todas menos la primera con `hidden`), los botones `.ta-carousel-nav--prev/--next` y el `<span class="ta-carousel-counter">1/2</span>` (o `1/3`); las publicaciones de una sola foto siguen sin ese markup. Se confirmó que `ta-carousel.js` y `style.css` se sirven con `?ver=1.1.0`. No se pudo probar el clic real de las flechas en un navegador (sin entorno gráfico disponible en esta sesión).

### 4.7 Bug: el carrusel se veía pero no cambiaba de imagen (regla global `img { display: block; }`)

El usuario reportó, con una captura de la tarjeta "auto" (contador mostrando "2/2"), que el carrusel de "Nuestras Soluciones" era visible pero no cambiaba de imagen al hacer clic. El JS de `initCarousel()` (§1.9) era correcto: el contador sí avanzaba (por eso se veía "2/2"), lo que descartaba un problema en la lógica de `show()`/los listeners de los botones.

**Causa real:** `style.css` (§1.1) tiene una regla global `img { display: block; }` (línea del reset inicial del tema). Como es una regla de **origen autor**, siempre gana sobre la regla `[hidden] { display: none }` del stylesheet por defecto del navegador (origen *user-agent*), sin importar especificidad — el atributo `hidden` de las `<img class="ta-carousel-slide">` no ocultas se seguía marcando correctamente en el DOM, pero visualmente todas las fotos quedaban en `display: block`, apiladas dentro de `.ta-solution-figure` (que tiene `overflow: hidden` y una altura fija por `aspect-ratio`), así que siempre se veía la primera, sin importar cuál fuera el índice actual del carrusel. Es el mismo patrón de bug que ya se había encontrado y corregido en el plugin (Docs_Plugin.md, regla `.jg-gallery [hidden] { display: none !important; }`), pero no se había replicado la misma defensa acá al construir el carrusel del tema.

**Corrección:** se agregó `.ta-carousel-slide[hidden] { display: none !important; }` en `style.css`, justo antes de las reglas `.ta-carousel-nav`. `TA_VERSION` subido de `1.1.0` a `1.1.1` en el mismo cambio.

**Verificación:** se confirmó por `curl` que la regla nueva se sirve en `style.css?ver=1.1.1` y que `ta-carousel.js` también se sirve con `?ver=1.1.1`. No se pudo confirmar visualmente el cambio de imagen al hacer clic en un navegador real (sin entorno gráfico en esta sesión) — queda pendiente de que el usuario lo confirme tras recargar la página.

**Lección:** al agregar cualquier componente nuevo que dependa del atributo `hidden` (como ya pasó con los formularios de edición del plugin y ahora con este carrusel), hay que revisar de entrada si existe alguna regla `display` de alcance amplio (`img { display: block; }`, `.algo { display: flex; }`, etc.) que pueda competir con él, en vez de esperar a que falle en producción.

### 4.8 Headers `Requires at least` y `Requires PHP` en `style.css`

Mismo pedido que en el plugin (Docs_Plugin.md §12.22): el tema no declaraba versión mínima de WordPress ni de PHP en su cabecera. Se agregaron `Requires at least: 6.0` y `Requires PHP: 7.4` al docblock de `style.css`, verificados con `get_file_data()` de WordPress. Cambio puramente de metadata — no afecta el comportamiento del tema, así que no se subió `TA_VERSION`.

### 4.9 Navbar rediseñado: logo propio, etiquetas nuevas y botón "Cotizar" (versión 1.2.0)

A pedido, con una captura de referencia del navbar deseado, se rehizo el header para que coincida visualmente:

- **Logo** (`header.php`, §1.3): se reemplazó el logo de texto (`bloginfo('name')`) por una imagen. El archivo se descargó una sola vez desde la URL que pasó el usuario (`https://i.postimg.cc/PqPb6fdn/Logo-todo-acrilico.webp`) y se guardó localmente en `assets/img/logo.webp` (§1 estructura de archivos) — no se hotlinkea el host externo en cada visita, evita depender de que ese servicio siga arriba. `width`/`height` en el `<img>` fijados a las dimensiones reales del archivo (360×356, escaladas a 90×89) para que el navegador reserve el espacio correcto y no haya salto de layout. CSS nuevo: `.ta-logo` pasa de estilos de texto a `display:flex; align-items:center;`, y `.ta-logo-img` fija la altura visible a 56px.
- **Etiquetas del menú:** "Nuestras Soluciones" → **"Catálogo"** y "Cuidados del Acrílico" → **"Cuidados"** (los slugs de las páginas y su contenido no cambiaron, solo el texto que se ve en el menú). "Inicio" y "Contacto" quedaron igual, coincidiendo con la captura de referencia.
- **Botón "Cotizar":** ítem de menú nuevo, sin página propia — enlaza a Contacto — con clase `ta-nav-cta` para que se vea como botón oscuro (no como un link de texto más), igual que en la referencia.
- **Flecha decorativa en "Catálogo":** clase `ta-nav-has-caret` + un `::after` en CSS (borde en L rotado 45°, sin SVG ni imagen) para el indicador "▾" de la referencia. Es puramente visual — **no** abre un submenú desplegable con las categorías; si se quiere ese comportamiento habría que construirlo aparte.
- Los cambios de CSS del navbar se escribieron con el selector `.ta-nav-list .ta-nav-has-caret`/`.ta-nav-list .ta-nav-cta` (no como reglas sueltas) a propósito: el mismo menú "Menú principal" se reutiliza también en el footer con la clase `ta-footer-nav-list` (§1.4), y sin ese scoping la flecha y el botón oscuro se hubieran filtrado también ahí, donde no correspondían.
- `TA_VERSION` subido de `1.1.1` a `1.2.0` (cambio de CSS/markup del header). El `Version:` del docblock de `style.css` se actualizó igual, a `1.2.0`.

**Nota de interpretación:** el mensaje del usuario decía textualmente "Cuidados del acrilico cambiar por Contacto", lo cual habría implicado fusionar/eliminar el ítem "Cuidados" — pero la captura de referencia muestra claramente 4 etiquetas de texto distintas (Inicio, Catálogo, Cuidados, Contacto) más el botón Cotizar. Se priorizó la imagen sobre el texto (probable error de tipeo) y se dejaron los 4 ítems más el botón, tal como se ve en la captura. Si la intención real era otra, hay que decírmelo para corregirlo.

**Verificación:** `php -l` sin errores en `header.php`/`functions.php`; se confirmó por `curl` que el menú principal en `/` trae las 5 etiquetas correctas en el orden correcto (Inicio, Catálogo, Cuidados, Contacto, Cotizar), que el logo (`assets/img/logo.webp`) responde `200`, y que `style.css`/`ta-carousel.js` se sirven con `?ver=1.2.0`. Se confirmó que las clases `ta-nav-has-caret`/`ta-nav-cta` llegan también al footer (mismo menú) pero sin aplicar estilo visual ahí, por el scoping a `.ta-nav-list`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.10 Hero de Inicio rediseñado: eyebrow + título + botones + imagen de fondo editable (versión 1.3.x)

A pedido, con una captura de referencia del hero de Inicio deseado, se reconstruyó `front-page.php` en dos pasos:

**Bug encontrado de paso (versión 1.2.0 del tema):** al hacer esto se detectó que la vitrina de imágenes debajo del hero (primeras 4 fotos de la primera categoría) estaba rota — `<img src="">` vacío — porque leía `$image['url']`, una clave que ya no existe desde que `ta_get_solutions_by_category()` se cambió a `$image['images']` (array de fotos, Docs_Plugin.md §12.19) al construir el carrusel del tema (§4.6). Quedó sin detectarse porque nada había vuelto a tocar `front-page.php` desde ese cambio. Se corrigió a `$image['images'][0]` (primera foto de esa publicación).

**Primer intento — hero de dos columnas:** texto a la izquierda, imagen en una caja aparte a la derecha (`.ta-hero-media`, con estado vacío en caja punteada si no había imagen todavía). El usuario corrigió: la imagen debía ir **como fondo de la sección, no al lado del texto**.

**Versión final:**
- **`ta_button()`** (nuevo, `functions.php` §1.2): componente de botón reutilizable, variantes `accent`/`dark`/`outline`, flecha opcional. Usado para "Ver catálogo" (`dark` + flecha, enlaza a `nuestras-soluciones`) y "Cotizar un proyecto" (`outline`, enlaza a `contacto`).
- **Imagen del hero editable sin tocar código:** `ta_customize_register()` (`functions.php` §1.2) agrega un control de imagen al Personalizador (`Apariencia → Personalizar → Identidad del sitio → Imagen del hero (Inicio)`), guardado como `theme_mod` `ta_hero_image_url` (sanitizado con `esc_url_raw`). `ta_get_hero_image_url()` lo expone; devuelve `''` mientras no se haya cargado nada.
- **La imagen se aplica como `background-image` de `<section class="ta-hero">`** (atributo `style` inline, solo cuando hay URL), con un degradado blanco superpuesto (`linear-gradient(90deg, rgba(255,255,255,0.94) 0%, ... 0.35) 100%)`) para que el texto (a la izquierda) siga siendo legible sin importar el contenido de la foto. Sin imagen cargada, la sección usa el degradado gris de respaldo de siempre y se muestra un aviso corto (`.ta-hero-image-hint`) con las instrucciones para cargarla desde el Personalizador — no hay ningún `<img>` roto ni hueco vacío mientras tanto.
- `TA_VERSION` subido 1.2.0 → 1.3.0 (primer intento, dos columnas) → 1.3.1 (versión final, fondo). El `Version:` del docblock de `style.css` sigue la misma numeración.

**Verificación:** `php -l` sin errores en `functions.php`/`front-page.php`. Por `curl`: la vitrina debajo del hero ya trae `src` con URL real (bug corregido). Se probó temporalmente `set_theme_mod('ta_hero_image_url', 'https://example.com/test-hero.jpg')` y se confirmó que la sección recibe la clase `ta-hero--has-image` y el `style` con el degradado + `url(...)` correctos; se revirtió con `remove_theme_mod()` antes de terminar, sin dejar datos de prueba. Se confirmó también que el control `ta_hero_image_url` se registra en el Personalizador sin errores (`WP_Customize_Image_Control`). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — falta cargar la imagen real y revisar que el degradado deje el texto legible sobre la foto definitiva.

### 4.11 Grilla de categorías reutilizable en Inicio (`ta_render_feature_grid()`, versión 1.4.0)

A pedido, con una captura de referencia (eyebrow "CATEGORÍAS", título "Cuatro maneras de trabajar el acrílico.", 4 tarjetas con ícono/título/bajada/link "Ver piezas →"), se reemplazó la vitrina automática que había debajo del hero de Inicio (primeras 4 imágenes de la primera categoría no vacía) por un componente reutilizable nuevo:

- **`ta_icon( $name )`** (`functions.php` §1.2): banco de 5 íconos SVG en línea (`grid`, `eye`, `shield`, `info`, `pane`), solo trazo (`stroke="currentColor"`, sin relleno) para heredar el color vía CSS. Nombre no reconocido → ícono genérico (círculo), para que nunca falte un ícono.
- **`ta_render_feature_grid( $eyebrow, $title, $items )`** (`functions.php` §1.2): imprime la sección completa (eyebrow + título + grilla de tarjetas). Cada tarjeta usa `ta_button()` (§4.10) con la variante nueva `'text'` (sin fondo ni borde) para "Ver piezas →". No tiene nada de categorías "quemado" en el componente — recibe `$items` como parámetro, así que sirve para cualquier otra grilla ícono+texto+link que se necesite después.
- **`ta_get_solutions_by_category()`** (`functions.php` §1.2): se agregó `'slug' => $term->slug` a cada grupo, y **`page-nuestras-soluciones.php`** (§1.8) le puso `id="categoria-{slug}"` a cada `.ta-solutions-group`, para que los links "Ver piezas →" puedan saltar directo a la categoría correspondiente en la página Catálogo (`#categoria-organizador`, etc.).
- **`front-page.php`** (§1.6) llama a `ta_render_feature_grid()` con las 4 tarjetas de la captura (Organizar/Exhibir/Proteger/Informar, copy exacto de la referencia) en vez de las 5 categorías reales de la taxonomía — se dejó "vidrios" fuera a propósito porque no aparecía en la captura y el título dice "cuatro maneras". Los links sí son reales (slugs verificados contra la base de datos: `exhibir`, `informar`, `organizador`, `proteger`, `vidrios`).
- **Bug encontrado y corregido de paso:** la vitrina automática que se reemplazó tenía `<img src="">` roto — leía `$image['url']`, una clave que ya no existe desde que `ta_get_solutions_by_category()` devuelve `$image['images']` (array de fotos, §4.6/Docs_Plugin.md §12.19). Nadie lo había notado porque nada volvió a tocar `front-page.php` desde ese cambio de modelo de datos.
- `TA_VERSION` subido 1.3.1 → 1.4.0.

**Verificación:** `php -l` sin errores en los 3 archivos PHP tocados. Por `curl`: las 4 tarjetas de Inicio traen el ícono, título, bajada y link correctos, con `href` apuntando a `nuestras-soluciones#categoria-{slug}`; la página Catálogo trae los `id="categoria-{slug}"` correspondientes en los grupos que sí tienen imágenes publicadas (`exhibir`, `vidrios` en este entorno — `organizador`/`proteger`/`informar` todavía no tienen imágenes, así que ese link no tiene ancla que encontrar hasta que se suban fotos a esas categorías, pero sigue llevando a la página Catálogo sin error). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.12 Grilla de "destacados" reutilizable en Inicio (`ta_render_showcase_grid()`, versión 1.5.0)

A pedido, con una segunda captura de referencia ("DESTACADOS" / "Piezas más solicitadas." / link "Ver todo el catálogo" / 3 tarjetas grandes de foto — Buzón, Organizador de escritorio, Bandeja modular), se agregó un componente reutilizable nuevo debajo de la grilla de categorías (§4.11):

- **`ta_render_showcase_grid( $eyebrow, $title, $items, $cta = null )`** (`functions.php` §1.2): distinto de `ta_render_feature_grid()` — tarjetas grandes de **foto** (sin ícono, sin borde), y un link de encabezado opcional (`$cta`) en vez de un botón por tarjeta. Usa `ta_button()` con la variante `text` + una clase extra `ta-showcase-cta` (subrayado verde) para "Ver todo el catálogo".
- El usuario indicó explícitamente que las fotos de las 3 piezas ("son URL") las va a agregar más adelante. Por eso `front-page.php` llama al componente con `image_url => ''` en los 3 `$items` — el componente ya contempla ese caso: si `image_url` viene vacío, pinta un placeholder rayado (`.ta-showcase-figure-empty`) en vez de un `<img src="">` roto. Cuando el usuario mande las URLs reales, solo hay que completar el array de `$items` en `front-page.php` — no hay que tocar el componente ni el CSS.
- `TA_VERSION` subido 1.4.0 → 1.5.0.

**Verificación:** `php -l` sin errores en `functions.php`/`front-page.php`. Por `curl`: la sección trae el eyebrow, título, link "Ver todo el catálogo" (enlazado a `nuestras-soluciones`) y las 3 tarjetas con título/bajada correctos y el placeholder rayado en lugar de imagen (ya que `image_url` está vacío en los 3). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.13 Sección "Nuestra historia" reutilizable en Inicio (`ta_render_story_section()`, versión 1.6.0)

A pedido, con una tercera captura de referencia (foto grande a la izquierda con insignia del logo superpuesta, "NUESTRA HISTORIA" / "Más de 20 años trabajando el acrílico a medida." / 2 párrafos / línea divisoria / 2 estadísticas "5k+ Proyectos realizados" y "20+ Años de experiencia"), se agregó un tercer componente reutilizable, debajo de "Destacados" (§4.12):

- **`ta_render_story_section( $args )`** (`functions.php` §1.2): foto + insignia a la izquierda, eyebrow + título + párrafos + estadísticas a la derecha. La insignia (`.ta-story-badge`, esquina inferior derecha de la foto, con sombra) reutiliza el logo del tema que ya existía en el sitio (`assets/img/logo.webp`, agregado en §4.9) — no necesitó ninguna URL nueva, a diferencia de la foto principal.
- Igual que `ta_render_showcase_grid()` (§4.12), la foto principal se pasa con `image_url => ''` porque el usuario indicó que la va a agregar más adelante; mientras tanto se ve el placeholder rayado (`.ta-story-media-empty`), consistente visualmente con el mismo patrón ya usado en "Destacados" y en el hero.
- `TA_VERSION` subido 1.5.0 → 1.6.0.

**Verificación:** `php -l` sin errores en `functions.php`/`front-page.php`. Por `curl`: la sección trae el eyebrow, título, los 2 párrafos, las 2 estadísticas (`5k+`/`20+`) y el placeholder rayado en la foto; la insignia (`.ta-story-badge`) trae el `<img>` apuntando al logo local del tema, no a una URL externa. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.14 Sección de cierre reutilizable en Inicio (`ta_render_cta_section()`, versión 1.7.0)

A pedido, con una cuarta captura de referencia ("¿Tienes una idea en mente?" / bajada / botón oscuro "Hablemos de tu proyecto", todo centrado), se agregó el quinto y último componente reutilizable de Inicio, al final de la página:

- **`ta_render_cta_section( $title, $description, $button_label, $button_url )`** (`functions.php` §1.2): título + bajada opcional + un botón `ta_button()` en variante `dark`, todo centrado (`.ta-cta-inner`, `max-width: 640px`, `margin: 0 auto`). Es el más simple de los 5 componentes de Inicio — no maneja imágenes ni tiene estado vacío que resolver, así que no aplica el patrón de placeholder de los otros 3 (§4.11, §4.12, §4.13).
- `front-page.php` lo llama con el copy exacto de la captura y el botón enlazado a la página `contacto` (reutilizando `$contacto_url`, ya calculado para el hero, §4.10).
- `TA_VERSION` subido 1.6.0 → 1.7.0.

**Verificación:** `php -l` sin errores en `functions.php`/`front-page.php`. Por `curl`: la sección trae el título, la bajada y el botón "Hablemos de tu proyecto" con `href` apuntando a la URL real de la página Contacto. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.15 Footer rediseñado: 3 columnas, fondo claro, datos de contacto (versión 1.8.0)

A pedido, con una captura de referencia (logo + bajada / columna "Explorar" / columna "Contacto" con íconos / barra inferior con copyright, 2 íconos decorativos y 2 textos legales), se reconstruyó `footer.php` de cero — el footer anterior era una sola barra oscura con copyright + el menú `primary` repetido:

- **`ta_icon()`** (`functions.php` §1.2) ganó un segundo parámetro, `$size` (default 28), y 5 íconos nuevos: `'pin'`, `'mail'`, `'clock'`, `'undo'`, `'redo'`. `$size` reemplaza el `width="28" height="28"` del SVG devuelto — así el mismo banco de íconos sirve para las tarjetas de categoría (28px) y para el footer (18px/16px), sin duplicar SVGs.
- **`footer.php`** (§1.4) ya no llama a `wp_nav_menu()`: arma la columna "Explorar" a mano con `get_page_by_path()` (Inicio/Catálogo/Cuidados/Contacto, 4 links) porque el menú `primary` real tiene 5 ítems (incluye "Cotizar", §4.9) y la referencia solo mostraba 4. La columna "Contacto" es texto de ejemplo tomado literalmente de la captura (`Santiago, Chile`, `hola@todoacrilico.cl`, horario) — **no son datos verificados del negocio real**, hay que confirmarlos.
- El fondo del footer cambió de oscuro (`var(--ta-ink)`) a claro (`var(--ta-bg)`), consistente con el resto del sitio — es el cambio visual más grande del rediseño.
- Los 2 íconos circulares de la barra inferior (`undo`/`redo`) se agregaron como **decorativos únicamente** (`aria-hidden="true"`, sin `<a>` ni `<button>`, sin `onclick`): la captura no dejaba clara su función, así que se replicó solo la apariencia en vez de inventarles un comportamiento.
- "Términos y condiciones" enlaza a `#` — **no existe esa página todavía**, es un placeholder visual hasta que se cree el contenido legal real.
- `TA_VERSION` subido 1.7.0 → 1.8.0.

**Verificación:** `php -l` sin errores en `functions.php`/`footer.php`. Por `curl`: las 4 columnas y sus datos coinciden con la captura, los 4 links de "Explorar" traen URLs reales de páginas existentes, el copyright muestra el año actual (`gmdate('Y')`) + "TODO Acrílico SpA", y los íconos se sirven al tamaño correcto (18px en la columna Contacto, 16px en la barra inferior). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.16 Íconos decorativos "undo"/"redo" del footer, eliminados (versión 1.8.1)

A pedido explícito del usuario ("del footer quita estos iconos negros", con una captura recortada de los 2 círculos), se quitaron por completo los íconos decorativos que se habían agregado en §4.15:

- **`footer.php`** (§1.4): se eliminó el `<div class="ta-footer-deco">` completo (los 2 `<span>` con `ta_icon('undo'|'redo', 16)`).
- **`style.css`** (§1.1): se eliminaron las reglas `.ta-footer-deco` y `.ta-footer-deco-btn`.
- **`ta_icon()`** (`functions.php` §1.2): se eliminaron las entradas `'undo'` y `'redo'` del banco de íconos — se verificó primero con `grep` que no se usaban en ningún otro archivo del tema antes de borrarlas, para no dejar íconos muertos en el código pero tampoco romper nada.
- `TA_VERSION` subido 1.8.0 → 1.8.1.

**Verificación:** `php -l` sin errores en `functions.php`/`footer.php`. Por `curl`: `ta-footer-deco` ya no aparece en el HTML servido, y el resto de la barra inferior (copyright + "Términos y condiciones" + "Diseñado con foco en usabilidad") sigue intacto. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.17 Barra de scroll oculta en todas las páginas (versión 1.8.2)

El usuario pidió "quitar el scroll a todas las pestañas", junto con una captura mostrando la barra de scroll vertical del navegador. Antes de tocar código se preguntó qué significaba exactamente ("ocultar la barra de scroll" vs. "corregir un espacio en blanco de más"), porque son dos problemas distintos con soluciones distintas — el usuario confirmó que era lo primero: **ocultar la barra de scroll visualmente**, sin quitar la posibilidad de scrollear.

Se agregó a `style.css` (§1.1), en la regla `html` del reset inicial:

```css
scrollbar-width: none; /* Firefox */
-ms-overflow-style: none; /* IE / Edge legacy */
```

y una regla nueva `html::-webkit-scrollbar { display: none; }` (Chrome/Safari/Edge Chromium). Al estar en el selector `html` (no en un contenedor específico), aplica a **todas** las páginas del sitio automáticamente, sin tocar ninguna plantilla — todas comparten el mismo `style.css` vía `header.php` (§1.3). El scroll con teclado, rueda del mouse o gestos táctiles sigue funcionando igual; solo desaparece la barra visual.

`TA_VERSION` subido 1.8.1 → 1.8.2.

**Verificación:** `php -l` sin errores en `functions.php`. Por `curl`, se confirmó que las 4 rutas principales (Inicio, Catálogo, Cuidados, Contacto) sirven `style.css?ver=1.8.2`, y que ese archivo contiene las 3 reglas nuevas. No se pudo confirmar visualmente en un navegador real que la barra efectivamente desaparece (sin entorno gráfico en esta sesión) — es una técnica CSS estándar y ampliamente soportada, pero queda pendiente la confirmación visual.

### 4.18 Navbar más ancho y logo más grande (versión 1.8.3)

A pedido, se ajustaron dos medidas del header en `style.css` (§1.1):

- `.ta-header-inner`: `max-width` de `1100px` a `1320px` — el navbar ahora es más ancho que el resto del contenido del sitio (que sigue en `1100px` vía `.ta-container`), a propósito, ya que el pedido era específico para el navbar.
- `.ta-logo-img`: `height` de `56px` a `76px` (~35% más grande). `width: auto` ya estaba, así que el ancho se ajusta solo manteniendo la proporción del logo — no hizo falta tocar los atributos `width`/`height` del `<img>` en `header.php` (son solo para reservar espacio y evitar saltos de layout, el tamaño visible lo controla el CSS).

`TA_VERSION` subido 1.8.2 → 1.8.3.

**Verificación:** `php -l` sin errores en `functions.php`. Por `curl`, se confirmó que `style.css?ver=1.8.3` se sirve en la portada y que contiene ambos valores nuevos (`max-width: 1320px` en `.ta-header-inner`, `height: 76px` en `.ta-logo-img`). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.19 Corrección: navbar a todo el ancho, no solo "más ancho" (versión 1.8.4)

Después de §4.18, el usuario mandó una captura aclarando que quería el navbar **a todo el ancho del viewport** (logo pegado al borde izquierdo, "Cotizar" pegado al borde derecho, sin columna centrada) — no solo un `max-width` mayor que seguía centrando el contenido con márgenes grandes en pantallas anchas, que es lo que se había hecho en §4.18.

Se quitó por completo el `max-width: 1320px` y el `margin: 0 auto` de `.ta-header-inner` (`style.css` §1.1), dejando solo `padding: 16px 32px` (subido de `24px` a `32px` de lado para que no quede pegado literalmente al borde del navegador). Sin `max-width`, el navbar ahora ocupa el 100% del ancho de `.ta-header`, que ya era full-bleed. El resto del sitio (`.ta-container`, `max-width: 1100px`) no se tocó — el pedido era específicamente sobre el navbar.

`TA_VERSION` subido 1.8.3 → 1.8.4.

**Verificación:** `php -l` sin errores en `functions.php`. Por `curl`, se confirmó que `style.css?ver=1.8.4` se sirve en la portada y que `.ta-header-inner` ya no tiene `max-width` ni `margin: 0 auto`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.20 Navbar tapado por la barra de administración de WordPress al hacer scroll (versión 1.8.5)

El usuario, con sesión iniciada en wp-admin, reportó que el navbar se veía "cortado" al hacer scroll — una captura mostraba el logo con la mitad superior tapada por una barra negra. **Causa:** `#wpadminbar` (la barra de WordPress con el saludo "Hola, {usuario}") es `position: fixed; top: 0;` con `z-index: 99999` — muy por encima del `z-index: 20` del navbar del tema. El navbar (`.ta-header`, `position: sticky; top: 0;`) seguía pegado al borde superior real del viewport, así que al hacer scroll quedaba **debajo** de la barra de admin, con su parte superior tapada.

**Importante:** este problema **solo ocurre con sesión de WordPress iniciada** — un visitante público del sitio (sin `#wpadminbar`) nunca lo ve. Por eso no lo había detectado ninguna de las verificaciones anteriores por `curl` (curl no está autenticado, así que nunca carga la barra de admin).

**Corrección:** en `style.css` (§1.1), se agregó `body.admin-bar .ta-header { top: 32px; }` — WordPress agrega automáticamente la clase `admin-bar` al `<body>` cuando el usuario tiene sesión iniciada y la barra está activa, así que esta regla solo aplica en ese caso, sin afectar la vista pública. Se agregó también `body.admin-bar .ta-header { top: 46px; }` dentro de `@media (max-width: 782px)`, replicando el mismo breakpoint que usa el propio core de WordPress para agrandar `#wpadminbar` a 46px en pantallas chicas.

**Nota sobre versiones desincronizadas:** al revisar el archivo se encontró que el `Version:` del docblock de `style.css` había quedado en `1.3.1` mientras que `TA_VERSION` (la constante real usada para el cache-busting) ya iba en `1.8.4` — el usuario había estado editando `style.css` directamente en el editor (probando distintos `max-width` para `.ta-header-inner`, ver conversación) y en algún punto ese campo quedó desactualizado. Se resincronizaron ambos a `1.8.5`.

**Verificación:** `php -l` sin errores en `functions.php`. Por `curl`, se confirmó que `style.css?ver=1.8.5` se sirve en la portada y que contiene las reglas `body.admin-bar .ta-header` (32px y 46px). No se pudo probar con sesión real iniciada en un navegador (sin entorno gráfico en esta sesión) — la corrección se basa en el comportamiento documentado y estándar de `#wpadminbar` en WordPress core, no en una repetición visual de la captura del usuario.

### 4.21 Footer "desconfigurado" restaurado y ancho sincronizado con el navbar (`--ta-shell-width`, versión 1.8.6)

El usuario reportó que el footer "se desconfiguró" y pidió que tuviera el mismo ancho que el navbar. Al revisar `style.css` se encontró la causa: el bloque `/* ---- footer ---- */` había quedado con las reglas **viejas**, previas al rediseño de §4.15 (`.ta-footer` oscuro, `.ta-footer-inner`, `.ta-footer-nav-list`) — ninguna de esas clases existe en el `footer.php` actual (que usa `.ta-footer-top`, `.ta-footer-col`, `.ta-footer-heading`, `.ta-footer-links`, `.ta-footer-contact-list`, `.ta-footer-bottom`, etc.). El HTML y el CSS habían quedado desincronizados — probablemente durante la edición manual del usuario en el editor mientras probaba distintos `max-width` para el navbar (§4.18/§4.19/conversación) — así que el footer se renderizaba sin ningún estilo del tema.

**Corrección — 2 partes:**

1. Se reconstruyó por completo el bloque de CSS del footer (`.ta-footer-top`, `.ta-footer-logo`, `.ta-footer-brand`, `.ta-footer-heading`, `.ta-footer-links`, `.ta-footer-contact-list`, `.ta-footer-icon`, `.ta-footer-bottom`, `.ta-footer-copy`, `.ta-footer-legal`, más el `@media (max-width: 860px)` de la grilla y el `@media (max-width: 640px)` de la barra inferior), igual al que se había perdido, verbatim contra lo documentado en §4.15/§4.16.
2. Se agregó una variable nueva en `:root` (`style.css` §1.1): **`--ta-shell-width: 2200px`**. `.ta-header-inner`, `.ta-footer-top` y `.ta-footer-bottom` ahora usan `max-width: var(--ta-shell-width)` en vez de un valor fijo cada uno — así quedan sincronizados automáticamente: cambiar `--ta-shell-width` una sola vez mueve el ancho del navbar y de las 2 franjas del footer a la vez, sin tener que editar 3 lugares y sin riesgo de que se vuelvan a desalinear.

Se verificó con `python3` que las llaves `{`/`}` del archivo completo quedan balanceadas (94/94) y que no hay selectores duplicados, antes de dar por buena la reconstrucción.

`TA_VERSION` subido 1.8.5 → 1.8.6.

**Verificación:** `php -l` sin errores en `functions.php`. Por `curl`: el HTML del footer (`.ta-footer-top`, `.ta-footer-bottom`, `.ta-footer-heading`) coincide con las clases que ahora sí existen en `style.css?ver=1.8.6`, y tanto `.ta-header-inner` como `.ta-footer-top`/`.ta-footer-bottom` usan `max-width: var(--ta-shell-width)`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.22 Todos los componentes de sección alineados al ancho del navbar (versión 1.8.7)

A pedido ("todos los componentes deben tener el mismo ancho que el nav"), se extendió el uso de la variable `--ta-shell-width` (creada en §4.21 para sincronizar navbar y footer) a los 2 selectores contenedores que faltaban en `style.css` (§1.1):

- **`.ta-container`**: pasó de `max-width: 1100px` fijo a `max-width: var(--ta-shell-width)`. Es el contenedor que envuelve la página Catálogo completa y, más importante, los 4 componentes reutilizables de Inicio construidos en `functions.php` — `ta_render_feature_grid()` (§4.11), `ta_render_showcase_grid()` (§4.12), `ta_render_story_section()` (§4.13) y `ta_render_cta_section()` (§4.14) todos envuelven su contenido en un `<div class="ta-container">` (confirmado con `grep` antes de tocar el CSS). Este era el cambio que de verdad afectaba a "todos los componentes".
- **`.ta-hero-inner`**: mismo cambio, de `1100px` fijo a `var(--ta-shell-width)` — es el único contenedor de sección que no reutiliza `.ta-container` (tiene su propio wrapper).

Con esto, navbar, footer (§4.21) y los 5 bloques de Inicio (hero + 4 componentes) comparten el mismo ancho máximo, controlado desde un solo lugar (`--ta-shell-width` en `:root`).

**Lo que se dejó fuera, a propósito:** `.ta-page-content` (720px, usado en `page.php` — Cuidados del Acrílico y Contacto) y los anchos en `ch` (`46ch`, `60ch`, `30ch`, `32ch` — límites de longitud de línea de texto dentro de tarjetas/párrafos, no anchos de sección). Estirar un bloque de texto corrido a 2200px de ancho lo haría casi imposible de leer (las líneas quedarían demasiado largas) — es una restricción de legibilidad tipográfica, no un contenedor de layout como el navbar o las grillas. Si en realidad se quería que **también** las páginas de puro texto fueran a todo el ancho, hay que decirlo explícitamente para cambiar `.ta-page-content` también.

`TA_VERSION` subido 1.8.6 → 1.8.7.

**Verificación:** `php -l` sin errores en `functions.php`; conteo de llaves `{`/`}` balanceado (94/94) en `style.css`. Por `curl`, se confirmó que `style.css?ver=1.8.7` se sirve en la portada y que tanto `.ta-container` como `.ta-hero-inner` usan `max-width: var(--ta-shell-width)`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.23 Imagen real del hero + título más grande + fondo gris (versión 1.8.8)

El usuario mandó la URL de la foto real para el fondo del hero (`https://i.postimg.cc/TYX3CTrK/home.webp`, la pila de láminas de acrílico) y dos capturas comparando cómo se veía contra cómo debía verse — pidiendo explícitamente 2 ajustes: **texto más grande** y **fondo gris** (no blanco).

- **Imagen del hero:** se verificó primero que la URL respondiera `200` con `content-type: image/webp` (`curl -I`), y se cargó con `set_theme_mod( 'ta_hero_image_url', 'https://i.postimg.cc/TYX3CTrK/home.webp' )` — el mismo mecanismo que un editor real usaría desde **Personalizar → Identidad del sitio** (§4.10), solo que aplicado por línea de comandos al tener acceso al servidor. No se descargó la imagen localmente (a diferencia del logo, §4.9): esta es contenido editable vía Personalizador por diseño, igual que las fotos de "Destacados" y "Nuestra historia", así que apunta a la URL externa directamente.
- **Texto más grande:** `.ta-hero h1` (`style.css` §1.1) pasó de `clamp(2.2rem, 4.2vw, 3rem)` a `clamp(2.6rem, 5.4vw, 4rem)`, con `line-height` de `1.15` a `1.1`. `.ta-hero` ganó más padding vertical (`72px` → `96px`) para que el bloque respire más, como en la referencia.
- **Fondo gris, no blanco:** el degradado de respaldo de `.ta-hero` (cuando no hay imagen) pasó de `linear-gradient(180deg, #ffffff, var(--ta-bg))` a `linear-gradient(180deg, var(--ta-bg), var(--ta-bg))` — gris parejo en vez de blanco arriba. El degradado de superposición sobre la foto (en `front-page.php`, aplicado inline solo cuando hay `image_url`) cambió de blanco (`rgba(255,255,255,...)`) a gris (`rgba(245,247,246,...)`, el mismo valor que `--ta-bg`), manteniendo la misma progresión de opacidad (más opaco a la izquierda, donde está el texto, más transparente hacia la derecha).
- No se tocó el color verde de "pensadas para durar." (`.ta-hero-title-accent`, usa `var(--ta-accent)`) — el usuario pidió específicamente "texto más grande" y "fondo gris", no un cambio de color, así que se dejó igual.

`TA_VERSION` subido 1.8.7 → 1.8.8 (de paso se encontró que `functions.php` había quedado en `1.8.6` mientras `style.css` ya iba en `1.8.7` — otra vez desincronizados por edición manual entremedio; se resincronizaron ambos a `1.8.8`).

**Verificación:** `php -l` sin errores en `functions.php`/`front-page.php`; conteo de llaves balanceado (94/94) en `style.css`. Por `curl`, se confirmó que la portada trae `background-image: linear-gradient(90deg, rgba(245,247,246,...)..., url('https://i.postimg.cc/TYX3CTrK/home.webp')` y que `style.css?ver=1.8.8` contiene el nuevo `font-size` del `h1` y el nuevo `padding`/degradado gris de `.ta-hero`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.24 Corrección del hero: columna de texto angosta, foto sin recortar y gris más oscuro (versión 1.8.9)

Después de cargar la foto real (§4.23), el usuario mandó la misma captura de referencia dos veces más señalando que el resultado no se parecía: el texto quedaba "muy a la izquierda y muy pequeño", y después aclaró que además la imagen de fondo se veía "muy grande" y pidió un gris más oscuro. Diagnóstico: el cambio de §4.22 ("todos los componentes deben tener el mismo ancho que el nav") le había puesto `max-width: var(--ta-shell-width)` (2200px) a `.ta-hero-inner` — en la mayoría de las pantallas eso equivale a "sin límite", así que el texto quedaba estirado a todo el ancho del viewport en vez de mantenerse en una columna angosta como en la referencia; al verse "perdido" en un contenedor tan ancho, además parecía más pequeño y pegado al borde izquierdo.

**Correcciones — 4 partes, todas en `style.css` (§1.1) salvo la indicada:**

1. **`.ta-hero-inner`**: `max-width` de `var(--ta-shell-width)` a `620px` fijo, y `margin` de `0 auto` a `0` (ya no se centra: queda alineado a la izquierda dentro del `padding` de `.ta-hero`, igual que en la referencia). Este contenedor queda deliberadamente **fuera** del sistema `--ta-shell-width` de §4.21/§4.22 — es la única sección de Inicio con foto de fondo a página completa; estirar el texto al mismo ancho que el navbar lo hace ver perdido.
2. **`.ta-hero h1`**: `font-size` de `clamp(2.6rem, 5.4vw, 4rem)` a `clamp(2.75rem, 5.6vw, 4.25rem)`.
3. **Imagen de fondo sin recortar**: `.ta-hero` pasó de `background-size: cover; background-position: center;` a `background-size: contain; background-position: right center; background-repeat: no-repeat;` — con `cover`, la foto (1280×721, verificado con `getimagesize()`) se estiraba/recortaba para cubrir toda la sección, viéndose muy agrandada/recortada; con `contain` se ve completa, sin recortar, anclada a la derecha como en la referencia.
4. **Gris más oscuro**: el degradado de respaldo de `.ta-hero` y el degradado de superposición sobre la foto (inline, `front-page.php`) pasaron de un gris casi blanco (`var(--ta-bg)` / `rgba(245,247,246,...)`) a un gris más marcado, `#dde1e0` / `rgba(221,225,224,...)` — específico de esta sección, no se tocó `--ta-bg` global para no afectar el resto del sitio. También se subió el `padding` horizontal de `.ta-hero` de `24px` a `48px`, para que el texto no quede pegado al borde.

`TA_VERSION` subido 1.8.8 → 1.8.9.

**Verificación:** `php -l` sin errores en `functions.php`/`front-page.php`; llaves balanceadas (94/94) en `style.css`. Por `curl`: `style.css?ver=1.8.9` trae `max-width: 620px` en `.ta-hero-inner`, `background-size: contain; background-position: right center;` en `.ta-hero`, y el nuevo `font-size` del `h1`; la portada trae el degradado inline con los valores `rgba(221,225,224,...)` nuevos. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — tras 3 rondas de ajuste sin poder ver el resultado en pantalla, esta sigue siendo la limitación central de esta verificación.

### 4.25 Bug: regla `.ta-hero--has-image { height: 1200px; }` inflaba el hero (versión 1.9.0)

El usuario mandó una segunda captura mostrando el hero real como una franja gris enorme, casi vacía, con el texto y la foto reducidos a una esquina — muy distinto de la referencia. Al revisar `style.css` se encontró que había una regla nueva, agregada fuera de mis ediciones (probablemente mientras el usuario probaba cosas en el editor):

```css
.ta-hero--has-image{
   	
	height: 1200px;
}
```

Esa clase (`ta-hero--has-image`) la agrega `front-page.php` a la sección cuando hay una imagen de fondo cargada (§4.10) — forzarle `height: 1200px` hacía que la sección midiera 1200px de alto sin importar el contenido, dejando todo el texto arriba y una franja gris gigante debajo, y además con `background-size: contain` la foto se veía chica y flotando lejos del texto dentro de una caja tan alta.

**Corrección:** se eliminó por completo la regla — el alto del hero vuelve a determinarlo el contenido más el `padding` de `.ta-hero` (§4.24), como el resto de las secciones del tema. `TA_VERSION` subido a `1.9.0` (de paso, otra vez desincronizado: `functions.php` iba en `1.8.9` y el docblock de `style.css` había vuelto a `1.8.8`; se resincronizaron ambos).

**Nota:** de paso, el usuario (o el editor) también cambió `.ta-hero-inner` de `max-width: 620px` (§4.24) a `max-width: 720px` — se dejó tal cual, es un ajuste del usuario, no un error.

**Verificación:** `php -l` sin errores en `functions.php`; llaves balanceadas (94/94) en `style.css`. Por `curl` se confirmó que `style.css?ver=1.9.0` ya no contiene `ta-hero--has-image` ni `height: 1200px` en ningún lado. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — sigue pendiente la aprobación final del usuario viendo el resultado en su propio navegador.

### 4.26 Confirmación: el layout ya coincide con la referencia; título del hero agrandado de nuevo (versión 1.9.1)

El usuario mandó una captura de la portada completa (hero + grilla de categorías) confirmando que, tras corregir el bug de `height: 1200px` (§4.25), el layout **ya se parece** a la referencia — el único pendiente que señaló fue agrandar el título del hero un poco más ("actualmente solo se parece, si debes agrandar la letra para conseguirlo"). También aclaró una duda anterior: no había tocado ni pedido tocar el navbar (§4.25, mensaje previo) — la duda quedó resuelta al confirmar que el layout general ya está bien.

Se subió `.ta-hero h1` de `clamp(2.75rem, 5.6vw, 4.25rem)` a `clamp(3.2rem, 6.4vw, 4.75rem)`, con `line-height` de `1.1` a `1.05` (títulos más grandes suelen verse mejor con interlineado más ajustado). `TA_VERSION` subido 1.9.0 → 1.9.1.

**Verificación:** `php -l` sin errores en `functions.php`; llaves balanceadas (94/94) en `style.css`. Por `curl`, se confirmó que `style.css?ver=1.9.1` trae el nuevo `font-size` del `h1`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — sigue pendiente que el usuario confirme si este tamaño ya es suficiente o si hay que seguir ajustando.

### 4.27 Fotos reales de "Destacados" cargadas

El usuario mandó las 3 URLs de fotos reales para la sección "Destacados" (Buzón, Organizador de escritorio, Bandeja modular) que habían quedado con placeholder en §4.12. Se verificó primero que las 3 URLs respondieran `200` (`curl -I`) y se completó el argumento `image_url` de cada `$item` en la llamada a `ta_render_showcase_grid()` en `front-page.php` (§1.6):

| Pieza | URL |
|---|---|
| Buzón | `https://i.postimg.cc/fbkX7YdT/buzon-acrilico-transparente.webp` |
| Organizador de escritorio | `https://i.postimg.cc/qqcCBwf4/organizador-de-escritorio.webp` |
| Bandeja modular | `https://i.postimg.cc/jjGWzmtK/bandeja-modular.webp` |

No hizo falta subir `TA_VERSION`: es un cambio de contenido dentro de `front-page.php` (URLs en el array de `$items`), no de `style.css`/`ta-carousel.js` — esos son los únicos archivos que el cache-busting de `TA_VERSION` cubre (§4.15/lección de Docs_Plugin.md §12.20). El HTML de `front-page.php` se genera de nuevo en cada carga de página, sin caché de por medio.

**Verificación:** `php -l` sin errores en `front-page.php`. Por `curl`, se confirmó que las 3 tarjetas ya traen `<img src="...">` con las URLs reales y que ya no aparece ningún `.ta-showcase-figure-empty` (el placeholder rayado) en la portada. Con esto, "Destacados" queda completo — solo falta la foto de "Nuestra historia" (§4.13, sigue con placeholder). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.28 Foto real del equipo agregada en "Nuestra historia"

El usuario mandó la URL de la foto real del equipo (`https://i.postimg.cc/t4c7jw1W/equipo-todo-acrilico.webp`) para completar "Nuestra historia" (§4.13), la última de las 4 fotos que habían quedado pendientes en Inicio. Se verificó que la URL respondiera `200` (`curl -I`) y se completó el argumento `image_url` en la llamada a `ta_render_story_section()` en `front-page.php` (§1.6). Igual que en §4.27, no hizo falta subir `TA_VERSION` — es contenido de `front-page.php`, no un cambio de `style.css`/`ta-carousel.js`.

**Verificación:** `php -l` sin errores en `front-page.php`. Por `curl`, se confirmó el `<img src="https://i.postimg.cc/t4c7jw1W/equipo-todo-acrilico.webp">` en la sección, que ya no aparece `.ta-story-media-empty` (el placeholder), y que la insignia del logo (`.ta-story-badge`) sigue presente superpuesta en la esquina, tal como en la captura de referencia del usuario. Con esto, las 4 fotos pendientes de Inicio (3 de "Destacados" + 1 de "Nuestra historia") ya están completas. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.29 Bug: las reglas CSS de "Destacados", "Nuestra historia" y el CTA de cierre habían desaparecido (versión 1.9.2)

El usuario reportó que la sección de cierre ("¿Tienes una idea en mente?") no se veía como la referencia. Al revisar `style.css` se encontró que **no era un problema de tamaño o color** — la clase `.ta-cta` y todas sus reglas relacionadas (`.ta-cta-inner`, `.ta-cta-title`, `.ta-cta-description`) habían desaparecido por completo del archivo, mientras que `functions.php::ta_render_cta_section()` seguía generando el HTML con esas clases sin cambios. Se auditó el resto del archivo por si acaso, y se encontró que **lo mismo le había pasado a `.ta-showcase*` (Destacados, §4.12) y a `.ta-story*` (Nuestra historia, §4.13)** — las 3 últimas secciones reutilizables de Inicio se estaban renderizando sin ningún estilo del tema, probablemente perdidas durante alguna de las sesiones de edición manual del usuario en el editor (mismo patrón que el footer en §4.21).

**Corrección:** se reconstruyeron los 3 bloques de CSS completos, verbatim contra lo documentado en §4.12/§4.13/§4.14 (`.ta-showcase`, `.ta-showcase-header`, `.ta-showcase-eyebrow`, `.ta-showcase-title`, `.ta-showcase-cta`, `.ta-showcase-grid`, `.ta-showcase-figure(-empty)`, `.ta-showcase-card`; `.ta-story`, `.ta-story-inner`, `.ta-story-media(-empty)`, `.ta-story-badge`, `.ta-story-eyebrow`, `.ta-story-title`, `.ta-story-content`, `.ta-story-divider`, `.ta-story-stats(-value/-label)`, con su `@media (max-width: 860px)`; `.ta-cta`, `.ta-cta-inner`, `.ta-cta-title`, `.ta-cta-description`), insertados de nuevo justo después de `.ta-feature-card p` (fin de la grilla de categorías) y antes de `.ta-section`.

`TA_VERSION` subido 1.9.1 → 1.9.2.

**Verificación:** `php -l` sin errores en `functions.php`; llaves balanceadas (128/128, antes 94/94 — coherente con las ~34 reglas nuevas) en `style.css`. Por `curl`, se confirmó que `style.css?ver=1.9.2` vuelve a traer `.ta-showcase {`, `.ta-story {`, `.ta-cta {` y `.ta-cta-inner {`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — dado que ya van 2 incidentes de CSS perdido (footer en §4.21, estas 3 secciones ahora), conviene que el usuario evite guardar cambios a medio escribir en el editor mientras Claude tiene una edición en curso sobre el mismo archivo.

### 4.30 Página "Contacto" rediseñada: plantilla dedicada con info + formulario (versión 1.10.0)

A pedido, con una captura de referencia (columna de info con eyebrow/título/rayita/bajada/3 datos con ícono, y una tarjeta blanca con formulario), se reemplazó la página "Contacto" — hasta ahora la plantilla genérica `page.php` con un párrafo de texto placeholder pidiendo completar los datos — por una plantilla dedicada:

- **`page-contacto.php`** (nuevo archivo, §1.10): WordPress la selecciona automáticamente por la jerarquía de plantillas `page-{slug}.php` (mismo mecanismo que `page-nuestras-soluciones.php`, §1.8) — no hizo falta asignarla a mano. 2 columnas: info de contacto a la izquierda (usa `ta_icon('mail'|'pin'|'clock', 20)`, ya existentes desde el footer, §4.15), formulario en tarjeta blanca a la derecha.
- **`ta_icon()`** (`functions.php` §1.2) ganó un ícono nuevo, `'paperclip'`, para el botón de adjuntar archivo.
- **CSS nuevo** (`style.css` §1.1, bloque `/* ---- contacto: info + formulario ---- */`): ~25 reglas nuevas (`.ta-contact`, `.ta-contact-inner`, la lista de contacto, la tarjeta del formulario, los inputs, el botón de adjuntar archivo con el `<input type="file">` oculto de forma accesible, y 2 media queries para apilar en pantallas chicas). `.ta-contact-inner` usó al principio un `max-width: 1100px` propio (ni el ancho angosto de `.ta-page-content` ni el `--ta-shell-width` del resto de componentes); el usuario pidió después que quedara al mismo ancho que el navbar, y pasó a `max-width: var(--ta-shell-width)` (§4.32).
- **Dos cosas sin confirmar, explícitas en la documentación (§1.10):** (1) el correo `contacto@todoacrilico.cl`, la dirección y el horario vienen literalmente de la captura, no son datos verificados del negocio, y (2) ese correo es distinto del `hola@todoacrilico.cl` que ya usa el footer — falta que el usuario confirme si deben unificarse.
- **El formulario es solo visual por ahora:** tiene toda la estructura y los campos (incluido el adjuntar archivo), pero no envía nada — no hay `wp_mail()`, validación de servidor, ni protección anti-spam conectada. El pedido explícito era de visualización ("debe visualizarse de esta manera"), así que se dejó la lógica de envío como trabajo pendiente a pedir aparte.

`TA_VERSION` subido 1.9.2 → 1.10.0 (salto de versión menor por el archivo nuevo + componente grande, no solo un parche).

**Verificación:** `php -l` sin errores en `functions.php`/`page-contacto.php`; llaves balanceadas (156/156) en `style.css`. Por `curl` a `/contacto/`: la plantilla nueva se sirve (confirmado por las clases `ta-contact-*` en el HTML), con los 3 datos de contacto y los campos del formulario presentes; `style.css?ver=1.10.0` trae el bloque `.ta-contact`/`.ta-contact-form-card` nuevo. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.31 Página "Cuidados del Acrílico" rediseñada, reutilizando 3 componentes de Inicio (versión 1.11.0)

A pedido, con una captura de referencia (guía de mantenimiento en 4 tarjetas con ícono, un panel "consejo del experto" con foto, y el mismo CTA de cierre de Inicio), se reconstruyó "Cuidados del Acrílico" — reemplazando `page.php` con texto placeholder por una plantilla dedicada que **reutiliza 3 de los 4 componentes ya construidos para Inicio**, en vez de crear layouts nuevos:

- **`page-cuidados-del-acrilico.php`** (nuevo archivo, §1.11): llama a `ta_render_feature_grid()`, `ta_render_story_section()` y `ta_render_cta_section()` en secuencia — la última, exactamente igual que en Inicio (mismo texto, mismo botón), cero código nuevo para esa sección.
- **`ta_render_feature_grid()`** (`functions.php` §1.2) ganó 2 capacidades: un 4º parámetro `$description` (párrafo entre el título y la grilla) y `url` pasó a ser **opcional** por tarjeta — si no viene, no se imprime el botón "Ver piezas →" (las 4 tarjetas de la guía son solo informativas, sin link). Se verificó por `curl` que Inicio (que sí pasa `url` en sus 4 categorías) siguió mostrando el botón sin cambios.
- **`ta_render_story_section()`** ganó 2 parámetros: `media_position` (`'left'` default o `'right'` — la foto de Cuidados va a la derecha, al revés que en Inicio) y `panel` (bool — envuelve el contenido en una tarjeta con fondo gris `#ececea` y esquinas redondeadas en vez del layout a página completa). También se decoplé la rayita divisoria (`<hr class="ta-story-divider">`) de la condición de `stats`: ahora se imprime siempre después de los párrafos, con o sin estadísticas — en Cuidados no hay estadísticas pero la rayita sigue apareciendo, igual que en la referencia; en Inicio no cambió nada visualmente porque ahí sí hay estadísticas.
- **`ta_icon()`** ganó 4 íconos nuevos para las tarjetas de la guía: `'thermometer'`, `'prohibited'`, `'spray'`, `'circular'`.
- **CSS nuevo** (`style.css` §1.1): `.ta-features-description`, `.ta-story-inner--media-right` (invierte el orden con `order: 2` en `.ta-story-media`), `.ta-story--panel` (fondo, `border-radius`, padding interno, `max-width: 1100px` propio para la tarjeta — ni el ancho angosto de página de texto ni el `--ta-shell-width` del resto).
- La foto (`https://i.postimg.cc/Kj93gzYt/cuidados.webp`) se verificó con `curl -I` (200, `image/webp`) antes de usarla.

`TA_VERSION` subido 1.10.0 → 1.11.0.

**Verificación:** `php -l` sin errores en los 3 archivos tocados/nuevos; llaves balanceadas (161/161) en `style.css`. Por `curl` a `/cuidados-del-acrilico/`: las 4 tarjetas de la guía traen sus íconos y texto y **no** traen botón "Ver piezas →" (confirmado, cero coincidencias de esa cadena en el HTML); el panel trae la clase `ta-story--panel`, el contenedor trae `ta-story-inner--media-right`, la imagen real, y **no** trae `.ta-story-badge` (insignia desactivada, confirmado); el CTA de cierre coincide con el de Inicio. Se confirmó por separado que la portada de Inicio sigue mostrando la insignia del logo y las estadísticas sin cambios (backward-compat de las 2 funciones extendidas). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — el ancho/proporción exactos del panel gris son una aproximación a la captura de referencia, no una réplica pixel a pixel verificada.

### 4.32 Contacto al ancho del navbar; `<br>` manual habilitado con `wp_kses()` (versión 1.11.1)

El usuario pidió que Contacto (y lo relacionado con "Cotizar", que en este tema es el botón del navbar que lleva a esa misma página — no existe una página "Cotizar" separada) tuviera el mismo ancho que el navbar. Se cambió `.ta-contact-inner` (`style.css` §1.1) de `max-width: 1100px` a `max-width: var(--ta-shell-width)` — mismo mecanismo de sincronización que header/footer/componentes de Inicio (§4.21/§4.22).

Aparte, se encontró que el usuario ya había editado `functions.php` y `front-page.php` directamente: agregó `<br>` a varios textos (bajadas de tarjetas, párrafos de "Nuestra historia" y la guía de Cuidados) para forzar saltos de línea manuales, y cambió el escape de esos campos de `esc_html()` a `wp_kses( $texto, array( 'br' => array() ) )` en `ta_render_feature_grid()` y `ta_render_story_section()` — **un cambio correcto y seguro**: `wp_kses()` con esa lista blanca permite únicamente la etiqueta `<br>` (sin atributos) y elimina cualquier otra cosa, así que sigue protegido contra XSS aunque ese texto viniera de un origen no confiable. Se dejó tal cual, sin revertir.

`TA_VERSION` subido 1.11.0 → 1.11.1.

**Verificación:** `php -l` sin errores en `functions.php`, `front-page.php` y `page-cuidados-del-acrilico.php` (los 3 archivos con cambios del usuario o míos en este paso). Por `curl`: `.ta-contact-inner` en `style.css?ver=1.11.1` ya usa `var(--ta-shell-width)`; se confirmó que los `<br>` en las descripciones de Inicio y Cuidados se renderizan como etiqueta real (no como texto escapado `&lt;br&gt;`) tanto en `/` como en `/cuidados-del-acrilico/`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.33 Formulario de Contacto funcional: manejador, `wp_mail()`, nonce, honeypot, adjunto validado (versión 1.12.0)

El usuario preguntó si el formulario funcionaría en producción con un plugin SMTP, y pidió implementar los 5 puntos ya conversados. Se implementó todo en `functions.php` (§1.2) y `page-contacto.php` (§1.10):

1. **Manejador vía `admin-post.php`**: `ta_handle_contact_submit()`, registrado en `admin_post_ta_contact_submit` y `admin_post_nopriv_ta_contact_submit` (el `_nopriv_` es obligatorio — sin él, cualquier visitante sin sesión iniciada recibiría un error 403 al enviar el formulario). El `<form>` ahora tiene `action="admin_url('admin-post.php')"`, un campo oculto `action=ta_contact_submit`, y **`enctype="multipart/form-data"`, que faltaba antes** — sin eso, el navegador nunca habría enviado el archivo adjunto en el `POST`, aunque el campo existiera visualmente.
2. **Validación y sanitización**: `sanitize_text_field()` (nombre), `sanitize_email()` + `is_email()` (correo), `sanitize_textarea_field()` (descripción). Nombre y descripción no pueden venir vacíos.
3. **`wp_mail()`**: envía a `ta_get_contact_email()` (nueva función, filtrable con `ta_contact_email` — hoy devuelve `contacto@todoacrilico.cl`, el mismo valor ya mostrado en la página, ahora leído de un solo lugar en vez de estar repetido). `Reply-To` se arma con el nombre/correo de quien escribe (para que el dueño del sitio pueda responder directo desde su cliente de correo), pero **a propósito no se define un header `From` propio** — dejarlo así es lo que permite que un plugin SMTP (WP Mail SMTP, Easy WP SMTP, etc.) controle el remitente sin conflicto, porque esos plugins funcionan enganchándose al propio `wp_mail()`/PHPMailer, no reemplazándolo por otra librería.
4. **Adjunto validado con cuidado**: extensión en una lista blanca (`jpg`, `jpeg`, `png`, `gif`, `webp`) **y** contenido real verificado con `getimagesize()` (rechaza un archivo con extensión `.jpg` que en realidad no sea una imagen — probado con un `.txt` renombrado a `.jpg`, rechazado correctamente), tamaño máximo 5 MB (`TA_CONTACT_MAX_FILE_SIZE`), nombre de archivo saneado con `sanitize_file_name()` + `wp_unique_filename()`, movido con `move_uploaded_file()` (que por sí solo verifica que venga de una subida HTTP real) a `wp-content/uploads/ta-contacto-tmp/`, adjuntado al correo, y **borrado con `wp_delete_file()` inmediatamente después de intentar el envío** — con éxito o sin él, no se acumulan archivos.
5. **Anti-spam**: `wp_nonce_field( 'ta_contact_submit', 'ta_contact_nonce' )` + `wp_verify_nonce()` en el manejador; un honeypot (`ta_contact_website`, campo oculto de forma accesible con la misma técnica CSS del input de archivo — no `display:none`) que, si viene lleno, descarta el envío **en silencio simulando éxito**, para no darle pistas a quien lo esté probando.
6. **Mensaje de éxito/error sin recargar en blanco**: patrón Post/Redirect/Get — el manejador siempre termina en `wp_safe_redirect()` de vuelta a la página de Contacto (la URL real se manda en un campo oculto `redirect_to`, no está hardcodeada) con `?ta_contact=success` o `?ta_contact=error&reason={motivo}`; `page-contacto.php` lee esos parámetros y muestra un aviso verde o rojo (`.ta-contact-alert`) arriba del formulario. Funciona sin JavaScript y evita el reenvío accidental al refrescar la página.

**CSS nuevo**: `.ta-contact-honeypot` (oculto de forma accesible, misma técnica que el input de archivo) y `.ta-contact-alert`/`--success`/`--error` (aviso de resultado).

`TA_VERSION` subido 1.11.1 → 1.12.0.

**Verificación (punta a punta, con `curl` real contra `admin-post.php`, no solo lectura de HTML):**
- Campos vacíos → `?ta_contact=error&reason=campos` ✓
- Correo inválido → `reason=email` ✓
- Honeypot lleno → `ta_contact=success` sin enviar correo (verificado que no se procesa como envío real) ✓
- Nonce inválido → `reason=nonce` ✓
- Adjunto falso (un `.txt` con extensión `.jpg`) → `reason=archivo_tipo`, rechazado por `getimagesize()` aunque la extensión fuera válida ✓
- Adjunto real (un JPEG 1×1 válido generado para la prueba) → pasa toda la validación, llega hasta el intento de `wp_mail()`, y el archivo temporal se confirma borrado después (`find` sobre `ta-contacto-tmp/` da 0 archivos) ✓
- Envío válido completo (sin archivo) → llega hasta `wp_mail()`, que devuelve `false` en este entorno de desarrollo por no tener transporte de correo configurado (`reason=envio`) — **esperado**, no es un bug: confirma que toda la lógica previa al envío funciona, y que en producción con SMTP configurado el mismo camino terminaría en `ta_contact=success` ✓
- Los mensajes de éxito y error se confirmaron renderizados de verdad en el HTML de la página (no solo el parámetro en la URL) ✓

Se limpiaron los archivos de prueba (`/tmp/fake.jpg`, `/tmp/real.jpg`) y el directorio `ta-contacto-tmp/` quedó vacío/inexistente, sin residuos de las pruebas. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) ni la entrega real de un correo (requiere SMTP configurado en un hosting de verdad, fuera del alcance de este entorno local).

### 4.34 Sistema de diseño de `CLAUDE.md` aplicado al tema completo: tipografía Manrope, colores de marca y tamaños por bloque (versión 1.13.0)

El usuario abrió `CLAUDE.md` (raíz del proyecto, fuera del tema — no cubierto por esta bitácora hasta ahora) y pidió aplicar al tema los estilos ahí documentados: tipografía Manrope en todo el sitio, dos colores de marca (verde `#03854A`, negro `#1A1C1C`) y un tamaño/peso de fuente específico para cada bloque (navbar, banner, categorías, destacados, nuestra historia, CTA de cierre, footer).

1. **Manrope autohospedada, sin pedirla a Google en cada carga**: se descargó el archivo `woff2` que sirve la propia API de Google Fonts para `family=Manrope:wght@300;400;500;600;700;800` — resultó ser un único archivo de **fuente variable** (24 KB) que cubre todo el eje de peso 200–800, no un archivo distinto por cada peso. Se guardó en `assets/fonts/manrope-variable.woff2` (nuevo) y se declaró con un solo `@font-face` (`font-weight: 200 800`) al inicio de `style.css`. `body` pasó de `font-family: system-ui, ...` a `'Manrope', system-ui, ...` (con el stack anterior como *fallback* si el archivo no cargara). Se quitaron los `font-family: system-ui...` que estaban fijados directamente en `.ta-hero h1`/`.ta-hero p` (agregados en una sesión anterior) porque bloqueaban la herencia de Manrope solo en el hero.
2. **Colores de marca**: `--ta-accent` pasó de `#1f7a8c` (teal) a `#03854a` (el verde de `CLAUDE.md`), con `--ta-accent-dark` recalculado a `#026a3b` (mismo oscurecimiento proporcional que tenía el par anterior, para mantener el efecto *hover*). `--ta-ink` pasó de `#1b2023` a `#1a1c1c` (negro exacto del spec). Se agregó `--ta-label: #444748`, el color que `CLAUDE.md` pide para "ETIQUETAS DE CATEGORÍAS" (los *eyebrows*), reutilizado en hero/categorías/destacados/historia.
3. **Pesos explícitos en títulos**: varios títulos (`.ta-features-title`, `.ta-showcase-title`, `.ta-story-title`, `.ta-cta-title`, `.ta-feature-card h3`, `.ta-showcase-card h3`) son etiquetas `<h2>`/`<h3>` y este `style.css` nunca reseteó su `font-weight` — heredaban el *bold* por defecto del navegador. Como el spec pide pesos específicos no-bold para casi todos (Light, Regular, Medium), fue necesario agregar `font-weight` explícito a cada uno; sin eso, los cambios de tamaño no habrían sido visibles como el peso correcto.
4. **Aplicado bloque por bloque** (tamaños convertidos a `px` para que coincidan exacto con el spec, en vez de `rem` relativo):
   - Navbar: `.ta-nav-list a` → 16px Regular (era 0.94rem/peso 1000, un valor inválido que el navegador habría recortado a 900).
   - Banner/hero: `.ta-hero-eyebrow` → 12px ExtraBold `#444748`; `.ta-hero h1` → `clamp(2.25rem, 6vw, 5rem)` (80px en pantallas grandes) Light; `.ta-hero p` → 18px Regular (era 300/delgado, spec pide Regular).
   - Categorías (`ta_render_feature_grid()`): eyebrow 12px ExtraBold `#444748`; título 32px Regular; título de tarjeta 24px Medium; párrafo 15px Regular; el vínculo "Ver piezas →" (`.ta-feature-card .ta-button--text`) 15px ExtraBold.
   - Destacados (`ta_render_showcase_grid()`): mismo patrón que Categorías, incluyendo `.ta-showcase-cta` ("Ver todo el catálogo") a 15px ExtraBold.
   - Nuestra historia (`ta_render_story_section()`): eyebrow 12px ExtraBold `#444748`; título 48px Light; párrafo 18px Regular; "Cifras" (`.ta-story-stat-value`) 24px Medium; etiqueta bajo la cifra (`.ta-story-stat-label`) 15px ExtraBold.
   - CTA de cierre (`ta_render_cta_section()`): título 48px Light; párrafo 18px Regular; botón relleno (`.ta-button--dark`) a `#000000` puro (antes usaba `var(--ta-ink)`, que ya es casi negro pero el spec pide el hex exacto).
   - Botones en general (`.ta-button`, base compartida por todas las variantes): texto a peso ExtraBold (era 600) y `padding` de `12px 22px` a `20px 32px` — el spec dice *"Tamaño botón 20px alto"*, interpretado como más *padding* vertical/horizontal para un botón visiblemente más grande, ya que 20px como alto total del botón habría sido casi ilegible con texto de 16px adentro. **Nota de interpretación, sin confirmar con el usuario**: el spec también dice *"Texto botón Manrope #FFFFFF Extra bold 6px"* — 6px de tamaño de fuente es ilegible y el resto del documento tiene evidencia de ser un texto extraído de una imagen/PDF (acentos y espaciados irregulares, "Monrope" en vez de "Manrope"), así que se interpretó como un posible error de OCR y se usó 16px (consistente con el resto de "tamaño de texto de botón" implícito en el sitio) en vez del valor literal.
   - Footer: `.ta-footer-brand p` (párrafos) → 15px Regular; `.ta-footer-heading` (títulos de menú) → 15px ExtraBold; `.ta-footer-links a` (subtítulos/links) → 15px Regular. Se quitó el `font-family: ui-monospace...` de `.ta-footer-copy` y `.ta-footer-legal` (el copyright y los links legales) para que también hereden Manrope del `body`, ya que el spec dice "toda la tipografía es Manrope" sin excepción.
5. **Fuera de alcance a propósito**: la página de Contacto (`.ta-contact-*`) y "Nuestras Soluciones" no están cubiertas por `CLAUDE.md`, así que no se tocaron — siguen con los tamaños/colores que ya tenían (incluida `.ta-contact-eyebrow`, que sigue en `var(--ta-muted)` en vez de `var(--ta-label)`, por si el usuario quiere unificarlo después).

`TA_VERSION` subido 1.12.0 → 1.13.0.

**Verificación:** balance de llaves de `style.css` correcto (174 aperturas / 174 cierres); `php -l` sin errores en `functions.php`; `curl -I` confirma que `style.css?ver=1.13.0` y `assets/fonts/manrope-variable.woff2` responden `200 OK`; `grep` confirma que no quedó ningún rastro de los colores anteriores (`#1f7a8c`, `#16606f`, `#1b2023`) ni de `font-family` fijados a `system-ui`/monospace fuera de la declaración `@font-face` y el `body`. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — pendiente de revisión visual por el usuario, especialmente el tamaño de 80px del título del hero y la interpretación del tamaño de botón.

### 4.35 Corrección: §4.34 revertido casi por completo — el usuario solo pedía el color verde (versión 1.13.1)

El usuario reportó, con una captura del hero ya renderizado, que "solo había que cambiar el color verde y modificaste todo". Tenía razón: la instrucción original ("en el archivo CLAUDE.md están los estilos, puedes aplicarlos al theme") se interpretó como aplicar *todo* el spec de `CLAUDE.md` (tamaños de título, pesos de fuente, padding de botones, tipografía Manrope, footer), cuando lo que el usuario quería era mucho más acotado.

Se preguntó explícitamente qué parte del cambio de §4.34 debía quedarse, con tres opciones (solo color / color + Manrope / todo el spec). El usuario eligió **"solo el color verde"**, así que se revirtió todo lo demás a los valores exactos que tenía el archivo antes de §4.34:

- Se quitó el `@font-face` de Manrope y el archivo autohospedado `assets/fonts/manrope-variable.woff2` (borrado — quedaba sin usar). `body` volvió a `font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif`, igual que antes.
- Se revirtieron uno por uno los tamaños/pesos de: navbar, `.ta-hero-eyebrow`/`.ta-hero h1`/`.ta-hero p` (incluyendo el `font-family: system-ui...` explícito que había en el hero desde antes de §4.34), `.ta-button`/`.ta-button--dark` (padding, peso, relleno negro), `.ta-features-*`, `.ta-showcase-*`, `.ta-story-*`, `.ta-cta-*` y `.ta-footer-*` (incluyendo el `font-family: ui-monospace...` de `.ta-footer-copy`/`.ta-footer-legal`, que también se había quitado en §4.34).
- Se quitó la regla nueva `.ta-feature-card .ta-button--text` (no existía antes de §4.34).
- **Se mantuvo únicamente** el cambio de color en `:root`: `--ta-ink: #1a1c1c` (antes `#1b2023`), `--ta-accent: #03854a` (antes `#1f7a8c`) y `--ta-accent-dark: #026a3b` (recalculado para seguir sirviendo de estado *hover* del nuevo verde — antes `#16606f`, el par oscuro del teal viejo). Se quitó `--ta-label`, que ya no se usa en ningún lado.

`TA_VERSION` subido 1.13.0 → 1.13.1.

**Verificación:** balance de llaves de `style.css` correcto (172 aperturas/cierres — mismo conteo que antes de §4.34); `php -l` sin errores en `functions.php`; `curl` confirma que `.ta-hero h1`/`.ta-hero-eyebrow` y el resto de selectores tocados volvieron a los valores exactos previos a §4.34, con la única diferencia de los 3 tokens de color en `:root`. El usuario compartió una imagen de referencia después de esta corrección ("debe verse como la imagen") que coincide con el layout ya revertido (tamaños originales, verde de acento) — no se hicieron más cambios de código a partir de esa imagen, solo se confirmó que el estado actual ya la satisface.

### 4.36 Hero alineado al mismo margen izquierdo que el logo del navbar (versión 1.13.2)

El usuario reportó, con una captura del hero anotada, que el ícono del navbar (logo) no quedaba alineado con el texto "TALLER DE ACRÍLICO · CHILE · DESDE 2019" del hero — pedía que el hero "fuera más ancho, similar a la imagen adjunta" para lograr esa alineación.

La causa: `.ta-hero-inner` (`style.css` §1.1) tenía un `margin-left: 300px` fijo en píxeles, independiente del sistema de `--ta-shell-width`/`.ta-container` que ya usan el header, el footer y los 4 componentes de Inicio (categorías, destacados, historia, cierre — §4.21/§4.22) para compartir el mismo margen izquierdo sin importar el ancho de la ventana. El hero era el único bloque que no pasaba por ese mecanismo, así que su indentación no coincidía con la del logo salvo por coincidencia en un ancho de ventana específico.

Se corrigió envolviendo `.ta-hero-inner` dentro de un `<div class="ta-container">` en `front-page.php` (§1.6) — el mismo patrón que ya usan `ta_render_feature_grid()`, `ta_render_showcase_grid()`, etc. — y se quitó el `margin-left: 300px` de `.ta-hero-inner` en `style.css`, que ahora solo controla el ancho máximo de la columna de texto (`max-width: 620px`) sin fijar su posición: la posición la da `.ta-container`, igual que en el resto del sitio.

`TA_VERSION` subido 1.13.1 → 1.13.2.

**Verificación:** `php -l` sin errores en `front-page.php`; balance de llaves de `style.css` correcto (172/172, sin cambio neto — se quitó una línea, no una llave); por `curl` se confirmó que el HTML renderizado ahora envuelve `.ta-hero-inner` en `.ta-container`, y que `.ta-container`/`.ta-header-inner` comparten exactamente la misma fórmula de centrado (`max-width: var(--ta-shell-width); margin: 0 auto; padding: 24px` de lado). No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión).

### 4.37 Corrección: §4.36 revertido — el usuario pidió dejarlo como estaba (versión 1.13.3)

El usuario pidió deshacer el cambio de §4.36 ("déjalo como estaba sin los cambios"). Se revirtió exactamente:

- `front-page.php` (§1.6): se quitó el `<div class="ta-container">` que envolvía `.ta-hero-inner` — el hero volvió a su único `<div class="ta-hero-inner">` de siempre.
- `style.css` (§1.1): `.ta-hero-inner` recuperó el `margin-left: 300px` que tenía antes de §4.36.

`TA_VERSION` subido 1.13.2 → 1.13.3.

**Verificación:** `php -l` sin errores en `front-page.php`; balance de llaves de `style.css` correcto (172/172); por `curl` se confirmó que el HTML volvió a `<div class="ta-hero-inner">` sin el contenedor extra, y que `style.css?ver=1.13.3` tiene de nuevo `margin-left: 300px` en `.ta-hero-inner`.

### 4.38 Desplegable de "Catálogo" generado dinámicamente desde las categorías del plugin (versión 1.14.0)

El usuario pidió que, en el navbar, el desplegable de "Catálogo" se mantuviera sincronizado solo con las categorías reales del plugin: que una categoría nueva aparezca sin tocar el menú a mano, y que una que se borre o se quede sin imágenes desaparezca sola.

Antes de este cambio, "Catálogo" (ítem de menú `id=18`, con la clase `ta-nav-has-caret`) era un simple link a la página "Nuestras Soluciones" — el ícono de flecha (`::after` en `.ta-nav-has-caret > a`) era puramente decorativo, sin ningún desplegable real detrás (confirmado consultando directamente `wp_posts`/`wp_postmeta` en la base de datos: el ítem no tiene hijos en el menú).

Se agregó, en `functions.php` (§1.2):

1. **`class TA_Nav_Walker extends Walker_Nav_Menu`**: sobrescribe dos métodos del walker base:
   - **`start_el()`**: en el ítem de nivel raíz que apunta a la página `nuestras-soluciones` (comparando `$item->object_id` contra el ID real de esa página, cacheado en `$solutions_page_id`), si `ta_get_solutions_by_category()` devuelve categorías, le agrega la clase `ta-nav-has-caret` al `<li>` — así el CSS sabe que este ítem tiene un desplegable y `ta-nav.js` sabe que tiene un botón que controlar.
   - **`end_el()`**: en ese mismo ítem, si hay categorías, inyecta (a) un `<button type="button" class="ta-nav-caret-toggle" aria-expanded="false">` para que `ta-nav.js` (§1.12) pueda abrir/cerrar el desplegable con un tap en pantallas táctiles, y (b) un `<ul class="ta-nav-dropdown">` con un `<li><a>` por cada categoría — **reutilizando `ta_get_solutions_by_category()`**, la misma función que ya arma las secciones con ancla (`id="categoria-{slug}"`) de `page-nuestras-soluciones.php` (§1.8). Al ser la misma fuente de datos, el desplegable **nunca puede desincronizarse** de las secciones reales de la página: si una categoría no tiene imágenes publicadas, ya no aparece en ninguno de los dos lugares (esa función usa `hide_empty => true` en `get_terms()` y además descarta cualquier categoría sin posts publicados de tipo `jg_gallery_image`); si se borra la categoría desde el plugin, desaparece de ambos en la siguiente carga de página, sin ninguna acción manual.
2. En `header.php` (§1.3): se pasó `'walker' => new TA_Nav_Walker()` a `wp_nav_menu()`.
3. **CSS nuevo** en `style.css` (§1.1): `.ta-nav-list li { position: relative; }` (ancla de posicionamiento) y `.ta-nav-dropdown`/`.ta-nav-dropdown a` — un panel flotante que se abre con `:hover`/`:focus-within` sobre el `<li>` (sin JavaScript, funciona con teclado igual que con mouse).

`TA_VERSION` subido 1.13.3 → 1.14.0 (versión menor, no parche, por ser una función nueva y no un ajuste visual).

**Verificación:** `php -l` sin errores en `functions.php` y `header.php`; balance de llaves de `style.css` correcto (177 aperturas/cierres); se consultó la base de datos directamente (`wp_terms`/`wp_term_taxonomy`, taxonomía `jg_gallery_category`) para confirmar las 5 categorías reales (`organizador`, `proteger`, `vidrios`, `exhibir`, `informar`); por `curl` se confirmó que el HTML del desplegable trae exactamente esas 5, con el mismo slug/nombre y el mismo orden alfabético que usa `page-nuestras-soluciones.php`, y que los 5 `href="...#categoria-{slug}"` coinciden uno a uno con los 5 `id="categoria-{slug}"` reales de esa página. No se pudo confirmar visualmente en un navegador real (sin entorno gráfico en esta sesión) — falta comprobar el `:hover`/`:focus-within` a simple vista, aunque el mecanismo CSS es estándar y no depende de JavaScript.

### 4.39 Bug: no se podía hacer clic en ninguna categoría del desplegable de "Catálogo" (versión 1.14.1)

El usuario reportó que no podía seleccionar ninguna opción del desplegable agregado en §4.38. Causa: `.ta-nav-dropdown` (`style.css` §1.1) tenía `margin: 4px 0 0`, dejando un hueco de 4px entre el link "Catálogo" y el panel del desplegable. El `:hover`/`:focus-within` que lo mantiene visible depende de que el cursor esté sobre el `<li>` o alguno de sus descendientes en todo momento — al mover el mouse hacia abajo para hacer clic en una categoría, el cursor cruzaba ese hueco (que no pertenece a ningún elemento), el `:hover` se perdía y el desplegable se cerraba antes de poder hacer clic. Es el bug clásico de menús desplegables por CSS con un espacio entre el disparador y el panel.

Se quitó el margen (`margin: 0`) para que el desplegable toque directamente el borde inferior del `<li>`, sin ningún hueco muerto entre ambos — el resto del espaciado visual (`padding: 8px`) queda dentro del propio panel, que sigue siendo parte continua del área con hover.

De paso, el editor marcó "Unreachable code" en `TA_Nav_Walker::get_solutions_page_id()` (probablemente un falso positivo del analizador estático por no tener tipo declarado en la propiedad `$solutions_page_id`) — se tipó como `private ?int $solutions_page_id = null;` (tipo anulable, disponible desde PHP 7.4, que es el mínimo que ya exige el tema) para que el analizador tenga información correcta; el comportamiento en tiempo de ejecución no cambia.

`TA_VERSION` subido 1.14.0 → 1.14.1.

**Verificación:** `php -l` sin errores en `functions.php`; balance de llaves de `style.css` correcto (177/177); por `curl` se confirmó que `style.css?ver=1.14.1` ya no tiene el `margin-top` en `.ta-nav-dropdown`. No se pudo confirmar visualmente con un clic real en un navegador (sin entorno gráfico en esta sesión) — la corrección elimina la causa raíz identificada (el hueco sin hover), pero queda pendiente que el usuario confirme que ya puede seleccionar las categorías.

### 4.40 Estado verificado al cierre de esta bitácora

| Verificación | Resultado |
|---|---|
| Tema activo | Sí (`todo-acrilico`) |
| Título del sitio actualizado | Sí ("Todo Acrílico") |
| Las 4 páginas/rutas de navegación responden 200 | Sí |
| Menú asignado a la ubicación `primary`, visible en header; footer arma sus propios 4 links (§4.15) | Sí |
| Navbar a todo el ancho del viewport y logo más grande | Sí, verificado vía `curl` (§4.19, corrige §4.18) — sin confirmar visualmente en navegador |
| Navbar ya no queda tapado por `#wpadminbar` al hacer scroll con sesión iniciada | Sí, corregido (§4.20) — sin confirmar visualmente en navegador con sesión real |
| Footer, navbar y ahora también Contacto comparten ancho vía `--ta-shell-width` | Sí (§4.21, §4.32) |
| Los 4 componentes reutilizables de Inicio (categorías/destacados/historia/cierre) comparten ese mismo ancho | Sí, verificado vía `curl` (§4.22) — sin confirmar visualmente en navegador; `.ta-page-content`, el hero (§4.24) y el panel de Cuidados (§4.31) quedan deliberadamente con su propio ancho |
| Hero de Inicio: layout confirmado por el usuario como cercano a la referencia; título agrandado dos veces más (§4.24, §4.25, §4.26) | Sí — layout aprobado, tamaño de título sigue en ajuste iterativo |
| CSS de "Destacados", "Nuestra historia" y el CTA de cierre, restaurado tras desaparecer del archivo | Sí, verificado vía `curl` (§4.29) — sin confirmar visualmente en navegador |
| Página "Contacto" rediseñada (plantilla dedicada, info + formulario, ancho igual al navbar) | Sí, verificado vía `curl` (§4.30, §4.32) — **datos de contacto sin confirmar**, sin confirmar visualmente en navegador |
| Formulario de Contacto: envía correo de verdad, validado punta a punta por `curl` | Sí (§4.33) — **`wp_mail()` falla en este entorno de desarrollo por falta de transporte de correo (esperado); falta configurar SMTP en producción para entrega real** |
| Página "Cuidados del Acrílico" rediseñada, reutilizando 3 componentes de Inicio | Sí, verificado vía `curl` (§4.31) — sin confirmar visualmente en navegador |
| `<br>` manual en bajadas/párrafos, habilitado de forma segura con `wp_kses()` | Sí, verificado vía `curl` (§4.32) |
| "Nuestras Soluciones"/"Catálogo" muestra datos reales agrupados por categoría, con anclas por categoría | Sí (§4.11) |
| Carrusel reutilizable para publicaciones con más de una foto | Markup y JS correctos (§4.6); bug de CSS que impedía ver el cambio de imagen corregido (§4.7) — sin confirmar clic real |
| Grilla de categorías reutilizable (`ta_render_feature_grid()`) en Inicio | Sí, verificado vía `curl` (§4.11) — sin confirmar visualmente en navegador |
| Grilla de "destacados" reutilizable (`ta_render_showcase_grid()`) en Inicio, con las 3 fotos reales | Sí, verificado vía `curl` (§4.27) — sin confirmar visualmente en navegador |
| Sección "Nuestra historia" reutilizable (`ta_render_story_section()`) en Inicio, con la foto real del equipo | Sí, verificado vía `curl` (§4.28) — sin confirmar visualmente en navegador |
| Sección de cierre reutilizable (`ta_render_cta_section()`) en Inicio | Sí, verificado vía `curl` (§4.14, §4.29) |
| Íconos decorativos "undo"/"redo" del footer | Eliminados a pedido del usuario (§4.16) |
| Barra de scroll oculta en todas las páginas | Sí, restaurada por el usuario con una implementación equivalente (§4.17) — sin confirmar visualmente en navegador |
| `Version:` del docblock de `style.css` y `TA_VERSION` sincronizados | **No** — `TA_VERSION` está en `1.14.20` (`functions.php`) pero el docblock de `style.css` quedó en `1.14.15`. El sitio funciona (WP usa `TA_VERSION` para cache-busting), pero la versión mostrada en Escritorio → Temas no coincide |
| Bug de la vitrina vieja de Inicio (`$image['url']` inexistente) | Corregido, y la vitrina en sí reemplazada por la grilla de categorías (§4.11) |
| `TA_VERSION` (cache-busting de CSS/JS) al día tras el último cambio de frontend | Sí — `1.14.20` (§4.9, §4.10, §4.11, §4.12, §4.13, §4.14, §4.15, §4.16, §4.17, §4.19, §4.20, §4.21, §4.22, §4.24, §4.25, §4.26, §4.29, §4.30, §4.31, §4.32, §4.33, §4.34, §4.35, §4.36, §4.37, §4.38, §4.39) |
| Páginas del plugin (`/galeria/`, `/galeria-panel/`) sin cambios | Sí |
| Colores de marca (`--ta-accent: #03854A`, `--ta-ink: #1A1C1C`) aplicados en todo el tema; tipografía/tamaños/pesos/Manrope de §4.34 revertidos a pedido del usuario | Sí — el usuario solo quería el color (§4.35, corrige §4.34); verificado vía `curl`/`grep` que el resto del archivo volvió byte a byte a los valores previos a §4.34 |
| `.ta-hero-inner` con `margin-left: 300px` fijo, sin envolver en `.ta-container` | El intento de alinearlo con el logo (§4.36) fue revertido a pedido del usuario (§4.37) — el desalineamiento original sigue presente, pendiente de una nueva indicación si se quiere resolver |
| Desplegable de "Catálogo" generado dinámicamente desde las categorías del plugin (`TA_Nav_Walker`) | Sí, verificado vía `curl` + consulta directa a la base de datos — los 5 links coinciden uno a uno con las 5 anclas reales de "Nuestras Soluciones" (§4.38) |
| Bug del desplegable de "Catálogo" que no dejaba hacer clic en ninguna categoría (hueco sin hover) | Corregido quitando el `margin-top` de `.ta-nav-dropdown` (§4.39); sin confirmar con un clic real en navegador |
| Prueba manual en navegador (no solo `curl`) | **No verificada todavía** |

### 4.41 Formulario de Contacto 2.0: catálogo → "Cotizar esta pieza", celular chileno, adjuntos múltiples (imágenes/PDF) y correo real (versión 1.18.5)

El último commit del repositorio (`dfab9fd "version3 mejora en catalogo y formulario"`) rediseñó a la vez el catálogo y el formulario, pero la documentación quedó desactualizada. Esta entrada realinea `Docs_theme.md` (§1.1, §1.2, §1.10, §6.1) con el código **actual** — y de paso documenta los cambios que el código trajo. Lo verificado en `page-contacto.php`, `page-nuestras-soluciones.php`, `functions.php` y la página real `http://localhost/wordpress/proyecto3/index.php/contacto/` (formulario posando a `admin-post.php`):

1. **Correo real:** `ta_get_contact_email()` ahora devuelve `rodolfo.parada.gonzalez@gmail.com` (el `contacto@todoacrilico.cl` quedó comentado). El usuario lo confirmó como el correo real del negocio — así que el "Dato sin verificar" de §4.30 quedó resuelto en la parte del correo; siguen sin confirmar la dirección y el horario (ver §1.10).
2. **Pieza a cotizar desde el catálogo:** cada tarjeta de "Nuestras Soluciones" ahora trae un botón **"Cotizar esta pieza"** que enlaza a Contacto con `?imagen=&pieza=&descripcion=&medidas=` (la imagen viaja como URL, no como archivo). La página de Contacto muestra la tarjeta `.ta-contact-quote` con miniatura, título, descripción y medidas — con campos ocultos `adjunto_referencia/titulo/descripcion/medidas` que el manejador incrusta en el cuerpo **HTML** del correo (`esc_html`/`esc_url` en cada dato, imagen embebida con `max-width:100%`). El botón `×` los borra para enviar sin la pieza. La imagen se valida con `wp_http_validate_url()` antes de usarse.
3. **Celular chileno obligatorio:** nuevo campo `celular` con prefijo fijo `+56 9` y formateo JS a `XXXX XXXX`. El servidor exige 9 dígitos que empiecen en 9 (8 tras normalizar), y el número viaja al correo como `+56 9 XXXXXXXX`. Nuevo motivo de error `celular`.
4. **Adjuntos múltiples:** `adjunto[]` con `multiple`, `.pdf` + imágenes, hasta **5** archivos (`TA_CONTACT_MAX_FILES`) de 5 MB cada uno. El diseño lo gestiona con un `DataTransfer` en `#ta-adjunto-list` (filas con nombre/tamaño, botones "Agregar"/"Eliminar", se oculta el botón al llegar a 5). El manejador normaliza `$_FILES['adjunto']` (formato array de PHP para `multiple`), valida cada archivo por extensión **y** contenido real (PDF verificado por `%PDF`/MIME, imágenes con `getimagesize()`), mueve a `uploads/ta-contacto-tmp/`, y limpia **todos** los temporales tanto en el fallo (`$fail` recorre `$adjunto_paths`) como al terminar. Nuevos motivos de error: `archivos_muchos`, `archivo_tipo` (ahora menciona PDF).
5. **Dominio del correo:** se exige que el dominio del `email` termine en `.cl`, `.com`, `.net` u `.org` (misma razón `email`).
6. **Cambio de ancho de §1.2 en la doc:** la sección de "Reset de versión" sigue igual — `TA_VERSION` 1.18.5 vs `style.css` 1.14.15; la discrepancia documentada sigue viva (el valor cambió, el problema no).
7. **Catálogo rediseñado (avance para la doc §1.8):** `page-nuestras-soluciones.php` ya no arma las secciones con ancla `id="categoria-{slug}"`: ahora usa pestañas de filtro (`?categoria=` vía `ta_render_filter_tabs()`) y paginación (`?pagina=`, 9 por página, vía `ta_get_pagination_pages()`). La documentación de §1.8 quedó desactualizada por este cambio (fuera del alcance de esta entrada — queda anotado en §5). Consecuencia: los links del desplegable de "Catálogo" que aún apuntan a `#categoria-{slug}` (§4.38) ya **no tienen ancla correspondiente** en la nueva página — posible bug de navegación a revisar.

**Verificación:** `php -l` sin errores en `page-contacto.php` y `functions.php`; la página real por `curl` muestra el formulario nuevo (teléfono con prefijo `+56 9`, `adjunto[] multiple`, `accept="image/*,.pdf,application/pdf"`, campos `adjunto_*` ocultos) y el botón "Cotizar esta pieza" en el catálogo; el `<form>` de la página real posa a `admin-post.php` con `action=ta_contact_submit`. Sin confirmar visualmente en navegador.

### 4.42 Formulario de Contacto: checkbox opcional de consentimiento de comunicaciones comerciales (versión 1.18.6)

Pedido del usuario: debajo del botón de adjuntar archivo debe haber un checkbox **opcional** cuyo texto queda a la derecha — "(Opcional) Acepto recibir ofertas y comunicaciones comerciales." — y el correo debe reflejar siempre si el cliente aceptó o no recibir ofertas/comunicaciones.

Implementado:

1. **`page-contacto.php`** (§1.10): nuevo `<label class="ta-contact-optin">` entre `.ta-contact-files` y el botón "Enviar mensaje", con `<input type="checkbox" name="acepta_ofertas" value="1">` y `<span>(Opcional) Acepto recibir ofertas y comunicaciones comerciales.</span>`. **Sin `required`** — es opcional.
2. **`style.css`** (§1.1): reglas nuevas `.ta-contact-optin` + `.ta-contact-optin input[type="checkbox"]` + `.ta-contact-optin span` — el contenedor override el `display:flex; flex-direction:column` del label base a `row` con `align-items:flex-start` (texto a la derecha del check, `font-weight:400`, cursor pointer); el checkbox `width:auto` (para no heredar el `width:100%` de los inputs de texto), `accent-color: var(--ta-accent)` (check verde de marca) y `margin-top:2px`; el span `line-height:1.4`. **Corrección posterior en §4.43:** el selector inicial `.ta-contact-optin` (especificidad 0-1-0) no le ganaba a `.ta-contact-form label` (0-1-1), que recomponía la columna — el fix subió el selector a `.ta-contact-form .ta-contact-optin`.
3. **`functions.php`** (§1.2, `ta_handle_contact_submit()`): lee el campo con `$acepta_ofertas = empty( $_POST['acepta_ofertas'] ) ? '0' : '1';` (el checkbox solo se envía al estar marcado) y agrega **siempre** al cuerpo HTML del correo la línea `Consentimiento de comunicaciones comerciales:` seguida de:
   - "El cliente **aceptó** recibir ofertas y comunicaciones comerciales." (marcado, `'1'`), o
   - "El cliente **no aceptó** recibir ofertas y comunicaciones comerciales." (sin marcar, `'0'`).
   Así queda constancia explícita en cada mensaje, acepte o no.

`TA_VERSION` subido 1.18.5 → 1.18.6 (cambio de frontend). El docblock de `style.css` sigue en 1.14.15 (discrepancia ya documentada).

**Verificación:** `php -l` sin errores en `page-contacto.php` y `functions.php`; por `curl` la página real muestra el `<label class="ta-contact-optin">` con el checkbox `name="acepta_ofertas" value="1"` y el texto exacto a la derecha; la rama ternaria genera las dos frases correctamente (probado el build de ambos textos en CLI); el cache-busting ya sirve `style.css?ver=1.18.6` (y `ta-carousel.js`/`ta-nav.js`). La entrega real del correo sigue bloqueada por el SMTP de WP Mail SMTP con OAuth vencido (`invalid_grant`, §4.40/§6.2) — pendiente reconectar la cuenta de Google o configurar otro mailer.

### 4.43 Fix: el texto del checkbox de consentimiento se apilaba debajo del check (versión 1.18.7)

El usuario reportó que el texto "(Opcional) Acepto recibir ofertas y comunicaciones comerciales." se mostraba **debajo** del checkbox en vez de a su derecha.

**Causa raíz (especificidad CSS):** la regla global `.ta-contact-form label { display:flex; flex-direction:column; gap:8px; font-weight:600; }` tiene especificidad 0-1-1, y la regla nueva del optin `.ta-contact-optin { ... flex-direction:row; ... }` solo 0-1-0 — menos específica, así que el navegador aplicaba la **columna** del label base y apilaba check y texto verticalmente, ignorando el `flex-direction:row`.

**Fix:** subir el selector del contenedor a `.ta-contact-form .ta-contact-optin` (especificidad 0-2-0, le gana al 0-1-1): `display:flex; flex-direction:row; align-items:flex-start; gap:8px; font-weight:400; cursor:pointer;`. El checkbox y el span mantienen sus reglas (`.ta-contact-optin input[type="checkbox"]` con `width:auto`, `accent-color: var(--ta-accent)`, `margin-top:2px`; `.ta-contact-optin span` con `line-height:1.4`).

`TA_VERSION` subido 1.18.6 → 1.18.7 (cambio de CSS/visual). `style.css` docblock sigue en 1.14.15.

**Verificación:** el CSS servido (`style.css?ver=1.18.7`) trae `.ta-contact-form .ta-contact-optin` con `flex-direction: row`; por `curl` la página real enlaza el CSS con `?ver=1.18.7` (cache-busting correcto). Falta la confirmación visual en navegador — con el selector corregido el texto queda a la derecha del check (heredaba `gap:8px` de la base y `align-items:flex-start` descoloca bien el check respecto a un texto de 2 líneas).

### 4.44 Formulario de Contacto: nota de privacidad en los adjuntos (versión 1.18.8)

Pedido del usuario: entre el bloque "Adjuntar imagen" y el checkbox de consentimiento debe leerse la advertencia:
> **Nota:** Por favor no adjunte documentos con datos personales sensibles o cédula de identidad.

Implementado:

1. **`page-contacto.php`** (§1.10): nuevo `<p class="ta-contact-files-note">` entre el cierre de `.ta-contact-files` (la lista `#ta-adjunto-list`) y el `<label class="ta-contact-optin">`, con el texto exacto.
2. **`style.css`** (§1.1): regla `.ta-contact-files-note` — microcopy con el mismo estilo que `.ta-contact-char-limit` (el aviso "Límite 2000 caracteres"): `font-size: 0.78rem`, `font-weight: 400`, `color: var(--ta-muted)`, `line-height: 1.4`, `margin: 0`. Sin JS ni cambios en el manejador.

`TA_VERSION` subido 1.18.7 → 1.18.8 (cambio de frontend). `style.css` docblock sigue en 1.14.15.

**Verificación:** `php -l` OK en `page-contacto.php`; por `curl` la página real sirve el `<p class="ta-contact-files-note">` con el texto exacto entre los adjuntos y el check, y enlaza `style.css?ver=1.18.8` (cache-busting correcto). Sin confirmar visualmente en navegador.

### 4.45 Formulario de Contacto: componente Alert informativo de privacidad (versión 1.18.9)

Pedido del usuario: debajo del check de consentimiento, un componente **Alert (variante info)** — el nombre estándar en la mayoría de librerías (Bootstrap, Tailwind UI, shadcn/ui, Material UI) — con un ícono de seguridad en forma de escudo y el texto a su derecha:

> Sus datos serán tratados conforme a la ley 21.719 para gestionar su cotización. Consulte nuestra Política de Privacidad o ejerza sus derechos en contacto@todoacrilico.cl

Implementado:

1. **`page-contacto.php`** (§1.10): `<div class="ta-contact-alert ta-contact-alert--info" role="note">` entre el `ta-contact-optin` y el botón "Enviar mensaje". Contiene `<span class="ta-alert-icon">` con `ta_icon( 'shield', 18 )` (ícono de escudo que ya existía en el banco del tema, §1.2) y `<p class="ta-alert-text">` con el texto exacto. `role="note"` para que sea un aviso complementario, no una alerta urgente de `role="alert"`.
2. **`style.css`** (§1.1): variante `.ta-contact-alert--info` sobre la base `.ta-contact-alert` — `display:flex; gap:10px; align-items:flex-start` (ícono a la izquierda, texto a la derecha), fondo verde muy claro `#f0f6f3`, borde `#d6e6dc`, `color: var(--ta-ink)`, `font-weight:400`. El ícono `.ta-alert-icon` se pinta con `--ta-accent` (verde de marca). El texto `.ta-alert-text` con `margin:0; line-height:1.5`. Se integra a la paleta del tema (verde/negro) en vez del azul "informativo" típico de Bootstrap, por coherencia con la marca.

> **Correo de privacidad ≠ correo de cotizaciones:** el texto usa `contacto@todoacrilico.cl` (pedido literal del usuario) pero las cotizaciones llegan a `rodolfo.parada.gonzalez@gmail.com` (`ta_get_contact_email()`, §1.2/§4.41). Quedan como 2 direcciones distintas de propósito: una pública para derechos/privacy, otra real de recepción.

`TA_VERSION` subido 1.18.8 → 1.18.9 (cambio de frontend). `style.css` docblock sigue en 1.14.15.

**Verificación:** `php -l` OK en `page-contacto.php`; por `curl` la página real sirve el `.ta-contact-alert--info` con el SVG del escudo (18×18) y el texto exacto a su derecha, y enlaza `style.css?ver=1.18.9`. Sin confirmar visualmente en navegador.

### 4.46 Formulario de Contacto: correo con plantilla HTML estilo email (versión 1.18.10)

Pedido del usuario: al presionar "Enviar mensaje", el correo de la cotización debe verse con una plantilla HTML estilo email (tabla de 600px, tonos neutros) en vez del texto plano simple — con la aclaración "siempre puedes mejorarla y adaptarla pero se visualice correctamente con la seguridad correcta".

Adaptado el template de referencia (mantiene su estructura visual) con estos ajustes:

1. **`functions.php`** (§1.2, `ta_handle_contact_submit()`): el `$html_body` se construye ahora con estilos inline por bloques, respetando el diseño del template (contenedor 600px centrado, `border-radius`, header "Nuevo mensaje de contacto — {nombre}", etiqueta pequeña uppercase `DATOS DEL CLIENTE` con tabla de 3 filas NOMBRE/CELULAR/CORREO, `DESCRIPCIÓN DEL PROYECTO`, `PIEZA A COTIZAR` y el pie con `📎 N archivo(s) adjunto(s) · Analizados automáticamente`).
2. **Consentimiento** (`§4.42`): la línea está dentro de DATOS DEL CLIENTE y **siempre se muestra** — `✓ Aceptó recibir ofertas y comunicaciones comerciales.` en verde (`#15903e`) si `acepta_ofertas = 1`, o `✗ No aceptó recibir ofertas y comunicaciones comerciales.` en gris (`#64748b`) si no.
3. **Seguridad** (igual que antes, reforzada): todos los valores dinámicos con `esc_html` / `esc_url` / `esc_attr` (se verificó con un valor `<script>` de prueba que el cuerpo lo entrega escapado); `nl2br( esc_html( ... ) )` en descripción y observaciones de la pieza; la imagen de la pieza se incrusta por su **URL pública ya validada con `wp_http_validate_url()`** en vez de `cid:` — más compatible con webmail y sin datos sensibles. Los adjuntos reales del formulario **no se incrustan en línea** (un `cid:` o `data:` los convertiría en adjuntos visibles duplicados o los bloquearía el cliente) — van como archivos adjuntos de `wp_mail()` y el pie solo muestra el resumen con los nombres.
4. `TA_VERSION` subido 1.18.9 → 1.18.10 (cambio de funcionalidad del manejador).

> **Verificación:** `php -l` OK en `functions.php`; se ejecutó el bloque real de construcción del `$html_body` extraído del archivo (con stubs de `esc_*`) — genera el HTML completo `<head>`/`<body>`, ambas ramas del consentimiento (✓ verde / ✗ gris), el resumen "📎 3 archivos adjuntos" con nombres, y escapa un intento de XSS (`<script>` → `&lt;script&gt;`). El bloque embebido de §1.2 quedó **byte a byte idéntico** al código real (`diff`). La entrega real sigue condicionada al SMTP (`invalid_grant`, §4.40/§6.2).

### 4.47 Página legal "Términos y Condiciones" enlazada desde el footer (versión 1.18.11)

Pedido del usuario: al presionar "Términos y condiciones" en el footer debe abrirse una **nueva página web** que mantenga el nav y el footer (una página real de WordPress renderizada con `page.php`, §1.7) y que muestre el texto legal con los mismos estilos de la web.

Implementado:

1. **Página de WordPress** creada con `wp_insert_post()` (id=101, estado `publish`, slug `terminos-y-condiciones`, título "Términos y Condiciones"): el contenido es el texto legal íntegro del usuario — bienvenida + 8 cláusulas (Naturaleza del Sitio Web, Proceso y Validez de las Cotizaciones, Responsabilidad del Usuario sobre los Datos Ingresados, Propiedad Intelectual y Derechos de Autor, Disponibilidad del Servicio y Exclusión de Garantías, Tratamiento de Datos Personales, Modificaciones, Legislación Aplicable y Jurisdicción). Cada cláusula es un `<h2>`, los párrafos son `<p>` y los títulos embebidos de cada cláusula ("Carácter no vinculante:", "Plazo de vigencia:", etc.) son `<strong>` dentro del `<p>`. Se normalizó el doble espacio de "10  días" → "10 días". La URL real: `http://localhost/wordpress/proyecto3/index.php/terminos-y-condiciones/`.
2. **`footer.php`** (§1.4): se agregó `$ta_footer_terminos = get_page_by_path( 'terminos-y-condiciones' );` (igual patrón que los otros links del footer) y el link "Términos y condiciones" dejó de ser el placeholder `#` y ahora usa `get_permalink( $ta_footer_terminos )` con fallback a `home_url('/')` por si la página no existiera.
3. **`style.css`** (§1.1): bloque nuevo para el texto legal dentro de `.entry-content` — `h2` 1.25rem `font-weight:600` en `--ta-ink`, `p` 0.9375rem `line-height:1.7` en `--ta-muted`, `strong` en `--ta-ink` bold — con separación `> * + *` ya provista por `page.php`. Así el documento legal usa la tipografía/colores de la marca (Manrope + verde/negro del CLAUDE.md).
4. `TA_VERSION` subido 1.18.10 → 1.18.11 (cambio de frontend: footer + CSS).

**Verificación:** `php -l` OK en `footer.php`; por `curl`: la página `/index.php/terminos-y-condiciones/` renderiza con nav y footer (`ta-header`/`ta-footer` presentes), el `<h1>` "Términos y Condiciones", los 8 `<h2>` de las cláusulas y enlaza `style.css?ver=1.18.11`; el CSS servido trae el bloque "contenido de páginas legales" y el footer de otras páginas ya enlaza al permalink real (no más `href="#"`). La página se puede editar desde el dashboard (Páginas → Términos y Condiciones) si el texto legal cambia — la doc no embebe el contenido (vive en la BD). El bloque embebido de §1.4 quedó byte a byte idéntico al `footer.php` real (`diff`, sin contar el `\n` final del archivo).

### 4.48 Página legal "Política de Privacidad" enlazada en el footer (versión 1.18.12)

Pedido del usuario: a la **izquierda** de "Términos y condiciones" en el footer debe estar "Política de privacidad" y, al presionarlo, abrirse una nueva página con nav y footer — mismo patrón que §4.47.

Implementado:

1. **Página de WordPress** creada con `wp_insert_post()` (id=102, estado `publish`, slug `politica-de-privacidad`, título "Política de Privacidad", URL `http://localhost/wordpress/proyecto3/index.php/politica-de-privacidad/`). Como el usuario **no entregó el texto** en ese momento, se dejó un contenido **provisional de estructura** (tratamiento de datos conforme a la ley 21.719, correo `contacto@todoacrilico.cl` para ejercer derechos, y una nota `<em>` aclarando que se puede editar desde Páginas → Política de Privacidad). → El texto definitivo llegó después y se reemplazó íntegro en **§4.49**.
2. **`footer.php`** (§1.4): se agregó `$ta_footer_privacidad = get_page_by_path( 'politica-de-privacidad' );` y en `.ta-footer-legal` el link "Política de privacidad" quedó **antes** de "Términos y condiciones" (a su izquierda), con el mismo patrón `get_permalink()` + fallback a `home_url('/')`. No hizo falta CSS: `.ta-footer-legal` ya es `display:flex` con `gap:24px`.
3. `TA_VERSION` subido 1.18.11 → 1.18.12 (cambio de frontend: footer).

**Verificación:** `php -l` OK en `footer.php`; por `curl`: la página `/index.php/politica-de-privacidad/` renderiza con nav y footer, `<h1>` "Política de Privacidad" y los estilos de página legal; el footer servido muestra el `href` real a la página (primero Política, luego Términos). El bloque embebido de §1.4 quedó byte a byte idéntico al `footer.php` real (`diff`, sin contar el `\n` final). Sin confirmar visualmente en navegador.

### 4.49 Política de Privacidad: contenido íntegro del cliente + tabla y listas estilizadas (versión 1.18.13)

Pedido del usuario: agregar en la página `politica-de-privacidad` el texto completo de la Política (8 secciones, definiciones legales de la Ley 21.719, tabla de categorías de datos, derechos ARCOP, conservación, autoridad de control), reemplazando el placeholder de §4.48.

Implementado:

1. **Contenido de la página** (id=102, editado con `wp_update_post()`): se cargó el texto íntegro del cliente estructurado en HTML semántico — 8 `<h2>` (una por cláusula), `<p>` para párrafos, `<strong>` para los términos en negrita ("Nota de Vigencia:", definiciones del Art. 2, "Gestión Precontractual:", bases de licitud, derechos ARCOP, etc.), una `<table>` con `<thead>/<tbody>` de 3 columnas (Categoría de Dato / Naturaleza / Fuente) con 5 filas para la Sección 3, `<ul><li>` para la identificación del Responsable (Sección 1) y para las medidas de seguridad (Sección 5). El título de la página ya era "Política de Privacidad" (el `<h1>` de `page.php`). El contenido vive en la BD — este doc no lo embebe.
2. **`style.css`** (§1.1): se extendió el bloque "contenido de páginas legales" con estilos de `table` (bordes `--ta-border`, `th` en `--ta-bg` con `--ta-ink`, `td` en `--ta-muted`, `border-collapse`), y de `ul/li` (indent, `0.9375rem`, `line-height:1.7`, separación entre ítems). Con esto la tabla y listas de la política usan la paleta de la marca igual que el resto.
3. `TA_VERSION` subido 1.18.12 → 1.18.13 (cambio de frontend: CSS).

**Verificación:** por `curl` la página `/index.php/politica-de-privacidad/` sirve los 8 `<h2>`, la tabla (3 `<th>` + 5 filas de datos), los marcadores de contenido ("RUT: 77.863.522-4", "Nota de Vigencia:", "ARCOP") y enlaza `style.css?ver=1.18.13`. Sin confirmar visualmente en navegador.

### 4.50 Formulario de Contacto: aceptación obligatoria de Política y Términos (versión 1.18.14)

Pedido del usuario: **sobre el check opcional** (arriba) debe existir otro checkbox con el texto "He leído y acepto la Política de Privacidad y los Términos y condiciones.", donde **"la Política de Privacidad"** y **"Términos y condiciones"** son enlaces a las páginas legales (§4.47/§4.49).

Implementado:

1. **`page-contacto.php`** (§1.10): nuevo `<label class="ta-contact-optin ta-contact-accept">` **antes** del optin opcional (orden: nota de adjuntos → check obligatorio → check opcional → alert de privacidad → botón). Tiene `<input type="checkbox" name="acepto_terminos" value="1" required>` (attribute `required` = validación nativa del navegador, no permite enviar sin marcarlo) y el `<span>` con los dos enlaces. Para las URLs se agregaron arriba `$ta_privacy_url` y `$ta_terms_url` (con `get_page_by_path()` y fallback a `home_url('/')`), impresas con `esc_url()`. También se agregó el mensaje de error `'terminos'` al mapa `$ta_contact_error_messages`.
2. **`functions.php`** (§1.2, `ta_handle_contact_submit()`): validación server-side justo después del honeypot — `if ( empty( $_POST['acepto_terminos'] ) ) { $fail( 'terminos' ); }` — así un POST forjado sin el checkbox tampoco envía el correo (la validación no depende solo del `required` del navegador).
3. **`style.css`** (§1.1): reglas nuevas `.ta-contact-optin a` (enlacence en verde `--ta-accent`, subrayado con offset, hover `--ta-accent-dark`) y `.ta-contact-form .ta-contact-accept` (`font-weight:600` para diferenciar visualmente el check obligatorio del opcional).
4. `TA_VERSION` subido 1.18.13 → 1.18.14 (cambio de frontend + manejador).

**Verificación:** `php -l` OK en `page-contacto.php` y `functions.php`; por `curl` la página servida muestra el `input[type="checkbox"][name="acepto_terminos"][required]` **antes** del `acepta_ofertas`, los dos `href` reales dentro del `<span>` (Política y Términos), y enlaza `style.css?ver=1.18.14`. El bloque de §1.10 y el chunk de §1.2 quedaron byte a byte idénticos (los cambios se replicaron en la doc embebida). Sin confirmar visualmente en navegador.

### 4.51 Formulario de Contacto: tarjeta más angosta (versión 1.18.15)

Queja del usuario: "el formulario quedó muy ancho". Causa: la sección de Contacto usa `.ta-contact-inner { max-width: var(--ta-shell-width) }` (1850px, el mismo ancho deliberado del navbar y footer, §4.22), así que la columna `1fr` del grid dejaba la tarjeta en ~980px y los campos se estiraban a todo ese ancho.

Decisión (confirmada con el usuario): limitar la **tarjeta** del formulario, no la sección completa.

1. **`style.css`** (§1.1): `.ta-contact-form-card` ganó `max-width: 680px`. Como los inputs/textarea son `width:100%` y el form es `flex-direction:column`, todos los campos bajan proporcionalmente (≈680px − 64px de padding = ~616px útiles). La tarjeta queda alineada a la izquierda en su columna del grid (`align-items: start`), respetando el alineado con el navbar. No se tocó `.ta-contact-inner` ni `--ta-shell-width`, así que navbar/footer/reto de páginas no cambian.
2. `TA_VERSION` subido 1.18.14 → 1.18.15 (cambio de CSS).

**Verificación:** el CSS servido (`style.css?ver=1.18.15`) trae `max-width: 680px` en `.ta-contact-form-card`. Sin confirmar visualmente en navegador.

### 4.52 Formulario de Contacto: alineado con la descripción de la columna izquierda (versión 1.18.16)

Queja del usuario: el formulario debe empezar "justo en" la descripción de la columna izquierda ("Escríbenos con la mayor cantidad de detalles: medidas, uso y contexto…"), es decir a la misma altura.

Con `align-items: start` la tarjeta nunca quedaba alineada con el texto: arrancaba arriba en el tope del grid, junto al eyebrow de la columna izquierda.

Solución en **`style.css`** §1.1:
1. `.ta-contact-form-card { margin-top: 137px }` — desplazamiento calculado como eyebrow (~20,5px de línea) + su margen (8px) + título (línea ~66,6px) + su margen (16px) + divisora (2px) + su margen (24px). Así el borde superior de la tarjeta (y el primer campo, Nombre/Teléfono) coincide con la línea del párrafo de la descripción.
2. `@media (max-width: 860px)`: `.ta-contact-form-card { margin-top: 0 }` — al apilar las columnas (`.ta-contact-inner` pasa a 1fr) el margen solo agregaría un hueco raro; se resetea.

`TA_VERSION` 1.18.15 → 1.18.16.

**Verificación:** el CSS servido (`style.css?ver=1.18.16`) trae el `margin-top: 137px` con su comentario y el reset en el media query de 860px. Sin confirmar visualmente en navegador — el 137px es un cálculo tipográfico; si en pantalla no calza exacto (p. ej. con el breakpoint `clamp()` del título), se ajusta.

### 4.53 Contacto: subrayado grueso "en negritas" bajo el título (versión 1.18.17)

Pedido del usuario: debajo de "Cuéntanos" debe haber una línea (`_`) que llegue hasta las letras **Cu**, como subrayado, y en negritas.

Solución en **`style.css`** §1.1 — reemplaza la divisora gris de 40px:

1. `.ta-contact-title { position: relative; margin-bottom: 18px }` (antes 16).
2. **`.ta-contact-title::after`**: barra absoluta `width: 2.2ch`, `height: 6px` (gruesa/"negrita"), `background: var(--ta-accent)` (verde marca), `border-radius: 2px`, `left: 0; bottom: 4px`. Va debajo de las letras iniciales del título y llega hasta el final de "Cu".
   - El ancho se da en `ch` **relativo al propio título** (el `::after` hereda su `font-size`), así que la barra escala con el `clamp(2rem, 3.6vw, 2.6rem)` en vez de fijar un px que se desfasa en otros viewports.
3. `.ta-contact-divider`: se mantiene solo como espaciado (`margin: 0 0 24px`), la línea gris pasó a `background: transparent` — el subrayado visual lo aporta el `::after`.

`TA_VERSION` 1.18.16 → 1.18.17.

**Verificación:** el CSS servido (`style.css?ver=1.18.17`) trae el `::after` con su comentario. `2.2ch` ≈ ancho aproximado de "Cu" en Manrope; si en pantalla la barra no cae justo bajo las dos letras, se ajusta el `ch` o se cambia por una medida exacta. Sin confirmar visualmente en navegador.

### 4.54 Footer: columna "Síguenos" con Instagram y LinkedIn (versión 1.18.18)

Pedido del usuario: en el footer, al lado derecho de "Contacto", una columna con el título **Síguenos** y debajo, uno al lado de otro, el ícono de Instagram (enlace `https://www.instagram.com/todoacrilico_cl/`) y a su derecha el de LinkedIn (enlace `https://www.linkedin.com/company/todo-acrílico/`).

Cambios:

1. **`functions.php`** (§1.2): `ta_icon()` ganó 2 íconos nuevos, estilo Lucia (solo trazo, `currentColor`, consistentes con el resto del banco): `'instagram'` (rectángulo redondeado + círculo + punto) y `'linkedin'` (signo "in" dentro, con el círculo del punto). También se actualizó el docblock del banco con los nombres nuevos.
2. **`footer.php`** (§1.4): nueva columna `.ta-footer-col` justo después de la de Contacto, con `<h3 class="ta-footer-heading">Síguenos</h3>` y una fila `.ta-footer-social` con dos `<li>`: cada uno es un `<a href="…" target="_blank" rel="noopener noreferrer">` con `aria-label` descriptivo y dentro un `<span class="ta-footer-icon" aria-hidden="true">` con `ta_icon('instagram', 20)` / `ta_icon('linkedin', 20)`. El link de LinkedIn se dejó literalmente con la "í" acentuada tal como lo dio el usuario.
3. **`style.css`** (§1.1):
   - `.ta-footer-top` pasó de `grid-template-columns: 1.4fr 1fr 1fr` a **`1.4fr 1fr 1fr 1fr`** (4 columnas; sigue centrado con `--ta-shell-width` y en el media query ≤860px colapsa a 1fr).
   - Nueva `.ta-footer-social`: `display:flex; gap:12px` — los dos íconos quedan **uno al lado de otro**. Cada `a` es un botón cuadrado de 44×44px (`border: 1px solid var(--ta-border)`, `border-radius: 8px`, color muted); en `:hover` se pinta con el verde de marca (`--ta-accent`) y un fondo verde traslúcido. `.ta-footer-social .ta-footer-icon { color: inherit }` para que los íconos hereden el color del botón en vez del verde fijo que trae `.ta-footer-icon`.

`TA_VERSION` 1.18.17 → 1.18.18.

**Verificación:** `php -l` OK en `footer.php`/`functions.php`; por `curl` la home servida trae la columna con ambos `<a>` y las URLs correctas, y `ta_icon` entrega los SVG a 20px. El bloque de §1.4 embebido quedó idéntico al archivo real (descontando el salto de línea final de costumbre). Sin confirmar visualmente en navegador (los íconos y el hover verde).

### 4.55 Contacto: subrayado de "Cu" en negro y con la misma distancia que el eyebrow (versión 1.18.19)

Refinamiento del subrayado de §4.53: el usuario pidió que la barra que cubre "Cu" quedara **negra** y con una distancia hacia abajo **similar a la que separa el eyebrow "Contacto" del título** (8px del `margin-bottom` del eyebrow).

Cambio solo en `style.css` (§1.1), `.ta-contact-title::after`:
- `background: var(--ta-accent)` (verde marca) → **`var(--ta-ink)`** (negro `#1A1C1C`).
- `bottom: 4px` → **`bottom: 6px`**: con line-height 1.6 la base del texto queda ~20.8px sobre el borde inferior del título; con la barra de 6px a `bottom:6px`, su borde superior queda ~8px bajo la base — la misma distancia que el eyebrow ya pone sobre el título, pero hacia abajo.
- Se quitó el `border-radius: 2px` (subrayado "de golpe", más fiel a un subrayado que una barra redondeada) y se escribió el comentario explicando el porqué del `bottom`.

`TA_VERSION` 1.18.18 → 1.18.19.

**Nota de sincronización:** durante esta revisión se detectó que el bloque CSS embebido de §1.1 había quedado desactualizado (~650 líneas de diferencia con el `style.css` real, arrastrando varias entradas de bitácora de esta ronda). Se reemplazó el bloque completo por el archivo real: **§1.1 quedó byte a byte IDÉNTICO a `style.css`** (confirmado con `diff` programático).

**Verificación:** el CSS servido (`style.css?ver=1.18.19`) trae `background: var(--ta-ink)` y `bottom: 6px` en `.ta-contact-title::after`. Sin confirmar visualmente en navegador — el `6px` asume métricas tipográficas de Manrope; si la barra no calza ~8px bajo la base del texto, se ajusta el valor.

### 4.56 Contacto: subrayado de "Cu" con salto de línea (versión 1.18.20)

El usuario reportó que la raya negra de §4.55 quedaba **muy pegada a las letras** y pidió que hiciera "un salto de línea y luego la línea" — es decir, un espacio visible entre el texto y la barra, no un subrayado tocando la tipografía.

Causa: con `bottom: 6px` la barra quedaba ~3px debajo del descensor de la "u" (en Manrope la "u" baja ~0,2em bajo la linéa base), así que visualmente se veía pegada/tocando las letras.

Cambio en `style.css` (§1.1), `.ta-contact-title::after`: `bottom: 6px` → **`bottom: 16px`** — la barra baja ~10px más y queda con ~8px de aire bajo el descensor de la "u", un salto claro entre texto y raya. Se actualizó el comentario del bloque.

`TA_VERSION` 1.18.19 → 1.18.20. El bloque §1.1 se re-sincronizó completo (reemplazo con el archivo real) y volvió a quedar IDÉNTICO.

**Verificación:** el CSS servido (`style.css?ver=1.18.20`) trae `bottom: 16px` en `.ta-contact-title::after`. Sin confirmar visualmente en navegador — 16px es un cálculo tipográfico; si el aire aún se ve poco o demasiado, se ajusta.

### 4.57 Desplegable de "Catálogo": se abre con clic y navega por categoría (versión 1.18.21)

Pedido del usuario: hoy el desplegable de "Catálogo" se abre al pasar el mouse y un clic solo mueve la flecha; debe funcionar así: **clic sobre "Catálogo" → la flecha despliega el listado**, **seleccionar cualquiera del listado → dirige a su contenido**, con "lo demás" (hover, flecha, click-away) siguiendo igual.

Cambios:

1. **`assets/js/ta-nav.js`** (§1.12): se factorizó la lógica en una función `toggle(li)` y se le conectó el clic **al link "Catálogo"** (`:scope > a`, con `e.preventDefault()` para que no navegue en el primer click) además del botón `ta-nav-caret-toggle`. Ahora el click sobre el link despliega el listado y rota la flecha (`is-open` → CSS). Se agregó además cerrar con `Escape`. El clic fuera (click-away) y el `closeAll()` se mantuvieron.
2. **`functions.php`**, `TA_Nav_Walker::end_el()` (§1.2): los links del desplegable dejaron de apuntar a las anclas `#categoria-{slug}` y pasan a `?categoria={slug}` (`add_query_arg()` sobre el permalink) — el mecanismo real de la página del Catálogo son las pestañas `?categoria=` (§4.41, `page-nuestras-soluciones.php`), así que ahora seleccionar un ítem **dirige de verdad a su contenido**. Esto cerró de paso el bug anotado en §4.41/§5 (los anchors ya no existían en la página nueva).
3. Se agregó al tope del desplegable el ítem **"Todas las piezas"** (enlaza al catálogo completo, sin `categoria`) — como el click en "Catálogo" ahora despliega en vez de navegar, esta es la vía para llegar a todo el catálogo.
4. Sin cambios de CSS: el hover sigue abriendo el panel (`li:hover`), y `.is-open` ya mostraba la lista y rotaba la flecha.

`TA_VERSION` 1.18.20 → 1.18.21 (cambio de PHP + JS; cache-busting de ambos).

**Verificación:** `php -l` OK en `functions.php` y `node --check` OK en `ta-nav.js`; por `curl` la home sirve el desplegable con "Todas las piezas" + los links `?categoria=organizador|proteger|vidrios` (las categorías reales con imágenes), **sin** ningún `#categoria-`, y `ta-nav.js?ver=1.18.21`. El markup embebido de §1.12 quedó byte a byte idéntico al archivo real. Sin confirmar visualmente en navegador (el clic/test del hover).

> ⚠ **Corregido en §4.58:** aquí se mantuvo el hover (`li:hover`/`:focus-within`) abriendo el panel, pero el usuario aclaró que el listado NO debe aparecer con el mouse — solo con click. El estado vigente es §4.58 (versión 1.18.22).

### 4.58 Desplegable de "Catálogo": sin hover (solo click) — corrección del usuario (versión 1.18.22)

Corrección del usuario tras §4.57: "no se debe mostrar el listado si se pone el mouse sobre Catálogo; debe mostrarse únicamente cuando se haga click. Todo lo demás funciona." Es decir, §4.57 mantuvo por error el hover CSS (`li:hover`/`:focus-within`) como "lo demás" — el usuario confirmó que el hover NO debe abrir.

Cambios (todo en CSS, §1.1):

1. Se eliminó el selector `li:hover > .ta-nav-dropdown` del estado abierto del panel: ahora la regla es **solo** `.ta-nav-list li.is-open > .ta-nav-dropdown`.
2. Se eliminó también `li:focus-within` — conservarlo rompía el cierre por click (tras el `preventDefault()` el foco queda en el link y el panel seguiría abierto aunque se quite `is-open`).
3. En `@media (max-width:480px)` el modo acordeón también quedó con `li.is-open` solamente (idéntico motivo).
4. Se actualizaron los comentarios del CSS y del encabezado de `ta-nav.js` (§1.12 ya no menciona hover).

La flecha del caret ya solo dependía de `is-open` (`.ta-nav-has-caret.is-open .ta-nav-caret-toggle::after`), así que no se tocó. El hover de color sobre los ítems del listado (`.ta-nav-dropdown a:hover`) se conserva — es feedback visual, no apertura.

`TA_VERSION` 1.18.21 → 1.18.22 (cambio de CSS → cache-busting `style.css?ver`; el JS también se sirve a 1.18.22 por compartir la constante).

**Verificación:** CSS servido (`style.css?ver=1.18.22`) ya no contiene `li:hover > .ta-nav-dropdown` ni `:focus-within`; §§1.1 y 1.12 de la doc quedaron byte a byte idénticos a los archivos reales. Pendiente el recheck visual: **mouse sobre "Catálogo" NO abre; click SÍ abre** (y la flecha rota).

### 4.59 Bloque destacados "Piezas más solicitadas": fondo blanco (versión 1.18.23)

Pedido del usuario: el componente de Inicio "Piezas más solicitadas" (los Destacados, `ta_render_showcase_grid` en `functions.php` §1.2, sección `.ta-showcase` de `front-page.php`) debe tener **fondo blanco, como el nav** (`background: var(--ta-surface)`).

Cambio: en `style.css` §1.1 se habilitó la línea que estaba comentada en `.ta-showcase` — `background: var(--ta-surface)` (blanco `#ffffff`, la misma variable que usa `.ta-header`/`.ta-nav`). El resto (padding 64px, tarjetas, grid) intacto. Nota: la sección de "Nuestra historia" (`.ta-story`, §4.44 §4.48) ya usa `--ta-surface`; con esto la banda central de la home (hero → categorías → destacados → historia) queda continua en blanco.

`TA_VERSION` 1.18.22 → 1.18.23 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK en `functions.php`; §1.1 actualizado y byte a byte idéntico. Pendiente el recheck visual: el bloque "Piezas más solicitadas" se ve blanco, sin bordes visibles con el resto de la página.

### 4.60 CTA "¿Tienes una idea en mente?": fondo blanco (versión 1.18.24)

Pedido del usuario: el componente reutilizable de cierre "¿Tienes una idea en mente?" (`ta_render_cta_section` en `functions.php` §1.2, sección `.ta-cta`) debe tener **fondo blanco, como el nav**.

Cambio: en `style.css` §1.1 se agregó `background: var(--ta-surface)` a `.ta-cta` (blanco `#ffffff`, la misma variable del `.ta-header`/`.ta-nav`). El resto intacto (padding 72px, `.ta-cta-inner` centrado, botón negro).

`TA_VERSION` 1.18.23 → 1.18.24 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** §1.1 actualizado y byte a byte idéntico. Pendiente el recheck visual: el CTA se ve con fondo blanco.

### 4.61 Footer: sin franja de color por encima de la línea (versión 1.18.25)

Reporte del usuario: "el footer tiene una línea y sobre ella sigue el color de fondo del footer, eso no debe estar."

Causa: `.ta-footer` usaba `border-top: 1px solid var(--ta-border)` **y** `margin-top: 64px`; ese margen es transparente y deja ver el fondo gris del `body` (`var(--ta-bg`, el mismo color de fondo del footer), creando una franja del color del footer *por encima* de la línea.

Cambio: se quitó `margin-top: 64px` de `.ta-footer`. El aire lo da ahora el padding inferior de la sección que la precede (p. ej. `.ta-cta`, 72px en blanco); la línea `border-top` marca el arranque del gris del footer sin gris previo.

`TA_VERSION` 1.18.24 → 1.18.25 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK; §1.1 actualizado e idéntico. Pendiente el recheck visual: **por encima de la línea del footer ya no hay gris** (solo el blanco de la sección anterior, y luego la línea).

### 4.62 Guía de mantenimiento (Cuidados): fondo blanco (versión 1.18.26)

Pedido del usuario: en la pestaña Cuidados, el componente "GUÍA DE MANTENIMIENTO" debe tener fondo blanco (como el nav).

La sección la sirve `ta_render_feature_grid()` (`functions.php` §1.2, sección `.ta-features`), que también se usa en la home para el bloque de categorías — por eso se evitó pintar `.ta-features` en general:

1. **`functions.php`**: `ta_render_feature_grid()` acepta ahora un quinto parámetro `$class` (opcional) que agrega una clase al `<section>` (`esc_attr`), manteniendo compatibilidad con la llamada de la home (que no lo pasa).
2. **`page-cuidados-del-acrilico.php`** (§1.11): la llamada de la guía pasó `'ta-features--white'`.
3. **`style.css`** (§1.1): nueva clase `.ta-features--white { background: var(--ta-surface); }` (blanco `#ffffff`, el del nav). La home queda intacta (categorías sobre gris).

`TA_VERSION` 1.18.25 → 1.18.26 (PHP + CSS → cache-busting de ambos).

**Verificación:** `php -l` OK en `functions.php` y `page-cuidados-del-acrilico.php`; por `curl` la página Cuidados sirve `<section class="ta-features ta-features--white">`, la home sigue con `class="ta-features"` sin la variante, y `style.css?ver=1.18.26` trae `.ta-features--white`. §1.1 y chunk embebido de `ta_render_feature_grid` en §1.2 actualizados. Pendiente el recheck visual: la guía se ve sobre fondo blanco.

### 4.63 Consejo final del experto (Cuidados): fondo gris (versión 1.18.27)

Pedido del usuario: el componente "CONSEJO FINAL DEL EXPERTO" (la sección foto + texto con panel, `ta_render_story_section(..., 'panel' => true)` en `page-cuidados-del-acrilico.php`, sección `<section class="ta-story ta-story--panel">`) debe tener el **gris que se le quitó al componente anterior** — es decir, el gris general de página `var(--ta-bg)` (#f5f7f6) que tenía `.ta-features` antes de volverse blanco (§4.62).

Cambio: en `style.css` §1.1, `.ta-story--panel` pasó de `padding: 0 24px` (sobre el blanco de `.ta-story`) a `padding: 64px 24px; background: var(--ta-bg)`. El panel interior (`.ta-story--panel .ta-story-inner`, `#ececea`) queda igual — es la tarjeta, más clara que el gris de fondo. La home ("Nuestra historia", `.ta-story` sin `--panel`) no se toca: sigue blanco.

`TA_VERSION` 1.18.26 → 1.18.27 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK; §1.1 actualizado e idéntico. Pendiente el recheck visual: el bloque "Consejo final del experto" se ve sobre gris `#f5f7f6`.

### 4.64 Consejo final del experto: gris del ancho de las tarjetas (versión 1.18.28)

Ajuste del usuario sobre §4.63: "el fondo gris debe ser del ancho que ocupan las tarjetas del componente Guía de mantenimiento." El gris a todo lo ancho de pantalla sobre la franja blanca ya no — debe ser un bloque gris **centrado, del mismo ancho que las tarjetas** de la guía (`.ta-container` = `var(--ta-shell-width)`, 1850px max), con los lados blancos (igual que las secciones vecinas).

Cambio en `style.css` §1.1:

1. `.ta-story--panel` vuelve a `background: var(--ta-surface)` (blanco) y `position: relative`.
2. Pseudo-elemento `.ta-story--panel::before`: franja gris `var(--ta-bg)` **centrada** (`left: 50%; translateX(-50%)`), `width: 100%; max-width: var(--ta-shell-width)` — el mismo ancho del `.ta-container` de la guía — cubriendo todo el alto de la sección.
3. `.ta-story--panel .ta-story-inner` queda sobre la franja (`position: relative` ya lo tenía) y se le centró con `margin-inline: auto`. La tarjeta blanca interior (línea ~907, `background: var(--ta-surface) !important`) se conserva.

`TA_VERSION` 1.18.27 → 1.18.28 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK; §1.1 actualizado e idéntico. Pendiente el recheck visual: **el gris del "Consejo final del experto" ya no llega a los bordes de la pantalla — es un bloque centrado del ancho de las tarjetas de la guía, con blanco a los costados.**

### 4.65 Consejo final del experto: el interior con el mismo gris (versión 1.18.29)

Ajuste del usuario sobre §4.64: "el interior del componente final experto debe tener el fondo el mismo gris." Es decir, la tarjeta interior (`.ta-story--panel .ta-story-inner`) deja de ser blanca y usa el mismo gris de la franja (`var(--ta-bg)`).

Cambio en `style.css` §1.1: `.ta-story--panel .ta-story-inner` pasa de `background: var(--ta-surface) !important` a `background: var(--ta-bg) !important` (la regla gana por `!important` sobre el `#ececea` inicial ~línea 757). El `border-radius: 0`, `padding: 48px` y `max-width: 1100px` se mantienen. La foto interior (`.ta-story-media`, también `--ta-bg`) queda del mismo tono.

`TA_VERSION` 1.18.28 → 1.18.29 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK; §1.1 actualizado e idéntico. Pendiente el recheck visual: el interior del componente se ve del mismo gris que la franja que lo envuelve.

### 4.66 Consejo final del experto: gris a la misma anchura que la guía (versión 1.18.30)

Ajuste del usuario sobre §4.64-§4.65: "el ancho es un poco más ancho que el componente Cómo cuidar tus piezas; debe tener la misma anchura y debe ser responsive."

Causa: la franja gris (`::before`) usaba `max-width: var(--ta-shell-width)` (1850px), pero las tarjetas de la guía viven en `.ta-container`, que además tiene `padding: 0 24px` — entonces la franja quedaba 48px más ancha que la columna de las tarjetas.

Cambios en `style.css` §1.1:

1. `.ta-story--panel::before`: `width: calc(100% - 48px)` y `max-width: calc(var(--ta-shell-width) - 48px)` — el mismo contenedor de las tarjetas (shell menos 24px por lado), todo en `%`/`calc` para que sea responsive en cualquier ancho.
2. `.ta-story--panel .ta-story-inner`: `max-width: none` (antes tope fijo de 1100px) — la tarjeta ahora ocupa exactamente el mismo ancho de la columna de la guía, sin chocar con el gris en pantallas medianas.

`TA_VERSION` 1.18.29 → 1.18.30 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK; §1.1 actualizado e idéntico. Pendiente recheck visual: **el bloque gris del "Consejo final del experto" tiene exactamente la misma anchura que las tarjetas "Cómo cuidar tus piezas", en desktop y en mobile.**

### 4.67 Consejo final del experto: un solo gris, contenido directo (versión 1.18.31)

Reporte del usuario tras §4.66: "no se visualiza como espero. Está el fondo que sí se visualiza, pero hay otro fondo que deformó el componente." — El usuario distinguía dos fondos: la franja gris (bien) y la tarjeta interior, que al quitarle el tope de ancho (§4.66) estiraba el layout.

Entrevistado con opciones, el usuario eligió: **"Solo el gris, contenido directo"** — un único bloque gris del ancho de la guía, sin tarjeta; texto y foto directos sobre el gris, centrados, con buen espaciado.

Cambios en `style.css` §1.1:

1. La franja gris `.ta-story--panel::before` se conserva (ancho de la guía, responsive): es el único fondo.
2. `.ta-story--panel .ta-story-inner`: `background: transparent !important` (se elimina la tarjeta; el contenido queda sobre la franja), `max-width: 1100px` para un ancho de lectura cómodo centrado (ya no `none`), `padding: 64px 48px`.

`TA_VERSION` 1.18.30 → 1.18.31 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** `php -l` OK; §1.1 actualizado e idéntico. Pendiente recheck visual: **un solo bloque gris del ancho de "Cómo cuidar tus piezas", con el texto y la foto directos sobre el gris, sin tarjeta ni doble fondo.**

### 4.68 Tipografía del banner (home) y botones (versión 1.18.32)

Pedido del usuario, según las etiquetas de estilo de `CLAUDE.md`:

1. Eyebrow "Taller de acrílico · Chile · Desde 2019": **12px, extrabold** (`.ta-hero-eyebrow` — era `clamp(0.7–0.8rem)` y 700).
2. Título "Soluciones en acrílico, pensadas para durar.": **80px** y **interlineado de 100px** (`.ta-hero h1` — era `clamp(2–4.2rem)` con `line-height:1.1`). Se mantiene weight 300 (light).
3. Párrafo "Diseñamos y fabricamos…": **18px**, regular (`.ta-hero p` — era `clamp(1–1.15rem)` weight 300 → ahora 400).
4. **Todos los botones**: **12px, extra bold** (`.ta-button` — no tenía tamaño fijo; weight 600 → 800).

Cambios 100% en `style.css` §1.1. `TA_VERSION` 1.18.31 → 1.18.32 (solo CSS → cache-busting `style.css?ver`).

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: tamaños 12/80(p+100)/18 y botones a 12px. Nota: la tipografía del sitio sigue siendo `system-ui` (Manrope no está cargado — si el usuario la quiere, hay que agregar la fuente en otro paso).

### 4.69 Tipografía del bloque Categorías (versión 1.18.33)

Pedido del usuario (bloque "Categorías" de la home, `ta_render_feature_grid` en `front-page.php`, sección `.ta-features`), según las etiquetas de `CLAUDE.md`:

1. Eyebrow "Categorías": **12px** (`.ta-features-eyebrow`, era 0.8rem/700 → 12px/**800**).
2. Título "Cuatro maneras de trabajar el acrílico.": **32px** (`.ta-features-title`, era `clamp(1.6–2.1rem)`).
3. Títulos de tarjetas "Organizar / Exhibir / Proteger / Informar": **24px** (`.ta-feature-card h3`, era 1.15rem).
4. Texto bajo cada título: **15px** (`.ta-feature-card p`, era 0.92rem).
5. "Ver piezas": **12px** — ya lo da `.ta-button` (12px/800 desde §4.68), el vínculo `.ta-button--text` lo hereda; sin cambios.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.32 → 1.18.33.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: eyebrow 12, título 32, títulos de card 24, párrafos 15, "Ver piezas" 12.

### 4.70 Tipografía del bloque Destacados (versión 1.18.34)

Pedido del usuario (bloque "Piezas más solicitadas", `ta_render_showcase_grid` en `front-page.php`, sección `.ta-showcase`):

1. Eyebrow "Destacados": **12px** (`.ta-showcase-eyebrow`, era 0.8rem/700 → 12px/**800**).
2. Título "Piezas más solicitadas.": **32px** (`.ta-showcase-title`, era `clamp(1.6–2.1rem)`).
3. Títulos de tarjetas "Buzón / Organizador de escritorio / Bandeja modular": **20px** (`.ta-showcase-card h3`, era 1.1rem) — **nota:** el usuario pidió 20px aquí, distinto de los 24px de la guía de categorías (§4.69).
4. Texto bajo cada título: **15px** (`.ta-showcase-card p`, era 0.92rem).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.33 → 1.18.34.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: eyebrow 12, título 32, títulos de card 20, párrafos 15.

### 4.71 Tipografía del bloque Nuestra Historia (versión 1.18.35)

Pedido del usuario (bloque "Nuestra historia", `ta_render_story` en `front-page.php`, sección `.ta-story`):

1. Eyebrow "NUESTRA HISTORIA": **12px** (`.ta-story-eyebrow`, era 0.8rem/700 → 12px/**800**).
2. Título "Más de 20 años trabajando el acrílico a medida": **48px regular** (`.ta-story-title`, era `clamp(1.8–2.4rem)`; weight exitplícito 400 — el usuario pide "regular").
3. Párrafos: **18px regular** (`.ta-story-content p`, era 1rem; weight 400).
4. Cifras "5k+" / "20+": **Manrope medium 24px** (`.ta-story-stat-value`, era 1.6rem/700 → **24px/500**).
5. Etiquetas "Proyectos realizados" / "Años de experiencia": **Manrope extrabold 12px** (`.ta-story-stat-label`, era 0.85rem/bold → **12px/800**).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.34 → 1.18.35.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: eyebrow 12, título 48 regular, párrafos 18, cifras 24 medium, etiquetas 12 extrabold.

### 4.72 Manrope instalada en el tema + tipografía del footer (versión 1.18.36)

1. **Manrope se instala por primera vez en el proyecto** (pedido del usuario: "instales esa tipografía en el proyecto y se aplique a todos los textos automáticamente"):
   - Se descargaron de Google Fonts los 7 pesos estáticos de Manrope (200, 300, 400, 500, 600, 700, 800) y se guardaron en `wp-content/themes/todo-acrilico/fonts/manrope/Manrope-*.ttf` (self-hosted, no depende de CDN ni de conexión).
   - Se agregaron los bloques `@font-face` al inicio de `style.css` con `font-display: swap`.
   - `body { font-family: "Manrope", system-ui, … }` → **todos los textos del sitio la usan por defecto**.
   - Se eliminaron los `font-family: system-ui` explícitos del `.ta-hero h1` y `.ta-hero p` para que también hereden Manrope.
2. **Footer**, según el pedido:
   - Títulos de columna "EXPLORAR / CONTACTO / SÍGUENOS" (`.ta-footer-heading`): **12px extrabold** (era 0.8rem/700; ahora 12px/800, se mantienen `uppercase` y el letter-spacing).
   - Resto de textos —descripción "Más de 5 años…" (`.ta-footer-brand p`), enlaces "Inicio / Catálogo / Cuidados / Contacto" (`.ta-footer-links a`) y contacto "Santiago, Chile / contacto@todoacrilico.cl / Lun a Vie 9:00 – 18:00" (`.ta-footer-contact-list li`)—: **15px** (eran 0.92–0.95rem).

Cambios solo en `style.css` §1.1 + nueva carpeta `fonts/manrope/` (7 TTF). `TA_VERSION` 1.18.35 → 1.18.36.

**Verificación:** TTF válidos (`file`); §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: fuente Manrope aplicada globalmente y footer 12/15.

### 4.73 Navbar: 16px normal, bold al seleccionar (versión 1.18.37)

Pedido del usuario: el menú del navbar a **16px de peso normal**, y **bold cuando está seleccionado**.

- `.ta-nav-list > li > a`: `font-size: 0.94rem` (~15px) → **16px**; `font-weight: 1000` (valor inválido/excesivo que heredaba el estado) → **400**.
- Regla de estado activo/hover (`.ta-nav-list > li > a:hover, .ta-nav-list > li.current-menu-item > a`): se agrega `font-weight: 700` (bold), manteniendo el fondo gris y el color verde de marca existentes.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.36 → 1.18.37.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: items a 16px normales, seleccionado/hover en bold.

### 4.74 Hero: "Soluciones en acrílico," en una sola línea (versión 1.18.38)

Pedido del usuario: la primera frase del título del banner (componente "Taller de acrílico · Chile · Desde 2019") debe quedar en **una sola línea**.

- `.ta-hero h1` (80px): se agrega `white-space: nowrap` — "Soluciones en acrílico," ya no salta de línea (la segunda frase `pensadas para durar.` sigue siendo `display:block` en `.ta-hero-title-accent`, así que queda como línea propia con el interlineado de 100px).

**OJO responsive:** a 80px la frase mide ~900+px, más ancha que el tope de 760px del bloque de texto (`.ta-hero-inner > *`). Con `nowrap` no envuelve, pero en pantallas angostas puede desbordar lateralmente sobre la foto — verificar en navegador, y si desborda mal en mobile, hay que bajar el tamaño o poner `nowrap` solo en desktop.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.37 → 1.18.38.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: primera frase en una línea (y desborde en mobile).

### 4.75 Título de Categorías en regular (versión 1.18.39)

Pedido del usuario: "Cuatro maneras de trabajar el acrílico." debe verse **regular**.

- `.ta-features-title`: los `.ta-features-title` son `<h2>` y por defecto el navegador los muestra bold; se agrega `font-weight: 400` (regular) con su 32px ya fijado en §4.69.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.38 → 1.18.39.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: título de categorías en regular. Nota: los títulos de Destacados (`.ta-showcase-title`) y Nuestra Historia (`.ta-story-title`, ya 400) también son `<h2>`; el de la historia ya quedó regular, el de destacados sigue bold por defecto hasta que el usuario lo pida.

### 4.76 Título de Destacados en regular (versión 1.18.40)

Pedido del usuario: "Piezas más solicitadas." debe verse **regular**.

- `.ta-showcase-title`: se agrega `font-weight: 400` (era bold por defecto al ser `<h2>`). Con esto los tres títulos de bloque ya son regular (Categorías, Destacados, Nuestra Historia).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.39 → 1.18.40.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: título de destacados en regular.

### 4.77 CTA "¿Tienes una idea en mente?" 48px light (versión 1.18.41)

Pedido del usuario: el título del bloque CTA debe ser **48px light**.

- `.ta-cta-title`: era `clamp(1.6–2.1rem)` y bold por defecto (es `<h2>`) → **48px** y **`font-weight: 300`** (light). Coincide con la guía de `CLAUDE.md` ("Bloque Tienes una idea en mente: Título 48 Manrope Light").

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.40 → 1.18.41.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: título del CTA en 48px light.

### 4.78 CTA: párrafo a 18px (versión 1.18.42)

Pedido del usuario: "Estamos listos para materializar tu proyecto en acrílico. Desde piezas unitarias hasta grandes producciones." debe ser **18px** (párrafo del bloque CTA, `.ta-cta-description`, era 1rem). Coincide con la guía de `CLAUDE.md` ("Párrafo Manrope Regular 18px").

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.41 → 1.18.42.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: párrafo del CTA en 18px.

### 4.79 CTA: título en una sola línea (versión 1.18.43)

Pedido del usuario: "¿Tienes una idea en mente?" debe quedar en **una sola línea**.

- `.ta-cta-title` (48px light desde §4.77): se agrega `white-space: nowrap`.
- A 48px la frase mide ~560px, dentro del tope de 640px de `.ta-cta-inner` en desktop — ojo solo en mobile angosto (desbordaría; verificar en navegador).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.42 → 1.18.43.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: título del CTA en una línea (y desborde en mobile).

### 4.80 Nuestra Historia: párrafos regular + fix del eyebrow (versión 1.18.44)

Pedido del usuario: los dos párrafos de "Nuestra Historia" deben ser **regular** — ya lo eran (`font-weight: 400`, 18px, desde §4.71); sin cambios de tamaño/peso.

**Fix detectado durante la verificación:** el eyebrow "NUESTRA HISTORIA" es un `<p class="ta-story-eyebrow">` **dentro de** `.ta-story-content`. La regla `.ta-story-content p` (especificidad 0,1,1, definida después) le pisaba el estilo: en la práctica "NUESTRA HISTORIA" se renderizaba a 18px regular en vez de 12px extrabold (§4.71). Se sube el selector a `.ta-story-content .ta-story-eyebrow` (0,2,0) para que gane y el eyebrow quede en 12/800.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.43 → 1.18.44.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: párrafos en regular y eyebrow "NUESTRA HISTORIA" por fin en 12px extrabold.

### 4.81 "Ver todo el catálogo" 12px extrabold verde (versión 1.18.45)

Pedido del usuario: el link "Ver todo el catálogo" del encabezado de Destacados debe ser **12px como los botones, extrabold y en verde** (adorar el color, no el tinta).

- Es un botón text (`ta_button()`, variant `text`, class `ta-showcase-cta`) → ya hereda 12px/800 de `.ta-button` (§4.68), pero venía en `var(--ta-ink)`.
- Se agrega `.ta-button--text.ta-showcase-cta { font-size: 12px; font-weight: 800; color: var(--ta-accent); }` — selector compuesto para ganarle la especificidad a `.ta-button--text` (color ink). El hover sigue usando el verde (`.ta-button--text:hover` → accent).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.44 → 1.18.45.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: "Ver todo el catálogo" en 12px extrabold verde.

### 4.82 Cuidados: "Cómo cuidar tus piezas" 48px regular (versión 1.18.46)

Pedido del usuario: en la página de Cuidados el título de la guía "Cómo cuidar tus piezas" debe ser **48px y regular**.

- El título usa la misma `.ta-features-title` que el home (32px, §4.69) — para no pisar el home se agrega un modifier solo en Cuidados:
  - `page-cuidados-del-acrilico.php`: el 5º parámetro de `ta_render_feature_grid()` pasa de `'ta-features--white'` a `'ta-features--white ta-features--hero-title'`.
  - `style.css`: `.ta-features--hero-title .ta-features-title { font-size: 48px; }` (el weight regular 400 ya lo da `.ta-features-title`).
- Verificado por curl: Cuidados sirve `class="ta-features ta-features--white ta-features--hero-title"`; el home sigue en `class="ta-features"` (32px intacto).

Cambios en `style.css` §1.1 + `page-cuidados-del-acrilico.php`. `TA_VERSION` 1.18.45 → 1.18.46.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK en ambos PHP; HTML de Cuidados con el modifier nuevo. Pendiente recheck visual: título de la guía en Cuidados a 48px regular.

### 4.83 Cuidados: descripción de la guía 18px regular (versión 1.18.47)

Pedido del usuario: el texto "El acrílico es un material noble y duradero…" (descripción de la grilla de Cuidados) debe ser **regular 18px**.

- `.ta-features-description` (párrafo del cuarto parámetro `$description` de `ta_render_feature_grid`): de `0.98rem` y peso heredado → **18px/400**. Solo aparece en Cuidados (la home no le pasa descripción).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.46 → 1.18.47.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: descripción de la guía en Cuidados a 18px regular.

### 4.84 Títulos de las cards medium (versión 1.18.48)

Pedido del usuario: las cards de Cuidados ("Evitar el calor / No uses estos productos / Limpieza suave / ¿Rayones?") deben ser **medium 24px**.

- `.ta-feature-card h3` ya estaba en 24px (§4.69) pero sin peso (rendía bold por ser `<h3>`); se agrega `font-weight: 500` (medium).
- **Nota:** el selector es compartido, así que también quedan medium las cards de Categorías en home ("Organizar / Exhibir / Proteger / Informar") y las de la página Nuestras Soluciones — lo cual coincide con la guía de `CLAUDE.md` ("Título Card 24px Medium").

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.47 → 1.18.48.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: títulos de cards en 24px medium (Cuidados y home).

### 4.85 Cuidados: textos de las cards a 16px regular (versión 1.18.49)

Pedido del usuario: los textos debajo de los títulos de las cards de Cuidados ("No expongas el acrílico a fuentes de calor…" etc.) deben ser **16px regular**.

- La home mantiene los 15px de §4.69; se agrega `.ta-features--hero-title .ta-feature-card p { font-size: 16px; font-weight: 400; }` (scoped al modifier de Cuidados).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.48 → 1.18.49.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: textos de las cards de Cuidados a 16px regular (home intacta en 15px).

### 4.86 Consejo final del experto: eyebrow semibold 12px (versión 1.18.50)

Pedido del usuario: el "Consejo final del experto" (eyebrow del panel de Cuidados) debe ser **semibold 12px**.

- `ta_render_story_section` con `'panel' => true` imprime `<p class="ta-story-eyebrow">` dentro de `.ta-story-content`, igual que en la home.
- Home ("NUESTRA HISTORIA") queda en 12px/**800** con `.ta-story-content .ta-story-eyebrow` (regla base).
- Cuidados ("Consejo final del experto") pasa a **600 semibold** con `.ta-story--panel .ta-story-eyebrow` (mismo 12px, misma especificidad 0,2,0, definida después para ganarle).

**Estado final tras la corrección:** la regla base mantiene el 800 (home) y el panel recibe el 600. §1.1 IDENTICO; `php -l` OK.

**Pendiente recheck visual:** eyebrow "CONSEJO FINAL DEL EXPERTO" en semibold 12px y "NUESTRA HISTORIA" intacto.

### 4.87 Consejo final del experto: título 48px regular (confirmado, sin cambios)

Pedido del usuario: "El secreto reside en la limpieza." debe ser **48px regular** — ya lo era: `.ta-story-title` está en 48px/400 desde §4.71 (mismo estilo que el título de "Nuestra Historia"). No hubo cambios de código ni de versión (1.18.50).

**Pendiente recheck visual:** título del panel en 48px regular a dos líneas (tope `max-width: 22ch`).

### 4.88 Todos los botones extrabold (versión 1.18.51)

Pedido del usuario: **todos los botones** extrabold.

- Los botones del tema ya eran 800 (`.ta-button`, y sus variantes `--accent/--dark/--outline/--text`, desde §4.68), incluyendo "Ver todo el catálogo" (§4.81) y el submit de Contacto (`ta-contact-submit`, usa `.ta-button`).
- Falta único: las píldoras de filtro del catálogo (`.ta-filter-tabs__tab`) estaban en 600 → **800**.
- Se dejan como estaban: el caret del navbar (es una flecha, sin texto) y la paginación del plugin (`.jg-pagination`, no es botón del tema).

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.50 → 1.18.51.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: píldoras de filtro del catálogo en extrabold.

### 4.89 Íconos de las cards a 40×40 (versión 1.18.52)

Pedido del usuario: los íconos de las cards deben ser más grandes, **40×40px**.

- Los SVG salen de `ta_icon()` con `width/height="28"` (atributos). CSS gana sobre los atributos de presentación: se agrega `.ta-feature-icon svg { width: 40px; height: 40px; }`. Aplica a las cards de Categorías (home) y de la guía de Cuidados; no toca los íconos del footer (`.ta-footer-icon`) ni los de contacto.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.51 → 1.18.52.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: íconos de las cards a 40×40.

### 4.90 Catálogo: eyebrow "Nuestro Catálogo" 12px semibold (versión 1.18.53)

Pedido del usuario: en el catálogo el texto "Nuestro Catálogo" debe ser **12px semibold**.

- El eyebrow usa `.ta-features-eyebrow` (compartido, 12px/800 en home y Cuidados). Para no tocar los otros, se agrega un modifier solo al catálogo:
  - `page-nuestras-soluciones.php`: `<section class="ta-features ta-features--catalog">`.
  - `style.css`: `.ta-features--catalog .ta-features-eyebrow { font-weight: 600; }` (mantiene los 12px).
- Verificado por curl: el catálogo sirve `class="ta-features ta-features--catalog"`.

Cambios en `style.css` §1.1 + `page-nuestras-soluciones.php`. `TA_VERSION` 1.18.52 → 1.18.53.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK en ambos PHP. Pendiente recheck visual: "Nuestro Catálogo" en semibold (home y Cuidados intactos en 800).

### 4.91 Catálogo: título 48px light y sin punto (versión 1.18.54)

Pedido del usuario: "Encuentra la Pieza que Necesitas" debe ser **48px light** y se debe **quitar el punto** final.

- `page-nuestras-soluciones.php`: se eliminó el "." del texto ("Encuentra la Pieza que Necesitas." → "Encuentra la Pieza que Necesitas").
- `style.css`: `.ta-features--catalog .ta-features-title { font-size: 48px; font-weight: 300; }` (scoped al catálogo; home queda 32px regular, Cuidados 48px regular).

Cambios en `style.css` §1.1 + `page-nuestras-soluciones.php`. `TA_VERSION` 1.18.53 → 1.18.54.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK en ambos PHP; HTML del catálogo sin el punto. Pendiente recheck visual: título del catálogo en 48px light.

### 4.92 Tabs del catálogo a 12px regular (versión 1.18.55)

Pedido del usuario: los tabs del filtro del catálogo (Todos, Organizador, Proteger, Exhibir, Informar, Trabajos…) deben ser **12px regular**.

- `.ta-filter-tabs__tab`: `font-size: 0.9rem` → **12px**; `font-weight: 800` (del "todos los botones extrabold" de §4.88) → **400** para estos tabs.

Cambios solo en `style.css` §1.1. `TA_VERSION` 1.18.54 → 1.18.55.

**Verificación:** §1.1 actualizado e idéntico; `php -l` OK. Pendiente recheck visual: tabs del catálogo en 12px regular.

### 4.93 Imágenes de la home locales (versión 1.18.56)

Pedido del usuario: las imágenes del inicio deben quedar **dentro del proyecto** (self-hosted) y **no ser enlaces externos**.

- Se descargaron las 4 imágenes de `i.postimg.cc` + la del hero a `wp-content/themes/todo-acrilico/assets/img/home/` (webp válidos: hero 1280×721, buzón 800×800, organizador 800×800, bandeja 342×342, equipo 520×650).
- `front-page.php`: las 3 `image_url` del bloque Destacados y la `image_url` de Nuestra Historia ahora usan `get_template_directory_uri() . '/assets/img/home/…'`.
- `functions.php` `ta_get_hero_image_url()`: el default y el valor guardado legado `https://i.postimg.cc/TYX3CTrK/home.webp` se ignoran → se devuelve la local `/assets/img/home/home.webp`; si el usuario sube otra imagen por Personalizar, esa se sigue respetando.
- Quedan no-locales solo lo esperado (no imágenes del tema): emojis de WP core, `api.w.org` (REST) y los enlaces sociales del footer (Instagram/LinkedIn), que son links, no imágenes.

Cambios en `functions.php` + `front-page.php` + nueva carpeta `assets/img/home/` (5 webp). `TA_VERSION` 1.18.55 → 1.18.56.

**Verificación:** `php -l` OK en ambos; la home sirve las 5 imágenes locales (200) y no quedan más `http` de `i.postimg.cc`. Pendiente recheck visual: fotos del hero, destacados e historia cargando desde el tema.

### 4.94 Imagen de Cuidados local (versión 1.18.57)

Pedido del usuario: las imágenes de Cuidados también deben estar **dentro del proyecto**.

- Se descargó `https://i.postimg.cc/Kj93gzYt/cuidados.webp` (1280×853) a `assets/img/cuidados/cuidados.webp`.
- `page-cuidados-del-acrilico.php`: `'image_url'` ahora usa `get_template_directory_uri() . '/assets/img/cuidados/cuidados.webp'`.
- Verificado: solo quedan externos los emojis/core de WP, `api.w.org` y los enlaces sociales del footer (link, no imagen). Con esto **no queda ninguna imagen del tema servida desde hosting externo**.

Cambios en `page-cuidados-del-acrilico.php` + nueva carpeta `assets/img/cuidados/`. `TA_VERSION` 1.18.56 → 1.18.57.

**Verificación:** `php -l` OK; Cuidados sirve la imagen local (200); sin `i.postimg.cc` en la página. Pendiente recheck visual: foto del consejo final cargando desde el tema.

---

### 4.95 Pestañas del catálogo dinámicas (versión 1.18.58)

Pedido del usuario: una categoría creada en `/galeria-panel/` debe aparecer como pestaña en el catálogo ("Nuestras Soluciones"), y las imágenes subidas por el panel deben verse en el catálogo.

- **Antes:** `page-nuestras-soluciones.php` tenía una lista fija `$tab_labels` (exhibir, informar, organizador, proteger, trabajos-especiales); las categorías nuevas del panel no aparecían como pestañas y sus imágenes solo eran visibles en "Todos".
- **Ahora:** las pestañas se construyen con `get_terms('jg_gallery_category', hide_empty=false, orderby=name, order=ASC)` — "Todos" + una pestaña por término (label = nombre, value = slug). Al crear categoría desde el panel se recarga la taxonomía y aparece la pestaña al instante; al subir una imagen con esa categoría aparece bajo su pestaña (el query ya filtraba por slug).
- El panel ya refrescaba sus propias pestañas (`loadCategories` → `renderCategoryTabs`), no requirió cambios.
- Verificación e2e por REST (token firmado): crear categoría → subir imagen con esa categoría → la pestaña aparece en el catálogo y la pieza se ve en `?categoria=<slug>`; al borrar post y categoría, el archivo local se elimina y las pestañas vuelven a la lista previa. Pendiente recheck visual en navegador.

Cambio en `page-nuestras-soluciones.php` (se eliminó `$tab_labels`/`$terms_by_name`). `TA_VERSION` 1.18.57 → 1.18.58. Verificado: `php -l` OK y pestañas/slugs correctos por `curl`.

### 4.96 Campo "Uso" en panel y catálogo (versión 1.18.59)

Pedido del usuario: agregar un input "Uso" en el formulario del panel (`/galeria-panel/`) entre Descripción y Medidas.

- **Panel (plugin v1.4.2):** el formulario de subida y el de edición ahora tienen el campo "Uso" (`name="usage"`, máx 120 caracteres). El backend guarda `_jg_usage` (subida y edición), lo devuelve en `format_image` y valida longitud con `get_usage()`; si viene vacío se elimina la meta. Las tarjetas del panel muestran "Uso:" entre descripción y medidas.
- **Catálogo:** la tarjeta pública muestra `<p class="ta-solution-usage"><strong>Uso:</strong> …</p>` entre la descripción y las medidas.
- **Cotización:** la URL lleva `uso=…` y `page-contacto.php` lo captura (`search_str` `$ta_quote_uso`), lo muestra en el bloque de pieza a cotizar y lo envía como hidden `adjunto_uso`; `functions.php` lo incluye en el correo (línea "Uso:" antes de Medidas).
- Verificado: `php -l` en todos los archivos tocados, `node --check` del JS, y e2e por REST (token): subida con `usage` → respuesta REST con `usage`; edición con `X-HTTP-Method-Override: PUT` actualiza `_jg_usage`; catálogo página 3 muestra la card "Uso: Exhibición en ferias" entre descripción y medidas; el enlace "Cotizar" lleva `uso=` y `page-contacto.php` lo renderiza y lo pasa como `adjunto_uso` al correo.

`TA_VERSION` 1.18.58 → 1.18.59 y plugin 1.4.1 → 1.4.2. Cambios en `functions.php`, `page-nuestras-soluciones.php`, `page-contacto.php` (tema) y `jwt-gallery.php`, `class-jg-rest.php`, `class-jg-shortcode.php`, `jwt-gallery.js` (plugin).

### 4.97 Imágenes del catálogo como las del inicio (versión 1.18.60)

Pedido del usuario: las imágenes del catálogo ("Nuestras Soluciones") deben verse igual que las del inicio en "Piezas más solicitadas" (`.ta-showcase-card`).

- **Antes:** `.ta-solution-card` era una tarjeta con borde `1px var(--ta-border)` y fondo blanco; la figura usaba fondo `#eee`; el cuerpo iba sobre `var(--ta-bg)` con padding lateral; grilla `auto-fill minmax(240px,1fr)` gap 20px; título 16px y párrafos 14px.
- **Ahora (misma línea que `ta-showcase`):** tarjetas sin borde ni fondo (`background: transparent; border: none`); figura 4/3 con `background: var(--ta-bg)` y `margin-bottom: 18px`; `img` con `display: block`; cuerpo sin fondo ni padding; título `1.25rem` (20px); párrafos `0.9375rem` (15px); grilla `auto-fit minmax(260px,1fr)` gap 28px.
- Se conservan los elementos funcionales que el home no tiene (no se tocan): badge de categoría, mini-carrusel con contra flechas, contador, líneas "Uso:"/"Medidas:" y el botón "Cotizar esta pieza".
- Verificado: `curl` de la página muestra `style.css?ver=1.18.60`; count de `.ta-solution-card` correcto (9 en página 1). Pendiente recheck visual en navegador.

`TA_VERSION` 1.18.59 → 1.18.60. Cambio solo en `style.css` (bloques `.ta-solutions-grid`, `.ta-solution-card`, `.ta-solution-figure`, `.ta-solution-body`).

### 4.98 Tipografía de catálogo y panel alineada a "Cuatro maneras de trabajar" (versión 1.18.61)

Pedido del usuario: en el catálogo, el título, la descripción, el uso y las medidas — y sus respectivos inputs del panel — deben usar la misma tipografía que el componente del inicio "Cuatro maneras de trabajar el acrílico" (`.ta-feature-card`). No considerar el botón "ver pieza" / "Cotizar".

- **Catálogo (tema):** `.ta-solution-body h3` pasa de 20px/400 a `24px / 500 / margin 0 0 10px` (idéntico a `.ta-feature-card h3`); `.ta-solution-body p` (descripción, Uso:, Medidas:) queda a 15px/`line-height:1.5`/muted (idéntico a `.ta-feature-card p`). El botón "Cotizar esta pieza" no se tocó.
- **Panel (plugin v1.4.7):** `body.jg-blank-page` y el overlay de modales abandonan `system-ui` y usan `"Manrope", system-ui, ...` (la fuente Manrope ya llega al panel porque `ta_assets()` encola `style.css` del tema con los 7 `@font-face` de Manrope en todas las páginas front). Además se añadió un bloque final en `jwt-gallery.css` que aplica Manrope explícitamente a **cada sección** (topbar, sidebar, content, forms, category manager, edit-form, grid, cards, pagination, modal, login) y alinea la escala: títulos de sección a 24px/500 (Título Card del theme), hints y labels a 15px, título de tarjetas a 16px; los inputs heredan con `font: inherit`.
- Verificado: `curl` del panel muestra `themes/todo-acrilico/style.css?ver=<TA_VERSION>` cargado (fuente disponible) y `jwt-gallery.css?ver=1.4.7` con 3 reglas `font-family: "Manrope"` (body, overlay y bloque por sección). Pendiente recheck visual en navegador (el CSS puede quedar cacheado).

`TA_VERSION` 1.18.60 → 1.18.61; plugin 1.4.5 → 1.4.7. Cambios en `style.css` (tema), `jwt-gallery.css` (plugin) y versiones en `functions.php` / `jwt-gallery.php`.

---

### 4.99 Compatibilidad con PHP 7.2.24 (servidor con WordPress 6.9.9, 2026-09-29)

Contexto: el servidor de producción corre **WordPress 6.9.9** y no se puede actualizar. La API oficial de WordPress indica que 6.9.9 exige **PHP 7.2.24** como mínimo (7.0 y 7.1 exigen 7.4), así que el hosting solo necesita 7.2.24+ y el sitio no debe cargar WP 7.x. El "error crítico" que showed la portada **no venía de WordPress 6.9.9**: el tema usaba una *typed property* (`private ?int`), sintaxis exclusiva de PHP 7.4, que en 7.3 es un **error de parseo** y tumba `functions.php` (y con él todo el sitio) en cada request.

- **Fatal corregido:** `functions.php` línea 798, dentro de `class TA_Nav_Walker extends Walker_Nav_Menu`: `private ?int $solutions_page_id = null;` → `private $solutions_page_id = null;`. Reproducido con el binario real `php7.3 -l`: la versión con `?int` da `syntax error, unexpected '?'`; la corregida da `No syntax errors detected`.
- **Cabecera bajada:** `style.css` pasó de `Requires PHP: 7.4` a `Requires PHP: 7.2.24`, alineada con el mínimo real de WP 6.9.9. Se verificó con `wp_get_theme( 'todo-acrilico' )` de WordPress: `RequiresWP => 6.0`, `RequiresPHP => 7.2.24`, `Errores => ninguno` (estructura del tema válida) y `get_plugin_data()` del plugin devuelve los mismos valores.
- **Auditoría de compatibilidad 7.2.24** (16 archivos, tema + plugin): sin sintaxis de 7.3+ / 7.4+ / 8.x — nada de `fn()`, `??=`, `?->`, `match`, *typed properties*, *spread* en array, separadores numéricos, uniones de tipos, `mixed`/`never`/`static`, *constructor promotion*, comas finales en llamadas, heredocs flexibles. Tampoco se usan funciones introducidas en 7.1→8.3 (`array_key_first`, `is_countable`, `str_contains`, `get_debug_type`, etc.). Los hooks del tema usan API disponible desde WP 5.x/6.0, así que **no hay incompatibilidad con 6.9.9**.
- **`mime_content_type()`** (`functions.php` ~507, validación de PDF en el formulario de contacto) ya estaba protegido con `function_exists()`, de modo que un hosting sin `fileinfo` no lo rompe.
- Verificación final: `php7.3 -l` pasa en los 16 `.php`. Se generaron de nuevo `TodoAcrilico.zip` (530K) y `TodoAcrilico-Panel.zip` (28K) con el fix dentro.

Nota operativa: al reinstalar un tema, WordPress **sobreescribe los archivos homónimos pero no borra los que ya no existen en el ZIP nuevo**, y puede quedar más de una carpeta del tema. Si el error persiste tras subir el ZIP, hay que revisar por FTP que en `wp-content/themes/` exista una sola copia y que en la línea 798 de `functions.php` **no** aparezca `?int`.

`TA_VERSION` sin cambio (1.18.61): el fallo era un parseo, no un cambio de comportamiento del tema.

---

### 4.100 El nav siempre muestra Inicio, Catálogo, Cuidados y Cotizar (versión 1.18.62)

Síntoma reportado en producción después de migrar: el tema cargaba pero **el nav aparecía vacío** y, a la vez, los botones perdían sus estilos y los textos del hero se encimaban.

- **Nav vacío:** no era un bug de código sino de datos. `header.php` pedía el menú con `'fallback_cb' => false`, así que si no hay ningún menú asignado a la ubicación `primary` (registrada en `functions.php` §1.2) **WordPress no imprime absolutamente nada**. Tras una migración es lo habitual: el menú se crea, pero la asignación vive en el theme mod `nav_menu_locations`, que no siempre se copia. Se verificó en local: con la ubicación desconectada, `wp_nav_menu()` no emitía nada.
- **Solución:** nueva función `ta_nav_menu_fallback( $args )` en `functions.php` (junto a `ta_find_page()`, §4.101) y `header.php` pasa a `'fallback_cb' => 'ta_nav_menu_fallback'`. El fallback arma una lista **explícita y cerrada** — Inicio, Catálogo, Cuidados y Cotizar — resueltas desde 1.18.63 con `ta_find_page()` (slug canónico o título, nunca dependiente del slug de apariencia) + `home_url( '/' )` como último recurso; desde esa versión una página que no se encuentre **se lista igual apuntando a la portada**, para que el nav nunca quede con menos de 4 ítems. Replica las clases del menú real (`menu-item`, `current-menu-item`/`current_page_item` en la página actual, `ta-nav-cta` en Cotizar) y el desplegable de "Catálogo" (`.ta-nav-has-caret`, `.ta-nav-caret-toggle`, `.ta-nav-dropdown` con "Todas las piezas" + `ta_get_solutions_by_category()`), de modo que `assets/js/ta-nav.js` funciona igual. **Nunca** se usa `wp_page_menu()`, que expondría cualquier página publicada.
- **Verificación e2e (local):** (a) con menú asignado sigue mandando el menú real (`<ul id="menu-menu-principal">` con sus `menu-item-*`); (b) con `remove_theme_mod( 'nav_menu_locations' )` el header imprime el fallback con los 4 destinos y el desplegable completo; (c) se restauró la asignación original (`primary => 8`) y las 4 páginas responden HTTP 200.
- **Estilos rotos:** causa independiente y **no** de código — todo el CSS del tema está en un solo archivo, `style.css` (44.201 bytes, 2.091 líneas, 7 `@font-face` de Manrope). Botones sin estilo + textos encimados = ese archivo no se sirve (404 o subida truncada). Se documentó el manifiesto del tema: **26 archivos, 987.412 bytes** (`style.css` 44.201 · `functions.php` 39.758 · `page-contacto.php` 12.722 · `page-nuestras-soluciones.php` 8.405 · `front-page.php` 5.080 · `footer.php` 3.322 · `page-cuidados-del-acrilico.php` 2.585 · `header.php` 1.011 · `index.php` 498 · `page.php` 275 · 7 `.webp` en `assets/img/` · 2 `.js` en `assets/js/` · 7 `Manrope-*.ttf` en `fonts/manrope/`). Comprobación en el servidor: `curl -s https://<dominio>/wp-content/themes/todo-acrilico/style.css | wc -c` debe dar 44.201.

`TA_VERSION` 1.18.61 → 1.18.62. Cambios en `functions.php` (nueva función) y `header.php` (`fallback_cb`).

---

### 4.101 El nav y los enlaces ya no dependen de los slugs (versión 1.18.63)

Síntoma en producción tras una migración Softaculous: las páginas conservaron el título pero **cambiaron de slug** — `nuestras-soluciones` → `services`, `cuidados-del-acrilico` → `cuidados`, `contacto` → `contact` — y faltaban las páginas `galeria`/`galeria-panel`. Con el código 1.18.62 el fallback solo encontraba páginas por slug canónico, así que en esa instalación quedó **un nav con solo "Inicio"**: los botones de la portada ("Ver catálogo", "Cotizar un proyecto") apuntaban todos a `home_url('/')` y los enlaces del footer y de Contacto caían a la portada. El CSS se servía perfecto (44.201 bytes), o sea no era un problema de estilos sino de resolución de páginas.

- **Nuevo helper central `ta_find_page( $needles )`** (`functions.php`, junto a `ta_nav_menu_fallback`):
  1. Coincidencia exacta por **slug canónico** (`get_page_by_path( $slug )`).
  2. Si no, barre las páginas **publicadas** y devuelve la primera cuyo título o slug contengan (sin mayúsculas ni acentos, vía el nuevo `ta_normalize_text()`) alguna de las palabras clave dadas.
- **Palabras clave por destino:** Catálogo — `nuestras-soluciones`, `soluciones`, `solucione`, `pieza`; Cuidados — `cuidados-del-acrilico`, `cuidados`, `cuidado`; Contacto — `contacto`, `contact`; legales — `terminos-y-condiciones`/`termino` y `politica-de-privacidad`/`privacidad`.
- **Aplicado a:** `ta_nav_menu_fallback()` (menú de reserva), `TA_Nav_Walker::get_solutions_page_id()` (desplegable de Catálogo cuando hay menú real), `ta_assets()` (solo enlaza el CSS del plugin en el catálogo, con `is_page()` resuelto), `front-page.php` (botones del hero), `footer.php` (5 enlaces) y `page-contacto.php` (formulario + legales).
- **Sin página encontrada** (título y slug muy distintos, o página borrada): el ítem del fallback se muestra igual apuntando a la portada — el nav **siempre** tiene 4 ítems.
- **Verificación e2e (local, `php8.3` sobre wp-load):** renombrados los slugs de las páginas 14/15/16 a `services`/`cuidados`/`contact` (los de producción) → `ta_find_page()` resuelve catálogo/cuidados/contacto y el nav imprime los 4 ítems con el desplegable `?categoria=`; con esas páginas pasadas a `draft` el nav sigue imprimiendo 4 ítems hacia la portada; se restauraron slugs y estado. `php7.3 -l` limpio en los 7 archivos tocados.

`TA_VERSION` 1.18.62 → **1.18.63**. Cambios en `functions.php` (`ta_normalize_text`, `ta_find_page`, fallback, walker, `ta_assets`), `front-page.php`, `footer.php`, `page-contacto.php`, `page-cuidados-del-acrilico.php` y `page-nuestras-soluciones.php`.

---

### 4.102 Las plantillas dedicadas se asignan sin depender del slug (versión 1.18.64)

Síntoma en producción tras la migración Softaculous: `/contact/`, `/services/` y `/cuidados/` cargaban "con el tema" pero **no se veían como en local** — se renderizaban con la plantilla genérica `page.php` (solo el `<h1>` del título y el contenido del editor, vacío), sin formulario de contacto, sin pestañas de catálogo y sin la guía de cuidados.

- **Por qué:** WordPress elige la plantilla de una página con la jerarquía `page-{slug}.php` **antes** que `page.php`. Como las páginas migradas quedaron con slug `contact` / `services` / `cuidados`, no existía `page-contact.php` ni `page-services.php`, y caían directo a `page.php`. El slug canónico (`contacto`, `nuestras-soluciones`, `cuidados-del-acrilico`) sí habría funcionado, pero depender de él es justo lo que se quiso eliminar.
- **Solución:** filtro `ta_page_template_by_role()` en `template_include` (`functions.php`, prioridad 20), registrado con `add_filter()`. Cuando WordPress resolvió `page.php` y la página actual es una de las tres, devuelve la plantilla dedicada correspondiente, identificándola con el **mismo** `ta_find_page()` del nav (§4.101) — no por su slug. Mapa: `soluciones` → `page-nuestras-soluciones.php` · `cuidados` → `page-cuidados-del-acrilico.php` · `contacto` → `page-contacto.php`.
- **Salvaguardas:** solo actúa si la plantilla resuelta es `page.php` (si el administrador asignó una plantilla a mano, o si ya resolvió `page-{slug}.php` correctamente, no se toca nada) y nunca en la portada (`is_front_page()`). Se salta cualquier otra plantilla (p. ej. las `blank-page.php` del plugin para `/galeria/` y `/galeria-panel/`, que no pasan por `page.php`).
- **Verificación e2e (local, slugs de producción simulados):** renombradas las páginas 14/15/16 a `services`/`cuidados`/`contact` y vaciado el título de Contacto (así está en producción) → `/index.php/services/` renderiza `ta-solutions` (pestañas del catálogo), `/index.php/cuidados/` renderiza `ta-features` (guía), `/index.php/contact/` renderiza `ta-contact` con el formulario (43 ocurrencias del marcador). Se restauraron los slugs y el título originales.

`TA_VERSION` 1.18.63 → **1.18.64**. Cambio único en `functions.php` (nueva función + filtro).

---

### 4.103 El filtro también reemplaza las plantillas en blanco de Elementor (versión 1.18.65)

En producción `/cuidados/` seguía sin mostrar la guía del tema aunque 1.18.64 ya estuviera activa. Causa: la página ID 2 tenía asignada la plantilla **"Elementor Header Footer"** (`body class="page-template-elementor_header_footer"`), así que WordPress no resolvía `page.php` sino la blank de Elementor, y el filtro 1.18.64 — que solo intervenía cuando la plantilla resuelta era `page.php` — la respetaba. En pantalla salía el contenido Elementor viejo de 2023 (con su imagen `uploads/2025/04/cuidado-web-768x1204.png`) en vez de `page-cuidados-del-acrilico.php` (que usa `assets/img/cuidados/cuidados.webp`).

- **Cambio:** `ta_page_template_by_role()` ahora acepta también las plantillas cuyo path está en `/plugins/elementor/` (`canvas.php` y `header_footer.php`), no solo `page.php`. Son el valor por defecto que deja el constructor de Elementor al editar una página, no un diseño elegido para el sitio, así que no justifican saltarse la plantilla del tema.
- **Sigue respetando:** cualquier otra plantilla —las del propio tema, `blank-page.php` del plugin de galería para `/galeria/` y `/galeria-panel/`, o una asignada a mano— y nunca intercepta la portada.
- **Escape hatch:** filtro `ta_override_elementor_templates` (por defecto `true`). Devolver `false` devuelve el comportamiento anterior, que respeta la plantilla de Elementor.
- **Verificación e2e (local, 10 casos):** `page.php` y Elementor `header_footer`/`canvas` resuelven a la plantilla dedicada correcta para Catálogo, Cuidados y Contacto; una página ajena al tema con Elementor y `/galeria/` con `blank-page.php` no se tocan; con el escape hatch en `false` se respeta Elementor y al volver a `true` se recupera el tema; la portada nunca se intercepta.

`TA_VERSION` 1.18.64 → **1.18.65**. Cambio único en `functions.php`.

---

### 4.104 El pie de página se veía completo en el DOM pero invisible (Elementor activo)

Síntoma: en producción el pie existía en el HTML —las cuatro columnas y el correo, markup idéntico al local— pero en el navegador no se veía nada. "No es culpa de Elementor" fue la primera hipótesis y era falsa.

Cómo se descartó una por una, sin acceso al wp-admin:

- **Markup:** los 4 enlaces de "Explorar" y el `mailto:` presentes en las 4 páginas de producción, con las mismas clases que en local.
- **CSS del tema:** `style.css` servía con el mismo md5 que el local, y ninguna regla dentro de `@media` toca el pie.
- **CSS de terceros:** `elementor-frontend-inline-css`, `global-styles-inline-css`, el CSS externo de Elementor y el de Click-to-Chat no tienen ninguna regla de pie; sus `display:none` sobre `li/ul/a` son internos de widgets (tabs, slideshow).
- **Caché:** ninguna. Apache sin headers de caché y sin plugins de caché de página activos.
- **Chrome headless (CDP):** se midieron los estilos computados reales. `getComputedStyle` de los enlaces del pie daba `visibility:visible`, `opacity:1`, sin `transform` ni `clip`, pero **`font-size: 1px`** y un rectángulo de `2x1` — es decir, texto pintado a un píxel: presente, invisible. Esa medición sí era la del navegador real, no una suposición.

Al desactivar Elementor en producción el pie se ve correctamente y las cuatro páginas quedaron verificadas con estilos computados a `15px`. Conclusión práctica: el conflicto viene del CSS de Elementor en producción, no del tema; si Elementor se reactiva hay que volver a revisar el pie.

Sin cambios de código. `TA_VERSION` sigue en **1.18.65**.

Comprobado de paso, ya con Elementor desactivado: `/`, `/contact/` y `/cuidados/` renderizan sus plantillas del tema (la guía de cuidados se sigue viendo gracias al filtro 4.103, porque WordPress cae en `page.php` al no existir la plantilla del plugin). `/services/` renderiza la plantilla del catálogo pero **vacía**, porque el plugin `jwt-gallery` sigue inactivo y no hay `galeria`/`galeria-panel`.

---

### 4.105 El catálogo vacío no era del tema: el plugin estaba inactivo

Lo que se veía como un fallo de la plantilla del catálogo en `/services/` era el plugin. Confirmado por HTTP público:

- `/wp-json/` listaba 130 rutas del sitio y **ninguna** `jwt-gallery/v1`.
- `/galeria/` respondía 200 pero **con el texto `[jwt_gallery]` en crudo**: WordPress no procesa el shortcode cuando el plugin está inactivo.
- El login respondía 404 `rest_no_route`, un código que el plugin nunca devuelve (devuelve 400, 401, 403 y 429).

Reinstalación limpia del plugin y, a continuación, `/galeria/` y `/galeria-panel/` volvieron a renderizar su markup con `data-jg-root` y el namespace reapareció en el índice REST. Detalle completo en `Docs_Plugin.md` §12.44.

**Para el tema, la lección es una regla de diagnóstico:** cuando una página que depende de un shortcode se ve vacía o con el shortcode en crudo, primero hay que confirmar que el plugin está activo, y solo después culpar a la plantilla. Comprobarlo es una petición a `/wp-json/` y un `grep` del HTML.

### 4.106 Cuentas del sitio en producción (no es un cambio de tema, pero afecta al catálogo)

La instalación de producción tiene cuentas del instalador del hosting: `pablo@todoacrilico.cl` (usuario público "Pablo", ID 1), `prn@todoacrilico.cl` y `contacto@todoacrilico.cl`. Local y producción son bases de datos distintas, así que los usuarios no se comparten.

El catálogo se administer desde la cuenta `admin@todoacrilico.cl` con rol `editor`, creada por la pantalla de credenciales del plugin. La cuenta real del sitio no se toca. Detalle y estado en `Docs_Plugin.md` §12.45.

Sin cambios en el tema. `TA_VERSION` sigue en **1.18.65**.

---

### 4.107 El título del hero se salía de la tarjeta, y el logo tapaba el nav (versión 1.18.66)

Dos defectos de la portada, reportados por el cliente en el bloque del hero ("Taller de acrílico · Chile · Desde 2019") y en el bloque "Nuestra historia".

**1. El texto "Soluciones en acrílico, pensadas para durar." se salía de la tarjeta.**

Causa raíz, tres reglas que se combinaban mal:

```css
.ta-hero h1 {
    font-size: 80px;          /* fijo, sin ninguna escala hacia abajo */
    line-height: 100px;       /* fijo, en px y no como ratio */
    white-space: nowrap;      /* impedía partir el título */
}
```

`nowrap` impedía partir la línea, y a 80px la frase "Soluciones en acrílico," mide más que la columna de texto (`.ta-hero-inner > *` con `max-width: min(100%, 760px)`), así que el texto se salía de la tarjeta. En móvil era peor: 80px fijos en un viewport de 375px desbordan la pantalla entera.

La corrección:

```css
font-size: clamp(30px, 7.2vw, 80px);  /* 80px de diseño, baja solo si hace falta */
line-height: 1.25;                    /* 100/80, como ratio: los 100px fijos dejaban
       renglones separados por 100px de aire en móvil */
white-space: normal;
text-wrap: balance;                    /* reparte los renglones parejo */
overflow-wrap: break-word;
max-width: min(100%, 1000px);         /* en grande cabe en un renglón como el diseño;
       en móvil `min(100%,…)` lo ata a la tarjeta */
```

**Medido con Chrome headless, no a ojo**, en cinco anchos:

| Ancho | Fuente | Interlineado | Ancho h1 / caja | Desborde | Scroll horizontal |
| --- | --- | --- | --- | --- | --- |
| 375px | 30px | 37.5px | 335 / 335 | 0px | no |
| 480px | 34.6px | 43.2px | 440 / 440 | 0px | no |
| 768px | 55.3px | 69.1px | 720 / 720 | 0px | no |
| 1024px | 73.7px | 92.2px | 976 / 976 | 0px | no |
| 1440px | **80px** | **100px** | 1000 / 1392 | −392px | no |

A 1440px se conservan exactamente los 80px y los 100px de interlineado del diseño. El desborde interno a 375px también es cero (`scrollWidth` 335 = `clientWidth` 335), que es la comprobación que distingue "el texto cabe" de "el elemento no se sale".

Y el diseño se mantiene: el título sigue en **2 renglones** (uno por frase) de 1920px hasta 375px. Solo a 320px pasa a 3, que es lo razonable en un ancho así.

**2. El logo de "Nuestra historia" se colaba por encima del nav al hacer scroll.**

El badge (`.ta-story-badge`, el logo que va sobre la foto) tenía `z-index: 10`, y el header también:

```css
.ta-header { position: sticky; top: 0; z-index: 10; }
```

Con dos elementos en el mismo `z-index` **gana el que va después en el DOM**, y el badge está en el contenido, después del header. Por eso el logo se veía por encima del nav durante el scroll.

La corrección es una línea: `z-index: 100` en `.ta-header`. Por encima del badge, y muy por debajo de la barra de administración de WordPress, que usa `99999`.

**Verificado forzando la solapa** (desplazamiento exacto para que el centro del badge caiga bajo el centro del nav) y preguntando con `elementFromPoint` qué elemento está realmente en ese punto:

| Ancho | z nav | z logo | ¿solapan? | Elemento encima |
| --- | --- | --- | --- | --- |
| 1280px | 100 | 10 | sí | `ta-header-inner` (el nav) |
| 375px | 100 | 10 | sí | `A` (enlace del nav) |

Nunca el badge. Con `elementFromPoint` se mide lo que el navegador realmente pinta, no lo que dice el CSS.

**Nota de método.** La primera medición de la solapa dio "no se solapan" y no probaba nada: el script no llegó a superponerlos. Un test que sale "OK" porque la condición que debía disparar nunca se cumplió es un test que no prueba. Hubo que forzar el desplazamiento.

`TA_VERSION` 1.18.65 → **1.18.66**.

---

### 4.108 El título de "¿Tienes una idea en mente?" se salía de la tarjeta (versión 1.18.67)

Mismo tipo de fallo que §4.107, en el bloque CTA del final de la portada. La causa era idéntica:

```css
.ta-cta-title {
    font-size: 48px;
    white-space: nowrap;  /* "¿Tienes una idea en mente?" en una sola línea */
}
```

Pero aquí había un problema **más profundo que el desborde visible**, y la primera corrección lo destapó.

**Lo que había debajo.** La causa de fondo no era el `nowrap`: era que **el contenedor era más angosto que el título**. Midiendo el texto real en canvas con la fuente y el peso exactos del navegador:

- `¿Tienes una idea en mente?` a 48px mide **605px**.
- La caja útil del título era de **592px** (`.ta-cta-inner` con `max-width: 640px`, menos los 24px de padding de `.ta-container` por lado).

O sea que el título no cabía en su propia caja, en escritorio, con el diseño puesto. El `nowrap` no lo almacenaba: lo empujaba 13px fuera de la tarjeta. Era un desborde de 13px, fácil de no ver, contra los cientos de píxeles del caso móvil.

**Por qué la primera corrección no bastaba.** Quitar el `nowrap` y poner `clamp(30px…)` eliminó el desborde, pero dejó el título en **2 renglones en escritorio**, cuando el diseño pide 48px en uno. Y el síntoma fue confuso: a 1440px salían 2 renglones y a 900px salía 1. La fuente bajaba con `clamp`, y al bajar lo suficiente el texto volvía a caber en los 592px. Un título que "se parte más cuanto más ancho es el pantallón" es la señal de que el problema es el ancho del contenedor, no el tamaño de la letra.

**La corrección completa**, tres partes:

```css
.ta-cta-inner { max-width: 700px; }        /* el título a 48px necesita 605px */

.ta-cta-title {
    font-size: clamp(28px, 5.2vw, 48px);     /* 48px de diseño, baja solo si hace falta */
    line-height: 1.2;
    white-space: normal;
    text-wrap: balance;
    overflow-wrap: break-word;
}

.ta-cta-description { max-width: 620px; margin: 0 auto 32px; }
```

El párrafo se le puso su propio `max-width` y se centró con `margin` automático porque al abrir el contenedor a 700px, una línea de 700px a 18px es larga de leer. Así el título gana el ancho que necesita y el párrafo conserva una medida cómoda.

**Medido** (renglones reales contados por rango de `getClientRects()`, no dividiendo alturas):

| Ancho | Fuente | Renglones | Ancho del texto / caja |
| --- | --- | --- | --- |
| 1920px | **48px** | 1 | 605px / 652px |
| 1440px | **48px** | 1 | 605px / 652px |
| 1024px | **48px** | 1 | 605px / 652px |
| 900px | 46.8px | 1 | 589px / 652px |
| 768px | 39.9px | 1 | 503px / 652px |
| 480px | 28px | 1 | 353px / 384px |
| 375px | 28px | 2 | 353px / 279px |

48px en un solo renglón en escritorio, como manda el diseño, y 2 renglones parejos a 375px. En todos los anchos, sin desborde: `scrollWidth` = `clientWidth` del título, párrafo y botón dentro de la tarjeta, y sin scroll horizontal en la página.

#### Auditoría de toda la portada (el mismo fallo, buscado en todo el sitio)

Ya habían salido dos casos del mismo error, así que se pasó el detector por **todos** los elementos de la portada en cinco anchos. Cada elemento se midió contra tres cosas: si se sale de la ventana, si es más ancho que su padre, y si su contenido es más ancho que su propia caja.

Encontró un tercer desbordamiento real, ya presente y nunca reportado:

```css
/* .ta-features-grid, .ta-showcase-grid, .ta-solutions-grid */
grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
```

`minmax(260px, 1fr)` obliga a la columna a medir **260px aunque el contenedor sea más angosto**. A 320px la caja del grid de Destacados era de 224px y la tarjeta medía 260px: 36px fuera. En un viewport de 320px el sitio, entero, se iba 36px a la derecha.

La corrección canónica, aplicada a los tres grids:

```css
grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr));
```

El `min(260px, 100%)` es lo que arregla: la columna nunca puede ser más ancha que la caja que la contiene.

**Lo que la auditoría marcó y NO era un fallo** (para no "arreglar" cosas deliberadas):

| Marcado | Por qué se queda como está |
| --- | --- |
| `li.ta-nav-has-caret` "96px sin partir" (768px y 1440px) | Es el `<li>` de "Catálogo" con su desplegable. El panel es más ancho que el `<li>`, como debe ser. `scrollWidth` cuenta los hijos aunque no se salgan de la pantalla. |
| `.ta-story-media` "10px sin partir" (1440px) | El badge de "Nuestra historia" se sale 10px **a propósito**, y para eso está `overflow: visible !important` en esa regla. Es el logo superpuesto a la foto. |
| `.ta-nav` / `.ta-nav-list` "6px sin partir" (320/375/414px) | 6px constantes en los tres anchos, ningún hijo se sale de la caja del nav y los desplegables están en `display: none`. Sin scroll horizontal en la página. No produce nada visible. |

**Sobre la auditoría.** Es un detector de candidatos, no un verificador: marca todo lo que *podría* salirse, y entre 40 hallazgos por ancho había que separar los reales de los que el diseño quiere así. Por eso cada marcado se comprobó uno por uno contra su causa antes de tocar algo. Un detector que se aplica sin revisar lo que devuelve termina "corrigiendo" el diseño.

`TA_VERSION` 1.18.66 → **1.18.67**.

---

### 4.109 Nav y footer alineados al ancho del contenido del body (versión 1.19.3)

**Pedido del usuario:** el logo del nav debe empezar en el mismo borde izquierdo que el contenido del body y el botón "Cotizar" debe terminar en el mismo borde derecho (p. ej. la línea derecha de las cards de Destacados); "el nav siempre debe tener el mismo ancho que el body". Al redimensionar (de pantalla grande hacia abajo, hasta ~691–861px) el nav quedaba **más ancho** que el body.

**Causa raíz — doble padding en el body.** Las secciones de la portada y de las páginas internas tienen **dos** niveles de padding: el de la propia sección (`.ta-features`, `.ta-showcase`, `.ta-story`, `.ta-hero`, `.ta-cta`, …) **más** el de su `.ta-container` interno:

| Elemento | Padding horizontal base (<861px) | Padding horizontal desktop (≥861px) |
| --- | --- | --- |
| `.ta-container` (contenedor) | `46.8px` | `60px` |
| Sección que lo envuelve (`.ta-features`, `.ta-showcase`, `.ta-hero`, `.ta-story`, `.ta-cta`, …) | `24px` | `60px` |
| **Inset total del contenido del body** | **`70.8px`** | **`120px`** |

El header y el footer, en cambio, solo tenían el padding de `.ta-container` una vez (`46.8px` base / `60px` desktop). Es decir, el contenido del nav arrancaba **70.8/120px antes** que el del body: por eso el nav se veía más ancho, y el desfase se notaba más al achicar la pantalla (cuando dejaba de aplicar el tope de ancho grande).

**La corrección.** Se le dio al header y al footer **la misma caja de contenido que a las secciones del body**: `max-width: var(--ta-shell-width)` (igual que `.ta-container`) y padding horizontal `70.8px` base / `120px` en `@media (min-width: 861px)`. La regla va al **final** de `style.css` y con `!important` porque la media query compartida (`@media (min-width: 861px)`) fija `60px` sobre `.ta-container, .ta-header-inner, … .ta-footer-top, .ta-footer-bottom`; sin `!important` esa regla le ganaría.

```css
/* Nav y footer: misma caja de contenido que las secciones del body
   (padding de sección + padding de .ta-container). */
.ta-header-inner,
.ta-footer-top,
.ta-footer-bottom {
	max-width: var(--ta-shell-width) !important;
	padding-left: 70.8px !important;
	padding-right: 70.8px !important;
}

@media (min-width: 861px) {
	.ta-header-inner,
	.ta-footer-top,
	.ta-footer-bottom {
		padding-left: 120px !important;
		padding-right: 120px !important;
	}
}
```

Recordatorio de estructura: `header.php` usa un **único** `<div class="ta-container ta-header-inner">` (las dos clases en el mismo elemento, no anidadas); `footer.php` usa `<div class="ta-container ta-footer-top">` y `<div class="ta-container ta-footer-bottom">`.

**Invariante a mantener** (esto es lo que hay que preservar en cambios futuros): el borde del contenido del nav/footer debe coincidir con el borde del contenido de las secciones del body, es decir **padding de sección + padding de `.ta-container`**. Si en el futuro se cambia el padding de las secciones (24/60) o el de `.ta-container` (46.8/60), hay que recalcular el total y actualizar `70.8px`/`120px` en la regla de arriba. En anchos normales (≤ `var(--ta-shell-width)`) la alineación es exacta; en pantallas muy anchas el nav puede quedar unos píxeles **más angosto** que el body (nunca más ancho), porque el `max-width` de la caja del nav no compensa el padding extra de la sección.

**Verificación.** El usuario confirmó "se visualiza como quiero". Se publicó como `TodoAcrilico-1.19.3.zip` (`theme-ultimo-estable-eliminar/`, estructura `todo-acrilico/`) y `TA_VERSION`/`style.css` subieron juntos a **1.19.3**.

`TA_VERSION` 1.18.67 → **1.19.3**.

### 4.110 SEO, rendimiento y GEO (2026-10-20, sin cambio de versión en su momento)

- **Metadescripción:** `ta_meta_description()` en `wp_head` con prioridad 1. Usa el extracto del post, o los primeros 160 caracteres del contenido, o la descripción del sitio, o un texto fijo de respaldo. Ojo: usa `substr()` por bytes y puede cortar una letra con tilde a la mitad (pendiente menor: usar `mb_substr()`).
- **`ta_lazy_load_images()`:** agrega `loading="lazy"` en el filtro `wp_get_attachment_image`.
- **`ta_cache_headers()`** (`send_headers`): `Cache-Control` de 1 hora para HTML de visitantes sin sesión.
- **`.htaccess`:** GZIP (`mod_deflate`), `mod_expires` y `Cache-Control: public, max-age=31536000, immutable` para CSS, JS, fuentes e imágenes. **Consecuencia:** todo cambio de CSS o JS exige subir `TA_VERSION` (§4.117).
- **SEO:** `sitemap.xml` y `robots.txt` viven en el tema y `.htaccess` los sirve en la raíz (`RewriteRule ^sitemap\.xml$` y `^robots\.txt$`). Redirecciones 301: `contact/` → `wp/cotizar/` y `wp/catalogos/` → `wp/catalogo/`. Las rutas en `.htaccess` son de local (`/wordpress/proyecto3/`) y hay que ajustarlas en producción.
- **GEO:** `header.php` agrega metas `ai-content-policy`, `ai-training` y `robots-ai`, y `<link rel="ai-policy" href="/.well-known/ai.json">`. El `href` es absoluto desde la raíz del dominio y no funciona en un WordPress instalado en subcarpeta. Ver `GEO.md`, que también está desactualizado: dice que `robots.txt` está en la raíz.
- **Catálogo:** la categoría por defecto `vidrios` del plugin pasó a llamarse `trabajos-especiales` (Docs_Plugin.md §12.48).

### 4.111 Formulario: envío AJAX, el formulario nunca se limpia en error (2026-10-20)

- El `submit` hace `preventDefault()` y envía `FormData` por `fetch` a `data-ajax-url` (`home_url('/wp-admin/admin-post.php')`; con `admin_url()` daba 404 desde `fetch`), con la cabecera `X-Requested-With: XMLHttpRequest`.
- `ta_handle_contact_submit()` detecta esa cabecera (`$is_ajax`). `$fail()`, el honeypot y el éxito responden con `wp_send_json()`. Sin JS, se mantiene el redirect PRG.
- El mapa `$error_messages` está en functions.php y **duplicado** en `$ta_contact_error_messages` de page-contacto.php.
- Errores del servidor: alerta roja `.ta-contact-alert--error` (siempre está en el HTML, oculta). Ya **no existe** la alerta verde de éxito.
- **Límite de envíos:** el transient `ta_contact_rate_{md5(IP)}` (60 s) se crea solo **después** de un `wp_mail()` exitoso. Antes se creaba en cada intento y bloqueaba al usuario indefinidamente.
- **Si `wp_mail()` falla:** `error_log()` con el detalle. El `Reply-To` solo se agrega si `is_email()` es válido.

### 4.112 Formulario: reglas de validación nuevas (2026-10-20)

Todas existen en JS (`validateField()`) **y** en PHP. Ver la tabla completa en SPEC.md.

- **Nombre:** nombre y apellido, con la regex `^\p{L}[\p{L}'’-]*\p{L}(\s+\p{L}[\p{L}'’-]*\p{L})+$` (flag `u`). `TA_CONTACT_NAME_MIN` = 2. El servidor cuenta con `mb_strlen`. Nuevo `reason` `nombre_invalido`. Mensaje: "Ingresa al menos un nombre y un apellido.". Mientras se escribe se borran los dígitos y el texto pasa a MAYÚSCULAS.
- **Teléfono:** 8 dígitos tras `+56 9`, que deben empezar con 3–9 (actualizado desde 4–9, §4.120; `^[3-9]\d{7}$`). En el servidor se corrigió que quitaba un `56` inicial de números válidos: ahora solo quita `9` o `569` si el número trae 9 u 11 dígitos. Mensaje: "Ingrese un teléfono válido de 8 dígitos.".
- **Correo:** la lista `gmail.com`, `outlook.com`, `hotmail.com`, `live.cl`, `yahoo.com`, `icloud.com`, más cualquier dominio `.cl`, `.org` o `.net`. Se convierte a minúsculas al escribir.
- **Descripción:** mínimo 20 caracteres (JS con `trim()`; PHP `TA_CONTACT_DESC_MIN` con `strlen`). El error va en `.ta-contact-textarea-footer`, a la izquierda de "Límite 2000 caracteres", que queda fijo a la derecha con `margin-left: auto`.
- **Adjuntos:** validados en JS al elegirlos (5 MB, extensiones, máximo 5). El error aparece bajo la lista y el archivo no se agrega.

### 4.113 Formulario: errores solo al enviar + `novalidate` (2026-10-20)

- `<form ... novalidate>`. `validateField(field, onSubmit)`: con `onSubmit=true`, un campo `required` vacío muestra su mensaje bajo el campo.
- Nombre, correo y descripción: el evento `input` solo **oculta** el error. Teléfono: el `input` lo limpia y pone el borde verde cuando ya es válido. No hay validación en `blur` para esos 4 campos.
- Términos sin marcar: `termsCheckbox.reportValidity()` (aviso nativo), solo si no hay otro campo con error.
- Se eliminó la llamada a `updateFieldValidation()`, que no existía y lanzaba un `ReferenceError` en el teléfono.

### 4.114 Formulario: borrador en `sessionStorage` (2026-10-20)

Un IIFE en page-contacto.php guarda `nombre`, `celular`, `email`, `descripcion` y los dos checkboxes en `sessionStorage['ta_contact_draft']` en cada `input` o `change`, y los restaura al cargar. Se borra en el éxito AJAX. Los adjuntos no se restauran (limitación del navegador). Está declarado en la Política de Privacidad, sección 8.

### 4.115 Modales del formulario (2026-10-20)

- Componentes reutilizables `.ta-modal`, `.ta-modal-overlay`, `.ta-modal-content` y `.ta-modal-close` (X; con `--right` queda a la derecha), con estilos Manrope (título 32px light por defecto).
- Un IIFE cierra cualquier `.ta-modal` con la X, con `.ta-modal-button` o con un clic en el overlay.
- **`#ta-contact-rate-limit-modal`:** cuenta regresiva de 60 s guardada en `localStorage['ta_rate_limit_timestamp']`. Sobrevive a F5 sin reiniciar y, al llegar a 0, limpia `reason=rate_limit` de la URL con `history.replaceState`.
- **`#ta-contact-success-modal`** (`.ta-modal-content--success`), diseño aprobado tras varias iteraciones:
  - Check SVG blanco en un círculo `#03854A` de 40px, con halo `box-shadow: 0 0 0 8px rgba(3,133,74,.15)`.
  - "¡Mensaje enviado!" en 20px y peso 800.
  - Texto de plazo en 15px.
  - Botón "Cerrar" negro de ancho completo, centrado y con radio 12px.
  - **El cliente rechazó:** el emoji ✅, un bloque de WhatsApp y el enlace "Enviar otro mensaje".
  - Tras el éxito, a los 3 s la página se recarga con `?ta_contact=success`.

### 4.116 Páginas legales: tabla de cookies, ancho y acordeón (2026-10-20)

- **Contenido (base de datos, no el tema):**
  - Política de Privacidad (ID 102): nueva sección **"8. Uso de Cookies y Almacenamiento Local"**, con una tabla de `ta_contact_draft`, `ta_rate_limit_timestamp`, `jg_session` y `wordpress_*`/`wp-settings-*`, seguida de "Base de licitud" y "Gestión y revocación". El cliente pidió quitar "Lo que no utilizamos". La antigua sección 8 pasó a ser la 9.
  - Términos (ID 101): la sección 6 remite a "la sección 8 de dicha Política".
  - Las versiones anteriores están en Revisiones de WordPress.
- **`style.css`:**
  - `.ta-table-scroll` envuelve las tablas y da scroll horizontal solo bajo 860px.
  - Estilos para `h3` y `code` dentro de `.entry-content`, y `vertical-align: top` en `td`.
  - `.ta-page-content--wide` fija `max-width: 1280px`.
- **Acordeón** (`ta_is_legal_page()` y `ta_render_legal_accordion()` en functions.php, llamados desde `page.php`):
  - Divide el contenido ya filtrado por cada `<h2>`. Lo que va antes del primer `h2` se muestra como introducción.
  - De "N. Título" saca el número y el título.
  - Dibuja un índice lateral sticky ("CONTENIDO", `top: 168px` por el header sticky) y una tarjeta `<details class="ta-legal-item" id="seccion-N">` por sección, con el número en un cuadro verde, la flecha y `scroll-margin-top: 168px`.
  - Un script abre la sección al hacer clic en el índice o al llegar con `#seccion-N`.
  - Bajo 860px el índice se oculta.
  - **Para editar, basta escribir `<h2>N. Título</h2>` en WordPress.**

### 4.117 Versión 1.20.0 (2026-10-20)

`TA_VERSION` y la cabecera de `style.css` pasaron de 1.19.3 a **1.20.0**. Todos los cambios de §4.110–§4.116 se hicieron sin subir la versión, y con la caché `immutable` de 1 año los visitantes habrían seguido viendo el CSS anterior. Verificado: el HTML ahora pide `style.css?ver=1.20.0`.

### 4.118 Versión 2.0.0 (2026-10-20)

_(ver también §4.119)_

A pedido del cliente, el tema y el plugin quedan ambos en **2.0.0**: `TA_VERSION` y la cabecera de `style.css` pasan de 1.20.0 a 2.0.0, y `JG_VERSION` y la cabecera del plugin pasan de 1.9.0 a 2.0.0. No cambia la funcionalidad. La versión solo se usa para el cache-busting de CSS y JS (`?ver=2.0.0`); no hay migraciones ni `version_compare()` que dependan de ella. Verificado con `wp_get_theme()`, `get_plugin_data()` y el HTML servido.

### 4.119 Acordeón legal no aparecía en producción: slugs distintos (2026-10-20, sigue en 2.0.0)

- **Causa:** `ta_is_legal_page()` usaba `is_page()` con los slugs de local, pero producción usa `politica-privacidad` (ID 2190) y `termino-y-condiciones` (ID 2193). Las páginas se mostraban como texto normal, sin acordeón ni ancho amplio.
- **Arreglo:** la función ahora compara el ID consultado con `ta_find_page()`, usando las variantes de ambos entornos y búsquedas parciales (`privacidad`, `termino`). Es el mismo mecanismo que usan los enlaces del formulario y del footer.
- **Versión:** es un cambio solo de PHP, así que la versión sigue en 2.0.0 y no hace falta invalidar caché. Commit `e6d554f`.
- **Pendiente de contenido:** en producción, la Política de Privacidad no tiene la sección 8 de cookies (la base de datos no viaja en el ZIP). Hay que pegarla antes de "8. Autoridad de Control", renumerar esa sección a 9, y agregar en Términos, sección 6, la remisión a "la sección 8 de dicha Política".

### 4.120 Teléfono: se aceptan móviles que comienzan con 3 (2026-10-20, sigue en 2.0.0)

A pedido del cliente, cuyo propio móvil empieza con `+56 9 3…`, la regla pasa de `^[4-9]\d{7}$` a **`^[3-9]\d{7}$`** en los tres lugares: `validateField()` y el listener `input` del teléfono en page-contacto.php, y `ta_handle_contact_submit()` en functions.php. Siguen rechazándose los números que empiezan con 0, 1 o 2, y los que no tienen 8 dígitos. Como el JS va dentro de la plantilla PHP y no en un archivo cacheado, no hace falta subir la versión.

### 4.121 Menú hamburguesa en móvil y tablet (2026-10-22, versión 2.0.1)

- **Antes:** a 861px o menos, el menú se mostraba siempre desplegado bajo el logo.
- **Ahora:**
  - **header.php:** botón `.ta-nav-toggle` (44×44, borde `--ta-border`, radio 10px, tres líneas `--ta-ink`) con `aria-controls="ta-primary-nav"`, `aria-expanded` y una `aria-label` que cambia entre "Abrir menú" y "Cerrar menú". El `<nav>` lleva `id="ta-primary-nav"`.
  - **ta-nav.js:** `setNavOpen()` alterna la clase `.is-nav-open` en `.ta-header`. Escape cierra el menú y devuelve el foco al botón; al pasar a más de 861px el menú se cierra.
  - **style.css, bloque `@media (max-width: 861px)`:**
    - El logo y el botón van en una fila. El `.ta-nav` queda oculto y aparece debajo, en columna, con `.is-nav-open`.
    - Las tres líneas forman una X al abrir.
    - Catálogo se abre como acordeón; su flecha mide 44px de alto para no esconderse detrás del submenú.
    - "Cotizar" ocupa el ancho completo con el texto centrado.
    - `overflow-x: hidden` y el submenú con `width: auto` evitan el scroll horizontal.
    - Las reglas que antes solo aplicaban a 480px o menos ahora aplican a 861px o menos.
  - En escritorio no cambia nada: el botón tiene `display: none`.
- **Versión:** `TA_VERSION` y `style.css` pasan de 2.0.0 a **2.0.1**, porque cambiaron el CSS y el JS (caché `immutable`).
- **Verificado** con Chrome headless por DevTools Protocol, a 390px y 1280px: cerrado, abierto, Catálogo abierto (`scrollWidth` igual a `clientWidth`), cerrado de nuevo, y escritorio sin botón. Commit `9494b77`.

### 4.122 Nombre: no permite caracteres especiales (2026-10-22, sigue en 2.0.1)

- **Antes:** el filtro de escritura del campo `nombre` (page-contacto.php) solo borraba dígitos.
- **Ahora:** borra todo lo que no sea letra (`\p{L}`, incluye tildes y ñ), espacio, `-`, `'` o `’`, con la regex `/[^\p{L}\s'’-]/gu`. Son los mismos caracteres que acepta la validación del nombre en JS y PHP. Sigue convirtiendo a MAYÚSCULAS y ahora conserva la posición del cursor cuando se borra un carácter a mitad del texto.
- **Servidor:** sin cambios. La regex de `ta_handle_contact_submit()` ya rechazaba esos caracteres (`nombre_invalido`).
- **Versión:** el JS va dentro de la plantilla PHP, así que no hace falta subirla.
- **Verificado** con Chrome headless: al escribir `juan@#$ pérez!123` queda `JUAN PÉREZ`; al insertar `*x` en la posición 4 queda `JUANX PÉREZ` con el cursor en 5; al pegar `<script>ñuño o'brien-soto; DROP` queda `SCRIPTÑUÑO O'BRIEN-SOTO DROP`.

### 4.123 Contacto: la tarjeta del formulario alineada con la descripción (2026-10-22, versión 2.0.2)

- **Antes:** `.ta-contact-form-card` usaba `margin-top: 137px` para coincidir con la descripción de la columna izquierda. Como el título usa `clamp()` y puede ocupar una o dos líneas, la alineación fallaba: medido con Chrome, la descripción empezaba en 329px y la tarjeta en 344px a 1440px de ancho; a 1100px, en 374px y 344px.
- **Ahora (solo `style.css`, sin tocar el HTML):**
  - `.ta-contact-inner` define `grid-template-rows: auto auto auto auto auto 1fr`, `column-gap: 64px` y `row-gap: 0`.
  - `.ta-contact-info` usa `display: contents`: sus hijos (eyebrow, título, divisora, descripción, lista) pasan a ser filas 1 a 5 de la columna 1.
  - La tarjeta va en `grid-column: 2; grid-row: 4 / -1`. La última fila, `1fr`, absorbe el alto extra de la tarjeta, así la lista conserva su separación natural de 36px.
  - A 860px o menos: una columna, `grid-template-rows: none` y la tarjeta en `grid-row: auto` con `margin-top: 40px`, debajo de la lista.
- **Verificado** con Chrome: la descripción y la tarjeta empiezan a la misma altura a 1440, 1100 y 900px (329/329, 374/374, 357/357). A 390px la tarjeta queda debajo de la lista, con el mismo ancho de columna que antes (71–361px).
- **No cambió:** el contenido del formulario. Se sube a 2.0.2 porque cambió el CSS.

### 4.124 Microsoft Clarity con banner de consentimiento (2026-11-03, versión 2.1.0)

**Pedido del cliente:**
- Integrar Clarity sin afectar el sitio ni el formulario.
- No ocultar nunca los datos del formulario en pantalla.
- Marcar el formulario para que Clarity no grabe datos personales.
- Banner de consentimiento previo.
- Actualizar la Política de Privacidad, sección 8.

**Implementación:**
- **functions.php:**
  - `TA_CLARITY_DEFAULT_ID` = `z81r92medx`.
  - `ta_get_clarity_id()` lee el theme_mod `ta_clarity_id` (con el ID por defecto, filtrable con `ta_clarity_id`) y `ta_sanitize_clarity_id()` lo valida.
  - Personalizar tiene una sección nueva, "Analítica (Microsoft Clarity)"; guardarla vacía desactiva todo.
  - Si hay ID, se encola `assets/js/ta-consent.js` con `window.taConsentConfig = {clarityId}` (inline, antes).
  - `ta_render_consent_banner()` imprime el banner `#ta-consent` oculto en `wp_footer`.
- **assets/js/ta-consent.js** (nuevo):
  - Lee y guarda `localStorage['ta_cookie_consent']` (`{analytics, date, v}`, 12 meses).
  - Sin decisión muestra el banner. Con "Aceptar" inyecta `clarity.ms/tag/{ID}` y llama `clarity('consent')`. Con "Rechazar" no carga nada.
  - `[data-ta-consent-open]` reabre el banner. Si el visitante retira el consentimiento, se borran `_clck`, `_clsk`, `CLID`, `ANONCHK`, `MR`, `MUID` y `SM` y se recarga.
  - Reserva el alto del banner con `padding-bottom` en el body y `scroll-padding-bottom` en `html`, recalculado con `ResizeObserver` (el alto cambia al cargar Manrope).
  - Todo va en `try/catch`.
- **footer.php:** botón "Preferencias de cookies" en `.ta-footer-legal`, solo si Clarity está activo.
- **page-contacto.php:** `data-clarity-mask="true"` en `.ta-contact-form-card`. No cambia nada visible.
- **style.css:**
  - `.ta-consent*`: fijo abajo, `z-index` 9000 (los modales usan 9999), botones del mismo tamaño y apilado a 640px o menos. También `.ta-footer-consent`.
  - **Arreglos de desborde horizontal en móvil:** `.ta-footer-legal { flex-wrap: wrap }` (con el enlace nuevo medía 474px en un teléfono de 390px) y `.ta-catalog-pagination .jg-pagination-nav { flex-wrap: wrap }` (problema anterior, 442px). Los dos dejaban el banner fuera de la pantalla.
- **Política de Privacidad (ID 102, base de datos local):** la sección 8 quedó con dos tablas (técnicos + `ta_cookie_consent`; cookies de Clarity), un párrafo sobre Microsoft y la transferencia internacional, la base de licitud separada (técnicos: necesarios; Clarity: consentimiento) y la revocación desde "Preferencias de cookies". **Hay que replicarla en producción (ID 2190).**

**Verificado** con Chrome headless por DevTools Protocol, a 390px:
- Antes de aceptar: 0 peticiones a `clarity.ms` y sin script.
- El banner no tapa el final de la página, ni el botón "Enviar mensaje", ni el campo al que salta el formulario cuando hay errores.
- Enviar vacío sigue mostrando los 4 errores. El filtro del nombre funciona, el borrador se conserva y los datos no cambian al aceptar.
- Con "Aceptar" se carga `scripts.clarity.ms`; con "Rechazar" desde "Preferencias de cookies" deja de cargar.
- El modal (9999) queda sobre el banner (9000).
- 0 errores de JavaScript. Sin ID, el HTML es idéntico al anterior (salvo el atributo `data-clarity-mask`).

### 4.125 Google Tag Manager con el mismo consentimiento (2026-11-04, versión 2.2.0)

**Pedido:** integrar el contenedor `GTM-K7FR5V9K`. El cliente entregó un snippet para el `<head>`, pero **no se pegó tal cual**: cargaba GTM sin consentimiento y no empujaba el evento `gtm.js`, sin el cual no se disparan los activadores "Todas las páginas".

**Implementación:**
- **functions.php:**
  - `TA_GTM_DEFAULT_ID`, `ta_get_gtm_id()` (filtrable con `ta_gtm_id`) y `ta_sanitize_gtm_id()` (`^GTM-[A-Z0-9]{4,12}$`).
  - `ta_analytics_enabled()` (Clarity o GTM) decide si hay banner, enlace y script.
  - `taConsentConfig` ahora es `{clarityId, gtmId}`.
  - El texto del banner nombra las herramientas activas.
  - La sección del Customizer pasa a llamarse "Analítica (Clarity y Google Tag Manager)", con un campo nuevo para GTM.
- **ta-consent.js:**
  - `loadGtm()` fija `gtag('consent','default', {analytics_storage:'granted', ad_*:'denied'})`, empuja `{'gtm.start', event:'gtm.js'}` y carga `gtm.js?id=`.
  - `loadAnalytics()` carga Clarity y GTM.
  - `deleteAnalyticsCookies()` suma `_ga`, `_ga_*`, `_gid` y `_gat*`.
  - No hay `<noscript>` de GTM, porque sin JavaScript no hay consentimiento.
- **footer.php:** el enlace "Preferencias de cookies" aparece con `ta_analytics_enabled()`.
- **Política, sección 8 (local, ID 102):**
  - Lista de las dos herramientas.
  - Filas `_ga` y `_ga_<ID>` (2 años).
  - GTM no instala cookies propias.
  - Publicidad desactivada.
  - Google como proveedor, con su enlace.
  - Base de licitud y revocación para ambas.
  - **Falta replicarla en producción (ID 2190).**

**Hallazgo:** el contenedor publicado (versión 1) **no tiene etiquetas**. No hay GA4 ni ID `G-…`, solo variables integradas. GTM carga, pero Analytics no recibe datos hasta que se agregue la etiqueta "Google tag" con el ID `G-…` en GTM y se publique.

**Verificado** en Chrome headless, a 390px. Las peticiones de envío de datos (`collect`) se bloquearon para no ensuciar las estadísticas reales.
- 0 peticiones a Google y Clarity antes de aceptar.
- El formulario valida igual antes y después de aceptar, y el `preventDefault` del envío AJAX sigue funcionando.
- Al aceptar se cargan `gtm.js?id=GTM-K7FR5V9K` y Clarity. `dataLayer` tiene el consentimiento por defecto y el evento `gtm.js`, y `google_tag_manager['GTM-K7FR5V9K']` existe.
- Al recargar vuelven a cargar.
- Al retirar el consentimiento: 0 peticiones y ninguna cookie `_ga` ni `_cl`.
- 0 errores de JavaScript.

### 4.126 Google Analytics 4 directo con gtag.js (2026-11-04, versión 2.3.0)

**Pedido:** activar GA4 con el ID de medición `G-2KL8SPE9DY` (el cliente entregó el snippet de gtag.js).

**Decisión:** cargarlo **directo desde el tema** y no dentro de GTM. Así funciona sin configurar nada en tagmanager.google.com, cuyo contenedor está vacío. **Por eso no se debe agregar GA4 también en GTM:** duplicaría cada visita.

**Implementación:**
- **functions.php:**
  - `TA_GA4_DEFAULT_ID`, `ta_get_ga4_id()` (filtrable con `ta_ga4_id`) y `ta_sanitize_ga4_id()` (`^G-[A-Z0-9]{6,12}$`).
  - `ta_analytics_enabled()` incluye GA4 y `taConsentConfig.ga4Id` se pasa al JS.
  - El banner dice "Microsoft Clarity y Google Analytics".
  - El Customizer tiene un campo nuevo para el ID de GA4.
- **ta-consent.js:**
  - `setGoogleConsentMode()` fija el consentimiento por defecto **una sola vez** (analítica concedida, publicidad denegada), compartido por GA4 y GTM.
  - `loadGa4()` hace `gtag('js')` y `gtag('config', ID)` e inyecta `gtag/js?id=`.
  - `injectScript()` es común a los dos.
  - Orden verificado en `dataLayer`: `consent default` → `js` → `config:G-2KL8SPE9DY` → `gtm.js`.
- **Política, sección 8 (local):** la línea de GA4 ya no dice "cargado a través de Google Tag Manager". GTM se menciona aparte.

**Verificado** en Chrome headless, con los envíos de datos bloqueados:
- 0 peticiones antes de aceptar.
- Al aceptar se cargan `gtag/js?id=G-2KL8SPE9DY`, `gtm.js` y Clarity.
- El formulario valida igual y no navega.
- Al retirar el consentimiento: 0 peticiones y ninguna cookie `_ga`.
- 0 errores de JavaScript.

---

## 5. Próximos pasos sugeridos (actualizado 2026-10-22)

- **GTM:** el contenedor `GTM-K7FR5V9K` está vacío. **No agregar GA4 ahí** (ya va directo desde el tema, §4.126). Usarlo solo para etiquetas adicionales, declarándolas en la Política.
- **Producción:** subir el ZIP del tema **2.3.0**. Agrega Clarity y GTM con banner y, si producción sigue en 2.0.0, también el menú hamburguesa, el filtro del nombre y la alineación del formulario. **Además, pegar la nueva sección 8 de cookies en la Política de producción (ID 2190).**
- **Contenido legal en producción:** pegar la sección 8 de cookies en la Política de Privacidad (ID 2190) y la remisión en Términos (ID 2193). Ver §4.119. La base de datos no viaja en el ZIP.
- **`.htaccess` de producción:** revisar `RewriteBase`, las redirecciones 301 y las reglas de `sitemap.xml` y `robots.txt` (las de local usan `/wordpress/proyecto3/`).
- **GEO:** el `<link rel="ai-policy" href="/.well-known/ai.json">` apunta a la raíz del dominio, que no existe en una instalación en `/wp/`. Además, `ai.json` usa `info@` y el sitio `contacto@`. Ver GEO.md.
- **Menores:** usar `mb_substr()` en `ta_meta_description()`; validar en el servidor el máximo de 2000 caracteres de la descripción; quitar el correo del cliente del `error_log()` cuando falla `wp_mail()`.
- **Pendiente desde antes:** confirmar la dirección y el horario reales en la página Cotizar ("Av. Las Condes 1234, Oficina 502" y "09:00–18:30" son de partida).

---

## 6. Seguridad del tema — estado real (verificado en el código)

> **Nota importante:** una versión anterior de esta sección (§6 de la entrega 1.14.20) documentaba un "endurecimiento de seguridad" con cabeceras HTTP inyectadas desde PHP, bloqueos de archivos `.md` en `.htaccess` y `FORCE_SSL_ADMIN` en `wp-config.php`. Esos cambios **fueron revertidos** y **no están presentes** en el código actual. Esta sección describe lo que el tema realmente hace (y no hace) en materia de seguridad, verificado directamente en los archivos el 2026-08-24.

### 6.1 Lo que el tema sí hace

- **`ta_handle_contact_submit()`** (§1.2, §4.33, §4.41) — el formulario de Contacto sanea cada campo (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`, `esc_url_raw`), valida nonce (`wp_verify_nonce`), usa honeypot, exige celular chileno real y dominio de correo dentro de `.cl|.com|.net|.org`, valida cada adjunto por extensión **y** contenido real (`getimagesize()` para imágenes, MIME `%PDF` para PDF), limita cantidad (`TA_CONTACT_MAX_FILES`, 5) y tamaño (`TA_CONTACT_MAX_FILE_SIZE`, 5 MB), usa `move_uploaded_file()` (verifica que el archivo venga de una subida HTTP real), borra los temporales apenas termina (éxito o error, vía `$fail`), y redirige con `wp_safe_redirect()` (no recarga en blanco). Es la pieza de seguridad más completa del tema.
- **Escapes de salida** — todo el HTML usa `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses()` (con lista blanca de `<br>` nada más) en las plantillas y en los componentes de `functions.php`. No hay `echo` de datos sin escapar.
- **`fallback_cb => 'ta_nav_menu_fallback'`** en `wp_nav_menu()` (header.php, §4.100) — si no hay ningún menú asignado a la ubicación `primary`, en vez de no dibujar nada (que dejaba el nav vacío tras una migración) se imprime una lista cerrada y explícita de las cuatro páginas del sitio: Inicio, Catálogo, Cuidados y Cotizar. Nunca se resuelve con `wp_page_menu()`, que expondría cualquier página publicada.

### 6.2 Lo que el tema NO hace (pendientes de seguridad)

> **Revisado el 2026-10-20:** los archivos `.md` de la raíz (`CLAUDE.md`, `SPEC.md`, `Docs_*.md`, `GEO.md`, `README.md`) **siguen respondiendo 200** a cualquier visitante (verificado con `curl`). Hay que bloquearlos en el servidor de producción o no subirlos. Los nuevos controles del formulario están en Docs_Seguridad.md, Fixes 13–15.

| Pendiente | Detalle | Impacto |
|---|---|---|
| Cabeceras de seguridad HTTP | No hay `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` ni `Content-Security-Policy` inyectadas desde el tema ni desde `.htaccess`. | El sitio se puede incrustar en iframes de terceros (clickjacking), el navegador puede adivinar MIME, y el `Referer` filtra la URL completa al saltar a un sitio externo. |
| `.htaccess` de la raíz | Solo tiene las reglas estándar de WordPress (rewrite). No bloquea el acceso público a `CLAUDE.md`, `README.md`, `Docs_Plugin.md`, `Docs_theme.md` ni `image.png` — cualquier visitante puede leerlos. | **Alto** — `README.md` contiene credenciales de admin y de BD. Si el entorno es públicamente accesible, están expuestas. |
| `FORCE_SSL_ADMIN` | No está definido en `wp-config.php`. | En producción (no en local `http://localhost`), la administración viaja sin forzar HTTPS. |
| `WP_DEBUG` | `WP_DEBUG` está en `false` (valor por defecto de WP). No hay `WP_DEBUG_LOG` ni `WP_DEBUG_DISPLAY` definidos. | Los errores no se registran en `wp-content/debug.log`; en producción un error puede mostrar información sensible en pantalla según la config del servidor. |
| `wp_mail()` sin SMTP | El formulario de Contacto depende de `wp_mail()`. Sin un plugin SMTP, los mensajes no se entregan. | Riesgo operativo (no de seguridad): los mensajes de contacto se pierden silenciosamente. |
| `image.png` en la raíz | La referencia `![alt text](image.png)` al final de este archivo apunta a un `image.png` de la raíz que es públicamente accesible (no bloqueado por `.htaccess`). | Bajo — solo si el archivo contiene información sensible (verificar su contenido). |


/*
configuración del correo
Lo que normalmente hay que hacer, sea cual sea el hosting:

Instalar un plugin SMTP gratuito desde el propio WordPress (Plugins → Añadir nuevo) — los más usados son WP Mail SMTP o Easy WP SMTP.
Conectarlo a una cuenta real de envío: puede ser una casilla de Gmail/Google Workspace, el correo que venga con el hosting (si el hosting ofrece cuentas de correo, cosa común en planes de hosting compartido con cPanel), o un servicio dedicado a esto (SendGrid, Mailgun, Amazon SES — con plan gratuito para volúmenes bajos).
Seguir el asistente de configuración del plugin (te pide las credenciales SMTP de esa cuenta).

 */

 ![alt text](image.png)

### §4.127 — Página "Política de cookies" (tema 2.3.1, 2026-11-04)

- **Pedido del cliente:** "Preferencias de cookies" del footer se reemplaza por "Política de cookies", y la política se redacta en lenguaje legal, sin tecnicismos. No va dentro del banner ni en una ventana modal (ambas opciones se descartaron).
- **functions.php:** `ta_get_cookies_page()` (`ta_find_page( 'politica-de-cookies', 'politica-cookies', 'cookies' )`) y `ta_is_cookies_page()`. `ta_is_legal_page()` incluye la página, así que se muestra con acordeón y en ancho de 1280px. En el banner, "Más información" lleva a la Política de cookies (o a Privacidad `#seccion-8` si no existe).
- **page.php:** en la Política de cookies, si `ta_analytics_enabled()`, agrega después del acordeón `.ta-cookie-prefs` con el botón "Cambiar preferencias de cookies" (`data-ta-consent-open`, ta-consent.js lo abre sin cambios).
- **footer.php:** enlace "Política de cookies" si la página existe; si no, el botón antiguo "Preferencias de cookies".
- **style.css:** `.ta-cookie-prefs`. Versión 2.3.1.
- **Base de datos local:** página ID 161 `politica-de-cookies` con 8 secciones (Responsable, Qué son, Necesarias, Analítica, Que no utilizamos, Base de licitud, Revocación y derechos, Modificaciones). En la Privacidad (ID 102), sección 8, la revocación ahora apunta al botón de la Política de cookies.
- **Producción:** hay que crear la página a mano (Páginas → Añadir; título "Política de cookies", slug `politica-de-cookies`, contenido en el Editor de código) y actualizar la frase de revocación de la sección 8 de la Privacidad.
- Verificado en Chrome headless a 390px y 1280px: 8 secciones, sin desborde horizontal, el botón abre el banner y "Aceptar" guarda el consentimiento. Sin errores de JavaScript.
- **Actualización (2026-11-04), revisión contra el texto de la Ley 21.719:** la Política de cookies (ID 161) quedó en **versión 1.0 del 4-11-2026** y con **10 secciones**: Responsable; Qué son y origen de los datos; Necesarias, con plazos (consentimiento 12 meses, borrador hasta cerrar la pestaña, espera de 60 s, sesión del panel hasta 24 h); Analítica, con categorías de datos y Microsoft/Google como encargados; Transferencia internacional a EE. UU. (sin adecuación declarada por la Agencia, amparada en el DPA de Microsoft y los Google Ads Data Processing Terms); No utilizamos ni hay perfiles; Base de licitud y oposición; Revocación sin efecto retroactivo; Derechos (30 días, prorrogables) y seguridad; Modificaciones. La ley se cita como "Ley N° 19.628, sobre Protección de los Datos Personales". El domicilio queda como "Av. Las Condes 1234, Oficina 502, comuna de Las Condes, Región Metropolitana de Santiago, Chile" (también en la Privacidad ID 102). **Pendiente:** el representante legal (Art. 14 ter b), que el cliente aún no indica. Si cambian los plazos (TTL del JWT, límite de envíos o duración del consentimiento), hay que actualizar este texto.
- **Actualización (2026-11-04), Política de Privacidad 2.0 sin redundancia:** a pedido del cliente, la Privacidad (ID 102) se reescribió en lenguaje jurídico y sin detalle de cookies. Quedó con 11 secciones; la **9. Cookies** solo remite a la Política de Cookies. Se agregó a Google LLC como proveedor de correo (el formulario se envía por WP Mail SMTP con Gmail), con la transferencia a EE. UU.; plazos de conservación (12 meses si no hay contrato); seguridad descrita sin exagerar. Respaldo del texto anterior: `scratchpad/priv-102-respaldo-antes-v2.html`. Términos (ID 101), sección 6: remite a la Política de Cookies por nombre. Las cookies del panel de WordPress (`wordpress_*`, hasta 14 días; `wp-settings-*`, 1 año) pasaron a la sección 3 de la Política de Cookies. En `ta_render_consent_banner()`, el enlace de respaldo apunta a `#seccion-9`.

### §4.128 — Sin banner ni analítica en el panel privado (tema 2.3.1, 2026-11-04)

- **Pedido del cliente:** el banner de cookies no debe aparecer en el plugin, porque el panel es privado de la empresa.
- **functions.php:** `ta_is_private_page()` es verdadera en las páginas singulares cuyo contenido tiene `[jwt_gallery]` o `[jwt_gallery_panel]` (filtro `ta_is_private_page`). `ta_should_load_analytics()` = `ta_analytics_enabled() && ! ta_is_private_page()`, y la usan `ta_assets()` (no se encola `ta-consent.js` ni `taConsentConfig`) y `ta_render_consent_banner()`. Así, en el panel no se carga Clarity, GA4 ni GTM aunque el usuario los haya aceptado antes en el sitio público.
- **Verificado:** `/galeria/` y `/galeria-panel/` sin banner ni script; portada, Catálogo, Cuidados, Cotizar y Política de cookies con el banner.

### §4.129 — Textos legales versionados dentro del tema (tema 2.3.2, 2026-11-04)

- **Pedido del cliente:** local y producción deben verse iguales. Hasta ahora los textos legales vivían solo en la base de datos y no viajaban en el ZIP; producción tenía la Privacidad antigua, Términos antiguo y no tenía Política de cookies.
- **Decisión (aprobada por el cliente):** el tema lleva los textos en `legal/*.html`. `ta_sync_legal_pages()` (hook `init`, prioridad 20) compara la opción `ta_legal_version` con `TA_LEGAL_VERSION` (`2026-11-04.1`). Si difieren, recorre `ta_legal_documents()`: busca cada página con `ta_find_page()`, la actualiza si el contenido cambió (revisión de WordPress) o la crea si falta (título y slug del arreglo). Usa un bloqueo `ta_legal_sync_lock` de 60 s contra visitas simultáneas, deja el resultado en `ta_legal_sync_log` y solo marca la versión como aplicada si todo salió bien.
- **Consecuencia:** lo que se edite a mano en WordPress se respeta mientras no cambie `TA_LEGAL_VERSION`, y se reemplaza al subirla.
- **Prueba local, simulando producción** (Privacidad antigua, Términos con la frase antigua y sin página de cookies): en la primera visita, Privacidad y Términos se actualizaron y Cookies se creó. Las tres quedaron idénticas a los archivos. No hubo una segunda ejecución, se respetó la edición manual y no aparecieron páginas duplicadas. La página de cookies local cambió de **ID 161 a 179**.

### §4.130 — El desplegable "Catálogo" muestra todas las categorías (tema 2.3.3, 2026-11-04)

- **Pedido del cliente:** al agregar o eliminar una categoría en el panel, debe aparecer o desaparecer directamente en el menú "Catálogo".
- **Antes:** el desplegable usaba `ta_get_solutions_by_category()`, que solo incluye categorías con al menos una pieza publicada. Una categoría nueva (vacía) no aparecía. Además, la función consultaba todas las piezas de cada categoría y se llamaba dos veces por página (`start_el` y `end_el` del walker).
- **Ahora:** `ta_get_catalog_categories()` hace un solo `get_terms( hide_empty => false )` por carga de página (caché estática), con el mismo criterio que las pestañas del catálogo. La usan `TA_Nav_Walker` y `ta_nav_menu_fallback()`. Una categoría vacía lleva a su pestaña, que muestra "Todavía no hay productos publicados en esta categoría.". `ta_get_solutions_by_category()` queda sin uso en el menú.
- **Verificado por API** (cuenta de prueba ID 4): crear "Zz Menu Prueba" → aparece; renombrar → cambia; borrar → desaparece. Catálogo sin errores.

### §4.131 — Botón "Configurar cookies" en el footer (tema 2.3.4, 2026-11-06)

- **Pedido del cliente:** "según la ley debe tener ese botón para aceptar o rechazar las cookies". Ley 21.719, Art. 12: el consentimiento se revoca "en cualquier momento y sin expresión de causa, utilizando medios similares o equivalentes a los empleados para su otorgamiento", y esos medios deben estar "permanentemente disponibles". Antes el botón solo estaba al final de la Política de cookies (desde 2.3.1).
- **footer.php:** enlace "Política de cookies" (si existe la página) **y** el botón `<button class="ta-footer-consent" data-ta-consent-open>Configurar cookies</button>` con `ta_should_load_analytics()`, así que no aparece en las páginas privadas del panel. Reabre el mismo banner (`ta-consent.js`, sin cambios).
- **page.php:** el botón final de la Política de cookies pasa a llamarse "Configurar cookies" (un solo nombre en todo el sitio).
- **legal/politica-de-cookies.html:** versión 1.1, del 6-11-2026. La sección 8 menciona el botón "Configurar cookies" del pie de cada página y del final de la Política, que abre el mismo aviso con "Aceptar" y "Rechazar". `TA_LEGAL_VERSION` = `2026-11-06.1`, así que llega sola a producción.
- **Versión:** `TA_VERSION` y `style.css` a **2.3.4** (cambio solo de PHP y de texto legal; se sube para distinguir el ZIP).
- **Verificado** en Chrome a 390px y 1280px: el botón aparece en el footer y reabre el banner después de haber rechazado; "Aceptar" guarda la decisión; el banner no tapa el botón; no hay desborde horizontal; la Política de cookies muestra la versión 1.1 y el nuevo texto; el login del panel no tiene banner ni botón; no hay errores de JavaScript.

### §4.132 — sitemap.xml del tema sin el panel (tema 2.3.5, 2026-11-06)

- Se quitaron `/galeria/` y `/galeria-panel/` del `sitemap.xml` estático del tema (el XML sigue siendo válido). El ocultamiento real lo hace el plugin 2.0.2 (Docs_Plugin.md §12.54).
- Nota: ese archivo está desactualizado (usa slugs antiguos como `nuestras-soluciones` y `cuidados-del-acrilico`, y no incluye las páginas legales). En producción no se usa, porque `/wp/sitemap.xml` redirige a `wp-sitemap.xml`, que WordPress genera solo. El que se envía a Search Console es `wp-sitemap.xml`.

### §4.133 — Categorías del catálogo en el sitemap, canónica propia y 301 de vidrios (tema 2.3.6, 2026-11-06)

- **Pedido del cliente:** `/catalogo/?categoria=exhibir`, `informar`, `organizador`, `proteger` y `trabajos-especiales` (no `vidrios`) deben estar en el sitemap, y cualquier categoría nueva debe aparecer sola para que Google la indexe.
- **Problema de fondo encontrado:** todas las URL `?categoria=…` declaraban como canónica `/catalogo/`, así que Google las trataba como duplicadas aunque estuvieran en el sitemap. Y en producción la categoría ID 9 tenía el slug `vidrios` (nombre "Trabajos Especiales"), porque se renombró desde el panel, que solo cambia el nombre.
- **functions.php** (bloque "Categorías del catálogo en Google", antes de `ta_get_solutions_by_category()`):
  - `ta_catalog_category_url()`, `ta_catalog_legacy_slugs()` (filtrable), `ta_is_catalog_page()` y `ta_requested_catalog_category()`.
  - `template_redirect`: si `?categoria=` trae un slug antiguo que ya no existe, redirige con **301** al nuevo.
  - Filtro `get_canonical_url`: en el catálogo con una categoría válida, la canónica incluye `?categoria={slug}`. Con una categoría inexistente queda `/catalogo/`.
  - `wp_sitemaps_init`: registra el proveedor `TA_Sitemaps_Catalog` (`catalogo` / `categorias`) → `wp-sitemap-catalogo-categorias-1.xml` en el índice. Lista todas las categorías (`ta_get_catalog_categories()`), con `lastmod` = la pieza más reciente de cada una.
- **Plugin 2.0.3:** `JG_CPT::migrate_legacy_slugs()` corrige el slug `vidrios` → `trabajos-especiales` una sola vez (Docs_Plugin.md §12.55).
- **Verificado en local:**
  - Con el estado de producción simulado (slug `vidrios`), la primera visita migró el slug: mismo ID 4, 4 piezas. El menú y el sitemap muestran `trabajos-especiales` y `?categoria=vidrios` responde 301.
  - Crear "Zz Sitemap Prueba" por la API la agregó al sitemap con su canónica, y borrarla la quitó (sin restos).
