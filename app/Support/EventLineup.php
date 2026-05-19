<?php

namespace App\Support;

use Illuminate\Support\Collection;

class EventLineup
{
    public static function payloadFromState(array $lineupData): array
    {
        $lineup = collect($lineupData)
            ->filter(function (array $row): bool {
                $enabled = $row['enabled'] ?? true;
                $hasDj = ! empty($row['dj_id']);

                return $enabled && $hasDj;
            })
            ->values();

        $payload = [];
        $position = 1;

        foreach ($lineup as $row) {
            $djId = (int) $row['dj_id'];

            if ($djId === 0 || isset($payload[$djId])) {
                continue;
            }

            $baseEntry = [
                'role' => $row['role'] ?? 'warmup',
                'position' => $position,
                'time_slot' => $row['time_slot'] ?? null,
                'guest_limit' => isset($row['guest_limit']) && $row['guest_limit'] !== ''
                    ? (int) $row['guest_limit']
                    : null,
            ];

            $b2bDjId = ! empty($row['is_b2b']) ? (int) ($row['b2b_dj_id'] ?? 0) : 0;

            if ($b2bDjId > 0 && $b2bDjId !== $djId && ! isset($payload[$b2bDjId])) {
                $payload[$djId] = [
                    ...$baseEntry,
                    'b2b_with_dj_id' => $b2bDjId,
                ];

                $payload[$b2bDjId] = [
                    ...$baseEntry,
                    'b2b_with_dj_id' => $djId,
                ];

                $position++;

                continue;
            }

            $payload[$djId] = [
                ...$baseEntry,
                'b2b_with_dj_id' => null,
            ];

            $position++;
        }

        return $payload;
    }

    public static function formStateFromDjs(Collection $djs): array
    {
        $rows = [];
        $processed = [];
        $djsById = $djs->keyBy('id');

        foreach ($djs as $dj) {
            if (isset($processed[$dj->id])) {
                continue;
            }

            $partner = self::resolvePartner($dj, $djsById, $processed);

            if ($partner) {
                $processed[$dj->id] = true;
                $processed[$partner->id] = true;

                $rows[] = [
                    'dj_id' => $dj->id,
                    'is_b2b' => true,
                    'b2b_dj_id' => $partner->id,
                    'role' => $dj->pivot->role ?? 'warmup',
                    'time_slot' => $dj->pivot->time_slot ?? $partner->pivot->time_slot ?? null,
                    'guest_limit' => $dj->pivot->guest_limit ?? $partner->pivot->guest_limit ?? null,
                    'enabled' => true,
                ];

                continue;
            }

            $processed[$dj->id] = true;

            $rows[] = [
                'dj_id' => $dj->id,
                'is_b2b' => false,
                'b2b_dj_id' => null,
                'role' => $dj->pivot->role ?? 'warmup',
                'time_slot' => $dj->pivot->time_slot ?? null,
                'guest_limit' => $dj->pivot->guest_limit ?? null,
                'enabled' => true,
            ];
        }

        return $rows;
    }

    public static function displayEntries(Collection $djs): Collection
    {
        $entries = collect();
        $processed = [];
        $djsById = $djs->keyBy('id');

        foreach ($djs as $dj) {
            if (isset($processed[$dj->id])) {
                continue;
            }

            $partner = self::resolvePartner($dj, $djsById, $processed);

            if ($partner) {
                $processed[$dj->id] = true;
                $processed[$partner->id] = true;

                $entries->push([
                    'type' => 'b2b',
                    'djs' => collect([$dj, $partner]),
                    'role' => $dj->pivot->role ?? 'warmup',
                    'time_slot' => $dj->pivot->time_slot ?? $partner->pivot->time_slot ?? null,
                    'guest_limit' => $dj->pivot->guest_limit ?? $partner->pivot->guest_limit ?? null,
                ]);

                continue;
            }

            $processed[$dj->id] = true;

            $entries->push([
                'type' => 'single',
                'djs' => collect([$dj]),
                'role' => $dj->pivot->role ?? 'warmup',
                'time_slot' => $dj->pivot->time_slot ?? null,
                'guest_limit' => $dj->pivot->guest_limit ?? null,
            ]);
        }

        return $entries->values();
    }

    private static function resolvePartner(object $dj, Collection $djsById, array $processed): ?object
    {
        $partnerId = (int) ($dj->pivot->b2b_with_dj_id ?? 0);

        if ($partnerId === 0 || isset($processed[$partnerId])) {
            return null;
        }

        $partner = $djsById->get($partnerId);

        if (! $partner) {
            return null;
        }

        $isReciprocal = (int) ($partner->pivot->b2b_with_dj_id ?? 0) === (int) $dj->id;
        $samePosition = (int) ($partner->pivot->position ?? -1) === (int) ($dj->pivot->position ?? -2);
        $sameRole = ($partner->pivot->role ?? null) === ($dj->pivot->role ?? null);

        return $isReciprocal && $samePosition && $sameRole ? $partner : null;
    }
}
