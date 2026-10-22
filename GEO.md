# Generative Engine Optimization (GEO) - TODO Acrílico

Este documento describe la política de **Generative Engine Optimization (GEO)** del sitio. GEO es el conjunto de prácticas que permiten que modelos de IA y motores de búsqueda generativos (como ChatGPT, Claude, Gemini, etc.) accedan, indexen y utilicen el contenido del sitio de forma ética y eficiente.

> **Estado real verificado el 2026-10-22 (leer antes de modificar):**
>
> | Pieza | Local (`/wordpress/proyecto3/`) | Producción (`https://todoacrilico.cl/wp/`) |
> |---|---|---|
> | `robots.txt` GEO | ✅ El archivo vive en `wp-content/themes/todo-acrilico/robots.txt` y `.htaccess` lo sirve en la raíz (`RewriteRule ^robots\.txt$`) | ❌ Se sirve el `robots.txt` **por defecto de WordPress** (`Disallow: /wp/wp-admin/`). La regla de `.htaccess` del tema no está en producción |
> | `.well-known/ai.json` | Existe en la raíz de `proyecto3` | ❌ **404** en `/.well-known/ai.json` y en `/wp/.well-known/ai.json` |
> | Metas GEO en `header.php` | ✅ | ✅ Presentes, pero `<link rel="ai-policy" href="/.well-known/ai.json">` apunta a una URL que da 404 |
> | `sitemap.xml` | Servido desde el tema por `.htaccess` | Redirige (301) |
>
> **Para activarlo en producción:** copiar las reglas de `robots.txt` y `sitemap.xml` al `.htaccess` de producción (ajustando la ruta del tema) y subir `.well-known/ai.json` a la raíz del dominio. Si no, cambiar el `href` del `<link>` en `header.php`. Además, `ai.json` usa `info@todoacrilico.cl`, mientras el sitio y el formulario usan **`contacto@todoacrilico.cl`**: unificarlos si el cliente lo confirma.
>
> **No toques** `header.php` ni `.htaccess` por GEO sin revisar `CLAUDE.md` (reglas 4 y 5: versión y caché).

---

## 📋 Archivos de Configuración GEO

### 1. **`robots.txt`** (archivo en `wp-content/themes/todo-acrilico/robots.txt`, servido en la raíz por `.htaccess`)
Define qué bots y IA pueden acceder al sitio.

**Características:**
- ✅ Permite acceso a todos los bots éticos de IA principales
- ✅ Bloquea acceso a áreas administrativas (`/wp-admin/`, `/wp-login.php`)
- ✅ Permite crawling de motores de búsqueda generativos
- ✅ Incluye directivas para:
  - OpenAI (GPTBot, CCBot)
  - Anthropic (Claude-Web, anthropic-ai)
  - Google (Googlebot, Google-Extended para Gemini)
  - Perplexity (PerplexityBot)
  - Meta (facebookexternalhit)
  - Bing (Bingbot)

**Bots permitidos:**
```
User-agent: GPTBot (OpenAI)
User-agent: Claude-Web (Anthropic)
User-agent: PerplexityBot (Perplexity)
User-agent: Google-Extended (Google Gemini)
User-agent: CCBot (Common Crawl/OpenAI)
User-agent: anthropic-ai (Anthropic)
User-agent: facebookexternalhit (Meta)
User-agent: Bingbot (Microsoft)
User-agent: Cohere-AI (Cohere)
```

---

### 2. **`.well-known/ai.json`** (Política de IA)
Archivo JSON que especifica la política de IA del sitio. Sigue estándares emergentes para GEO.

**Contenido:**
```json
{
  "version": "1.0.0",
  "name": "TODO Acrílico",
  "description": "Soluciones en acrílico de alta calidad...",
  "policies": {
    "generation": {
      "allow": true,
      "attribution_required": true,
      "commercial_use": "allowed_with_credit"
    },
    "crawling": {
      "allow": true,
      "frequency": "daily"
    },
    "training": {
      "allow": true,
      "data_types": ["text_content", "images", "product_information"],
      "restrictions": ["personal_data", "user_credentials"]
    }
  }
}
```

**Acceso:** `/.well-known/ai.json`

---

### 3. **Meta Tags en HTML** (`header.php`)
Etiquetas que indican la política de IA directamente en el HTML.

```html
<!-- Generative Engine Optimization (GEO) -->
<meta name="ai-content-policy" content="allowed">
<meta name="ai-training" content="allowed">
<meta name="robots-ai" content="allow-generation-training">
<link rel="ai-policy" href="/.well-known/ai.json">
```

**Significado:**
- `ai-content-policy="allowed"` → Contenido puede ser usado por IA
- `ai-training="allowed"` → Contenido puede usarse para entrenar modelos
- `robots-ai="allow-generation-training"` → Permitir generación y entrenamiento

---

## 🤖 Agentes de IA Permitidos

El sitio está configurado para permitir acceso a los siguientes agentes:

| Agente | Empresa | Propósito | Permitido |
|---|---|---|---|
| **GPTBot** | OpenAI | Entrenamiento de ChatGPT | ✅ Sí |
| **Claude-Web** | Anthropic | Indexación para Claude | ✅ Sí |
| **PerplexityBot** | Perplexity | Motor de búsqueda generativo | ✅ Sí |
| **Google-Extended** | Google | Entrenamiento de Gemini | ✅ Sí |
| **CCBot** | Common Crawl/OpenAI | Indexación pública | ✅ Sí |
| **anthropic-ai** | Anthropic | Indexación general | ✅ Sí |
| **Bingbot** | Microsoft | Búsqueda Copilot | ✅ Sí |
| **facebookexternalhit** | Meta | Crawling Meta | ✅ Sí |

---

## 📊 Políticas Específicas

### Generación de Contenido
- **Permitido:** Usar contenido para generar respuestas en IA
- **Requerido:** Atribución al sitio (ej: "Según TODO Acrílico...")
- **Comercial:** Permitido con crédito

### Entrenamiento de Modelos
- **Permitido:** Usar contenido para entrenar nuevos modelos
- **Restricciones:**
  - ❌ NO usar datos personales
  - ❌ NO usar credenciales
  - ✅ SÍ usar contenido público y productos
  - ✅ SÍ usar información de soluciones

### Crawling
- **Frecuencia:** Diaria
- **Áreas permitidas:** Contenido público, catálogo, soluciones
- **Áreas bloqueadas:** `/wp-admin/`, `/wp-login.php`, `/wp-includes/`

### Indexación
- **Permitido:** Indexar para búsqueda y recuperación
- **Propósito:** Mejorar resultados de búsqueda generativa

---

## 🔒 Cumplimiento Legal

El sitio cumple con:
- ✅ **GDPR** (Regulación General de Protección de Datos)
- ✅ **CCPA** (California Consumer Privacy Act)
- ✅ **Estándares éticos de IA** (Anthropic, OpenAI)

**Datos protegidos:**
- No se comparte información de usuarios
- No se comparte credenciales
- No se comparte datos personales
- Solo contenido público es accesible

---

## 📍 Ubicaciones de Archivos GEO

```
/ (raíz de WordPress)
├── .htaccess                     ← RewriteRule ^robots\.txt$ y ^sitemap\.xml$ → archivos del tema
├── .well-known/
│   └── ai.json                   ← Política detallada de IA
└── wp-content/themes/todo-acrilico/
    ├── robots.txt                ← Política de bots y IA (viaja en el ZIP del tema)
    ├── sitemap.xml               ← Sitemap (viaja en el ZIP del tema)
    └── header.php                ← Meta tags GEO
```

---

## 🔧 Cómo Verificar GEO

### 1. Verificar que robots.txt existe
```bash
curl http://localhost/wordpress/proyecto3/robots.txt      # local
curl https://todoacrilico.cl/robots.txt                     # producción (hoy: el de WordPress, no el GEO)
```

### 2. Verificar que ai.json es accesible
```bash
curl http://localhost/wordpress/proyecto3/.well-known/ai.json   # local
curl https://todoacrilico.cl/.well-known/ai.json                  # producción (hoy: 404)
```

### 3. Verificar meta tags en HTML
```bash
curl -s https://todoacrilico.cl/wp/ | grep "ai-content-policy"
```

### 4. Probar con curl de IA
```bash
curl -A "Claude-Web" https://todoacrilico.cl/wp/
curl -A "GPTBot" https://todoacrilico.cl/wp/
```

---

## 🚀 Beneficios de GEO

1. **Visibilidad en IA:** El contenido aparece en respuestas de ChatGPT, Claude, etc.
2. **Tráfico mejorado:** Cuando una IA referencia el sitio, envía tráfico
3. **SEO mejorado:** Los motores generativos toman en cuenta las políticas
4. **Posicionamiento:** Mayor presencia en resultados de búsqueda generativa
5. **Control:** Define exactamente qué puede hacer cada IA con tu contenido

---

## 📅 Última Actualización

- **Fecha:** 2026-10-22 (revisión del estado real)
- **Versión GEO:** 1.0.0
- **Estado:** ✅ Activo en local · ⚠️ **Parcial en producción**: solo las metas. Ver la tabla al inicio

---

## 📞 Contacto

Para consultas sobre la política GEO:
- Email: info@todoacrilico.cl
- Web: https://todoacrilico.cl
- Responsable: TODO Acrílico

---

## 📚 Referencias

- [OpenAI Bot - GPTBot Documentation](https://openai.com/gptbot)
- [Anthropic - Claude Web Crawler](https://www.anthropic.com/)
- [Robots.txt Standard](https://www.robotstxt.org/)
- [Well-Known Directory RFC 5785](https://www.ietf.org/rfc/rfc5785.txt)
- [Generative Engine Optimization Guide](https://en.wikipedia.org/wiki/Generative_engine_optimization)
