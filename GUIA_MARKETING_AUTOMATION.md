# 🚀 Guía de Implementación - Sistema de Marketing Automation

## 📋 Índice

1. [Requisitos Previos](#requisitos-previos)
2. [Configuración Inicial](#configuración-inicial)
3. [Migraciones de Base de Datos](#migraciones-de-base-de-datos)
4. [Configuración de Servicios Externos](#configuración-de-servicios-externos)
5. [Implementación del Popup](#implementación-del-popup)
6. [Sistema de Queues](#sistema-de-queues)
7. [Testing](#testing)
8. [Monitoreo](#monitoreo)

---

## ✅ Requisitos Previos

- PHP 8.2+
- Laravel 12
- Composer
- Node.js & NPM
- SQLite/MySQL
- Cuenta de Mailtrap
- Cuenta de Twilio (opcional)

---

## ⚙️ Configuración Inicial

### 1. Instalar Dependencias

```bash
# Backend
composer install

# Frontend
npm install
```

### 2. Variables de Entorno

Actualiza tu archivo `.env` con las siguientes variables:

```env
# App
APP_NAME=Lapsique
APP_URL=http://localhost

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/lapsique-web/database/database.sqlite

# Queue
QUEUE_CONNECTION=database

# Mail (Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@lapsique.com
MAIL_FROM_NAME="${APP_NAME}"

# Twilio (opcional)
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_SMS_FROM=+1234567890
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
TWILIO_VOICE_FROM=+1234567890
TWILIO_VERIFY_SID=your_verify_sid
```

---

## 🗄️ Migraciones de Base de Datos

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará las siguientes tablas:

- ✅ `customers` (mejorada con campos de marketing)
- ✅ `guest_list_entries` (simplificada)
- ✅ `contact_logs` (nuevo - historial de comunicaciones)
- ✅ `email_trackings` (nuevo - tracking de emails)
- ✅ `campaigns` (nuevo - campañas de marketing)
- ✅ `automations` (nuevo - flujos automatizados)
- ✅ `jobs` (queue de Laravel)
- ✅ `failed_jobs` (jobs fallidos)

### 2. Verificar Migraciones

```bash
php artisan migrate:status
```

Todas deben aparecer como "Ran".

---

## 🔧 Configuración de Servicios Externos

### Mailtrap

#### Desarrollo (Sandbox)

1. Crea una cuenta en [mailtrap.io](https://mailtrap.io)
2. Ve a "Email Testing" → "My Inbox"
3. Copia las credenciales SMTP a tu `.env`
4. Configura el webhook:
   - URL: `https://tu-dominio.com/webhooks/mailtrap/events`
   - Eventos: `delivered`, `opened`, `clicked`, `bounced`, `complained`

#### Producción (Live)

1. Ve a "Email Sending" → "Sending Domains"
2. Agrega y verifica tu dominio
3. Actualiza `.env` con:
   ```env
   MAIL_HOST=live.smtp.mailtrap.io
   ```

### Twilio (Opcional)

1. Crea una cuenta en [twilio.com](https://twilio.com)
2. Ve al Dashboard y copia:
   - Account SID
   - Auth Token
3. Compra un número de teléfono para SMS
4. Para WhatsApp:
   - Ve a "Messaging" → "Try it out" → "Send a WhatsApp message"
   - Sigue las instrucciones para conectar tu número de WhatsApp Business
5. Configura webhooks:
   - SMS Status: `https://tu-dominio.com/webhooks/twilio/sms/status`
   - WhatsApp Status: `https://tu-dominio.com/webhooks/twilio/whatsapp/status`

---

## 🎨 Implementación del Popup

### 1. Agregar el Popup al Layout Principal

Edita `/resources/views/layouts/app.blade.php` y agrega antes del cierre del `</body>`:

```blade
{{-- Lead Capture Popup --}}
<x-lead-capture-popup />
```

### 2. Configurar Triggers

El popup se dispara automáticamente por:

- **Exit Intent**: Cuando el mouse sale del viewport
- **Scroll**: Al llegar al 50% de la página
- **Tiempo**: Después de 30 segundos en la página

### 3. Personalización

Edita `/resources/views/components/lead-capture-popup.blade.php` para:

- Cambiar colores
- Modificar campos del formulario
- Ajustar triggers
- Cambiar frecuencia (default: 7 días)

---

## ⚡ Sistema de Queues

### 1. Configurar Workers

#### Desarrollo

En una terminal separada:

```bash
php artisan queue:work --queue=high,default,low,notifications --tries=3
```

O usar el script npm:

```bash
composer run dev
# Esto inicia: server, queue, logs (pail) y vite simultáneamente
```

#### Producción

**Supervisor** (Recomendado):

Crea `/etc/supervisor/conf.d/lapsique-worker.conf`:

```ini
[program:lapsique-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lapsique-web/artisan queue:work database --queue=high,default,low,notifications --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/lapsique-web/storage/logs/worker.log
stopwaitsecs=3600
```

Luego:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start lapsique-worker:*
```

### 2. Prioridades de Queue

El sistema usa 4 colas con diferentes prioridades:

| Cola | Prioridad | Uso | Workers |
|------|-----------|-----|---------|
| `high` | Alta | Emails transaccionales (bienvenida, confirmaciones) | 3 |
| `notifications` | Alta | Notificaciones tiempo real | 3 |
| `default` | Media | Marketing, recordatorios, campañas | 5 |
| `low` | Baja | Tracking, analytics, scoring | 2 |

### 3. Monitorear Queues

```bash
# Ver trabajos pendientes
php artisan queue:monitor

# Ver trabajos fallidos
php artisan queue:failed

# Reintentar trabajos fallidos
php artisan queue:retry all

# Limpiar trabajos fallidos
php artisan queue:flush
```

---

## 🧪 Testing

### 1. Probar Captura de Leads

Abre tu sitio en modo incógnito y:

1. Navega por una página
2. Espera 30 segundos o scroll al 50%
3. El popup debería aparecer
4. Llena el formulario con datos de prueba
5. Revisa Mailtrap para el email de bienvenida

### 2. Probar Guest List

1. Ve a la página de un evento
2. Regístrate en la guest list
3. Verifica que recibas 2 emails:
   - Email de bienvenida (si eres nuevo)
   - Confirmación del evento

### 3. Probar Tracking

1. Abre un email de prueba en Mailtrap
2. Haz click en "View" para simular apertura
3. Haz click en algún link del email
4. Verifica en la base de datos:

```sql
-- Ver trackings de email
SELECT * FROM email_trackings WHERE customer_id = 1;

-- Ver logs de contacto
SELECT * FROM contact_logs WHERE customer_id = 1;

-- Ver lead score
SELECT name, email, lead_score, lifecycle_stage FROM customers WHERE id = 1;
```

### 4. Probar SMS/WhatsApp (si configuraste Twilio)

```php
// En tinker: php artisan tinker
$customer = \App\Models\Customer::first();

// SMS
dispatch(new \App\Jobs\SendSMSJob($customer, 'Hola! Este es un mensaje de prueba.'));

// WhatsApp
dispatch(new \App\Jobs\SendWhatsAppJob($customer, 'Hola! Este es un mensaje de WhatsApp de prueba.'));
```

---

## 📊 Monitoreo

### 1. Logs

Todos los logs están en `/storage/logs/`:

```bash
# Ver logs en tiempo real
php artisan pail

# Ver logs del día
tail -f storage/logs/laravel.log

# Ver logs de workers
tail -f storage/logs/worker.log
```

### 2. Métricas Importantes

#### Base de Datos

```sql
-- Total de customers por status
SELECT status, COUNT(*) FROM customers GROUP BY status;

-- Lead score promedio
SELECT AVG(lead_score) FROM customers;

-- Emails abiertos hoy
SELECT COUNT(*) FROM email_trackings 
WHERE DATE(first_opened_at) = CURDATE();

-- Tasa de apertura
SELECT 
    (SELECT COUNT(*) FROM email_trackings WHERE opens_count > 0) * 100.0 / 
    (SELECT COUNT(*) FROM contact_logs WHERE channel = 'email' AND status = 'sent')
AS open_rate;
```

#### Queues

```bash
# Tamaño de cada queue
php artisan queue:monitor database:high,database:default,database:low

# Ver failed jobs
php artisan queue:failed
```

### 3. Panel de Administración (Filament)

Accede a `/admin` y revisa:

- **Customers**: Lista de todos los contactos
- **Campaigns**: Campañas de marketing
- **Contact Logs**: Historial de comunicaciones
- **Queue Monitor**: Estado de las colas (próximamente)

---

## 🔄 Flujos Automatizados

### Welcome Flow (Implementado)

**Trigger**: Nuevo signup desde popup o guest list

**Pasos**:
1. Crear Customer
2. → SendWelcomeEmailJob (delay: 5s)
3. → Track opens/clicks
4. → Update lead score

### Event Registration Flow (Implementado)

**Trigger**: Registro en guest list

**Pasos**:
1. Find/Create Customer
2. Create GuestListEntry
3. → SendEventConfirmationJob (delay: 10s)
4. → Track engagement
5. → Update lead score

### Event Reminder Flow (Listo para usar)

**Trigger**: 24h antes del evento (ejecutar manualmente o con cron)

**Cómo ejecutar**:

```php
// En tinker o en un comando
$event = \App\Models\Event::find(1);
dispatch(new \App\Jobs\SendEventReminderJob($event, hoursBeforeEvent: 24));
```

**Pasos**:
1. Obtener todos los registrados
2. → Enviar email recordatorio
3. → Enviar SMS (si opt-in)
4. → Enviar WhatsApp (si opt-in)

### Crear un Cron Job para Recordatorios

Agrega a `/app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Enviar recordatorios 24h antes
    $schedule->call(function () {
        $tomorrow = now()->addDay();
        
        $events = \App\Models\Event::whereDate('starts_at', $tomorrow->toDateString())->get();
        
        foreach ($events as $event) {
            dispatch(new \App\Jobs\SendEventReminderJob($event, 24));
        }
    })->dailyAt('12:00'); // Ejecutar a las 12pm cada día
}
```

Luego en producción:

```bash
# Agregar al crontab
crontab -e

# Agregar esta línea
* * * * * cd /var/www/lapsique-web && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 Próximos Pasos

### Panel de Queue Monitoring en Filament

1. Se implementará un recurso en Filament para:
   - Ver estado de queues en tiempo real
   - Iniciar/detener workers
   - Ver jobs fallidos
   - Reintentar jobs

### Campañas de Marketing

1. Crear campaña desde Filament
2. Seleccionar audiencia (filtros)
3. Diseñar contenido
4. Programar envío
5. Ver métricas en tiempo real

### A/B Testing

1. Crear variantes de emails
2. Dividir audiencia
3. Enviar versiones A y B
4. Medir resultados
5. Elegir ganador automáticamente

---

## ❓ Troubleshooting

### El popup no aparece

1. Verifica que Alpine.js está cargado
2. Revisa la consola del navegador por errores
3. Verifica que el componente está incluido en el layout
4. Limpia localStorage: `localStorage.removeItem('lapsique_popup_seen')`

### Los emails no se envían

1. Verifica las credenciales de Mailtrap en `.env`
2. Verifica que el queue worker está corriendo
3. Revisa failed jobs: `php artisan queue:failed`
4. Revisa logs: `storage/logs/laravel.log`

### Los trabajos quedan en pending

1. Asegúrate de que el worker está corriendo
2. Verifica la tabla `jobs`: debe estar decreciendo
3. Reinicia el worker: `php artisan queue:restart`

### El tracking no funciona

1. Verifica que las rutas están registradas: `php artisan route:list | grep track`
2. Revisa que el token se está generando correctamente
3. Verifica que el pixel está en el email
4. Revisa logs de `ProcessEmailOpenJob`

---

## 📞 Soporte

Para dudas o problemas:

1. Revisa los logs en `/storage/logs/`
2. Consulta la arquitectura en `MARKETING_AUTOMATION_ARCHITECTURE.md`
3. Revisa el código en `/app/Jobs/`, `/app/Models/`, `/app/Services/`

---

## 🎉 ¡Todo Listo!

Tu sistema de marketing automation está configurado. Ahora puedes:

✅ Capturar leads desde el popup  
✅ Registrar personas en guest lists  
✅ Enviar emails automáticos con tracking  
✅ Enviar SMS y WhatsApp (con Twilio)  
✅ Hacer seguimiento de engagement  
✅ Calcular lead scores  
✅ Segmentar tu audiencia  
✅ Crear campañas de marketing  

**¡A crecer la comunidad! 🎧🎉**

