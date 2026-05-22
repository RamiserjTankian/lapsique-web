<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Models\ContentBooking;
use App\Support\ContentBookingSalesInsights;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ContentBookingSalesOrdersTableWidget extends TableWidget
{
    protected static ?string $heading = 'Ventas de sesiones confirmadas';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->recordUrl(fn (ContentBooking $record): string => ContentBookingResource::getUrl('view', ['record' => $record]))
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('paid_at')
                    ->label('Vendida')
                    ->state(fn (ContentBooking $record): ?\Illuminate\Support\Carbon => $record->paid_at ?? $record->created_at)
                    ->dateTime('d M Y H:i')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $saleAt = ContentBookingSalesInsights::saleAtExpression();

                        return $query->orderByRaw("{$saleAt} {$direction}");
                    }),
                TextColumn::make('client_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (ContentBooking $record) => $record->client_email),
                TextColumn::make('service_name')
                    ->label('Servicio')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn (ContentBooking $record) => $record->formatted_amount)
                    ->sortable(),
                TextColumn::make('payment_provider')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'stripe' => 'Stripe',
                        'mercadopago' => 'Mercado Pago',
                        'internal' => 'Prueba / manual',
                        default => $state ? ucfirst($state) : '—',
                    }),
                TextColumn::make('utm_source')
                    ->label('Fuente')
                    ->placeholder('Directo')
                    ->limit(20),
                TextColumn::make('slot.date')
                    ->label('Sesión')
                    ->date('d/m/Y')
                    ->description(fn (ContentBooking $record) => $record->slot?->time_label ?? '—'),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (ContentBooking $record): string => ContentBookingResource::getUrl('view', ['record' => $record])),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $saleAt = ContentBookingSalesInsights::saleAtExpression();

        return ContentBooking::query()
            ->with('slot')
            ->where('status', 'confirmed')
            ->orderByRaw("{$saleAt} desc");
    }
}
