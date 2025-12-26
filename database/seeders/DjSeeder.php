<?php

namespace Database\Seeders;

use App\Models\Dj;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DjSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
        * Run the database seeds.
        */
    public function run(): void
    {
        $djs = [
            ['name' => 'BRYZ', 'headline' => 'Esente Records', 'featured' => true, 'priority' => 0],
            ['name' => 'Kapi'],
            ['name' => 'Baruck'],
            ['name' => 'John Pavas'],
            ['name' => 'C.C[TDL]'],
            ['name' => 'Rui'],
            ['name' => 'Lagunes Jr.'],
            ['name' => 'Jimbo-Star'],
            ['name' => 'Kalani', 'featured' => true, 'priority' => 10],
            ['name' => 'Giselle'],
        ];

        foreach ($djs as $index => $dj) {
            $baseSlug = Str::slug($dj['name']);
            $slug = $baseSlug ?: 'dj-' . ($index + 1);
            $suffix = 1;

            while (Dj::withTrashed()->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            Dj::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $dj['name'],
                    'slug' => $slug,
                    'bio' => $dj['headline'] ?? null,
                    'instagram_handle' => null,
                    'youtube_url' => null,
                    'soundcloud_url' => null,
                    'website_url' => null,
                    'is_featured' => $dj['featured'] ?? false,
                    'priority' => $dj['priority'] ?? ($index + 1),
                ]
            );
        }
    }
}
