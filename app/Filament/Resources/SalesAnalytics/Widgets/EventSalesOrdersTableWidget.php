<?php

namespace App\Filament\Resources\SalesAnalytics\Widgets;

use App\Filament\Resources\SalesAnalytics\Concerns\InteractsWithSalesAnalyticsRecord;
use App\Filament\Resources\TicketOrders\TicketOrderResource;
use App\Filament\Support\TicketOrderCancelAction;
use App\Models\TicketOrder;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class EventSalesOrdersTableWidget extends TableWidget
{
    use InteractsWithSalesAnalyticsRecord;

    protected static ?string $heading = 'Historial de ventas';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $eventId = $this->getSalesAnalyticsRecord()?->event_id;

        return $table
            ->query(
                TicketOrder::query()
                    ->where('event_id', $eventId ?: 0)
                    ->latest('created_at')
            )
            ->recordUrl(fn (TicketOrder $record): string => TicketOrderResource::getUrl('view', ['record' => $record]))
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => ['failed', 'cancelled'],
                        'info' => 'refunded',
                    ]),
                TextColumn::make('buyer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('buyer_email')
                    ->label('Email')
                    ->copyable()
                    ->placeholder('-')
                    ->limit(32),
                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn (TicketOrder $record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('attendees_expected')
                    ->label('Tickets'),
                TextColumn::make('source')
                    ->label('Origen')
                    ->state(fn (TicketOrder $record): string => $record->utm_source ?: (data_get($record->metadata, 'referrer') ?: 'Directo'))
                    ->limit(24),
                TextColumn::make('payment_provider')
                    ->label('Pago')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Pagada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (TicketOrder $record): string => TicketOrderResource::getUrl('view', ['record' => $record])),
                TicketOrderCancelAction::make(),
            ]);
    }
}
