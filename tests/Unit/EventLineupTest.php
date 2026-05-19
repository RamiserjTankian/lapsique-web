<?php

namespace Tests\Unit;

use App\Support\EventLineup;
use Tests\TestCase;

class EventLineupTest extends TestCase
{
    public function test_it_builds_payload_with_b2b_entries(): void
    {
        $payload = EventLineup::payloadFromState([
            [
                'dj_id' => 1,
                'is_b2b' => true,
                'b2b_dj_id' => 2,
                'role' => 'headliner',
                'time_slot' => '23:00 - 01:00',
                'guest_limit' => 30,
                'enabled' => true,
            ],
            [
                'dj_id' => 3,
                'is_b2b' => false,
                'role' => 'warmup',
                'time_slot' => '21:00 - 23:00',
                'guest_limit' => null,
                'enabled' => true,
            ],
        ]);

        $this->assertSame([
            1 => [
                'role' => 'headliner',
                'position' => 1,
                'time_slot' => '23:00 - 01:00',
                'guest_limit' => 30,
                'b2b_with_dj_id' => 2,
            ],
            2 => [
                'role' => 'headliner',
                'position' => 1,
                'time_slot' => '23:00 - 01:00',
                'guest_limit' => 30,
                'b2b_with_dj_id' => 1,
            ],
            3 => [
                'role' => 'warmup',
                'position' => 2,
                'time_slot' => '21:00 - 23:00',
                'guest_limit' => null,
                'b2b_with_dj_id' => null,
            ],
        ], $payload);
    }

    public function test_it_groups_existing_b2b_rows_into_one_form_entry(): void
    {
        $rows = EventLineup::formStateFromDjs(collect([
            $this->makeDj(1, 'DJ One', [
                'role' => 'headliner',
                'position' => 1,
                'time_slot' => '23:00 - 01:00',
                'guest_limit' => 20,
                'b2b_with_dj_id' => 2,
            ]),
            $this->makeDj(2, 'DJ Two', [
                'role' => 'headliner',
                'position' => 1,
                'time_slot' => '23:00 - 01:00',
                'guest_limit' => 20,
                'b2b_with_dj_id' => 1,
            ]),
            $this->makeDj(3, 'DJ Three', [
                'role' => 'local',
                'position' => 2,
                'time_slot' => null,
                'guest_limit' => null,
                'b2b_with_dj_id' => null,
            ]),
        ]));

        $this->assertSame([
            [
                'dj_id' => 1,
                'is_b2b' => true,
                'b2b_dj_id' => 2,
                'role' => 'headliner',
                'time_slot' => '23:00 - 01:00',
                'guest_limit' => 20,
                'enabled' => true,
            ],
            [
                'dj_id' => 3,
                'is_b2b' => false,
                'b2b_dj_id' => null,
                'role' => 'local',
                'time_slot' => null,
                'guest_limit' => null,
                'enabled' => true,
            ],
        ], $rows);
    }

    private function makeDj(int $id, string $name, array $pivot): object
    {
        $dj = new \stdClass();
        $dj->id = $id;
        $dj->name = $name;
        $dj->instagram_handle = null;
        $dj->tags = [];
        $dj->pivot = (object) $pivot;

        return $dj;
    }
}
