<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\GuestListInviteLink;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateLink')
                ->label('Generar Link de Registro')
                ->icon('heroicon-o-link')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('dj_id')
                        ->label('DJ (Opcional)')
                        ->options(fn () => $this->record->djs()->pluck('name', 'id'))
                        ->searchable(),
                    \Filament\Forms\Components\Select::make('rp_id')
                        ->label('RP (Opcional)')
                        ->options(fn () => $this->record->rps()->pluck('name', 'id'))
                        ->searchable(),
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nombre del Link')
                        ->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('max_registrations')
                        ->label('Límite de Registros')
                        ->numeric()
                        ->minValue(0),
                ])
                ->action(function (array $data): void {
                    $link = GuestListInviteLink::create([
                        'event_id' => $this->record->id,
                        'dj_id' => $data['dj_id'] ?? null,
                        'rp_id' => $data['rp_id'] ?? null,
                        'name' => $data['name'] ?? ($data['dj_id'] ? 'Link para ' . $this->record->djs()->find($data['dj_id'])->name : 'Link General'),
                        'token' => GuestListInviteLink::generateToken(),
                        'max_registrations' => $data['max_registrations'] ?? null,
                        'is_active' => true,
                    ]);

                    Notification::make()
                        ->title('Link generado exitosamente')
                        ->success()
                        ->body('Link: ' . $link->invite_url)
                        ->persistent()
                        ->send();
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->syncLineup();
    }

    protected function syncLineup(): void
    {
        /** @var Event $event */
        $event = $this->record;
        
        // Get lineup data directly from form state
        $lineupData = $this->form->getRawState()['lineup'] ?? [];

        \Log::info('Syncing lineup for event: ' . $event->id, [
            'lineup_data' => $lineupData,
            'count' => count($lineupData)
        ]);

        if (empty($lineupData)) {
            // If no lineup data provided, don't change anything
            return;
        }

        $lineup = collect($lineupData)
            ->filter(function ($row) {
                $enabled = $row['enabled'] ?? true;
                $hasDj = !empty($row['dj_id']);
                
                \Log::info('Processing lineup row', [
                    'dj_id' => $row['dj_id'] ?? 'null',
                    'role' => $row['role'] ?? 'null',
                    'enabled' => $enabled,
                    'has_dj' => $hasDj,
                    'will_include' => $enabled && $hasDj
                ]);
                
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

        \Log::info('Final payload to sync', [
            'payload' => $payload,
            'count' => count($payload)
        ]);

        $event->djs()->sync($payload);
        
        \Log::info('Lineup synced successfully', [
            'event_id' => $event->id,
            'djs_count' => $event->djs()->count()
        ]);
    }
}
