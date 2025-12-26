# Arquitectura de Marketing Automation - Lapsique

## 📋 Análisis de la Situación Actual

### Problemas Identificados

1. **Duplicación de Datos**: `GuestListEntry` almacena información redundante que ya existe en `Customer`
2. **Falta de Tracking**: No hay seguimiento de interacciones (email opens, SMS, llamadas)
3. **Sin Automatización**: Los envíos de notificaciones son síncronos, sin queues
4. **No hay Logs de Contacto**: Imposible rastrear el historial de comunicaciones
5. **Popup sin Funcionalidad**: No existe un sistema de captura de leads desde el frontend
6. **Sin Integración Twilio**: No hay capacidad de SMS/WhatsApp/llamadas automatizadas
7. **Email Tracking Básico**: No se rastrean opens, clicks, ni conversiones

### Estructura Actual

```
Customer (leads/clientes)
  ├── name, email, phone, instagram
  ├── source (origen del contacto)
  └── last_interaction_at

GuestListEntry (inscripciones a eventos)
  ├── DUPLICA: full_name, email, whatsapp, instagram
  ├── event_id
  └── status
```

## 🎯 Arquitectura Propuesta

### 1. Modelo de Datos Unificado

#### A. Customer (Lead/Cliente Central)
```
customers
  - id
  - name
  - email (unique, indexed)
  - phone
  - instagram_handle
  - whatsapp
  - status: enum(lead, prospect, customer, inactive)
  - source: enum(popup, guestlist, manual, api, referral)
  - tags: json (segmentación)
  - metadata: json (campos personalizados)
  - subscribed_newsletter: boolean
  - subscribed_sms: boolean
  - subscribed_whatsapp: boolean
  - email_verified_at
  - phone_verified_at
  - last_interaction_at
  - lifecycle_stage: enum(subscriber, lead, mql, sql, customer, evangelist)
  - lead_score: integer
  - utm_source, utm_medium, utm_campaign (tracking)
  - ip_address
  - user_agent
  - created_at, updated_at, deleted_at
```

#### B. GuestListEntry (Simplificado)
```
guest_list_entries
  - id
  - customer_id (FK) ← ÚNICA fuente de verdad
  - event_id (FK)
  - status: enum(pending, confirmed, attended, cancelled, no_show)
  - check_in_at
  - gender (para aforo/estadísticas)
  - notes (específicas del evento)
  - invited_by (referral tracking)
  - plus_ones: integer
  - created_at, updated_at, deleted_at
```

#### C. ContactLog (Nuevo - Historial Completo)
```
contact_logs
  - id
  - customer_id (FK)
  - event_id (FK nullable) - si está relacionado a un evento
  - channel: enum(email, sms, whatsapp, call, popup, guestlist, manual)
  - type: enum(notification, marketing, transactional, reminder, followup)
  - subject
  - message
  - metadata: json (detalles específicos del canal)
  - status: enum(pending, sent, delivered, opened, clicked, bounced, failed)
  - sent_at
  - delivered_at
  - opened_at
  - clicked_at
  - failed_at
  - error_message
  - campaign_id (FK nullable)
  - automation_id (FK nullable)
  - created_by (user_id)
  - created_at, updated_at
```

#### D. EmailTracking (Nuevo - Tracking Detallado)
```
email_trackings
  - id
  - contact_log_id (FK)
  - customer_id (FK)
  - tracking_token: uuid (único para cada email)
  - opens_count: integer
  - first_opened_at
  - last_opened_at
  - clicks_count: integer
  - first_clicked_at
  - last_clicked_at
  - clicked_links: json (URLs clickeadas)
  - user_agent
  - ip_address
  - location: json (geolocalización)
  - device_type: enum(desktop, mobile, tablet, unknown)
  - created_at, updated_at
```

#### E. Campaigns (Nuevo - Campañas de Marketing)
```
campaigns
  - id
  - name
  - type: enum(email, sms, whatsapp, multi_channel)
  - status: enum(draft, scheduled, active, paused, completed)
  - target_audience: json (filtros de segmentación)
  - starts_at
  - ends_at
  - total_recipients: integer
  - sent_count: integer
  - delivered_count: integer
  - opened_count: integer
  - clicked_count: integer
  - conversion_count: integer
  - metadata: json
  - created_by
  - created_at, updated_at, deleted_at
```

#### F. Automations (Nuevo - Flujos Automatizados)
```
automations
  - id
  - name
  - trigger_type: enum(signup, event_registration, event_reminder, abandoned_cart, birthday, anniversary)
  - trigger_config: json
  - status: enum(active, paused, archived)
  - steps: json (flujo de acciones)
  - total_triggered: integer
  - total_completed: integer
  - created_at, updated_at
```

### 2. Sistema de Jobs (Queue)

#### Jobs Prioritarios

```php
// Alta prioridad - Tiempo real
SendWelcomeEmailJob (queue: high)
SendEventConfirmationJob (queue: high)
SendTransactionalSmsJob (queue: high)

// Media prioridad - Marketing
SendMarketingEmailJob (queue: default)
SendEventReminderJob (queue: default)
ProcessCampaignJob (queue: default)

// Baja prioridad - Procesamiento
ProcessEmailOpenJob (queue: low)
ProcessEmailClickJob (queue: low)
UpdateLeadScoreJob (queue: low)
SyncCustomerDataJob (queue: low)
```

#### Arquitectura de Queues

```
Queues:
  - high: 3 workers, timeout 60s
  - default: 5 workers, timeout 120s
  - low: 2 workers, timeout 180s
  - notifications: 3 workers (dedicado a notificaciones)
```

### 3. Integración con Servicios Externos

#### A. Mailtrap
```
Desarrollo/Staging:
  - MAIL_MAILER=smtp
  - MAIL_HOST=sandbox.smtp.mailtrap.io
  - MAIL_PORT=2525
  - Webhook para tracking de opens/clicks

Producción:
  - MAIL_HOST=live.smtp.mailtrap.io
  - Email Tracking API
  - Bounce/Complaint handling
```

#### B. Twilio
```
Servicios:
  - SMS (notificaciones transaccionales)
  - WhatsApp Business (campañas marketing)
  - Voice (llamadas automatizadas)
  - Verify API (verificación 2FA)

Webhooks:
  - /webhooks/twilio/sms/status
  - /webhooks/twilio/whatsapp/status
  - /webhooks/twilio/voice/status
```

### 4. Frontend - Popup de Captura de Leads

#### Triggers del Popup
```javascript
1. Scroll 50% de la página
2. Exit intent (mouse sale del viewport)
3. Tiempo en página > 30 segundos
4. Después de ver un video
5. Intento de cerrar tab (beforeunload)
6. Click en "Saber más" / CTAs específicos

Configuración:
  - Frecuencia: 1 vez cada 7 días (cookie)
  - No mostrar si ya es customer
  - A/B testing de contenido
```

#### Datos a Capturar
```
Campos del Popup:
  - name (required)
  - email (required)
  - phone/whatsapp (optional)
  - instagram_handle (optional)
  - interests: tags[] (checkboxes)
  - source: hidden (popup)
  - utm_*: hidden (tracking)
  - current_page: hidden
  - referrer: hidden
```

### 5. Flujos de Automatización

#### A. Nuevo Lead desde Popup
```
1. Usuario llena popup → POST /api/leads
2. CreateLeadJob (queue: high)
   ├── Validar datos
   ├── Crear/Actualizar Customer
   ├── Registrar ContactLog
   └── Dispatch SendWelcomeEmailJob
3. SendWelcomeEmailJob (queue: high)
   ├── Generar tracking_token
   ├── Enviar email con Mailtrap
   ├── Crear EmailTracking record
   └── Log en ContactLog
4. [Async] UpdateLeadScoreJob (queue: low)
```

#### B. Registro en Guest List
```
1. Usuario se registra → POST /guest-list
2. ProcessGuestListRegistrationJob (queue: high)
   ├── Find or Create Customer
   ├── Crear GuestListEntry
   ├── Registrar ContactLog
   ├── Dispatch SendEventConfirmationJob
   └── [Si nuevo] Dispatch SendWelcomeEmailJob
3. SendEventConfirmationJob
   ├── Email con detalles del evento
   ├── QR code para check-in
   └── Add to calendar link
4. [24h antes] SendEventReminderJob
   ├── Email/SMS recordatorio
   └── WhatsApp message (si opt-in)
```

#### C. Tracking de Email Opens
```
1. Email enviado con pixel tracking
   ← <img src="/track/email/{token}/open.gif">
2. Usuario abre email → GET /track/email/{token}/open
3. ProcessEmailOpenJob (queue: low)
   ├── Registrar en EmailTracking (opens_count++)
   ├── Actualizar ContactLog (status=opened)
   └── UpdateLeadScoreJob (+5 puntos)
```

#### D. Campaña de Marketing
```
1. Admin crea Campaign en Filament
2. Selecciona segmento: 
   - tags: [techno, afterhours]
   - lifecycle_stage: [lead, prospect]
   - last_interaction < 30 days
3. Schedule campaign
4. ProcessCampaignJob
   ├── Fetch recipients (chunked)
   └── Dispatch SendMarketingEmailJob x N
5. Dashboard en tiempo real muestra métricas
```

### 6. Panel de Administración Filament

#### Recursos Nuevos/Mejorados

##### A. CustomerResource (Mejorado)
```
Tabs:
  - Overview (datos principales)
  - Contact History (tabla de ContactLogs)
  - Events (GuestListEntries)
  - Email Stats (EmailTrackings)
  - Lead Score (timeline de score)

Actions:
  - Send Email
  - Send SMS
  - Send WhatsApp
  - Add to Campaign
  - Add Tag
  - Change Status

Widgets:
  - Lifecycle Stage (visual pipeline)
  - Engagement Score
  - Recent Activity
```

##### B. QueueMonitorResource (Nuevo)
```
Features:
  - Real-time queue status
  - Jobs pending/processing/failed
  - Worker status
  - Start/Stop workers desde UI
  - Retry failed jobs
  - Clear queue
  - Job details y logs

Widgets:
  - Jobs per hour (chart)
  - Queue performance
  - Failed jobs alerts
  - Worker health status
```

##### C. CampaignResource (Nuevo)
```
Wizard de creación:
  1. Campaign Details
  2. Audience Selection (query builder)
  3. Content (email/SMS template)
  4. Schedule
  5. Review & Launch

Dashboard:
  - Real-time metrics
  - Opens/Clicks map
  - Device breakdown
  - A/B test results
```

##### D. AutomationResource (Nuevo)
```
Visual Flow Builder:
  - Drag & drop triggers
  - Conditions (if/else)
  - Actions (send email, wait, tag)
  - A/B split testing

Monitoring:
  - Active automations
  - Performance metrics
  - Debug logs
```

### 7. APIs y Webhooks

#### Public APIs
```
POST /api/leads - Crear lead desde popup
POST /api/guest-list - Registro guest list
GET  /api/events/{id}/availability - Check disponibilidad

Autenticación:
  - API Token (stateless)
  - Rate limiting: 60 req/min
  - CORS configurado
```

#### Webhooks
```
POST /webhooks/mailtrap/events
  - email.delivered
  - email.opened
  - email.clicked
  - email.bounced
  - email.complained

POST /webhooks/twilio/sms
  - message.delivered
  - message.failed
  
POST /webhooks/twilio/whatsapp
  - message.delivered
  - message.read
  - message.failed
```

### 8. Métricas y Reporting

#### Dashboard Principal
```
Widgets:
  1. New Leads Today/Week/Month
  2. Email Open Rate (trend)
  3. SMS Delivery Rate
  4. Top Campaigns Performance
  5. Queue Health Status
  6. Event Registrations
  7. Lead Score Distribution
  8. Conversion Funnel
```

#### Reports Automáticos
```
Daily:
  - Queue performance
  - Failed jobs summary
  - New leads report

Weekly:
  - Campaign performance
  - Email engagement
  - Lead quality score

Monthly:
  - Growth metrics
  - ROI analysis
  - Churn analysis
```

## 🔧 Stack Tecnológico

```
Backend:
  - Laravel 12 (PHP 8.2)
  - Filament 4.3 (Admin Panel)
  - Queue: Database driver
  - Jobs: High/Default/Low queues

Email:
  - Mailtrap (SMTP + API)
  - Laravel Mail + Mailables
  - Pixel tracking + URL tracking

SMS/WhatsApp/Voice:
  - Twilio SDK
  - Webhook listeners

Frontend:
  - Livewire 3 (interactividad)
  - Alpine.js (popup y interactions)
  - Tailwind CSS

Monitoring:
  - Laravel Horizon (Redis alternative)
  - Laravel Pulse (health monitoring)
  - Custom Filament dashboard
```

## 📦 Paquetes Requeridos

```bash
# Ya instalados
filament/filament: ^4.3
spatie/laravel-medialibrary: ^11.17

# A instalar
composer require twilio/sdk
composer require guzzlehttp/guzzle (ya incluido en Laravel)
composer require spatie/laravel-tags (opcional - para tagging)
composer require spatie/laravel-activitylog (opcional - para audit)

# Para Queue Monitoring
composer require filament/spatie-laravel-settings-plugin (settings)

# Dev/Testing
composer require --dev laravel/telescope (debugging)
```

## 🚀 Plan de Implementación

### Fase 1: Base de Datos (Día 1)
- [x] Análisis completado
- [ ] Crear migraciones para nuevas tablas
- [ ] Actualizar modelos existentes
- [ ] Seeders para datos de prueba

### Fase 2: Jobs y Queues (Día 1-2)
- [ ] Crear estructura de Jobs
- [ ] Configurar queues con prioridades
- [ ] Implementar manejo de fallos y reintentos
- [ ] Crear listeners para webhooks

### Fase 3: Integraciones Externas (Día 2)
- [ ] Configurar Mailtrap
- [ ] Integrar Twilio
- [ ] Crear servicios para cada canal
- [ ] Implementar webhooks

### Fase 4: Mailables y Notificaciones (Día 2-3)
- [ ] Crear mailables con tracking
- [ ] Diseñar templates HTML
- [ ] Implementar SMS templates
- [ ] WhatsApp message templates

### Fase 5: Frontend - Popup (Día 3)
- [ ] Crear componente Alpine.js
- [ ] API endpoint para captura
- [ ] Lógica de triggers
- [ ] Cookie management

### Fase 6: Panel Filament (Día 3-4)
- [ ] Mejorar CustomerResource
- [ ] Crear QueueMonitorResource
- [ ] Crear CampaignResource
- [ ] Crear AutomationResource
- [ ] Widgets y dashboards

### Fase 7: Automatizaciones (Día 4-5)
- [ ] Welcome automation
- [ ] Event reminders
- [ ] Re-engagement campaigns
- [ ] Lead scoring system

### Fase 8: Testing y Documentación (Día 5)
- [ ] Unit tests para Jobs
- [ ] Feature tests para APIs
- [ ] Documentación de uso
- [ ] Guía de configuración

## 📊 KPIs a Monitorear

```
Engagement:
  - Email Open Rate (objetivo: >20%)
  - Email Click Rate (objetivo: >5%)
  - SMS Delivery Rate (objetivo: >95%)
  - WhatsApp Read Rate (objetivo: >70%)

Conversion:
  - Popup → Lead (objetivo: >3%)
  - Lead → Event Registration (objetivo: >15%)
  - Event Registration → Attendance (objetivo: >60%)

Technical:
  - Queue Processing Time (objetivo: <5min)
  - Failed Jobs Rate (objetivo: <1%)
  - Email Bounce Rate (objetivo: <3%)
  - API Response Time (objetivo: <200ms)
```

## 🔒 Seguridad y Privacidad

```
GDPR Compliance:
  - Consent management (checkboxes explícitos)
  - Right to erasure (soft deletes + anonymization)
  - Data export (JSON export de todos los datos)
  - Opt-out mechanisms (unsubscribe de cada canal)

Security:
  - Rate limiting en APIs
  - CSRF protection
  - XSS protection en emails
  - SQL injection prevention (Eloquent)
  - Encrypted sensitive data (email tracking tokens)
  - Webhook signature verification (Twilio)
```

## 📝 Notas Finales

Este documento servirá como referencia durante toda la implementación. Cada componente está diseñado para ser:

- **Escalable**: Maneja desde 100 hasta 100,000+ contactos
- **Mantenible**: Código limpio, bien documentado
- **Observable**: Logs, métricas y dashboards
- **Testeable**: Cobertura de tests >80%
- **Performante**: Async processing, caching, indexación

**Próximo paso**: Comenzar con Fase 1 - Migraciones de base de datos.

