<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use App\Jobs\ProcessCampaignJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar campos virtuales desde content y target_audience
        if (isset($data['content']['email'])) {
            $data['email_subject'] = $data['content']['email']['subject'] ?? null;
            $data['email_body'] = $data['content']['email']['body'] ?? null;
            $data['button_text'] = $data['content']['email']['button_text'] ?? null;
            $data['button_url'] = $data['content']['email']['button_url'] ?? null;
        }

        if (isset($data['target_audience'])) {
            $data['target_tags'] = $data['target_audience']['tags'] ?? null;
            $data['target_lifecycle_stages'] = $data['target_audience']['lifecycle_stages'] ?? null;
            $data['target_statuses'] = $data['target_audience']['statuses'] ?? null;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Preparar el contenido del email
        $content = [
            'email' => [
                'subject' => $data['email_subject'] ?? null,
                'body' => $data['email_body'] ?? null,
                'button_text' => $data['button_text'] ?? 'Ver Más',
                'button_url' => $data['button_url'] ?? null,
            ],
        ];

        // Preparar la audiencia objetivo
        $targetAudience = [];
        if (!empty($data['target_tags'])) {
            $targetAudience['tags'] = $data['target_tags'];
        }
        if (!empty($data['target_lifecycle_stages'])) {
            $targetAudience['lifecycle_stages'] = $data['target_lifecycle_stages'];
        }
        if (!empty($data['target_statuses'])) {
            $targetAudience['statuses'] = $data['target_statuses'];
        }

        $data['content'] = $content;
        $data['target_audience'] = $targetAudience;

        // Remover campos virtuales
        unset($data['email_subject'], $data['email_body'], $data['button_text'], $data['button_url']);
        unset($data['target_tags'], $data['target_lifecycle_stages'], $data['target_statuses']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Enviar Campaña')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Enviar Campaña')
                ->modalDescription(fn ($record) => 
                    "¿Estás seguro de que deseas enviar esta campaña? Se enviará a " . 
                    ($record->getRecipientsQuery()->count() ?? 0) . " destinatarios."
                )
                ->modalSubmitActionLabel('Sí, Enviar')
                ->visible(fn ($record) => in_array($record->status, ['draft', 'scheduled', 'paused']))
                ->action(function ($record) {
                    try {
                        // Preparar el contenido de la campaña
                        $content = [
                            'email' => [
                                'subject' => $record->email_subject ?? 'Newsletter',
                                'body' => $record->email_body ?? '',
                                'button_text' => $record->button_text ?? 'Ver Más',
                                'button_url' => $record->button_url ?? null,
                            ],
                        ];

                        // Preparar la audiencia objetivo
                        $targetAudience = [];
                        if (!empty($record->target_tags)) {
                            $targetAudience['tags'] = $record->target_tags;
                        }
                        if (!empty($record->target_lifecycle_stages)) {
                            $targetAudience['lifecycle_stages'] = $record->target_lifecycle_stages;
                        }
                        if (!empty($record->target_statuses)) {
                            $targetAudience['statuses'] = $record->target_statuses;
                        }

                        // Actualizar la campaña
                        $record->update([
                            'content' => $content,
                            'target_audience' => $targetAudience,
                            'status' => $record->starts_at && $record->starts_at->isFuture() ? 'scheduled' : 'active',
                            'created_by' => auth()->id(),
                        ]);

                        // Si no hay fecha programada, enviar inmediatamente
                        if (!$record->starts_at || $record->starts_at->isPast()) {
                            ProcessCampaignJob::dispatch($record);
                            
                            Notification::make()
                                ->title('Campaña Enviada')
                                ->body('La campaña se está procesando y los emails se enviarán en breve.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Campaña Programada')
                                ->body('La campaña se enviará automáticamente en la fecha programada.')
                                ->success()
                                ->send();
                        }

                        // Refrescar el record para mostrar el nuevo estado
                        $this->record->refresh();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al Enviar')
                            ->body('Ocurrió un error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('view_stats')
                ->label('Ver Estadísticas')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->url(fn ($record) => CampaignResource::getUrl('view', ['record' => $record]))
                ->visible(fn ($record) => in_array($record->status, ['active', 'completed'])),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
