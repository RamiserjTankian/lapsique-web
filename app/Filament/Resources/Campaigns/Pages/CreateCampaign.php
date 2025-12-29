<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Resources\Campaigns\CampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
        $data['created_by'] = auth()->id();

        // Remover campos virtuales
        unset($data['email_subject'], $data['email_body'], $data['button_text'], $data['button_url']);
        unset($data['target_tags'], $data['target_lifecycle_stages'], $data['target_statuses']);

        return $data;
    }
}
