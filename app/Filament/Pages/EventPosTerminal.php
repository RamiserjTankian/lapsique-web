<?php

namespace App\Filament\Pages;

use App\Models\AybProduct;
use App\Models\PosCharge;
use App\Models\TicketAttendee;
use App\Services\EventPosService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class EventPosTerminal extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Cajero';

    protected static UnitEnum|string|null $navigationGroup = 'POS';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.event-pos-terminal';

    public string $manualPayload = '';
    public ?int $selectedProductId = null;
    public int $selectedQuantity = 1;
    public string $productTypeFilter = 'beverage';
    public string $productSearch = '';
    public ?array $scanOverlay = null;
    public ?array $lastCharge = null;
    public ?int $pendingAttendeeId = null;
    public array $products = [];

    public function mount(EventPosService $posService): void
    {
        $this->products = $posService->getAvailableProducts()->all();

        if (filled($this->products)) {
            $this->selectedProductId = $this->products[0]->id;
        }
    }

    public function updatedSelectedQuantity(mixed $value): void
    {
        $this->selectedQuantity = max((int) $value, 1);
    }

    public function setProductTypeFilter(string $type): void
    {
        $this->productTypeFilter = in_array($type, ['beverage', 'food'], true) ? $type : 'beverage';
    }

    public function selectProduct(int $productId): void
    {
        $this->selectedProductId = $productId;
    }

    public function handleScan(string $payload, EventPosService $posService): void
    {
        if ($this->scanOverlay) {
            return;
        }

        if (! $this->selectedProductId) {
            $this->notifyWarning('Primero configura y selecciona un producto AyB para cobrar.');

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

        $attendee = $this->findTicketAttendee($tokenOrCode);

        if (! $attendee) {
            $this->notifyError('No encontramos un ticket válido para este cliente.');

            return;
        }

        try {
            $product = $posService->getProduct($this->selectedProductId);
        } catch (\Throwable $exception) {
            $this->notifyError($exception->getMessage());

            return;
        }

        $quantity = max($this->selectedQuantity, 1);
        $total = round(((float) $product->price) * $quantity, 2);
        $balance = $posService->getBalanceForAttendee($attendee);
        $availableBalance = round((float) ($balance?->balance ?? 0), 2);

        $attendee->loadMissing(['customer', 'event', 'product', 'order']);

        $this->pendingAttendeeId = $attendee->id;
        $this->scanOverlay = [
            'customer' => $attendee->customer?->name ?? $attendee->name ?? 'Cliente',
            'email' => $attendee->customer?->email ?? $attendee->email,
            'event' => $attendee->event?->title ?? 'Evento',
            'ticket' => $attendee->product?->name ?? 'Ticket',
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_type' => $product->type,
            'quantity' => $quantity,
            'unit_price' => round((float) $product->price, 2),
            'total' => $total,
            'currency' => (string) ($product->currency ?: ($balance?->currency ?? config('pos.currency', 'MXN'))),
            'balance_before' => $availableBalance,
            'balance_after' => round($availableBalance - $total, 2),
            'can_charge' => $balance !== null && $availableBalance >= $total,
            'ticket_code' => $attendee->getCheckInCode(),
        ];

        $this->dispatch('qr-overlay:pause');
    }

    public function submitManualPayload(EventPosService $posService): void
    {
        $payload = $this->manualPayload;
        $this->manualPayload = '';

        $this->handleScan($payload, $posService);
    }

    public function confirmCharge(EventPosService $posService): void
    {
        if (! $this->pendingAttendeeId || ! $this->scanOverlay) {
            $this->dismissOverlay();

            return;
        }

        if (! ($this->scanOverlay['can_charge'] ?? false)) {
            $this->notifyWarning('No hay saldo suficiente para este cargo.');
            $this->dismissOverlay();

            return;
        }

        $attendee = TicketAttendee::with(['customer', 'event', 'product', 'order'])->find($this->pendingAttendeeId);

        if (! $attendee) {
            $this->notifyError('No encontramos el ticket del cliente.');
            $this->dismissOverlay();

            return;
        }

        try {
            $charge = $posService->chargeFromAttendee(
                $attendee,
                (int) $this->scanOverlay['product_id'],
                (int) $this->scanOverlay['quantity'],
                auth()->id(),
            );

            $this->lastCharge = [
                'customer' => $charge->customer?->name ?? 'Cliente',
                'event' => $charge->event?->title ?? 'Evento',
                'item' => $charge->item_name,
                'quantity' => $charge->quantity,
                'total' => $charge->total,
                'currency' => $charge->currency,
                'balance_after' => $charge->balance_after,
                'created_at' => $charge->created_at?->format('d/m/Y H:i'),
            ];

            Notification::make()
                ->title('Cargo aplicado')
                ->body("Se descontaron {$charge->total} {$charge->currency} del saldo del cliente.")
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            $this->notifyError($exception->getMessage());
        }

        $this->dismissOverlay();
    }

    public function cancelCharge(): void
    {
        $this->dismissOverlay();

        Notification::make()
            ->title('Cargo cancelado')
            ->body('No se realizó ningún descuento.')
            ->info()
            ->send();
    }

    public function getRecentChargesProperty()
    {
        return PosCharge::query()
            ->with(['customer', 'event', 'user', 'aybProduct'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function getSelectedProductProperty(): ?AybProduct
    {
        foreach ($this->products as $product) {
            if ($product->id === $this->selectedProductId) {
                return $product;
            }
        }

        return null;
    }

    public function getFilteredProductsProperty(): array
    {
        $search = mb_strtolower(trim($this->productSearch));

        return array_values(array_filter($this->products, function (AybProduct $product) use ($search): bool {
            if ($product->type !== $this->productTypeFilter) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            return str_contains(mb_strtolower($product->name), $search)
                || str_contains(mb_strtolower((string) $product->notes), $search);
        }));
    }

    protected function dismissOverlay(): void
    {
        $this->scanOverlay = null;
        $this->pendingAttendeeId = null;
        $this->dispatch('qr-overlay:resume');
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

    protected function findTicketAttendee(string $tokenOrCode): ?TicketAttendee
    {
        if ($this->isInviteToken($tokenOrCode)) {
            return TicketAttendee::with(['customer', 'event', 'product', 'order'])
                ->where('invite_token', strtolower($tokenOrCode))
                ->first();
        }

        if ($this->isCheckInCode($tokenOrCode)) {
            $matches = TicketAttendee::with(['customer', 'event', 'product', 'order'])
                ->where('invite_token', 'like', '%' . strtolower($tokenOrCode))
                ->get();

            if ($matches->count() === 1) {
                return $matches->first();
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
