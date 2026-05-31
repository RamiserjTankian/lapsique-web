<?php

namespace App\Filament\Resources\CustomerEventBalances\Tables;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Support\CustomerEventBalanceCancelSaleAction;
use App\Models\Event;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerEventBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('balance', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('posCharges'))
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Cliente eliminado')
                    ->url(fn ($record): ?string => $record->customer ? CustomerResource::getUrl('edit', ['record' => $record->customer]) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('customer.email')
                    ->label('Email')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->placeholder('Evento eliminado')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_credited')
                    ->label('Pagado')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Pendiente')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable()
                    ->color(fn ($state): string => ((float) $state > 0) ? 'warning' : 'success'),
                TextColumn::make('total_consumed')
                    ->label('Consumido')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable()
                    ->color('danger'),
                TextColumn::make('pos_charges_count')
                    ->label('Cargos POS')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'primary' : 'gray')
                    ->sortable(),
                TextColumn::make('consumption_ratio')
                    ->label('% consumido')
                    ->state(function ($record): string {
                        $credited = (float) ($record->total_credited ?? 0);
                        $consumed = (float) ($record->total_consumed ?? 0);

                        if ($credited <= 0) {
                            return '0%';
                        }

                        return number_format(($consumed / $credited) * 100, 1).'%';
                    }),
                TextColumn::make('lastTicketOrder.public_id')
                    ->label('Última orden')
                    ->placeholder('Sin orden')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lastTicketOrder.utm_source')
                    ->label('Fuente venta')
                    ->placeholder('Directo')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Último movimiento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Evento')
                    ->options(fn (): array => Event::query()
                        ->orderByDesc('starts_at')
                        ->pluck('title', 'id')
                        ->all())
                    ->searchable(),
                Filter::make('credited_range')
                    ->label('Fecha de pago')
                    ->form([
                        DatePicker::make('paid_from')->label('Desde'),
                        DatePicker::make('paid_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['paid_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'lastTicketOrder',
                                    fn (Builder $orderQuery): Builder => $orderQuery->whereDate('paid_at', '>=', $date)
                                )
                            )
                            ->when(
                                $data['paid_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereHas(
                                    'lastTicketOrder',
                                    fn (Builder $orderQuery): Builder => $orderQuery->whereDate('paid_at', '<=', $date)
                                )
                            );
                    }),
                Filter::make('has_pending_balance')
                    ->label('Con saldo pendiente')
                    ->query(fn (Builder $query): Builder => $query->where('balance', '>', 0)),
                Filter::make('has_consumption')
                    ->label('Con consumo')
                    ->query(fn (Builder $query): Builder => $query->where('total_consumed', '>', 0)),
            ])
            ->actions([
                ViewAction::make(),
                CustomerEventBalanceCancelSaleAction::make(),
            ]);
    }
}
