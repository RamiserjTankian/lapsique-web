<?php

namespace App\Filament\Pages;

use App\Models\PortfolioItem;
use App\Models\SiteSetting;
use App\Models\Video;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class SiteSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del Sitio';

    protected static UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public $logo_watermark = null;

    public string $booking_title = '';

    public string $booking_subtitle = '';

    public int $booking_price = 3000;

    public string $booking_whatsapp = '';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }

    public function getView(): string
    {
        return 'filament.pages.site-settings';
    }

    public function mount(): void
    {
        $logoPath = $this->getLogoPath();
        $this->logo_watermark = $logoPath ? 'images/logo-watermark.png' : null;

        $settings = SiteSetting::currentOrNew();
        $this->booking_title = $settings->booking_title ?? '';
        $this->booking_subtitle = $settings->booking_subtitle ?? '';
        $this->booking_price = $settings->booking_price ?? (int) config('booking.content_price', 3000);
        $this->booking_whatsapp = $settings->booking_whatsapp ?? '';

        $this->schema->fill([
            'logo_watermark' => $this->logo_watermark,
            'meta_pixel_id' => $settings->meta_pixel_id,
            'booking_title' => $this->booking_title,
            'booking_subtitle' => $this->booking_subtitle,
            'booking_og_image' => $settings->booking_og_image,
            'djset_og_image' => $settings->djset_og_image,
            'booking_price' => $this->booking_price,
            'booking_whatsapp' => $this->booking_whatsapp,
            'home_hero_proof_1_title' => $settings->home_hero_proof_1_title,
            'home_hero_proof_1_source' => $settings->home_hero_proof_1_source,
            'home_hero_proof_1_reference' => $settings->home_hero_proof_1_reference,
        ]);
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Logo del Sitio')
                    ->description('Configura el logo que se usará como marca de agua en las fotografías del portafolio.')
                    ->schema([
                        FileUpload::make('logo_watermark')
                            ->label('Logo para Marca de Agua')
                            ->helperText('Sube un logo en formato PNG con transparencia. Se guardará automáticamente en public/images/logo-watermark.png')
                            ->acceptedFileTypes(['image/png'])
                            ->maxSize(2048)
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([null, '16:9', '4:3', '1:1'])
                            ->disk('public')
                            ->directory('images')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable()
                            ->openable(),
                    ]),

                Section::make('Meta Pixel')
                    ->description('ID del pixel para el sitio público (Inertia). Si está vacío, se usa META_PIXEL_ID del .env. La API de campañas y CAPI se configuran en .env y en Configuración Meta Ads.')
                    ->schema([
                        TextInput::make('meta_pixel_id')
                            ->label('Pixel ID')
                            ->placeholder('Ej: 123456789012345')
                            ->maxLength(32)
                            ->helperText('Opcional. Debe coincidir con el pixel usado en META_CAPI_ENABLED y en Ads Manager.'),
                    ]),

                Section::make('Landing de proyectos')
                    ->description('Configuración base para CTAs, contacto y activos compartidos del sitio.')
                    ->schema([
                        TextInput::make('booking_title')
                            ->label('Título principal')
                            ->placeholder('Ej: Producción ejecutiva / booking')
                            ->maxLength(255),

                        TextInput::make('booking_subtitle')
                            ->label('Subtítulo / tagline')
                            ->placeholder('Ej: 1 reel + 10 fotos editadas en una sola sesión')
                            ->maxLength(255),

                        FileUpload::make('booking_og_image')
                            ->label('Imagen para compartir en redes (Open Graph)')
                            ->helperText('Recomendado 1200×630 px (JPG o PNG). Se usa al compartir el enlace del home / sesión de contenido en WhatsApp, Facebook, etc.')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1.91:1', '16:9'])
                            ->disk('public')
                            ->directory('images/og')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable()
                            ->openable(),

                        TextInput::make('booking_price')
                            ->label('Precio (MXN)')
                            ->numeric()
                            ->default(config('booking.content_price', 3000))
                            ->minValue(0)
                            ->prefix('$')
                            ->suffix('MXN')
                            ->helperText('Precio en pesos mexicanos que se cargará al cliente.'),

                        TextInput::make('booking_whatsapp')
                            ->label('WhatsApp de contacto')
                            ->placeholder('Ej: 5219841234567')
                            ->helperText('Número con código de país, sin + ni espacios. Usado para el botón de contacto.')
                            ->maxLength(30),
                    ]),

                Section::make('Open Graph alterno')
                    ->description('Open Graph para enlaces públicos de Lapsique Media en WhatsApp, Facebook, etc.')
                    ->schema([
                        FileUpload::make('djset_og_image')
                            ->label('Imagen para compartir (Open Graph)')
                            ->helperText('Recomendado 1200×630 px. Si está vacío, se usa una foto destacada del portafolio o un thumbnail de video.')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1.91:1', '16:9'])
                            ->disk('public')
                            ->directory('images/og')
                            ->visibility('public')
                            ->downloadable()
                            ->previewable()
                            ->openable(),
                    ]),

                Section::make('Video del hero (landing negocios)')
                    ->description('Un video en el panel lateral del home. Se reproduce en silencio en loop. Si dejas el origen vacío, se usa el primer video del portafolio.')
                    ->schema(self::homeHeroProofSlotFields()),
            ]);
    }

    /**
     * @return list<\Filament\Forms\Components\Component>
     */
    protected static function homeHeroProofSlotFields(): array
    {
        $titleKey = 'home_hero_proof_1_title';
        $sourceKey = 'home_hero_proof_1_source';
        $referenceKey = 'home_hero_proof_1_reference';

        return [
            TextInput::make($titleKey)
                ->label('Título')
                ->placeholder('Opcional. Si está vacío, usa el del origen.')
                ->maxLength(120),

            Select::make($sourceKey)
                ->label('Origen')
                ->options([
                    '' => 'Automático (portafolio)',
                    'portfolio_item' => 'Ítem de portafolio',
                    'video' => 'Video del catálogo',
                    'youtube' => 'YouTube (ID o URL)',
                    'url' => 'URL directa (.mp4, .webm)',
                ])
                ->native(false),

            Select::make($referenceKey)
                ->label('Referencia')
                ->options(function (Get $get) use ($sourceKey): array {
                    return match ($get($sourceKey)) {
                        'portfolio_item' => PortfolioItem::query()
                            ->where('is_active', true)
                            ->orderByDesc('is_featured')
                            ->orderBy('priority')
                            ->get()
                            ->mapWithKeys(fn (PortfolioItem $item) => [
                                (string) $item->id => $item->title ?: "Portafolio #{$item->id}",
                            ])
                            ->all(),
                        'video' => Video::query()
                            ->orderByDesc('is_featured')
                            ->orderBy('priority')
                            ->get()
                            ->mapWithKeys(fn (Video $video) => [
                                (string) $video->id => $video->title ?: "Video #{$video->id}",
                            ])
                            ->all(),
                        default => [],
                    };
                })
                ->searchable()
                ->visible(fn (Get $get): bool => in_array($get($sourceKey), ['portfolio_item', 'video'], true)),

            TextInput::make($referenceKey)
                ->label(fn (Get $get): string => $get($sourceKey) === 'url'
                    ? 'URL del archivo'
                    : 'YouTube')
                ->placeholder(fn (Get $get): string => $get($sourceKey) === 'url'
                    ? 'https://...'
                    : 'ID de 11 caracteres o URL completa')
                ->url(fn (Get $get): bool => $get($sourceKey) === 'url')
                ->visible(fn (Get $get): bool => in_array($get($sourceKey), ['youtube', 'url'], true)),
        ];
    }

    protected static function normalizeUploadPath(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        return filled($value) ? (string) $value : null;
    }

    protected function getLogoPath(): ?string
    {
        $logoPath = public_path('images/logo-watermark.png');

        return file_exists($logoPath) ? $logoPath : null;
    }

    protected function saveLogo(string $filePath): void
    {
        try {
            // Si la ruta ya es absoluta y existe, usarla directamente
            if (file_exists($filePath)) {
                $sourcePath = $filePath;
            } else {
                // Intentar desde storage/public
                $sourcePath = Storage::disk('public')->path($filePath);

                // Si no existe ahí, intentar como ruta relativa desde public
                if (! file_exists($sourcePath)) {
                    $sourcePath = public_path($filePath);
                }
            }

            $destinationPath = public_path('images/logo-watermark.png');

            // Crear directorio si no existe
            if (! file_exists(public_path('images'))) {
                mkdir(public_path('images'), 0755, true);
            }

            // Copiar el archivo a la ubicación final
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);

                // Limpiar caché de vistas
                \Artisan::call('view:clear');
            } else {
                throw new \Exception("No se pudo encontrar el archivo en: {$filePath}");
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar el logo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function deleteLogo(): void
    {
        $logoPath = public_path('images/logo-watermark.png');

        if (file_exists($logoPath)) {
            unlink($logoPath);

            Notification::make()
                ->title('Logo eliminado')
                ->success()
                ->send();
        }
    }

    public function save(): void
    {
        $data = $this->schema->getState();

        if (isset($data['logo_watermark']) && $data['logo_watermark']) {
            $uploadedPath = is_array($data['logo_watermark'])
                ? ($data['logo_watermark'][0] ?? null)
                : $data['logo_watermark'];

            if ($uploadedPath) {
                $this->saveLogo($uploadedPath);
            }
        }

        SiteSetting::query()->firstOrCreate([])->update([
            'meta_pixel_id' => filled($data['meta_pixel_id'] ?? null) ? $data['meta_pixel_id'] : null,
            'booking_title' => $data['booking_title'] ?? null,
            'booking_subtitle' => $data['booking_subtitle'] ?? null,
            'booking_og_image' => self::normalizeUploadPath($data['booking_og_image'] ?? null),
            'djset_og_image' => self::normalizeUploadPath($data['djset_og_image'] ?? null),
            'booking_price' => (int) ($data['booking_price'] ?? config('booking.content_price', 3000)),
            'booking_whatsapp' => $data['booking_whatsapp'] ?? null,
            'home_hero_proof_1_title' => filled($data['home_hero_proof_1_title'] ?? null) ? $data['home_hero_proof_1_title'] : null,
            'home_hero_proof_1_source' => filled($data['home_hero_proof_1_source'] ?? null) ? $data['home_hero_proof_1_source'] : null,
            'home_hero_proof_1_reference' => filled($data['home_hero_proof_1_reference'] ?? null) ? (string) $data['home_hero_proof_1_reference'] : null,
        ]);

        $this->logo_watermark = $this->getLogoPath() ? 'images/logo-watermark.png' : null;

        $this->schema->fill([
            'logo_watermark' => $this->logo_watermark,
        ]);

        Notification::make()
            ->title('Configuración guardada correctamente')
            ->success()
            ->send();
    }
}
