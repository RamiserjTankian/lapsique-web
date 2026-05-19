<?php

namespace App\Filament\Pages;

use App\Services\MailDeliveryService;
use App\Services\MailtrapEventsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Str;
use UnitEnum;

class MailtrapConnection extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Mailtrap';

    protected static ?string $title = 'Mailtrap';

    protected static UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?string $navigationParentItem = 'Configuración';

    protected static ?int $navigationSort = 12;

    public array $summary = [];

    public function mount(): void
    {
        $this->loadSummary();
    }

    public function getView(): string
    {
        return 'filament.pages.mailtrap-connection';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    $this->loadSummary();

                    Notification::make()
                        ->title('Estado actualizado')
                        ->success()
                        ->send();
                }),
            Action::make('sync_events')
                ->label('Sincronizar eventos')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn (): bool => (bool) ($this->summary['can_sync_events'] ?? false))
                ->action(function (MailtrapEventsService $eventsService): void {
                    try {
                        $processed = $eventsService->sync();

                        Notification::make()
                            ->title('Eventos sincronizados')
                            ->body("Se procesaron {$processed} evento(s).")
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('No se pudieron sincronizar eventos')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }

                    $this->loadSummary();
                }),
            Action::make('send_test')
                ->label('Enviar prueba')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (): bool => (bool) ($this->summary['can_send'] ?? false))
                ->form([
                    TextInput::make('email')
                        ->label('Email destino')
                        ->email()
                        ->required()
                        ->default(fn (): string => auth()->user()?->email ?? config('mail.from.address')),
                ])
                ->action(function (array $data, MailDeliveryService $mailDelivery): void {
                    $mailable = new class extends Mailable
                    {
                        public function build(): static
                        {
                            return $this
                                ->subject('Prueba Mailtrap Lapsique Media')
                                ->html(
                                    '<div style="font-family:Arial,sans-serif;padding:24px;">'
                                    . '<h1>Mailtrap operativo</h1>'
                                    . '<p>Este correo confirma que la integracion de Mailtrap en Lapsique Media puede enviar mensajes correctamente.</p>'
                                    . '<p><strong>Fecha UTC:</strong> ' . now()->toDateTimeString() . '</p>'
                                    . '</div>'
                                );
                        }
                    };

                    try {
                        $messageId = $mailDelivery->send($mailable, $data['email'], null, 'system-test');

                        Notification::make()
                            ->title('Correo de prueba enviado')
                            ->body('Message ID: ' . ($messageId ?: 'N/D'))
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('No se pudo enviar el correo de prueba')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function loadSummary(): void
    {
        $apiToken = (string) config('services.mailtrap.api_token', '');
        $eventsEndpoint = (string) config('services.mailtrap.events_endpoint', '');
        $webhookSecret = (string) config('services.mailtrap.webhook_secret', '');
        $mailDefault = (string) config('mail.default', '');
        $smtpUsername = (string) config('mail.mailers.mailtrap.username', '');
        $smtpPassword = (string) config('mail.mailers.mailtrap.password', '');

        $this->summary = [
            'mode' => $apiToken !== '' ? 'api' : ($mailDefault === 'mailtrap' ? 'smtp' : 'other'),
            'mode_label' => $apiToken !== '' ? 'API' : ($mailDefault === 'mailtrap' ? 'SMTP' : 'Sin configurar'),
            'mailer' => $mailDefault,
            'from_address' => (string) config('mail.from.address', ''),
            'from_name' => (string) config('mail.from.name', ''),
            'api_token_masked' => $this->maskSecret($apiToken),
            'api_ready' => $apiToken !== '',
            'smtp_ready' => $smtpUsername !== '' && $smtpPassword !== '',
            'smtp_host' => (string) config('mail.mailers.mailtrap.host', ''),
            'smtp_port' => (string) config('mail.mailers.mailtrap.port', ''),
            'webhook_secret_masked' => $this->maskSecret($webhookSecret),
            'webhook_ready' => $webhookSecret !== '',
            'webhook_url' => route('webhooks.mailtrap.events'),
            'account_id' => (string) config('services.mailtrap.account_id', ''),
            'events_endpoint' => $eventsEndpoint,
            'can_send' => $apiToken !== '' || ($mailDefault === 'mailtrap' && $smtpUsername !== '' && $smtpPassword !== ''),
            'can_sync_events' => $apiToken !== '' && $eventsEndpoint !== '',
        ];
    }

    protected function maskSecret(string $value): string
    {
        if ($value === '') {
            return 'No configurado';
        }

        return Str::mask($value, '*', 4, max(strlen($value) - 8, 0));
    }
}
