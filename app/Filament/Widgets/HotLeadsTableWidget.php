<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
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
                TextColumn::make('journey_timeline')
                    ->label('Timeline')
                    ->state(fn (Customer $record): string => $this->timelineSummary($record))
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('last_interaction_at')
                    ->label('Última interacción')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Customer $record): ?string => $this->whatsappUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Customer $record): bool => $this->whatsappUrl($record) !== null),
                Action::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->url(fn (Customer $record): string => 'mailto:'.$record->email)
                    ->openUrlInNewTab()
                    ->visible(fn (Customer $record): bool => filled($record->email)),
                Action::make('note')
                    ->label('Nota')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Textarea::make('note')
                            ->label('Nota de seguimiento')
                            ->rows(3)
                            ->maxLength(500)
                            ->required(),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        $metadata = is_array($record->metadata) ? $record->metadata : [];
                        $notes = is_array($metadata['follow_up_notes'] ?? null) ? $metadata['follow_up_notes'] : [];
                        $notes[] = [
                            'note' => $data['note'],
                            'created_at' => now()->toIso8601String(),
                            'user_id' => auth()->id(),
                        ];

                        $record->forceFill([
                            'metadata' => array_merge($metadata, ['follow_up_notes' => $notes]),
                            'last_interaction_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Nota guardada')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_follow_up')
                    ->label('Seguimiento')
                    ->icon('heroicon-o-flag')
                    ->color('warning')
                    ->action(function (Customer $record): void {
                        $metadata = is_array($record->metadata) ? $record->metadata : [];
                        $record->forceFill([
                            'metadata' => array_merge($metadata, [
                                'follow_up_status' => 'pending_follow_up',
                                'follow_up_marked_at' => now()->toIso8601String(),
                            ]),
                            'last_interaction_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Lead marcado para seguimiento')
                            ->success()
                            ->send();
                    }),
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

    protected function whatsappUrl(Customer $record): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) ($record->whatsapp ?: $record->phone));

        return $phone ? 'https://wa.me/'.$phone : null;
    }

    protected function timelineSummary(Customer $record): string
    {
        $firstSession = $record->analyticsSessions()->oldest()->first();
        $latestEvent = $record->analyticsEvents()->latest()->first();

        return collect([
            $firstSession ? 'Primera visita '.$firstSession->created_at?->format('d/m H:i') : null,
            $latestEvent ? 'Último evento: '.$latestEvent->name : null,
            $record->guest_list_entries_count ? $record->guest_list_entries_count.' guest list' : null,
            $record->ticket_orders_count ? $record->ticket_orders_count.' ticket orders' : null,
            $record->content_bookings_count ? $record->content_bookings_count.' bookings' : null,
        ])->filter()->implode(' · ') ?: 'Sin timeline todavía';
    }
}
