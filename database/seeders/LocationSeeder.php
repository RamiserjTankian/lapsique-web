<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'UMi Fest Venue',
                'description' => 'UMi Fest (Nov 22 - Jan 14), sunrise-to-sunset sessions with BRYZ and friends.',
                'city' => 'Riviera Maya',
                'country' => 'México',
                'maps_url' => null,
                'is_featured' => true,
                'priority' => 0,
            ],
        ];

        foreach ($locations as $index => $location) {
            $baseSlug = Str::slug($location['name']);
            $slug = $baseSlug ?: 'location-' . ($index + 1);
            $suffix = 1;

            while (Location::withTrashed()->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            Location::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $location['name'],
                    'slug' => $slug,
                    'description' => $location['description'] ?? null,
                    'address' => $location['address'] ?? null,
                    'city' => $location['city'] ?? null,
                    'country' => $location['country'] ?? null,
                    'maps_url' => $location['maps_url'] ?? null,
                    'is_featured' => $location['is_featured'] ?? false,
                    'priority' => $location['priority'] ?? ($index + 1),
                ]
            );
        }
    }
}
