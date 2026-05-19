<?php

namespace App\Filament\Widgets;

use App\Models\ContactLog;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentContactLogsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactLog::query()->latest())
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->limit(20)
                    ->placeholder('-'),
                BadgeColumn::make('channel')
                    ->label('Canal')
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                        'info' => 'whatsapp',
                        'warning' => 'call',
                        'secondary' => ['popup', 'guestlist', 'manual'],
                    ]),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'sent',
                        'success' => ['delivered', 'opened', 'clicked'],
                        'danger' => ['bounced', 'failed'],
                    ]),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
