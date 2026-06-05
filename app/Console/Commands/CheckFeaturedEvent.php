<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckFeaturedEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:featured-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el evento destacado y sus imágenes para compartir en redes sociales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando evento destacado...');
        $this->newLine();

        // Obtener evento destacado (misma lógica que HomeController)
        $featuredEvent = Event::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$featuredEvent) {
            $this->warn('❌ No se encontró ningún evento destacado.');
            $this->info('💡 Para destacar un evento, marca el campo "is_featured" como true en el admin.');
            return 1;
        }

        $this->info("✅ Evento destacado encontrado:");
        $this->line("   📅 Título: {$featuredEvent->title}");
        $this->line("   🔗 Slug: {$featuredEvent->slug}");
        $this->line("   ⭐ Destacado: " . ($featuredEvent->is_featured ? 'Sí' : 'No'));
        $this->line("   📝 Headline: " . ($featuredEvent->headline ?: 'No tiene'));
        $this->line("   📍 Ubicación: " . ($featuredEvent->venue ?: 'No especificada'));
        $this->line("   🏙️ Ciudad: " . ($featuredEvent->city ?: 'No especificada'));
        $this->line("   📆 Fecha: " . ($featuredEvent->starts_at ? $featuredEvent->starts_at->format('Y-m-d H:i') : 'No especificada'));
        $this->newLine();

        // Verificar imágenes
        $this->info('🖼️ Verificando imágenes del evento...');
        $this->newLine();

        $coverUrl = match ($featuredEvent->featured_poster) {
            'vertical' => $featuredEvent->getFirstMediaUrl('cover_vertical', 'poster_vertical') ?: $featuredEvent->getFirstMediaUrl('cover_vertical'),
            'cover' => $featuredEvent->getFirstMediaUrl('cover', 'cover_large') ?: $featuredEvent->getFirstMediaUrl('cover'),
            default => $featuredEvent->getFirstMediaUrl('cover_horizontal', 'poster_horizontal')
                ?: $featuredEvent->getFirstMediaUrl('cover', 'cover_large')
                ?: $featuredEvent->getFirstMediaUrl('cover'),
        };

        if (!$coverUrl) {
            $this->error('❌ No se encontró ninguna imagen para el evento destacado.');
            $this->info('💡 Sube una imagen en el admin del evento (cover, cover_horizontal o cover_vertical).');
            return 1;
        }

        // Convertir a URL absoluta
        if (!str_starts_with($coverUrl, 'http')) {
            $coverUrl = url($coverUrl);
        }
        if (str_starts_with($coverUrl, 'http://')) {
            $coverUrl = str_replace('http://', 'https://', $coverUrl);
        }

        $this->info("✅ Imagen encontrada:");
        $this->line("   📸 URL: {$coverUrl}");
        $this->line("   🎨 Tipo de poster: {$featuredEvent->featured_poster}");
        $this->newLine();

        // Verificar que la imagen sea accesible
        $this->info('🌐 Verificando accesibilidad de la imagen...');
        
        try {
            $ch = curl_init($coverUrl);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $fileSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
            curl_close($ch);

            if ($httpCode === 200) {
                $this->info("✅ La imagen es accesible (HTTP {$httpCode})");
                $this->line("   📦 Tipo: {$contentType}");
                $this->line("   💾 Tamaño: " . ($fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'Desconocido'));
                
                // Verificar requisitos de WhatsApp
                $this->newLine();
                $this->info('📱 Verificando requisitos para WhatsApp...');
                
                $warnings = [];
                if ($fileSize > 300 * 1024) {
                    $warnings[] = "⚠️  El tamaño es mayor a 300KB (recomendado para WhatsApp)";
                }
                if (!str_contains($contentType, 'image/jpeg') && !str_contains($contentType, 'image/png')) {
                    $warnings[] = "⚠️  El formato debería ser JPG o PNG para WhatsApp";
                }
                
                if (empty($warnings)) {
                    $this->info("✅ La imagen cumple con los requisitos de WhatsApp");
                } else {
                    foreach ($warnings as $warning) {
                        $this->warn($warning);
                    }
                }
            } else {
                $this->error("❌ La imagen no es accesible (HTTP {$httpCode})");
                $this->info("💡 Verifica que la imagen esté en el storage público y que los permisos sean correctos.");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error al verificar la imagen: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🔗 URLs para compartir:');
        $this->line("   🏠 Homepage: " . route('home'));
        $this->line("   📅 Evento: " . route('events.show', $featuredEvent));
        $this->newLine();

        // Generar meta tags de ejemplo
        $this->info('📋 Meta tags que se generarán:');
        $this->line("   og:title: {$featuredEvent->title} | Trascendental");
        $this->line("   og:description: " . ($featuredEvent->headline ?: $featuredEvent->title));
        $this->line("   og:image: {$coverUrl}");
        $this->line("   og:type: event");
        $this->newLine();

        $this->info('✅ Verificación completada!');
        $this->newLine();
        $this->info('💡 Para actualizar la caché de WhatsApp:');
        $this->line("   1. Ve a: https://developers.facebook.com/tools/debug/");
        $this->line("   2. Pega: " . route('home'));
        $this->line("   3. Haz clic en 'Scrape Again' varias veces");

        return 0;
    }
}
