<?php

namespace App\Filament\Resources\CustomerEventBalances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerEventBalanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cliente y evento')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Cliente')
                            ->default('Cliente eliminado'),
                        TextEntry::make('customer.email')
                            ->label('Email')
                            ->default('-'),
                        TextEntry::make('event.title')
                            ->label('Evento')
                            ->default('Evento eliminado'),
                        TextEntry::make('lastTicketOrder.public_id')
                            ->label('Última orden')
                            ->default('Sin orden'),
                    ]),
                Section::make('Resumen de saldo')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total_credited')
                            ->label('Pagado')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('balance')
                            ->label('Saldo pendiente')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('total_consumed')
                            ->label('Saldo consumido')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('pos_charges_count')
                            ->label('Cargos POS')
                            ->state(fn ($record) => $record->posCharges()->count())
                            ->badge(),
                        TextEntry::make('consumption_ratio')
                            ->label('% consumido')
                            ->state(function ($record): string {
                                $credited = (float) ($record->total_credited ?? 0);

                                if ($credited <= 0) {
                                    return '0%';
                                }

                                return number_format(((float) $record->total_consumed / $credited) * 100, 1).'%';
                            }),
                        TextEntry::make('lastTicketOrder.utm_source')
                            ->label('Fuente de venta')
                            ->placeholder('Directo'),
                    ]),
                Section::make('Atribución de venta')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('lastTicketOrder.utm_campaign')->label('Campaña')->placeholder('—'),
                        TextEntry::make('lastTicketOrder.payment_provider')->label('Proveedor')->placeholder('—'),
                        TextEntry::make('lastTicketOrder.metadata.analytics_session_id')
                            ->label('Session ID')
                            ->state(fn ($record) => data_get($record->lastTicketOrder?->metadata, 'analytics_session_id'))
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('lastTicketOrder.metadata.analytics_visitor_id')
                            ->label('Visitor ID')
                            ->state(fn ($record) => data_get($record->lastTicketOrder?->metadata, 'analytics_visitor_id'))
                            ->placeholder('—')
                            ->copyable(),
                    ]),
            ]);
    }
}
