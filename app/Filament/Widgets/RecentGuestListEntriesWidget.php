<?php

namespace App\Filament\Widgets;

use App\Models\GuestListEntry;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentGuestListEntriesWidget extends TableWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(GuestListEntry::query()->latest())
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->limit(20)
                    ->placeholder('-'),
                TextColumn::make('event.title')
                    ->label('Evento')
                    ->limit(20)
                    ->placeholder('-'),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'confirmed',
                        'success' => 'attended',
                        'danger' => ['cancelled', 'no_show'],
                    ]),
                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
