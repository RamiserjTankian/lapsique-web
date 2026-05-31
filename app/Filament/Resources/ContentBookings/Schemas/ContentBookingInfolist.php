<?php

namespace App\Filament\Resources\ContentBookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del Cliente')
                ->columns(2)
                ->schema([
                    TextEntry::make('customer.email')->label('Portal')->placeholder('Sin acceso asociado'),
                    TextEntry::make('client_name')->label('Nombre'),
                    TextEntry::make('client_email')->label('Email'),
                    TextEntry::make('client_phone')->label('WhatsApp'),
                    TextEntry::make('client_instagram')->label('Instagram')->placeholder('—'),
                    TextEntry::make('notes')->label('Notas')->columnSpanFull()->placeholder('—'),
                ]),

            Section::make('Sesión')
                ->columns(2)
                ->schema([
                    TextEntry::make('service_name')->label('Servicio'),
                    TextEntry::make('service_description')->label('Incluye'),
                    TextEntry::make('slot_summary')->label('Horario'),
                    TextEntry::make('shoot_location')->label('Locación')->placeholder('Por definir'),
                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn ($record) => $record->status_label)
                        ->color(fn ($record) => $record->status_color),
                    TextEntry::make('formatted_amount')->label('Monto'),
                    TextEntry::make('paid_at')->label('Pagado el')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('deliverables_ready_at')
                        ->label('Visible en portal desde')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('Sin entregables'),
                    TextEntry::make('deliverable_links_count')
                        ->label('Enlaces Drive')
                        ->state(fn ($record) => $record->deliverableLinks()->count())
                        ->badge()
                        ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                    TextEntry::make('public_id')->label('ID Público'),
                    TextEntry::make('admin_notes')->label('Notas internas')->columnSpanFull()->placeholder('—'),
                ]),

            Section::make('Entregables en Google Drive')
                ->description('Cada enlace añadido envía un correo al cliente y aparece en su portal.')
                ->schema([
                    TextEntry::make('deliverable_links_list')
                        ->label('Enlaces publicados')
                        ->state(function ($record) {
                            $links = $record->deliverableLinks;

                            if ($links->isEmpty()) {
                                return 'Sin enlaces — usa «Añadir entregables» arriba o la tabla inferior.';
                            }

                            return $links
                                ->map(fn ($link) => ($link->label ?: 'Material').': '.$link->url)
                                ->implode("\n");
                        })
                        ->markdown()
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record->status === 'confirmed'),

            Section::make('Pago')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextEntry::make('payment_provider')->label('Proveedor')->badge(),
                    TextEntry::make('mercadopago_preference_id')->label('MP Preference ID')->placeholder('—'),
                    TextEntry::make('mercadopago_payment_id')->label('MP Payment ID')->placeholder('—'),
                    TextEntry::make('mercadopago_status')->label('Estado MP')->placeholder('—'),
                    TextEntry::make('stripe_checkout_session_id')
                        ->label('Stripe Session')
                        ->placeholder('—')
                        ->url(fn ($record) => $record->stripe_checkout_session_id
                            ? 'https://dashboard.stripe.com/checkout/sessions/'.$record->stripe_checkout_session_id
                            : null, true),
                    TextEntry::make('stripe_payment_intent_id')
                        ->label('Stripe Intent')
                        ->placeholder('—')
                        ->url(fn ($record) => $record->stripe_payment_intent_id
                            ? 'https://dashboard.stripe.com/payments/'.$record->stripe_payment_intent_id
                            : null, true),
                    TextEntry::make('stripe_status')->label('Estado Stripe')->placeholder('—'),
                    TextEntry::make('google_calendar_event_id')->label('Evento GCal')->placeholder('—'),
                ]),

            Section::make('Atribución y Analytics')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextEntry::make('analytics_summary')
                        ->label('Resumen de sesión')
                        ->state(function ($record): string {
                            $session = $record->analytics_session_id
                                ? \App\Models\AnalyticsSession::query()
                                    ->where('session_id', $record->analytics_session_id)
                                    ->withCount(['pageviews', 'events'])
                                    ->first()
                                : null;

                            if (! $session) {
                                return 'Sin sesión web asociada.';
                            }

                            $source = $session->source_label ?: $session->utm_source ?: $session->referrer_domain ?: 'Directo';

                            return $source.' · '.$session->pageviews_count.' pageviews · '.$session->events_count.' eventos';
                        })
                        ->columnSpanFull(),
                    TextEntry::make('analytics_journey')
                        ->label('Journey antes de reservar')
                        ->state(function ($record): string {
                            if (! $record->analytics_session_id) {
                                return '—';
                            }

                            $session = \App\Models\AnalyticsSession::query()
                                ->where('session_id', $record->analytics_session_id)
                                ->with(['pageviews' => fn ($query) => $query->orderBy('created_at')])
                                ->first();

                            if (! $session) {
                                return '—';
                            }

                            return $session->pageviews
                                ->pluck('path')
                                ->filter()
                                ->unique()
                                ->take(8)
                                ->implode(' -> ') ?: '—';
                        })
                        ->columnSpanFull(),
                    TextEntry::make('utm_source')->label('UTM Source')->placeholder('—'),
                    TextEntry::make('utm_medium')->label('UTM Medium')->placeholder('—'),
                    TextEntry::make('utm_campaign')->label('UTM Campaign')->placeholder('—'),
                    TextEntry::make('utm_content')->label('UTM Content')->placeholder('—'),
                    TextEntry::make('referrer')->label('Referrer')->placeholder('—'),
                    TextEntry::make('landing_url')->label('Landing URL')->placeholder('—'),
                    TextEntry::make('analytics_visitor_id')->label('Visitor ID')->placeholder('—'),
                    TextEntry::make('analytics_session_id')->label('Session ID')->placeholder('—'),
                    TextEntry::make('fbp')->label('FBP')->placeholder('—'),
                    TextEntry::make('fbc')->label('FBC')->placeholder('—'),
                ]),

            Section::make('Fechas')
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextEntry::make('created_at')->label('Creado')->dateTime('d/m/Y H:i'),
                    TextEntry::make('updated_at')->label('Actualizado')->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}
