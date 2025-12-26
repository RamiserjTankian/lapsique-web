<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function afterCreate(): void
    {
        $this->syncLineup();
    }

    protected function syncLineup(): void
    {
        /** @var Event $event */
        $event = $this->record;
        
        // Get lineup data directly from form state
        $lineupData = $this->form->getRawState()['lineup'] ?? [];

        \Log::info('Creating lineup for new event: ' . $event->id, [
            'lineup_data' => $lineupData,
            'count' => count($lineupData)
        ]);

        if (empty($lineupData)) {
            // No lineup to sync
            return;
        }

        $lineup = collect($lineupData)
            ->filter(function ($row) {
                $enabled = $row['enabled'] ?? true;
                $hasDj = !empty($row['dj_id']);
                return $enabled && $hasDj;
            })
            ->values();

        $payload = [];
        $position = 1;

        foreach ($lineup as $row) {
            $djId = (int) $row['dj_id'];
            if (isset($payload[$djId])) {
                continue; // Skip duplicates
            }

            $payload[$djId] = [
                'role' => $row['role'] ?? 'warmup',
                'position' => $position++,
                'time_slot' => $row['time_slot'] ?? null,
                'guest_limit' => isset($row['guest_limit']) && $row['guest_limit'] !== '' ? (int) $row['guest_limit'] : null,
            ];
        }

        \Log::info('Final payload for new event', [
            'payload' => $payload,
            'count' => count($payload)
        ]);

        $event->djs()->sync($payload);
        
        \Log::info('Lineup created successfully', [
            'event_id' => $event->id,
            'djs_count' => $event->djs()->count()
        ]);
    }
}
