<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Models\ContentBooking;
use App\Support\ContentBookingSalesInsights;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ContentBookingPipelineTableWidget extends TableWidget
{
    protected static ?string $heading = 'Pipeline de sesiones por landing';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->recordUrl(fn (ContentBooking $record): string => ContentBookingResource::getUrl('view', ['record' => $record]))
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Entrada')
                    ->dateTime('d M H:i')
                    ->sortable(),
                TextColumn::make('client_name')
                    ->label('Lead / cliente')
                    ->searchable()
                    ->description(fn (ContentBooking $record): string => $record->client_email),
                TextColumn::make('menu_service')
                    ->label('Landing')
                    ->state(fn (ContentBooking $record): string => ContentBookingSalesInsights::menuServiceLabelForBooking($record))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DJ Sets' => 'primary',
                        'Vuelos con dron' => 'info',
                        'Avances de obra' => 'warning',
                        'Comida' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status_label')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (ContentBooking $record): string => $record->status_color),
                TextColumn::make('formatted_amount')
                    ->label('Monto'),
                TextColumn::make('payment_provider')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'stripe' => 'Stripe',
                        'mercadopago' => 'Mercado Pago',
                        'internal' => 'Manual',
                        default => $state ? ucfirst($state) : '—',
                    }),
                TextColumn::make('utm_source')
                    ->label('Fuente')
                    ->placeholder('Directo')
                    ->limit(20),
                TextColumn::make('slot.date')
                    ->label('Agenda')
                    ->date('d/m/Y')
                    ->description(fn (ContentBooking $record): string => $record->slot?->time_label ?? 'Sin horario'),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (ContentBooking $record): string => ContentBookingResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (ContentBooking $record): string => ContentBookingResource::getUrl('edit', ['record' => $record])),
            ]);
    }

    protected function query(): Builder
    {
        return ContentBooking::query()
            ->with('slot')
            ->whereIn('status', ['pending_payment', 'pending', 'confirmed'])
            ->where(function (Builder $query): void {
                foreach (array_keys(ContentBookingSalesInsights::menuServices()) as $service) {
                    $query->orWhere(fn (Builder $serviceQuery) => ContentBookingSalesInsights::constrainBookingsToMenuService($serviceQuery, $service));
                }
            })
            ->latest();
    }
}
