<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class HotLeadsTableWidget extends TableWidget
{
    protected static ?string $heading = 'Leads calientes';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->recordUrl(fn (Customer $record): string => CustomerResource::getUrl('edit', ['record' => $record]))
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(fn (Customer $record): string => $record->email),
                TextColumn::make('lead_score')
                    ->label('Score')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'gray')),
                TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->placeholder('direct'),
                TextColumn::make('analytics_sessions_count')
                    ->label('Sesiones')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('analytics_events_count')
                    ->label('Eventos')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('commercial_value')
                    ->label('Revenue')
                    ->state(fn (Customer $record): float => (float) ($record->paid_ticket_revenue ?? 0) + (float) ($record->confirmed_booking_revenue ?? 0))
                    ->formatStateUsing(fn ($state) => '$'.number_format((float) $state, 0)),
                TextColumn::make('last_interaction_at')
                    ->label('Última interacción')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->actions([
                EditAction::make()
                    ->url(fn (Customer $record): string => CustomerResource::getUrl('edit', ['record' => $record])),
            ]);
    }

    protected function query(): Builder
    {
        return Customer::query()
            ->withCount(['analyticsSessions', 'analyticsEvents', 'guestListEntries', 'ticketOrders', 'contentBookings'])
            ->withSum(['ticketOrders as paid_ticket_revenue' => fn (Builder $query) => $query->where('status', 'paid')], 'total')
            ->withSum(['contentBookings as confirmed_booking_revenue' => fn (Builder $query) => $query->where('status', 'confirmed')], 'amount')
            ->where(function (Builder $query): void {
                $query
                    ->where('lead_score', '>=', 40)
                    ->orWhereHas('analyticsSessions', fn (Builder $sessions) => $sessions->where('created_at', '>=', now()->subDays(30)))
                    ->orWhereHas('ticketOrders', fn (Builder $orders) => $orders->whereIn('status', ['pending', 'paid']))
                    ->orWhereHas('contentBookings', fn (Builder $bookings) => $bookings->whereIn('status', ['pending_payment', 'pending', 'confirmed']));
            })
            ->orderByDesc('lead_score')
            ->orderByDesc('last_interaction_at');
    }
}
