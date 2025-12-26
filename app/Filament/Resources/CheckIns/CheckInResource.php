<?php

namespace App\Filament\Resources\CheckIns;

use App\Filament\Resources\CheckIns\Pages\ListCheckIns;
use App\Filament\Pages\GuestListQrScanner;
use App\Models\GuestListEntry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class CheckInResource extends Resource
{
    protected static ?string $model = GuestListEntry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Check-in';

    protected static ?string $modelLabel = 'Check-in';

    protected static ?string $pluralModelLabel = 'Check-ins';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['event', 'customer', 'dj', 'rp', 'inviteLink']))
            ->defaultSort('check_in_at', 'desc')
            ->columns([
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Invitado')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.email')
                    ->label('Email')
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('dj.name')
                    ->label('DJ')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('rp.name')
                    ->label('RP')
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('inviteLink.name')
                    ->label('Link')
                    ->default('Manual')
                    ->badge()
                    ->color('warning')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => ['confirmed', 'attended'],
                        'danger' => ['cancelled', 'no_show'],
                    ]),
                BadgeColumn::make('check_in_at')
                    ->label('Check-in')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn ($record) => $record->check_in_at ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : 'Pendiente'),
                TextColumn::make('check_in_count')
                    ->label('Usos')
                    ->toggleable(),
                TextColumn::make('check_in_limit')
                    ->label('Límite')
                    ->toggleable(),
                TextColumn::make('plus_ones')
                    ->label('Acomp.')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Evento')
                    ->relationship('event', 'title')
                    ->searchable(),
                SelectFilter::make('dj_id')
                    ->label('DJ')
                    ->relationship('dj', 'name')
                    ->searchable(),
                SelectFilter::make('rp_id')
                    ->label('RP')
                    ->relationship('rp', 'name')
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'attended' => 'Asistió',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'No asistió',
                    ]),
                TernaryFilter::make('check_in_at')
                    ->label('Con check-in')
                    ->placeholder('Todos')
                    ->trueLabel('Solo con check-in')
                    ->falseLabel('Sin check-in')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('check_in_at'),
                        false: fn ($query) => $query->whereNull('check_in_at'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->headerActions([
                Action::make('qr_scanner')
                    ->label('Lector QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->url(GuestListQrScanner::getUrl()),
            ])
            ->recordActions([
                Action::make('check_in')
                    ->label('Check-in')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (GuestListEntry $record) => $record->canCheckIn())
                    ->requiresConfirmation()
                    ->action(function (GuestListEntry $record) {
                        if (! $record->checkIn()) {
                            return;
                        }
                    }),
                Action::make('abrir_pase')
                    ->label('Pase')
                    ->icon('heroicon-m-qr-code')
                    ->url(fn (GuestListEntry $record) => $record->getCheckInUrl(), true)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkAction::make('bulk_check_in')
                    ->label('Marcar check-in')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if ($record->canCheckIn()) {
                                $record->checkIn();
                            }
                        }
                    }),
            ])
            ->paginated(true)
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckIns::route('/'),
        ];
    }
}
