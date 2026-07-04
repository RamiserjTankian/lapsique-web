<?php

namespace App\Filament\Resources\ContentBookings\Pages;

use App\Filament\Resources\ContentBookings\ContentBookingResource;
use App\Models\BookingSlot;
use App\Models\ContentBooking;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateContentBooking extends CreateRecord
{
    protected static string $resource = ContentBookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): ContentBooking {
            $slot = BookingSlot::query()
                ->whereKey($data['booking_slot_id'])
                ->where('is_active', true)
                ->where('date', '>=', today())
                ->whereColumn('booked_count', '<', 'max_bookings')
                ->lockForUpdate()
                ->first();

            if (! $slot) {
                Notification::make()
                    ->title('Horario no disponible')
                    ->body('El horario seleccionado ya fue ocupado o está desactivado.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            $serviceType = $data['service_type'] ?? ContentBooking::SERVICE_CONTENT_SESSION;
            $status = $data['status'] ?? 'pending_payment';

            $booking = ContentBooking::create([
                ...$data,
                'public_id' => Str::uuid()->toString(),
                'booking_slot_id' => $slot->id,
                'service_type' => $serviceType,
                'amount' => filled($data['amount'] ?? null)
                    ? (int) $data['amount']
                    : ContentBooking::amountForService($serviceType),
                'currency' => $data['currency'] ?? 'MXN',
                'status' => $status,
                'payment_provider' => $data['payment_provider'] ?? 'internal',
                'paid_at' => $status === 'confirmed' ? now() : null,
                'metadata' => [
                    ...($data['metadata'] ?? []),
                    'source' => 'filament_manual_create',
                    'service_type' => $serviceType,
                ],
            ]);

            if (! in_array($booking->status, ['failed', 'cancelled'], true)) {
                $slot->increment('booked_count');
            }

            return $booking;
        });
    }
}
