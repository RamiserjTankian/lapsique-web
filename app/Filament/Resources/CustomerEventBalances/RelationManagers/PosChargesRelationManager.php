<?php

namespace App\Filament\Resources\CustomerEventBalances\RelationManagers;

use App\Models\PosCharge;
use App\Services\EventPosService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosChargesRelationManager extends RelationManager
{
    protected static string $relationship = 'posCharges';

    protected static ?string $title = 'Historial de consumos';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('item_name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('item_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'food' ? 'Alimento' : 'Bebida'),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Precio unitario')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->label('Saldo restante')
                    ->money(fn ($record) => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cajero')
                    ->default('Sistema')
                    ->toggleable(),
            ])
            ->actions([
                Action::make('cancel_charge')
                    ->label('Cancelar consumo')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar consumo')
                    ->modalDescription('Este consumo se removera del historial activo y el saldo se recalculara para devolver el monto gastado.')
                    ->modalSubmitActionLabel('Cancelar consumo')
                    ->form([
                        Textarea::make('reason')
                            ->label('Motivo')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Ejemplo: cargo incorrecto o consumo de prueba'),
                    ])
                    ->action(function (PosCharge $record, array $data): void {
                        try {
                            app(EventPosService::class)->cancelCharge(
                                $record,
                                $data['reason'] ?? null,
                                auth()->id()
                            );

                            Notification::make()
                                ->title('Consumo cancelado')
                                ->body('Se devolvio el saldo gastado y se recalculo el balance del cliente.')
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('No se pudo cancelar el consumo')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
