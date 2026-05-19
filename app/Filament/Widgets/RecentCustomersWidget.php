<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentCustomersWidget extends TableWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(Customer::query()->latest())
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(25),
                TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->limit(30),
                TextColumn::make('source')
                    ->label('Fuente')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
