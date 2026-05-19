<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        Campaign::create([
            'name' => 'Campaña de Ejemplo - Post de Instagram',
            'description' => 'Campaña de ejemplo para promocionar un post de Instagram',
            'type' => 'email',
            'status' => 'draft',
            'content' => [
                'email' => [
                    'subject' => '🎉 ¡Nuevo contenido exclusivo para ti!',
                    'body' => '<h2 style="color: #ffffff;">¡Hola!</h2>
<p style="color: #e5e7eb; margin: 20px 0;">
    Tenemos algo especial para ti. Acabamos de publicar un nuevo contenido en nuestro Instagram que sabemos que te va a encantar.
</p>
<p style="color: #e5e7eb; margin: 20px 0;">
    No te lo pierdas y descubre lo último de Lapsique. 🎧✨
</p>',
                    'button_text' => 'Ver en Instagram',
                    'button_url' => 'https://instagram.com/lapsique',
                ],
            ],
            'target_audience' => [
                'tags' => [],
                'lifecycle_stages' => [],
                'statuses' => ['active'],
            ],
            'created_by' => $user?->id,
        ]);
    }
}

