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
                    ]),
            ]);
    }
}
