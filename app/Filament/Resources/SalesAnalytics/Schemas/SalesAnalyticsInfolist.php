<?php

namespace App\Filament\Resources\SalesAnalytics\Schemas;

use App\Support\EventSalesInsights;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesAnalyticsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evento')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('event.title')
                            ->label('Evento')
                            ->placeholder('Evento eliminado'),
                        TextEntry::make('event.starts_at')
                            ->label('Fecha')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Sin fecha'),
                        TextEntry::make('event.venue')
                            ->label('Venue')
                            ->placeholder('Sin venue'),
                        TextEntry::make('event.city')
                            ->label('Ciudad')
                            ->placeholder('Sin ciudad'),
                        TextEntry::make('first_paid_at')
                            ->label('Primera venta')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Sin ventas'),
                        TextEntry::make('last_paid_at')
                            ->label('Última venta')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Sin ventas'),
                    ]),
                Section::make('Balance de ventas')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('orders_count')
                            ->label('Órdenes pagadas'),
                        TextEntry::make('tickets_sold')
                            ->label('Tickets vendidos'),
                        TextEntry::make('tickets_registered')
                            ->label('Tickets registrados'),
                        TextEntry::make('revenue_subtotal')
                            ->label('Consumible')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('revenue_fee')
                            ->label('Servicio')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('revenue_total')
                            ->label('Ingresos')
                            ->money(fn ($record) => $record->currency ?? 'MXN'),
                        TextEntry::make('avg_order_total')
                            ->label('Promedio por orden')
                            ->state(function ($record): string {
                                $orders = max(1, (int) ($record->orders_count ?? 0));
                                $avg = ((float) ($record->revenue_total ?? 0)) / $orders;

                                return number_format($avg, 2) . ' ' . ($record->currency ?? 'MXN');
                            }),
                        TextEntry::make('visitor_conversion')
                            ->label('Conversión visitante a pago')
                            ->state(function ($record): string {
                                $insights = new EventSalesInsights($record);
                                $summary = $insights->summary();

                                return number_format($summary['visitor_to_paid_rate'], 1) . '%';
                            }),
                        TextEntry::make('paid_customers')
                            ->label('Clientes que pagan')
                            ->state(function ($record): string {
                                $insights = new EventSalesInsights($record);
                                $summary = $insights->summary();

                                return number_format($summary['paid_customers']);
                            }),
                    ]),
            ]);
    }
}
