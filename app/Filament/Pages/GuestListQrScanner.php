<?php

namespace App\Filament\Pages;

use App\Models\GuestListEntry;
use App\Models\GuestListScan;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class GuestListQrScanner extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Lector QR';

    protected static UnitEnum|string|null $navigationGroup = 'Eventos';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.guest-list-qr-scanner';

    public ?array $lastScan = null;
    public string $manualPayload = '';
    public ?array $scanOverlay = null;
    public ?int $pendingEntryId = null;

    public function handleScan(string $payload): void
    {
        if ($this->scanOverlay) {
            return;
        }

        $payload = trim($payload);

        if ($payload === '') {
            $this->notifyError('El QR está vacío o no es válido.');
            return;
        }

        $tokenOrCode = $this->extractToken($payload);

        if (! $tokenOrCode) {
            $this->notifyError('No pudimos leer el QR. Intenta nuevamente.');
            return;
        }

        $entry = $this->findEntry($tokenOrCode);

        if (! $entry) {
            $this->notifyError('No encontramos al invitado en la guest list.');
            return;
        }

        $canCheckIn = $entry->canCheckIn();
        $usesCounters = $entry->supportsCheckInCounters();
        $scanStatus = $canCheckIn ? 'pending' : ($usesCounters ? 'limit_reached' : 'duplicate');

        $entry->loadMissing(['event', 'customer', 'dj', 'rp', 'inviteLink']);

        $listOwner = $this->resolveListOwner($entry);
        $checkedInAt = $entry->check_in_at?->format('H:i') ?? '—';

        $this->pendingEntryId = $entry->id;

        $this->showScanOverlay([
            'status' => $scanStatus,
            'status_label' => match ($scanStatus) {
                'limit_reached' => 'Consumos agotados',
                'duplicate' => 'Reescaneado',
                default => 'Lectura confirmada',
            },
            'guest' => $entry->customer?->name ?? 'Invitado',
            'event' => $entry->event?->title ?? 'Evento',
            'list_owner' => $listOwner,
            'checked_in_at' => $checkedInAt,
            'check_in_count' => $entry->getCheckInCount(),
            'check_in_limit' => $entry->getCheckInLimit(),
            'remaining_uses' => $entry->getRemainingCheckIns(),
        ]);
    }

    public function submitManualPayload(): void
    {
        $payload = $this->manualPayload;
        $this->manualPayload = '';

        $this->handleScan($payload);
    }

    protected function extractToken(string $payload): ?string
    {
        if ($this->isInviteToken($payload)) {
            return strtolower($payload);
        }

        if ($this->isCheckInCode($payload)) {
            return strtoupper($payload);
        }

        $parsed = parse_url($payload);

        if (is_array($parsed)) {
            $path = $parsed['path'] ?? '';

            if ($path && preg_match('#/check-in/([^/]+)#', $path, $matches)) {
                $candidate = $matches[1] ?? null;
                if ($candidate && $this->isInviteToken($candidate)) {
                    return strtolower($candidate);
                }

                if ($candidate && $this->isCheckInCode($candidate)) {
                    return strtoupper($candidate);
                }
            }

            $query = $parsed['query'] ?? '';
            parse_str($query, $queryParams);

            if (! empty($queryParams['token']) && $this->isInviteToken($queryParams['token'])) {
                return strtolower($queryParams['token']);
            }
        }

        return null;
    }

    protected function findEntry(string $tokenOrCode): ?GuestListEntry
    {
        if ($this->isInviteToken($tokenOrCode)) {
            return GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])
                ->where('invite_token', strtolower($tokenOrCode))
                ->first();
        }

        if ($this->isCheckInCode($tokenOrCode)) {
            $code = strtolower($tokenOrCode);
            $matches = GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])
                ->where('invite_token', 'like', '%' . $code)
                ->get();

            if ($matches->count() === 1) {
                return $matches->first();
            }

            if ($matches->count() > 1) {
                $this->notifyWarning('Código duplicado. Valida el nombre en la lista.');
            }
        }

        return null;
    }

    protected function isInviteToken(string $value): bool
    {
        $value = strtolower($value);

        return strlen($value) === 64 && ctype_xdigit($value);
    }

    protected function isCheckInCode(string $value): bool
    {
        $value = strtoupper($value);

        return strlen($value) === 6 && preg_match('/^[A-Z0-9]+$/', $value) === 1;
    }

    public function dismissScanOverlay(): void
    {
        $this->scanOverlay = null;
        $this->pendingEntryId = null;
        $this->dispatch('qr-overlay:resume');
    }

    protected function showScanOverlay(array $payload): void
    {
        $this->scanOverlay = $payload;
        $this->dispatch('qr-overlay:pause');
    }

    public function confirmScan(): void
    {
        if (! $this->pendingEntryId) {
            $this->dismissScanOverlay();
            return;
        }

        $entry = GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])
            ->find($this->pendingEntryId);

        if (! $entry) {
            $this->notifyError('No encontramos al invitado en la guest list.');
            $this->dismissScanOverlay();
            return;
        }

        $canCheckIn = $entry->canCheckIn();
        $usesCounters = $entry->supportsCheckInCounters();
        $scanStatus = $canCheckIn ? 'checked_in' : ($usesCounters ? 'limit_reached' : 'duplicate');

        if ($canCheckIn) {
            $entry->checkIn();
            $entry->refresh();
        }

        GuestListScan::create([
            'guest_list_entry_id' => $entry->id,
            'user_id' => auth()->id(),
            'scan_status' => $scanStatus,
            'scanned_at' => now(),
        ]);

        $this->setLastScanFromEntry($entry, $scanStatus);
        $this->dismissScanOverlay();
    }

    public function rejectScan(): void
    {
        $entry = $this->pendingEntryId
            ? GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])->find($this->pendingEntryId)
            : null;

        if ($entry) {
            GuestListScan::create([
                'guest_list_entry_id' => $entry->id,
                'user_id' => auth()->id(),
                'scan_status' => 'rejected',
                'scanned_at' => now(),
            ]);

            $this->setLastScanFromEntry($entry, 'rejected');
        }

        $this->dismissScanOverlay();
    }

    public function markScanAsRead(): void
    {
        $entry = $this->pendingEntryId
            ? GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])->find($this->pendingEntryId)
            : null;

        if ($entry) {
            GuestListScan::create([
                'guest_list_entry_id' => $entry->id,
                'user_id' => auth()->id(),
                'scan_status' => 'read',
                'scanned_at' => now(),
            ]);

            $this->setLastScanFromEntry($entry, 'read');
        }

        $this->dismissScanOverlay();
    }

    protected function setLastScanFromEntry(GuestListEntry $entry, string $status): void
    {
        $entry->loadMissing(['event', 'customer', 'dj', 'rp', 'inviteLink']);

        $this->lastScan = [
            'status' => $status,
            'guest' => $entry->customer?->name ?? 'Invitado',
            'email' => $entry->customer?->email,
            'event' => $entry->event?->title ?? 'Evento',
            'checked_in_at' => $entry->check_in_at?->format('d/m/Y H:i'),
            'list_owner' => $this->resolveListOwner($entry),
            'check_in_count' => $entry->getCheckInCount(),
            'check_in_limit' => $entry->getCheckInLimit(),
            'remaining_uses' => $entry->getRemainingCheckIns(),
        ];
    }

    protected function resolveListOwner(GuestListEntry $entry): string
    {
        if ($entry->dj?->name) {
            return 'DJ ' . $entry->dj->name;
        }

        if ($entry->rp?->name) {
            return 'RP ' . $entry->rp->name;
        }

        if ($entry->inviteLink?->name) {
            return $entry->inviteLink->name;
        }

        return 'Guest List';
    }

    protected function notifySuccess(string $message): void
    {
        Notification::make()
            ->title('QR validado')
            ->body($message)
            ->success()
            ->send();
    }

    protected function notifyWarning(string $message): void
    {
        Notification::make()
            ->title('Atención')
            ->body($message)
            ->warning()
            ->send();
    }

    protected function notifyError(string $message): void
    {
        Notification::make()
            ->title('Error')
            ->body($message)
            ->danger()
            ->send();
    }
}
