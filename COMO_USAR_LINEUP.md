# 🎵 Cómo Usar el Lineup de Eventos

## ✅ Problema Resuelto

Se corrigió el problema donde el lineup no se guardaba. Ahora todo funciona correctamente.

---

## 📝 Cómo Agregar el Lineup a un Evento

### Paso 1: Accede al Dashboard
1. Entra a `/admin` (tu dashboard de Filament)
2. Ve a la sección **"Eventos"**
3. Selecciona el evento al que quieres agregar lineup (o crea uno nuevo)

### Paso 2: Scroll al Campo "Line up"
1. Dentro del formulario del evento, baja hasta encontrar la sección **"Line up"**
2. Es un campo desplegable/colapsable

### Paso 3: Agregar DJs
1. Click en el botón **"Agregar"** (ícono +)
2. Se abrirá una nueva fila con 4 campos:
   - **DJ**: Selecciona el DJ de la lista (campo obligatorio)
   - **Rol**: Elige entre:
     - 🌟 **Headliner** - Artista principal
     - 🎵 **Warmup** - Artista de soporte/apertura
     - 🎧 **Local** - Artista local
   - **Horario**: Añade el horario del DJ set (ej: "11:00 PM - 12:30 AM") - NUEVO ⏰
   - **Activo**: Toggle para incluir/excluir sin borrar

### Paso 4: Ordenar el Lineup
1. **Arrastra** las filas para reordenar (usa el ícono de 6 puntos a la izquierda)
2. El orden define cómo aparecen en la página del evento

### Paso 5: Guardar
1. Click en **"Guardar"** al final del formulario
2. ¡Listo! El lineup se guardará automáticamente

---

## 🎨 Cómo se Visualiza en la Página

### Headliners
- Se muestran en **tarjetas grandes** (3 columnas en desktop)
- Con badge ⭐ "Headliner"
- Foto más grande
- Nombre en negrita
- **Horario del DJ set** (si lo añadiste) 🕒
- Instagram handle visible
- Border especial en blanco

### Warmups/Support
- Se muestran en **carrusel horizontal** deslizable
- Con badge 🎵 "Warmup"
- Tarjetas más pequeñas
- **Horario del DJ set** (si lo añadiste) 🕒
- Scroll horizontal para ver todos

### Orden de Aparición
1. **Primero**: Todos los Headliners (en el orden que definiste)
2. **Después**: Todos los Warmups (en el orden que definiste)

---

## 💡 Tips y Mejores Prácticas

### 1. Orden del Lineup
- **Headliners**: Ponlos en orden de importancia (el más importante primero)
- **Warmups**: Ponlos en el orden en que tocarán (primero en tocar = primero en la lista)

### 2. Roles Correctos
- Usa **Headliner** solo para los artistas principales (1-3 artistas máximo)
- Usa **Warmup** para todos los demás (pueden ser muchos)
- Usa **Local** para artistas de la ciudad/región

### 3. Toggle "Activo"
- Si necesitas **ocultar temporalmente** un DJ sin borrarlo, desactiva el toggle
- Útil cuando un artista cancela pero podría regresar

### 4. No Duplicar DJs
- El sistema automáticamente evita duplicados
- Si agregas el mismo DJ dos veces, solo se guardará una vez

### 5. Fotos de DJs
- Asegúrate que todos los DJs tengan fotos de perfil subidas
- Mejora la presentación visual del lineup
- Sube fotos en **Filament → DJs → Editar DJ → Profile**

### 6. Horarios de DJ Sets (Timetable) 🕒
- Añade horarios para crear una **narrativa completa** del evento
- Formato sugerido: "11:00 PM - 12:30 AM" o "23:00 - 00:30"
- Ordena los DJs según su horario de presentación
- Los horarios ayudan a los asistentes a planificar su llegada
- **Opcional**: Puedes dejarlo vacío si aún no tienes los horarios definidos
- Los horarios se muestran con un ícono de reloj 🕒 en la página del evento

---

## 🔧 Cambios Técnicos Realizados

### Base de Datos
- ✅ Agregadas columnas `role` y `position` a tabla `dj_event`
- ✅ Agregada columna `time_slot` para horarios de DJ sets (Dic 2025)
- ✅ Índice creado para mejor performance
- ✅ Valores por defecto configurados

### Código
- ✅ Mejorado método `syncLineup()` en `CreateEvent` y `EditEvent`
- ✅ Agregado `mutateFormDataBeforeSave/Create` para capturar datos
- ✅ Mejorada hidratación del formulario
- ✅ Agregado `columnSpanFull()` al Repeater

### Frontend
- ✅ Separación visual entre Headliners y Warmups
- ✅ Badges de colores
- ✅ Ordenamiento automático por role y position
- ✅ Efectos hover mejorados

---

## 📸 Ejemplo de Uso

```
EVENTO: "Techno Night 2025"

Lineup configurado así:

┌──────────────────────────────────────┐
│ Line up                               │
├──────────────────────────────────────┤
│ 1. DJ: AMELIE LENS                   │
│    Rol: Headliner 🌟                 │
│    Horario: 1:00 AM - 3:00 AM        │
│    Activo: ✅                         │
├──────────────────────────────────────┤
│ 2. DJ: CHARLOTTE DE WITTE            │
│    Rol: Headliner 🌟                 │
│    Horario: 11:00 PM - 1:00 AM       │
│    Activo: ✅                         │
├──────────────────────────────────────┤
│ 3. DJ: LOCAL ARTIST 1                │
│    Rol: Warmup 🎵                    │
│    Horario: 9:00 PM - 10:00 PM       │
│    Activo: ✅                         │
├──────────────────────────────────────┤
│ 4. DJ: LOCAL ARTIST 2                │
│    Rol: Warmup 🎵                    │
│    Horario: 10:00 PM - 11:00 PM      │
│    Activo: ✅                         │
└──────────────────────────────────────┘

Se visualizará en la página así:

⭐ HEADLINERS
[AMELIE LENS]              [CHARLOTTE DE WITTE]
🕒 1:00 AM - 3:00 AM       🕒 11:00 PM - 1:00 AM
(tarjetas grandes)

🎵 WARMUP / SUPPORT
[LOCAL 1]                  → [LOCAL 2] → 
🕒 9:00 PM - 10:00 PM        🕒 10:00 PM - 11:00 PM
(carrusel horizontal)
```

---

## ❓ Troubleshooting

### El lineup no aparece en la página
1. Verifica que hayas guardado el evento
2. Limpia caché: `php artisan cache:clear`
3. Recarga la página del evento

### Los DJs aparecen en orden incorrecto
1. En el dashboard, arrastra las filas para reordenar
2. Guarda de nuevo el evento
3. Los headliners SIEMPRE aparecen primero

### Un DJ aparece duplicado
1. El sistema previene duplicados automáticamente
2. Si ves duplicados, elimina uno y guarda

### El toggle "Activo" no funciona
1. Si está desactivado, el DJ NO aparecerá en la página
2. Activalo de nuevo para que aparezca

---

## 🎉 ¡Listo!

Ahora puedes:
- ✅ Agregar DJs al lineup
- ✅ Ordenarlos como quieras
- ✅ Distinguir entre Headliners y Warmups
- ✅ Ver el lineup bellamente en la página del evento

**¡A crear eventos increíbles! 🎵🔥**


