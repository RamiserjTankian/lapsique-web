<?php

namespace App\Filament\Resources\SessionCustomers\Schemas;

use App\Support\SessionCustomerInsights;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SessionCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Resumen comercial')
                ->columns(4)
                ->schema([
                    TextEntry::make('session_stats.bookings')
                        ->label('Reservas totales')
                        ->state(fn ($record) => SessionCustomerInsights::profileStats($record)['bookings']),
                    TextEntry::make('session_stats.confirmed')
                        ->label('Sesiones pagadas')
                        ->state(fn ($record) => SessionCustomerInsights::profileStats($record)['confirmed']),
                    TextEntry::make('session_stats.revenue')
                        ->label('Ingresos')
                        ->state(fn ($record) => '$'.number_format(
                            SessionCustomerInsights::profileStats($record)['revenue'],
                            0,
                        ).' MXN'),
                    TextEntry::make('session_stats.pending_delivery')
                        ->label('Entregas pendientes')
                        ->badge()
                        ->color(fn ($record) => SessionCustomerInsights::profileStats($record)['pending_delivery'] > 0 ? 'warning' : 'success')
                        ->state(fn ($record) => (string) SessionCustomerInsights::profileStats($record)['pending_delivery']),
                ]),

            Section::make('Contacto')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('email')->label('Email')->copyable(),
                    TextEntry::make('phone')->label('Teléfono')->placeholder('—'),
                    TextEntry::make('whatsapp')->label('WhatsApp')->placeholder('—'),
                    TextEntry::make('instagram_handle')
                        ->label('Instagram')
                        ->formatStateUsing(fn ($state) => $state ? '@'.$state : '—'),
                    TextEntry::make('status')
                        ->label('Estado CRM')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'lead' => 'Lead',
                            'prospect' => 'Prospecto',
                            'customer' => 'Cliente',
                            'inactive' => 'Inactivo',
                            default => ucfirst((string) $state),
                        }),
                    TextEntry::make('last_interaction_at')
                        ->label('Última interacción')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                ]),

            Section::make('Datos fiscales')
                ->columns(2)
                ->collapsible()
                ->schema([
                    TextEntry::make('fiscal_legal_name')->label('Razón social')->placeholder('—'),
                    TextEntry::make('fiscal_rfc')->label('RFC')->placeholder('—')->copyable(),
                    TextEntry::make('fiscal_regime')->label('Régimen fiscal')->placeholder('—'),
                    TextEntry::make('fiscal_cfdi_use')->label('Uso CFDI')->placeholder('—'),
                    TextEntry::make('fiscal_email')->label('Email facturación')->placeholder('—'),
                    TextEntry::make('fiscal_zip')->label('C.P.')->placeholder('—'),
                    TextEntry::make('fiscal_address')->label('Dirección')->columnSpanFull()->placeholder('—'),
                    TextEntry::make('fiscal_city')->label('Ciudad')->placeholder('—'),
                    TextEntry::make('fiscal_state')->label('Estado')->placeholder('—'),
                    TextEntry::make('fiscal_country')->label('País')->placeholder('MX'),
                ]),

            Section::make('Notas')
                ->schema([
                    TextEntry::make('notes')
                        ->label('Notas internas')
                        ->placeholder('Sin notas')
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }
}
