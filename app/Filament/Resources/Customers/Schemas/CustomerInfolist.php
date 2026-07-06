<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lead')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nombre'),
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable(),
                                TextEntry::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->placeholder(fn (Customer $record): string => $record->phone ?: '-')
                                    ->copyable(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                                TextEntry::make('lifecycle_stage')
                                    ->label('Etapa')
                                    ->badge(),
                                TextEntry::make('lead_score')
                                    ->label('Score')
                                    ->badge(),
                            ]),
                    ]),
                Section::make('Valor y actividad')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('content_bookings_count')
                                    ->label('Reservas')
                                    ->state(fn (Customer $record): int => $record->contentBookings()->count()),
                                TextEntry::make('confirmed_booking_revenue')
                                    ->label('Revenue sesiones')
                                    ->state(fn (Customer $record): string => '$'.number_format((float) $record->contentBookings()->where('status', 'confirmed')->sum('amount'), 0).' MXN'),
                                TextEntry::make('ticket_orders_count')
                                    ->label('Ordenes eventos')
                                    ->state(fn (Customer $record): int => $record->ticketOrders()->count()),
                                TextEntry::make('last_interaction_at')
                                    ->label('Última interacción')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ]),
                Section::make('Atribución')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('source')
                                    ->label('Origen')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('utm_source')
                                    ->label('UTM source')
                                    ->placeholder('-'),
                                TextEntry::make('utm_campaign')
                                    ->label('UTM campaign')
                                    ->placeholder('-'),
                            ]),
                    ]),
                Section::make('Notas')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('Sin notas')
                            ->columnSpanFull(),
                        TextEntry::make('follow_up_notes')
                            ->label('Notas de seguimiento')
                            ->state(function (Customer $record): string {
                                $notes = is_array($record->metadata) ? ($record->metadata['follow_up_notes'] ?? []) : [];

                                if (! is_array($notes) || $notes === []) {
                                    return 'Sin seguimiento registrado';
                                }

                                return collect($notes)
                                    ->map(fn (array $note): string => trim(($note['created_at'] ?? '').' - '.($note['note'] ?? '')))
                                    ->filter()
                                    ->join("\n");
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
