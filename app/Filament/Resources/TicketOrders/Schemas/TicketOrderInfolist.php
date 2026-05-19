<?php

namespace App\Filament\Resources\TicketOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Orden')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('public_id')->label('Orden'),
                        TextEntry::make('event.title')->label('Evento'),
                        TextEntry::make('status')->label('Estado'),
                        TextEntry::make('payment_provider')->label('Proveedor'),
                        TextEntry::make('subtotal')->label('Consumible')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('fee')->label('Servicio')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('total')->label('Total cobrado')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('created_at')->label('Creada')
                            ->dateTime('d M Y H:i'),
                    ]),
                Section::make('Comprador')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('buyer_name')->label('Nombre'),
                        TextEntry::make('buyer_email')->label('Email'),
                        TextEntry::make('buyer_whatsapp')->label('WhatsApp'),
                        TextEntry::make('buyer_instagram')->label('Instagram'),
                    ]),
                Section::make('Pago')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mp_payment_id')->label('Pago MP'),
                        TextEntry::make('mp_preference_id')->label('Preferencia MP'),
                        TextEntry::make('mp_status')->label('Estado MP'),
                        TextEntry::make('mp_status_detail')->label('Detalle'),
                        TextEntry::make('paid_at')->label('Pagado')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('stripe_session_id')->label('Sesion Stripe'),
                        TextEntry::make('stripe_status')->label('Estado Stripe'),
                    ]),
                Section::make('Items')
                    ->schema([
                        TextEntry::make('items')
                            ->label('Detalle')
                            ->formatStateUsing(function ($record) {
                                return $record->items->map(function ($item) use ($record) {
                                    return "{$item->quantity} x {$item->name} — " . number_format($item->total_price, 2) . " {$record->currency}";
                                })->join("\n");
                            })
                            ->extraAttributes(['style' => 'white-space: pre-line;']),
                    ]),
            ]);
    }
}
