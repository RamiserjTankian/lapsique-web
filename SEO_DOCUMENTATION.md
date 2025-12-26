# Documentación SEO - lapsique.media

## Cambios Implementados

Se ha actualizado completamente el SEO del sitio web para mejorar cómo se comparten los enlaces en redes sociales y motores de búsqueda.

## Meta Tags Implementadas

### 1. Meta Tags Básicas
- `title` - Título de la página
- `description` - Descripción de la página
- `keywords` - Palabras clave relevantes
- `canonical` - URL canónica para evitar contenido duplicado

### 2. Open Graph (Facebook, LinkedIn, WhatsApp)
- `og:type` - Tipo de contenido (website, article, event, video, profile)
- `og:url` - URL de la página
- `og:title` - Título para redes sociales
- `og:description` - Descripción para redes sociales
- `og:image` - Imagen destacada (1200x630px)
- `og:image:width` y `og:image:height` - Dimensiones de la imagen
- `og:site_name` - Nombre del sitio
- `og:locale` - Idioma y región (es_MX)

### 3. Twitter Cards
- `twitter:card` - Tipo de tarjeta (summary_large_image o player para videos)
- `twitter:url` - URL de la página
- `twitter:title` - Título para Twitter
- `twitter:description` - Descripción para Twitter
- `twitter:image` - Imagen destacada

### 4. Structured Data (JSON-LD)
Se implementó Schema.org structured data para mejorar la comprensión del contenido por parte de los motores de búsqueda:

- **Organization** (home)
- **CollectionPage** (índices: DJs, Eventos, Videos, Posts)
- **Person** (perfil de DJ)
- **Event** (página de evento)
- **VideoObject** (página de video)
- **BlogPosting** (página de post)
- **Blog** (índice de blog)

## Páginas Actualizadas

### Home (/)
- Meta tags dinámicas basadas en evento destacado o DJ destacado
- Schema Organization
- Imagen dinámica (del evento o DJ destacado)

### DJs (/djs)
- Meta tags para listado de DJs
- Schema CollectionPage
- Imagen del primer DJ o imagen por defecto

### DJ Individual (/djs/{slug})
- Meta tags personalizadas por DJ
- Schema Person
- Imagen de perfil del DJ
- Biografía como descripción

### Eventos (/eventos)
- Meta tags para listado de eventos
- Schema CollectionPage
- Imagen del primer evento o imagen por defecto

### Evento Individual (/eventos/{slug})
- Meta tags personalizadas por evento
- Schema Event con fecha y ubicación
- Imagen de poster del evento
- Headline o descripción del evento

### Videos (/videos)
- Meta tags para listado de videos
- Schema CollectionPage
- Thumbnail del primer video o imagen por defecto

### Video Individual (/videos/{slug})
- Meta tags personalizadas por video
- Schema VideoObject
- Thumbnail del video de YouTube
- Twitter player card para reproducción embebida

### Blog (/blog)
- Meta tags para listado de posts
- Schema Blog
- Cover del primer post o imagen por defecto

### Post Individual (/blog/{slug})
- Meta tags personalizadas por post
- Schema BlogPosting
- Cover del post
- Excerpt o contenido truncado como descripción
- Información de autor y fechas

## Imagen por Defecto

Se ha creado el directorio `/public/images/` que debe contener:

### og-default.jpg
**Requerido para**: Fallback cuando no hay imagen específica
**Dimensiones**: 1200 x 630 px
**Uso**: Compartir en redes sociales

### logo.png
**Requerido para**: Schema.org structured data
**Dimensiones**: 512 x 512 px (recomendado)
**Uso**: Logo en resultados de búsqueda

Ver `/public/images/README.md` para más detalles sobre cómo crear estas imágenes.

## Cómo Probar los Cambios

### 1. Validador de Open Graph (Facebook)
```
https://developers.facebook.com/tools/debug/
```
Ingresa la URL de tu sitio para ver cómo se verá al compartir en Facebook.

### 2. Twitter Card Validator
```
https://cards-dev.twitter.com/validator
```
Valida cómo se verán tus enlaces en Twitter.

### 3. LinkedIn Post Inspector
```
https://www.linkedin.com/post-inspector/
```
Valida cómo se verán tus enlaces en LinkedIn.

### 4. Rich Results Test (Google)
```
https://search.google.com/test/rich-results
```
Prueba el structured data de Schema.org.

### 5. Herramientas Generales
- **Metatags.io**: https://metatags.io/
- **OpenGraph.xyz**: https://www.opengraph.xyz/

## URLs de Prueba

### Ejemplos de URLs a probar:
```
https://tu-dominio.com/
https://tu-dominio.com/djs
https://tu-dominio.com/djs/nombre-del-dj
https://tu-dominio.com/eventos
https://tu-dominio.com/eventos/nombre-del-evento
https://tu-dominio.com/videos
https://tu-dominio.com/videos/nombre-del-video
https://tu-dominio.com/blog
https://tu-dominio.com/blog/titulo-del-post
```

## Limpieza de Caché

Cuando actualices contenido, es posible que necesites limpiar el caché de redes sociales:

### Facebook/WhatsApp
Usa el Facebook Debugger y haz clic en "Scrape Again"

### Twitter
Usa el Card Validator y valida la URL nuevamente

### LinkedIn
Usa el Post Inspector y vuelve a inspeccionar

## Mejores Prácticas

### Para Imágenes
1. **Dimensiones**: 1200 x 630 px para Open Graph
2. **Peso**: Menos de 1 MB
3. **Formato**: JPG o PNG
4. **Calidad**: 85-90% de compresión
5. **Contenido**: Texto legible, logo visible, estética del sitio

### Para Títulos
1. **Longitud**: 50-60 caracteres
2. **Incluir marca**: "Nombre | lapsique.media"
3. **Descriptivo**: Claro sobre el contenido
4. **Sin clickbait**: Honesto y directo

### Para Descripciones
1. **Longitud**: 150-160 caracteres
2. **Llamado a la acción**: Invita a hacer clic
3. **Keywords**: Incluye palabras clave relevantes
4. **Único**: Cada página debe tener descripción única

## Monitoreo

### Google Search Console
Monitorea:
- Impresiones y clics
- CTR (Click-Through Rate)
- Posición promedio
- Errores de structured data

### Analytics
Revisa:
- Tráfico de redes sociales
- Páginas más compartidas
- Bounce rate por fuente

## Próximos Pasos Recomendados

1. **Crear las imágenes requeridas** (`og-default.jpg` y `logo.png`)
2. **Probar todas las URLs** en los validadores mencionados
3. **Configurar Google Search Console** si aún no lo has hecho
4. **Crear un sitemap.xml** para mejorar indexación
5. **Implementar breadcrumbs** con Schema.org
6. **Agregar FAQPage schema** si tienes preguntas frecuentes

## Notas Técnicas

### Variables de Entorno
Asegúrate de tener configuradas:
```env
APP_URL=https://tu-dominio.com
LAPSIQUE_INSTAGRAM=https://www.instagram.com/lapsiquemedia/
LAPSIQUE_YOUTUBE_HANDLE=@LAPSIQUEMEDIA
```

### Archivos Modificados
```
resources/views/layouts/site.blade.php
resources/views/home.blade.php
resources/views/djs/index.blade.php
resources/views/djs/show.blade.php
resources/views/events/index.blade.php
resources/views/events/show.blade.php
resources/views/videos/index.blade.php
resources/views/videos/show.blade.php
resources/views/posts/index.blade.php
resources/views/posts/show.blade.php
public/images/README.md (nuevo)
```

## Soporte

Si encuentras problemas o tienes preguntas:
1. Verifica que todas las imágenes estén correctamente cargadas
2. Usa los validadores para identificar problemas específicos
3. Revisa los logs de Laravel para errores de servidor
4. Asegúrate de que APP_URL esté correctamente configurado

---

**Actualizado**: Diciembre 2025
**Versión**: 1.0

