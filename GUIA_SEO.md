# 🚀 Guía Rápida SEO - lapsique.media

## ✅ ¿Qué se actualizó?

Se implementó un sistema completo de SEO para que cuando compartas enlaces de tu sitio en redes sociales (Facebook, Twitter, LinkedIn, WhatsApp, etc.), se vean profesionales con:

- ✨ **Título correcto**
- 📝 **Descripción atractiva**
- 🖼️ **Imagen destacada** (del DJ, evento, video o post)
- 🔗 **URL canónica**
- 🎯 **Datos estructurados** para Google

## 📋 Páginas Actualizadas

Todas las páginas del sitio ahora tienen meta tags SEO optimizadas:

| Página | Meta Tags | Imagen |
|--------|-----------|--------|
| **Inicio** | ✅ Dinámicas | Evento o DJ destacado |
| **DJs (lista)** | ✅ Optimizadas | Primer DJ |
| **DJ (perfil)** | ✅ Personalizadas | Foto de perfil |
| **Eventos (lista)** | ✅ Optimizadas | Primer evento |
| **Evento (detalle)** | ✅ Personalizadas | Poster del evento |
| **Videos (lista)** | ✅ Optimizadas | Primer video |
| **Video (detalle)** | ✅ Personalizadas | Thumbnail de YouTube |
| **Blog (lista)** | ✅ Optimizadas | Primer post |
| **Post (detalle)** | ✅ Personalizadas | Cover del post |

## 🎨 Acción Requerida: Crear Imágenes

Debes crear 2 imágenes y colocarlas en `/public/images/`:

### 1. og-default.jpg
**Para**: Compartir en redes cuando no hay imagen específica
**Tamaño**: 1200 x 630 píxeles
**Contenido sugerido**:
- Logo de lapsique.media
- Texto: "Música electrónica, visuales y sets en vivo"
- Fondo monocromático (negro con detalles blancos)

### 2. logo.png
**Para**: Resultados de búsqueda de Google
**Tamaño**: 512 x 512 píxeles
**Contenido**: Logo de lapsique.media con fondo transparente

💡 **Tip**: Usa Canva (gratis) para crear estas imágenes fácilmente.

## 🧪 Cómo Probar

### 1. Facebook/WhatsApp
1. Ve a: https://developers.facebook.com/tools/debug/
2. Pega tu URL (ej: `https://tudominio.com/djs/nombre-dj`)
3. Haz clic en "Debug"
4. Verás cómo se ve el enlace al compartir

### 2. Twitter
1. Ve a: https://cards-dev.twitter.com/validator
2. Pega tu URL
3. Verás la preview de la tarjeta

### 3. LinkedIn
1. Ve a: https://www.linkedin.com/post-inspector/
2. Pega tu URL
3. Inspecciona cómo se verá

### 4. Google (Datos Estructurados)
1. Ve a: https://search.google.com/test/rich-results
2. Pega tu URL
3. Verifica que todo esté correcto

## 📱 Ejemplo de Cómo se Verá

Cuando alguien comparta un enlace de un DJ, se verá así:

```
┌────────────────────────────────┐
│ 🖼️ [Foto del DJ]              │
├────────────────────────────────┤
│ Nombre del DJ - DJ | lapsique  │
│                                │
│ Biografía del DJ (descripción) │
│                                │
│ lapsique.media                 │
└────────────────────────────────┘
```

## 🔧 Configuración Adicional

Asegúrate de tener en tu archivo `.env`:

```env
APP_URL=https://tudominio.com
LAPSIQUE_INSTAGRAM=https://www.instagram.com/lapsiquemedia/
LAPSIQUE_YOUTUBE_HANDLE=@LAPSIQUEMEDIA
```

## 🌟 Beneficios

1. **Más Clics**: Enlaces más atractivos = más gente haciendo clic
2. **Profesionalismo**: Tu sitio se ve serio y profesional
3. **Mejor SEO**: Google entiende mejor tu contenido
4. **Compartir Fácil**: Las imágenes y textos se comparten automáticamente

## ❓ Preguntas Frecuentes

### ¿Por qué no veo la imagen al compartir?
- Asegúrate de crear las imágenes en `/public/images/`
- Limpia el caché en el Facebook Debugger
- Verifica que APP_URL esté correctamente configurado

### ¿Cómo actualizo la preview después de cambiar algo?
1. Ve al Facebook Debugger
2. Pega tu URL
3. Haz clic en "Scrape Again"

### ¿Funciona para WhatsApp?
Sí, WhatsApp usa las mismas meta tags de Open Graph que Facebook.

### ¿Qué pasa si no creo las imágenes por defecto?
El sistema funcionará pero mostrará una URL de imagen que no existe cuando no haya imagen específica. Es mejor crear las imágenes.

## 📚 Documentación Completa

Para más detalles técnicos, consulta `SEO_DOCUMENTATION.md`.

## ✨ Próximos Pasos

1. ✅ SEO implementado
2. 🎨 **Crear imágenes** (`og-default.jpg` y `logo.png`)
3. 🧪 **Probar** todas las URLs en los validadores
4. 📊 **Monitorear** en Google Search Console
5. 🚀 **Compartir** en redes sociales y ver los resultados

---

💜 **¡Listo!** Ahora tu sitio está optimizado para compartir en todas las redes sociales.

