<?php

namespace App\Filament\Resources\SessionCustomers\Tables;

use App\Filament\Resources\SessionCustomers\Support\SessionCustomerModalActions;
use App\Support\SessionCustomerInsights;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SessionCustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return SessionCustomerInsights::applyBookingStats(
                    $query->whereHas('contentBookings'),
                );
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email),

                TextColumn::make('phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('confirmed_bookings_count')
                    ->label('Sesiones')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('total_revenue')
                    ->label('Ingresos')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '$'.number_format((int) $state, 0).' MXN')
                    ->color('success'),

                TextColumn::make('pending_delivery_count')
                    ->label('Entrega pendiente')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state) => (int) $state > 0 ? (string) $state : 'Al día'),

                TextColumn::make('last_booking_at')
                    ->label('Último pago')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('fiscal_rfc')
                    ->label('RFC')
                    ->searchable()
                    ->placeholder('Sin RFC')
                    ->toggleable(),

                TextColumn::make('fiscal_legal_name')
                    ->label('Razón social')
                    ->limit(28)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('instagram_handle')
                    ->label('Instagram')
                    ->formatStateUsing(fn ($state) => $state ? '@'.$state : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_interaction_at')
                    ->label('Última interacción')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('has_fiscal')
                    ->label('Datos fiscales')
                    ->trueLabel('Con RFC')
                    ->falseLabel('Sin RFC')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('fiscal_rfc')->where('fiscal_rfc', '!=', ''),
                        false: fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('fiscal_rfc')->orWhere('fiscal_rfc', '')),
                    ),

                SelectFilter::make('status')
                    ->label('Estado CRM')
                    ->options([
                        'lead' => 'Lead',
                        'prospect' => 'Prospecto',
                        'customer' => 'Cliente',
                        'inactive' => 'Inactivo',
                    ]),

                Filter::make('pending_delivery')
                    ->label('Con entrega pendiente')
                    ->query(fn (Builder $query) => $query->whereHas(
                        'contentBookings',
                        fn (Builder $q) => $q->where('status', 'confirmed')->whereNull('deliverables_ready_at'),
                    )),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                SessionCustomerModalActions::edit()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_booking_at', 'desc');
    }
}
