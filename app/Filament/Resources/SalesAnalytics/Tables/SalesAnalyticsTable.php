<?php

namespace App\Filament\Resources\SalesAnalytics\Tables;

use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Models\Event;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesAnalyticsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('revenue_total', 'desc')
            ->recordUrl(fn ($record): string => SalesAnalyticsResource::getUrl('view', ['record' => $record->getAttribute('id')]))
            ->columns([
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->formatStateUsing(fn ($state) => $state ?? 'Evento eliminado')
                    ->description('Abrir detalle de ventas'),
                TextColumn::make('orders_count')
                    ->label('Órdenes')
                    ->sortable(),
                TextColumn::make('tickets_sold')
                    ->label('Tickets')
                    ->sortable(),
                TextColumn::make('tickets_registered')
                    ->label('Registrados')
                    ->sortable(),
                TextColumn::make('revenue_subtotal')
                    ->label('Consumible')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('revenue_fee')
                    ->label('Servicio')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('revenue_total')
                    ->label('Ingresos')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('avg_ticket')
                    ->label('Ticket promedio')
                    ->getStateUsing(function ($record): string {
                        $tickets = (int) ($record->tickets_sold ?? 0);
                        $currency = $record->currency ?? 'MXN';

                        if ($tickets <= 0) {
                            return '0.00 ' . $currency;
                        }

                        $avg = ((float) $record->revenue_total) / $tickets;

                        return number_format($avg, 2) . ' ' . $currency;
                    }),
                TextColumn::make('first_paid_at')
                    ->label('Primera venta')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('last_paid_at')
                    ->label('Última venta')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Evento')
                    ->options(fn () => Event::query()
                        ->orderByDesc('starts_at')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable(),
                SelectFilter::make('payment_provider')
                    ->label('Proveedor')
                    ->options([
                        'mercadopago' => 'MercadoPago',
                        'stripe' => 'Stripe',
                    ]),
                Filter::make('paid_range')
                    ->label('Rango de pago')
                    ->form([
                        DatePicker::make('paid_from')->label('Desde'),
                        DatePicker::make('paid_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['paid_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('ticket_orders.paid_at', '>=', $date)
                            )
                            ->when(
                                $data['paid_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('ticket_orders.paid_at', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Ver detalle')
                    ->url(fn ($record): string => SalesAnalyticsResource::getUrl('view', ['record' => $record->getAttribute('id')])),
            ]);
    }
}
