# 🎨 Configuración de Marca de Agua - Portfolio

## ✅ ¿Qué se implementó?

Se agregó funcionalidad para aplicar automáticamente una marca de agua con el logo de Lapsique Media a todas las fotografías del portafolio cuando se suben.

## 📋 Requisitos

### 1. Crear el Logo para Marca de Agua

Necesitas crear un archivo de logo optimizado para marca de agua:

**Ubicación:** `/public/images/logo-watermark.png`

**Especificaciones recomendadas:**
- **Formato:** PNG con transparencia
- **Dimensiones:** 300-400px de ancho (proporcional)
- **Fondo:** Transparente
- **Color:** Blanco o con opacidad para que se vea bien sobre cualquier imagen
- **Estilo:** Logo simplificado que se vea bien en pequeño

**Opciones de diseño:**
- Logo completo en blanco con transparencia
- Solo texto "lapsique.media" en fuente elegante
- Icono/símbolo del logo

## ⚙️ Configuración Actual

La marca de agua se aplica automáticamente a:

### Conversiones con Marca de Agua:
1. **`thumb`** (600x600px)
   - Posición: Esquina inferior derecha
   - Padding: 20px desde los bordes
   - Opacidad: 60%
   - Ancho: 120px

2. **`large`** (máx. 1600x1200px)
   - Posición: Esquina inferior derecha
   - Padding: 30px desde los bordes
   - Opacidad: 50%
   - Ancho: 150px

3. **`watermarked`** (tamaño original)
   - Posición: Esquina inferior derecha
   - Padding: 30px desde los bordes
   - Opacidad: 50%
   - Ancho: 150px

## 🔄 Cómo Funciona

1. **Al subir una foto:** Se procesa automáticamente y se generan las conversiones con marca de agua
2. **Solo imágenes:** Los videos NO se procesan con marca de agua
3. **Procesamiento automático:** Se ejecuta al momento de subir (nonQueued)
4. **Fallback seguro:** Si no existe el logo, las imágenes se procesan normalmente sin marca de agua

## 📊 Impacto en el Sitio

### ✅ Ventajas:
- **Protección de contenido:** Las fotos tienen marca de agua visible
- **Branding consistente:** Todas las imágenes muestran tu marca
- **Automático:** No requiere acción manual por foto
- **No afecta videos:** Solo se aplica a imágenes

### ⚠️ Consideraciones:

1. **Rendimiento:**
   - **Tiempo de procesamiento:** Cada foto tarda ~1-3 segundos más en subirse (dependiendo del tamaño)
   - **Almacenamiento:** Se generan múltiples versiones (original, thumb, large, watermarked) = ~3x más espacio
   - **CPU:** Procesamiento de imágenes consume recursos del servidor

2. **Almacenamiento:**
   - **Antes:** 1 foto = ~2-5MB
   - **Después:** 1 foto = ~6-15MB (con todas las conversiones)
   - **Recomendación:** Monitorear espacio en disco

3. **Experiencia de Usuario:**
   - **Subida:** Puede tardar un poco más (especialmente fotos grandes)
   - **Visual:** La marca de agua es discreta pero visible
   - **Calidad:** No afecta la calidad de la imagen original

4. **Fotos Existentes:**
   - Las fotos ya subidas NO se actualizan automáticamente
   - Necesitas regenerar las conversiones manualmente o re-subirlas

## 🔧 Personalización

Si quieres ajustar la marca de agua, edita `/app/Models/PortfolioItem.php`:

```php
->watermarkPosition('bottom-right')  // Posición: top-left, top-right, bottom-left, bottom-right, center
->watermarkPadding(30, 30)          // Padding desde los bordes (x, y)
->watermarkOpacity(50)               // Opacidad: 0-100 (50 = 50%)
->watermarkWidth(150, Unit::PIXELS) // Ancho del logo en píxeles
```

## 🚀 Regenerar Conversiones Existentes

Si ya tienes fotos subidas y quieres agregarles marca de agua:

```bash
php artisan media-library:regenerate --only-missing
```

O para regenerar todas:

```bash
php artisan media-library:regenerate
```

## 📝 Notas Importantes

1. **El logo debe existir:** Si no existe `/public/images/logo-watermark.png`, las imágenes se procesan sin marca de agua (sin errores)

2. **Solo imágenes:** Los videos (MP4, MOV, WEBM) NO se procesan con marca de agua

3. **Conversiones:** Las conversiones se generan al momento de subir, no después

4. **Espacio en disco:** Asegúrate de tener suficiente espacio para almacenar múltiples versiones de cada imagen

5. **Rendimiento del servidor:** Si subes muchas fotos a la vez, puede haber un impacto temporal en el servidor

## 🎯 Próximos Pasos

1. ✅ Crear el logo en `/public/images/logo-watermark.png`
2. ✅ Probar subiendo una foto nueva
3. ⚠️ Regenerar conversiones de fotos existentes (opcional)
4. 📊 Monitorear espacio en disco y rendimiento

---

**¿Necesitas ayuda?** Revisa la documentación de Spatie Media Library: https://spatie.be/docs/laravel-medialibrary
