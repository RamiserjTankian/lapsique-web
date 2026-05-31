<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->with(['guestListEntries.inviteLink.event', 'guestListEntries.inviteLink.dj', 'guestListEntries.inviteLink.rp'])
                    ->withCount(['analyticsSessions', 'analyticsEvents', 'ticketOrders', 'contentBookings'])
                    ->withMax('analyticsSessions', 'last_seen_at')
                    ->withSum(['ticketOrders as paid_ticket_revenue' => fn ($q) => $q->where('status', 'paid')], 'total')
                    ->withSum(['contentBookings as confirmed_booking_revenue' => fn ($q) => $q->where('status', 'confirmed')], 'amount');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'lead',
                        'info' => 'prospect',
                        'success' => 'customer',
                        'danger' => 'inactive',
                    ])
                    ->sortable(),

                BadgeColumn::make('lifecycle_stage')
                    ->label('Stage')
                    ->colors([
                        'gray' => 'subscriber',
                        'info' => 'lead',
                        'warning' => 'mql',
                        'primary' => 'sql',
                        'success' => 'customer',
                        'purple' => 'evangelist',
                    ])
                    ->sortable(),

                TextColumn::make('lead_score')
                    ->label('Score')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'secondary')),

                TextColumn::make('analytics_sessions_count')
                    ->label('Visitas web')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => (int) $state > 1 ? 'success' : 'gray'),

                TextColumn::make('analytics_sessions_max_last_seen_at')
                    ->label('Última visita')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('customer_value')
                    ->label('Revenue')
                    ->state(fn ($record) => (float) ($record->paid_ticket_revenue ?? 0) + (float) ($record->confirmed_booking_revenue ?? 0))
                    ->formatStateUsing(fn ($state) => '$'.number_format((float) $state, 0).' MXN')
                    ->color('success'),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('instagram_handle')
                    ->label('Instagram')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ? '@'.$state : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('source')
                    ->label('Origen')
                    ->colors([
                        'primary' => 'popup',
                        'success' => 'guestlist',
                        'warning' => 'manual',
                        'info' => ['api', 'referral'],
                    ])
                    ->sortable(),

                TextColumn::make('guest_list_source')
                    ->label('Guest List')
                    ->getStateUsing(function ($record) {
                        try {
                            $latestEntry = $record->guestListEntries()
                                ->with(['inviteLink.event', 'inviteLink.dj', 'inviteLink.rp'])
                                ->latest()
                                ->first();

                            if (! $latestEntry) {
                                return null;
                            }

                            if (! $latestEntry->inviteLink) {
                                return 'Manual';
                            }

                            $link = $latestEntry->inviteLink;
                            $parts = [];

                            if ($link->name) {
                                $parts[] = $link->name;
                            }

                            if ($link->event && $link->event->title) {
                                $parts[] = $link->event->title;
                            }

                            if ($link->dj && $link->dj->name) {
                                $parts[] = 'DJ: '.$link->dj->name;
                            } elseif ($link->rp && $link->rp->name) {
                                $parts[] = 'RP: '.$link->rp->name;
                            }

                            return $parts ? implode(' | ', $parts) : 'Link General';
                        } catch (\Exception $e) {
                            return 'Error';
                        }
                    })
                    ->badge()
                    ->color('info')
                    ->limit(50)
                    ->toggleable(),

                IconColumn::make('subscribed_newsletter')
                    ->label('📧')
                    ->boolean()
                    ->sortable()
                    ->tooltip('Newsletter'),

                IconColumn::make('subscribed_sms')
                    ->label('📱')
                    ->boolean()
                    ->sortable()
                    ->tooltip('SMS')
                    ->toggleable(),

                IconColumn::make('subscribed_whatsapp')
                    ->label('💬')
                    ->boolean()
                    ->sortable()
                    ->tooltip('WhatsApp')
                    ->toggleable(),

                TextColumn::make('guestListEntries_count')
                    ->label('Events')
                    ->counts('guestListEntries')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('contactLogs_count')
                    ->label('Contacts')
                    ->counts('contactLogs')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_interaction_at')
                    ->label('Última interacción')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'lead' => 'Lead',
                        'prospect' => 'Prospect',
                        'customer' => 'Customer',
                        'inactive' => 'Inactive',
                    ]),

                SelectFilter::make('lifecycle_stage')
                    ->label('Lifecycle Stage')
                    ->options([
                        'subscriber' => 'Subscriber',
                        'lead' => 'Lead',
                        'mql' => 'MQL',
                        'sql' => 'SQL',
                        'customer' => 'Customer',
                        'evangelist' => 'Evangelist',
                    ]),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'popup' => 'Popup',
                        'guestlist' => 'Guest List',
                        'manual' => 'Manual',
                        'api' => 'API',
                        'referral' => 'Referral',
                    ]),

                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
