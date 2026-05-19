# Fix: Seguimiento de Clicks en Emails

## Problema Identificado

El sistema de tracking de emails **NO estaba registrando los clicks** en la tabla `email_trackings`, a pesar de que:
- Los webhooks de Mailtrap estaban recibiendo correctamente los eventos de click
- Los `ContactLog` se actualizaban correctamente con el estado "clicked"
- La infraestructura de tracking estaba implementada

### Causa Raíz

El servicio `MailtrapEventsService` solo actualizaba el `ContactLog` cuando recibía eventos de clicks y opens, pero **NO actualizaba la tabla `email_trackings`** con los detalles importantes:
- URL clickeada
- Dirección IP del usuario
- User Agent del navegador
- Timestamps de clicks
- Contador de clicks

## Cambios Realizados

### 1. Actualización de `MailtrapEventsService.php`

#### a) Importación del modelo EmailTracking
```php
use App\Models\EmailTracking;
```

#### b) Mejora del método `handleOpened()`
Ahora registra los detalles de apertura en `email_trackings`:
```php
protected function handleOpened(array $event): void
{
    $log = $this->resolveContactLog($event);

    if (! $log) {
        return;
    }

    $log->markAsOpened();

    // También actualizar el EmailTracking con detalles de apertura
    $emailTracking = $this->resolveEmailTracking($log);

    if ($emailTracking) {
        $ip = $event['ip'] ?? null;
        $userAgent = $event['user_agent'] ?? null;

        $emailTracking->recordOpen($ip, $userAgent);

        Log::info('Email open processed from Mailtrap webhook', [
            'tracking_token' => $emailTracking->tracking_token,
            'customer_id' => $emailTracking->customer_id,
            'opens_count' => $emailTracking->fresh()->opens_count,
        ]);
    }
}
```

#### c) Mejora del método `handleClicked()`
Ahora registra los detalles del click en `email_trackings`:
```php
protected function handleClicked(array $event): void
{
    $log = $this->resolveContactLog($event);

    if (! $log) {
        return;
    }

    $log->markAsClicked();

    // También actualizar el EmailTracking con detalles del click
    $emailTracking = $this->resolveEmailTracking($log);

    if ($emailTracking) {
        $url = $event['url'] ?? null;
        $ip = $event['ip'] ?? null;
        $userAgent = $event['user_agent'] ?? null;

        if ($url) {
            $emailTracking->recordClick($url, $ip, $userAgent);

            Log::info('Email click processed from Mailtrap webhook', [
                'tracking_token' => $emailTracking->tracking_token,
                'customer_id' => $emailTracking->customer_id,
                'url' => $url,
                'clicks_count' => $emailTracking->fresh()->clicks_count,
            ]);
        }
    }
}
```

#### d) Nuevo método `resolveEmailTracking()`
```php
protected function resolveEmailTracking(ContactLog $contactLog): ?EmailTracking
{
    return EmailTracking::query()
        ->where('contact_log_id', $contactLog->id)
        ->first();
}
```

#### e) Mejora del método `resolveContactLog()`
Se agregaron más estados válidos para buscar ContactLogs:
```php
protected function resolveContactLog(array $event): ?ContactLog
{
    $messageId = $event['message_id'] ?? null;
    $email = $event['email'] ?? null;

    // Primero intentar por message_id (más preciso)
    if ($messageId) {
        $log = ContactLog::query()
            ->where('channel', 'email')
            ->where('metadata->mailtrap_message_id', $messageId)
            ->latest('created_at')
            ->first();

        if ($log) {
            return $log;
        }
    }

    // Si no se encuentra por message_id, buscar por email en estados válidos
    if ($email) {
        return ContactLog::query()
            ->where('channel', 'email')
            ->whereIn('status', ['pending', 'sent', 'delivered', 'opened', 'clicked'])
            ->whereHas('customer', fn ($query) => $query->where('email', $email))
            ->latest('created_at')
            ->first();
    }

    return null;
}
```

### 2. Corrección de relación en `ContactLog.php`

Se corrigió la relación `emailTracking()` de `belongsTo` a `hasOne`:

```php
use Illuminate\Database\Eloquent\Relations\HasOne;

public function emailTracking(): HasOne
{
    return $this->hasOne(EmailTracking::class);
}
```

Esto es correcto porque:
- La tabla `email_trackings` tiene la columna `contact_log_id`
- Un `ContactLog` tiene un `EmailTracking`
- Un `EmailTracking` pertenece a un `ContactLog`

## Resultados

### Antes del Fix
- Total EmailTrackings: 815
- Con clicks registrados: **0**

### Después del Fix
- Total EmailTrackings: 815
- Con clicks registrados: **2+** (y contando)

### Logs de Ejemplo
```
[2025-12-30 13:38:02] production.INFO: Email click processed from Mailtrap webhook 
{
    "tracking_token":"f2dedc2e-7423-4d06-9868-2050a91de109",
    "customer_id":113,
    "url":"https://lapsique.media/eventos",
    "clicks_count":1
}

[2025-12-30 13:39:33] production.INFO: Email click processed from Mailtrap webhook 
{
    "tracking_token":"5a64dbba-4b8f-48ad-8f79-dc010309ab31",
    "customer_id":103,
    "url":"https://test.com",
    "clicks_count":1
}
```

## Datos Registrados

Ahora cuando un usuario hace click en un email, se registra:

1. **En ContactLog:**
   - Estado actualizado a "clicked"
   - Timestamp de click (`clicked_at`)

2. **En EmailTracking:**
   - URL clickeada
   - Dirección IP del usuario
   - User Agent del navegador
   - Timestamp del click
   - Contador de clicks incrementado
   - Array de todos los links clickeados con timestamps
   - Tipo de dispositivo (mobile/desktop/tablet)

3. **En Customer:**
   - Lead score incrementado en 10 puntos (primer click)

## Verificación

Para verificar que el tracking funciona correctamente:

```bash
php artisan tinker

# Ver trackings con clicks
App\Models\EmailTracking::where('clicks_count', '>', 0)
    ->with('customer')
    ->latest('last_clicked_at')
    ->get(['id', 'customer_id', 'clicks_count', 'clicked_links', 'last_clicked_at']);
```

## Próximos Pasos

El sistema ahora está completamente funcional. Los clicks futuros de Mailtrap se procesarán automáticamente y se registrarán todos los detalles en la base de datos.

### Monitoreo

Puedes monitorear los clicks en:
1. **Logs:** `/storage/logs/laravel.log` - buscar "Email click processed"
2. **Base de datos:** Tabla `email_trackings` - columnas `clicks_count`, `clicked_links`
3. **Filament:** Panel de administración - sección de Email Trackings

### Webhooks de Mailtrap

Asegúrate de que el webhook de Mailtrap esté configurado correctamente:
- URL: `https://tu-dominio.com/webhooks/mailtrap/events`
- Eventos: `click`, `open`, `delivery`, `bounce`, etc.

## Notas Técnicas

- Los eventos de Mailtrap se procesan de forma asíncrona
- Mailtrap reescribe automáticamente los enlaces en los emails con su propio sistema de tracking
- El sistema soporta tanto el tracking directo (rutas `/track/email/{token}/click`) como el tracking vía webhooks de Mailtrap
- El tracking vía webhooks es más confiable porque Mailtrap maneja la redirección

