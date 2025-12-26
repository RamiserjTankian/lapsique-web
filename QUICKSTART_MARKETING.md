# 🚀 Quick Start - Marketing Automation

## Setup en 5 Minutos

### 1. Instalar & Configurar

```bash
# Instalar dependencias
composer install

# Configurar .env (Mailtrap obligatorio, Twilio opcional)
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
QUEUE_CONNECTION=database
```

### 2. Migrar Base de Datos

```bash
php artisan migrate
```

### 3. Iniciar Queue Worker

```bash
php artisan queue:work --queue=high,default,low --tries=3
```

O usar el script de desarrollo:

```bash
composer run dev
# Esto inicia: server + queue + logs + vite
```

### 4. Agregar Popup al Layout

En `resources/views/layouts/app.blade.php` antes del `</body>`:

```blade
<x-lead-capture-popup />
```

## ✅ Testing Rápido

### Popup

1. Abre tu sitio
2. Espera 30 segundos o scroll al 50%
3. Completa el formulario
4. ✅ Email de bienvenida en Mailtrap

### Guest List

1. Regístrate en un evento
2. ✅ Email de confirmación en Mailtrap
3. ✅ Customer creado en DB
4. ✅ Lead score asignado

### Verificar

```sql
-- Ver customers
SELECT id, name, email, lead_score, lifecycle_stage, source FROM customers;

-- Ver emails enviados
SELECT * FROM contact_logs WHERE channel = 'email' ORDER BY created_at DESC LIMIT 10;

-- Ver tracking
SELECT * FROM email_trackings ORDER BY created_at DESC LIMIT 10;
```

## 📊 Features Disponibles

✅ **Popup de Captura** - Auto-trigger inteligente  
✅ **Email Tracking** - Opens & clicks  
✅ **Lead Scoring** - Automático  
✅ **Guest Lists** - Integrado con eventos  
✅ **Welcome Emails** - Automático  
✅ **Event Confirmations** - Automático  
✅ **SMS/WhatsApp** - Con Twilio (opcional)  
✅ **Queue System** - 4 colas priorizadas  
✅ **Contact Logs** - Historial completo  

## 🎯 Próximos Pasos

1. Configurar Supervisor para workers en producción
2. Implementar campañas de marketing desde Filament
3. Configurar cron para recordatorios de eventos
4. Integrar Twilio para SMS/WhatsApp

## 📚 Documentación Completa

Ver `GUIA_MARKETING_AUTOMATION.md` para:
- Configuración detallada
- Troubleshooting
- Flujos automatizados
- Monitoreo y métricas

---

**¿Dudas?** Revisa `MARKETING_AUTOMATION_ARCHITECTURE.md` para la arquitectura completa.

