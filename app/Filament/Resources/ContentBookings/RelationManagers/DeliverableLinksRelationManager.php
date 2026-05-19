<?php

namespace App\Filament\Resources\ContentBookings\RelationManagers;

use App\Services\ContentBookingDeliverablesService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DeliverableLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'deliverableLinks';

    protected static ?string $title = 'Enlaces de entregables (Google Drive)';

    protected static ?string $recordTitleAttribute = 'label';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->latest())
            ->emptyStateHeading('Sin entregables publicados')
            ->emptyStateDescription('Añade un enlace de Google Drive. El cliente recibirá un correo y lo verá en su portal.')
            ->columns([
                TextColumn::make('label')
                    ->label('Nombre')
                    ->placeholder('Entrega'),

                TextColumn::make('url')
                    ->label('Enlace')
                    ->limit(50)
                    ->url(fn (Model $record) => $record->url, true)
                    ->copyable(),

                TextColumn::make('notified_at')
                    ->label('Correo enviado')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendiente')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('created_at')
                    ->label('Añadido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Añadir enlace')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Añadir entregables')
                    ->modalDescription('El cliente recibirá un correo con el enlace y también podrá verlo en su portal.')
                    ->form([
                        TextInput::make('label')
                            ->label('Nombre (opcional)')
                            ->placeholder('Ej: Reels editados, Fotos retocadas')
                            ->maxLength(120),

                        TextInput::make('url')
                            ->label('Enlace de Google Drive')
                            ->url()
                            ->required()
                            ->maxLength(2048)
                            ->placeholder('https://drive.google.com/drive/folders/...')
                            ->helperText('Carpeta o archivo compartido en Drive.'),
                    ])
                    ->using(function (array $data): Model {
                        return app(ContentBookingDeliverablesService::class)->addDriveLink(
                            $this->getOwnerRecord(),
                            $data['url'],
                            $data['label'] ?? null,
                        );
                    })
                    ->successNotification(
                        Notification::make()
                            ->title('Entregable añadido')
                            ->body('Se envió el correo al cliente y el enlace ya está en su portal.')
                            ->success(),
                    ),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Eliminar')
                    ->requiresConfirmation(),
            ]);
    }
}
