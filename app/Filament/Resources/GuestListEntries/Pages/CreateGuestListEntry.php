<?php

namespace App\Filament\Resources\GuestListEntries\Pages;

use App\Filament\Resources\GuestListEntries\GuestListEntryResource;
use App\Jobs\SendEventConfirmationJob;
use App\Models\GuestListEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGuestListEntry extends CreateRecord
{
    protected static string $resource = GuestListEntryResource::class;

    protected int $qrQuantity = 1;
    protected array $createdEntryIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $quantity = isset($data['qr_quantity']) ? (int) $data['qr_quantity'] : 1;
        $this->qrQuantity = max($quantity, 1);
        unset($data['qr_quantity']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $entries = [];
        $entries[] = GuestListEntry::create($data);

        for ($i = 1; $i < $this->qrQuantity; $i++) {
            $entries[] = GuestListEntry::create($data);
        }

        $this->createdEntryIds = collect($entries)->pluck('id')->all();

        return $entries[0];
    }

    protected function afterCreate(): void
    {
        $entryIds = $this->createdEntryIds ?: array_filter([$this->getRecord()?->id]);

        if (empty($entryIds)) {
            return;
        }

        $entries = GuestListEntry::query()
            ->with('customer')
            ->whereIn('id', $entryIds)
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $missingEmail = $entries->every(fn (GuestListEntry $entry) => ! $entry->customer || ! $entry->customer->email);

        if ($missingEmail) {
            Notification::make()
                ->title('Email faltante')
                ->body('El invitado no tiene un email registrado para enviar la confirmación.')
                ->warning()
                ->send();
            return;
        }

        foreach ($entries as $entry) {
            if ($entry->status !== 'confirmed') {
                continue;
            }

            if (! $entry->customer || ! $entry->customer->email) {
                continue;
            }

            SendEventConfirmationJob::dispatchAfterResponse($entry);
        }
    }
}
