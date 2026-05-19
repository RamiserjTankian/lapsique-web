<?php

namespace App\Filament\Resources\SessionCustomers\Support;

use App\Filament\Resources\SessionCustomers\Schemas\SessionCustomerForm;
use App\Filament\Resources\SessionCustomers\SessionCustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;

class SessionCustomerModalActions
{
    /**
     * @var list<string>
     */
    public const FORM_ATTRIBUTES = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'instagram_handle',
        'status',
        'fiscal_legal_name',
        'fiscal_rfc',
        'fiscal_regime',
        'fiscal_cfdi_use',
        'fiscal_email',
        'fiscal_zip',
        'fiscal_address',
        'fiscal_city',
        'fiscal_state',
        'fiscal_country',
        'notes',
        'subscribed_whatsapp',
        'subscribed_newsletter',
    ];

    public static function create(): Action
    {
        return self::applySlideOver(
            Action::make('createSessionCustomer')
                ->label('Nuevo cliente')
                ->icon(Heroicon::OutlinedPlus)
                ->modalHeading('Alta de cliente')
                ->modalDescription('Completa el asistente para registrar un cliente de sesiones.')
                ->steps(SessionCustomerForm::steps())
        )
            ->action(function (array $data) {
                $payload = self::normalizePayload($data);
                $payload['source'] = 'manual';

                $customer = Customer::create($payload);

                Notification::make()
                    ->title('Cliente creado')
                    ->body($customer->name)
                    ->success()
                    ->send();

                return redirect(SessionCustomerResource::getUrl('view', ['record' => $customer]));
            });
    }

    public static function edit(?string $name = 'editSessionCustomer', ?int $startStep = null): Action
    {
        $action = self::applySlideOver(
            Action::make($name)
                ->label('Editar cliente')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->modalHeading(fn (Customer $record): string => 'Editar — '.$record->name)
                ->modalDescription('Actualiza contacto, facturación y notas sin salir del perfil.')
                ->fillForm(fn (Customer $record): array => self::formData($record))
                ->steps(SessionCustomerForm::steps())
        )
            ->action(function (array $data, Customer $record): void {
                $record->update(self::normalizePayload($data));

                Notification::make()
                    ->title('Cliente actualizado')
                    ->success()
                    ->send();
            });

        if ($startStep !== null) {
            $action->startOnStep($startStep);
        }

        return $action;
    }

    public static function editAtStep(int $step, string $label, Heroicon $icon): Action
    {
        return self::edit('editSessionCustomerStep'.$step, $step)
            ->label($label)
            ->icon($icon)
            ->modalHeading(fn (Customer $record): string => $label.' — '.$record->name);
    }

    protected static function applySlideOver(Action $action): Action
    {
        return $action
            ->slideOver()
            ->modalWidth('3xl')
            ->closeModalByClickingAway(false);
    }

    /**
     * @return array<string, mixed>
     */
    public static function formData(Customer $record): array
    {
        return $record->only(self::FORM_ATTRIBUTES);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizePayload(array $data): array
    {
        $payload = Arr::only($data, self::FORM_ATTRIBUTES);

        if (isset($payload['fiscal_rfc']) && is_string($payload['fiscal_rfc'])) {
            $payload['fiscal_rfc'] = strtoupper(trim($payload['fiscal_rfc']));
        }

        if (isset($payload['instagram_handle']) && is_string($payload['instagram_handle'])) {
            $payload['instagram_handle'] = ltrim(trim($payload['instagram_handle']), '@');
        }

        return $payload;
    }
}
