# Generative Engine Optimization (GEO) - TODO Acrílico

Este documento describe la política de **Generative Engine Optimization (GEO)** del sitio. GEO es el conjunto de prácticas que permiten que modelos de IA y motores de búsqueda generativos (como ChatGPT, Claude, Gemini, etc.) accedan, indexen y utilicen el contenido del sitio de forma ética y eficiente.

---

## 📋 Archivos de Configuración GEO

### 1. **`robots.txt`** (Raíz del proyecto)
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
/
├── robots.txt                    ← Política de bots y IA
├── .well-known/
│   └── ai.json                   ← Política detallada de IA
└── wp-content/themes/todo-acrilico/
    └── header.php               ← Meta tags GEO
```

---

## 🔧 Cómo Verificar GEO

### 1. Verificar que robots.txt existe
```bash
curl https://todoacrilico.cl/wordpress/proyecto3/robots.txt
```

### 2. Verificar que ai.json es accesible
```bash
curl https://todoacrilico.cl/wordpress/proyecto3/.well-known/ai.json
```

### 3. Verificar meta tags en HTML
```bash
curl -s https://todoacrilico.cl/ | grep "ai-content-policy"
```

### 4. Probar con curl de IA
```bash
curl -A "Claude-Web" https://todoacrilico.cl/wordpress/proyecto3/
curl -A "GPTBot" https://todoacrilico.cl/wordpress/proyecto3/
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

- **Fecha:** 2026-10-20
- **Versión GEO:** 1.0.0
- **Estado:** ✅ Activo y operativo

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
