<?php

namespace App\Filament\Resources\StripeSettings\Tables;

use App\Models\StripeSetting;
use App\Services\StripeIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StripeSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')
                    ->label('Habilitado')
                    ->boolean(),

                TextColumn::make('connection_status')
                    ->label('Conexión')
                    ->badge()
                    ->formatStateUsing(fn (StripeSetting $record): string => $record->connectionStatusLabel())
                    ->color(fn (string $state): string => match ($state) {
                        'connected' => 'success',
                        'misconfigured', 'disabled' => 'warning',
                        'error' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('currency')
                    ->label('Moneda')
                    ->badge(),

                TextColumn::make('masked_secret')
                    ->label('Secret key')
                    ->state(fn (StripeSetting $record): ?string => $record->maskedSecretKey() ?? '—'),

                TextColumn::make('last_verified_at')
                    ->label('Verificado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated(false)
            ->recordActions([
                Action::make('verifyConnection')
                    ->label('Verificar conexión')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (StripeSetting $record): void {
                        $ok = app(StripeIntegrationService::class)->verifyConnection($record);

                        Notification::make()
                            ->title($ok ? 'Stripe conectado correctamente' : 'No se pudo verificar Stripe')
                            ->body($ok ? null : ($record->fresh()->last_error_message ?? 'Revisa las llaves.'))
                            ->{$ok ? 'success' : 'danger'}()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
