<?php

namespace App\Filament\Resources\ContentBookings\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContentBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn ($record) => $record->client_email),

                TextColumn::make('customer.email')
                    ->label('Portal')
                    ->placeholder('Sin portal')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('slot.date')
                    ->label('Sesión')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->slot?->time_label ?? '—'),

                TextColumn::make('service_name')
                    ->label('Servicio')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn ($record) => $record->status_color),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($record) => $record->formatted_amount),

                TextColumn::make('payment_provider')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'stripe' => 'Stripe',
                        'mercadopago' => 'Mercado Pago',
                        default => $state ? ucfirst($state) : '—',
                    }),

                TextColumn::make('stripe_status')
                    ->label('Stripe')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('deliverables_count')
                    ->label('Archivos')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('utm_source')
                    ->label('Fuente')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending_payment' => 'Pendiente de pago',
                        'confirmed' => 'Confirmada',
                        'pending' => 'Pago en revisión',
                        'failed' => 'Pago fallido',
                        'cancelled' => 'Cancelada',
                    ]),
                SelectFilter::make('payment_provider')
                    ->label('Proveedor')
                    ->options([
                        'stripe' => 'Stripe',
                        'mercadopago' => 'Mercado Pago',
                    ]),
                SelectFilter::make('service_type')
                    ->label('Servicio')
                    ->options([
                        'content_session' => 'Sesión de contenido',
                        'dj_set' => 'DJ Set',
                    ]),
                Filter::make('paid_at')
                    ->label('Pagadas en periodo')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('paid_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('paid_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
