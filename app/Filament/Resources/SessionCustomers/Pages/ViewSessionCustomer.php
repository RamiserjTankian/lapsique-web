<?php

namespace App\Filament\Resources\SessionCustomers\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Filament\Resources\SessionCustomers\SessionCustomerResource;
use App\Filament\Resources\SessionCustomers\Support\SessionCustomerModalActions;
use App\Filament\Resources\SessionCustomers\Widgets\SessionCustomerProfileStatsWidget;
use App\Models\Customer;
use App\Services\CustomerPortalAccessService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewSessionCustomer extends ViewRecord
{
    protected static string $resource = SessionCustomerResource::class;

    public function infolist(Schema $schema): Schema
    {
        return SessionCustomerResource::infolist($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            SessionCustomerModalActions::edit(),

            ActionGroup::make([
                SessionCustomerModalActions::editAtStep(1, 'Contacto', Heroicon::OutlinedUser),
                SessionCustomerModalActions::editAtStep(2, 'Facturación', Heroicon::OutlinedDocumentText),
                SessionCustomerModalActions::editAtStep(3, 'Operación', Heroicon::OutlinedClipboardDocumentList),
            ])
                ->label('Ir a paso…')
                ->icon(Heroicon::OutlinedChevronDown)
                ->color('gray')
                ->button(),

            Action::make('quickNotes')
                ->label('Notas rápidas')
                ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                ->color('gray')
                ->slideOver()
                ->fillForm(fn (Customer $record) => ['notes' => $record->notes])
                ->schema([
                    Textarea::make('notes')
                        ->label('Notas internas')
                        ->rows(8)
                        ->required(),
                ])
                ->action(function (array $data, Customer $record): void {
                    $record->update(['notes' => $data['notes']]);

                    Notification::make()
                        ->title('Notas actualizadas')
                        ->success()
                        ->send();
                }),

            Action::make('manageBookings')
                ->label('Ver reservas')
                ->icon(Heroicon::OutlinedPhoto)
                ->url(ContentBookingResource::getUrl('index'))
                ->openUrlInNewTab(),

            Action::make('resendPortalAccess')
                ->label('Reenviar acceso al portal')
                ->icon(Heroicon::OutlinedKey)
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Se generará una nueva contraseña temporal y se enviará por email al cliente.')
                ->action(function (Customer $record): void {
                    app(CustomerPortalAccessService::class)->regeneratePortalAccessAndNotify($record);

                    Notification::make()
                        ->title('Acceso al portal reenviado')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SessionCustomerProfileStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
