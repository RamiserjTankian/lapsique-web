# ✅ Verificación de Imágenes en WhatsApp

**Estado**: Corregido

## 🔧 Causa del Problema
WhatsApp requiere que la etiqueta `og:image` tenga una **URL absoluta** (que empiece con `http://` o `https://`). El sistema estaba generando URLs relativas (ej: `/storage/imagen.jpg`), lo que impedía que WhatsApp cargara la imagen.

## ✅ Solución Aplicada
Se actualizó el código en todas las páginas de detalle (Eventos, DJs, Videos, Blog) para forzar que la URL de la imagen sea absoluta.

## 🧪 Cómo Probar

**Importante**: WhatsApp guarda en caché la información de los enlaces. Si ya intentaste compartir un enlace y falló, WhatsApp recordará ese fallo por un tiempo.

Para probar que funciona ahora:

1. **Usa el depurador de Facebook** (WhatsApp usa el mismo sistema):
   https://developers.facebook.com/tools/debug/
   - Pega tu enlace
   - Haz clic en "Debug"
   - Si no sale la imagen, haz clic en "Scrape Again"

2. **Prueba con una URL "nueva"**:
   Si tu evento es `lapsique.media/eventos/fiesta-techno`, intenta compartir en WhatsApp:
   `lapsique.media/eventos/fiesta-techno?v=2`
   (Al agregar `?v=2` al final, WhatsApp lo tratará como un enlace nuevo y buscará la imagen de nuevo).

## 📂 Archivos Modificados
- `resources/views/events/show.blade.php`
- `resources/views/djs/show.blade.php`
- `resources/views/posts/show.blade.php`
- `resources/views/videos/show.blade.php`

## 🖼️ Requisitos de Imagen para WhatsApp
Para asegurar que la imagen siempre se vea bien:
- **Tamaño**: Menos de 300KB
- **Formato**: JPG o PNG
- **Dimensiones**: 1200 x 630 píxeles (recomendado) o cuadrado (1:1)

Si alguna imagen específica sigue sin cargar, verifica que cumpla con estos requisitos.

