# Sistema de Tickets + MercadoPago + Stripe

## Resumen
Este modulo agrega venta de tickets, mesas, combos y multipass con MercadoPago o Stripe, registro individual por persona, envio de QR por email, control de accesos con QR y tracking Meta Pixel. Se integra con eventos existentes, RPs y links de invitacion. La UI de compra esta integrada en la pagina del evento.

## Configuracion
### Variables de entorno
Anadir en `.env`:
```
MERCADOPAGO_ACCESS_TOKEN=
MERCADOPAGO_PUBLIC_KEY=
MERCADOPAGO_WEBHOOK_SECRET=
MERCADOPAGO_STATEMENT_DESCRIPTOR="LAPSIQUE"
MERCADOPAGO_CURRENCY=MXN
MERCADOPAGO_SANDBOX=false

META_PIXEL_ENABLED=false
META_PIXEL_ID=
META_PIXEL_AUTO_TRACK=true

STRIPE_SECRET_KEY=
STRIPE_PUBLISHABLE_KEY=
STRIPE_WEBHOOK_SECRET=
STRIPE_CURRENCY=MXN
```

### Migraciones
```
php artisan migrate
```

### Webhook MercadoPago
Configurar la URL de notificaciones en MercadoPago:
```
POST https://TU_DOMINIO/webhooks/mercadopago
```
El webhook valida firma con `MERCADOPAGO_WEBHOOK_SECRET` y sincroniza el estado de la orden.

### Webhook Stripe
Configurar la URL de webhooks en Stripe:
```
POST https://TU_DOMINIO/webhooks/stripe
```
El webhook valida firma con `STRIPE_WEBHOOK_SECRET` (tolerancia de reloj configurable con `STRIPE_WEBHOOK_TOLERANCE_SECONDS`, por defecto 300) y sincroniza reservas de contenido y órdenes de tickets.

Eventos que debe suscribir el endpoint en Stripe Dashboard:

- `checkout.session.completed`
- `checkout.session.expired`
- `checkout.session.async_payment_succeeded`
- `checkout.session.async_payment_failed`
- `checkout.session.async_payment_pending`
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `payment_intent.canceled`
- `charge.refunded`
- `refund.updated`

Comportamiento resumido:

| Evento | Reserva de contenido | Orden de tickets |
|--------|----------------------|------------------|
| Checkout completado / async OK | Confirma y envía emails | Marca `paid`, genera asistentes |
| Checkout expirado / async fallido | Libera slot | Libera stock reservado y cancela/falla |
| Payment intent fallido / cancelado | Libera slot si aplica | Libera reserva y marca fallida |
| Reembolso (`charge.refunded`) | Cancela y libera slot si estaba confirmada | Revierte stock/balance y marca `refunded` |

Idempotencia: cada `event.id` de Stripe se guarda en `stripe_webhook_events` para no procesar dos veces.

## Flujo de compra
1. Crear productos desde Filament:
   - `Tickets` (TicketProductResource) -> asignar evento, precio, categoria y stock.
2. Si el evento tiene tickets activos, la UI de compra aparece en `eventos/{slug}`.
3. Al pagar se redirige a MercadoPago o Stripe y dispara webhook.
5. Con pago aprobado:
   - Reserva se confirma (stock).
   - Se generan asistentes pendientes.
   - Se envia email al comprador para registrar asistentes.
6. El comprador registra cada asistente (nombre, email, WhatsApp, Instagram).
7. Cada asistente recibe su QR por email.
8. Acceso por QR en `/tickets/check-in/{token}` (firmado).

## Registro individual por persona
- Cada ticket requiere un registro por persona.
- `access_units` define cuantas personas debe registrar por unidad.
- `check_in_limit` define cuantos accesos permite el QR (util para multipass).

## Seleccion de pasarela
En la UI de compra el usuario elige `MercadoPago` o `Stripe`. El proveedor se guarda en `payment_provider` dentro de la orden.

## Tracking con RP / Invitaciones
Se reutilizan los `GuestListInviteLink` existentes como fuente de referencia:
- Compartir links con token: `https://TU_DOMINIO/eventos/{slug}?invite={token}#tickets`
- Se guarda `rp_id` y `invite_link_id` en la orden y asistentes.

## Meta Pixel (eventos)
Eventos incluidos:
- `PageView` (auto, base pixel)
- `ViewContent` (al abrir el evento)
- `AddToCart` (cuando se seleccionan tickets)
- `InitiateCheckout` (al enviar el checkout)
- `AddPaymentInfo` (al enviar el checkout)
- `Purchase` (pago aprobado en la pagina de exito)
- `CompleteRegistration` (cuando se envian datos de asistentes)

## Admin (Filament)
- `Tickets`: crear productos (ticket/mesa/combo/multipass).
- `Ordenes Tickets`: ver estado de pago y resumen.
- `Accesos Tickets`: editar asistentes y reenviar QR.
- `Lector QR` (Guest List): ahora soporta tickets y guest list.

## Email
- Compra confirmada: `TicketOrderConfirmationEmail`
- Acceso con QR: `TicketAccessEmail`
- Acceso al portal: `CustomerPortalAccessEmail` (se envia al crear contraseña)

## Portal de cliente
- Login en `/mi-portal/login` con email + contraseña enviada tras el pago.
- Portal en `/mi-portal` con tickets, guest lists y compras.
- La contraseña se genera solo si el cliente no tenia una previa.

## PDF del pase
- El email de acceso adjunta un PDF con QR y codigo manual.
- Vista: `resources/views/pdfs/ticket-access.blade.php`

## Control de accesos
- QR contiene token unico por asistente.
- Check-in usa rutas firmadas y contador de usos por QR.
- Registro de scans en `ticket_scans`.

## Notas de seguridad
- Tokens de QR son aleatorios y unicos.
- Backoffice usa URLs firmadas para check-in.
- Webhook verifica firma si se configura el secret.

## Pruebas manuales sugeridas
1. Crear producto de ticket con stock 10.
2. Comprar 2 tickets y completar pago.
3. Registrar 2 asistentes y verificar emails con QR.
4. Escanear QR y validar check-in (contador y estado).
5. Probar link con `invite` y confirmar atribucion a RP.
6. Ver eventos de Meta Pixel en el panel de Meta.
