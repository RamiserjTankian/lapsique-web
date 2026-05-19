<?php

namespace App\Filament\Resources\ContentBookings\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
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
                    TextEntry::make('slot_summary')->label('Horario'),
                    TextEntry::make('shoot_location')->label('Locación')->placeholder('Por definir'),
                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn ($record) => $record->status_label)
                        ->color(fn ($record) => $record->status_color),
                    TextEntry::make('formatted_amount')->label('Monto'),
                    TextEntry::make('deliverables_count')->label('Entregables'),
                    TextEntry::make('deliverables_ready_at')->label('Publicado')->dateTime('d/m/Y H:i')->placeholder('Aún no'),
                    TextEntry::make('public_id')->label('ID Público'),
                    TextEntry::make('admin_notes')->label('Notas internas')->columnSpanFull()->placeholder('—'),
                ]),

            Section::make('Pago MercadoPago')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextEntry::make('mercadopago_preference_id')->label('Preference ID')->placeholder('—'),
                    TextEntry::make('mercadopago_payment_id')->label('Payment ID')->placeholder('—'),
                    TextEntry::make('mercadopago_status')->label('Estado MP')->placeholder('—'),
                    TextEntry::make('google_calendar_event_id')->label('Evento GCal')->placeholder('—'),
                ]),

            Section::make('Atribución y Analytics')
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
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
