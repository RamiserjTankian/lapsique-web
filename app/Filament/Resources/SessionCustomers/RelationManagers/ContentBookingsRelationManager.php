<?php

namespace App\Filament\Resources\SessionCustomers\RelationManagers;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentBookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'contentBookings';

    protected static ?string $title = 'Sesiones y entregables';

    protected static ?string $recordTitleAttribute = 'public_id';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['slot'])->latest())
            ->columns([
                TextColumn::make('public_id')
                    ->label('ID')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('slot_summary')
                    ->label('Sesión')
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn ($record) => $record->status_color),

                TextColumn::make('formatted_amount')
                    ->label('Monto'),

                TextColumn::make('payment_provider')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'stripe' => 'Stripe',
                        'mercadopago' => 'Mercado Pago',
                        default => $state ? ucfirst($state) : '—',
                    }),

                TextColumn::make('paid_at')
                    ->label('Pagado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),

                TextColumn::make('deliverables_drive_url')
                    ->label('Drive')
                    ->limit(30)
                    ->placeholder('Sin enlace')
                    ->toggleable(),

                TextColumn::make('deliverables_ready_at')
                    ->label('Publicado en portal')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendiente')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pendiente de pago',
                        'confirmed' => 'Confirmada',
                        'pending' => 'Pago en revisión',
                        'failed' => 'Fallida',
                        'cancelled' => 'Cancelada',
                    ]),
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => ContentBookingResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->label('Gestionar entrega')
                    ->url(fn ($record) => ContentBookingResource::getUrl('edit', ['record' => $record])),
                Action::make('publish')
                    ->label('Publicar entregables')
                    ->icon('heroicon-o-folder-open')
                    ->color('info')
                    ->visible(fn ($record) => filled($record->deliverables_drive_url) && ! $record->deliverables_ready_at)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['deliverables_ready_at' => now()])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
