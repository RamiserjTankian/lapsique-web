<?php

namespace App\Filament\Resources\PortfolioItems\Pages;

use App\Filament\Resources\PortfolioItems\PortfolioItemResource;
use App\Models\PortfolioItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BulkUploadPortfolioItems extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = PortfolioItemResource::class;

    protected static ?string $title = 'Carga masiva';

    protected string $view = 'filament.resources.portfolio-items.pages.bulk-upload-portfolio-items';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'type' => 'auto',
            'orientation' => 'auto',
            'is_active' => true,
            'is_featured' => false,
            'priority' => 0,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                FileUpload::make('files')
                    ->label('Archivos')
                    ->helperText('Selecciona varias fotos o videos para cargarlos al portafolio.')
                    ->multiple()
                    ->minFiles(1)
                    ->maxFiles(50)
                    ->maxSize(256000)
                    ->disk('public')
                    ->directory('portfolio-uploads')
                    ->preserveFilenames()
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/jpg',
                        'video/mp4',
                        'video/quicktime',
                        'video/webm',
                    ])
                    ->columnSpanFull(),
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'auto' => 'Auto (según archivo)',
                        'photo' => 'Fotografía',
                        'video' => 'Reel / Aftermovie',
                    ])
                    ->default('auto'),
                Select::make('orientation')
                    ->label('Orientación')
                    ->options([
                        'auto' => 'Auto (según dimensiones)',
                        'horizontal' => 'Horizontal',
                        'vertical' => 'Vertical',
                    ])
                    ->default('auto'),
                Toggle::make('is_active')
                    ->label('Visible en la web')
                    ->default(true),
                Toggle::make('is_featured')
                    ->label('Destacado'),
                TextInput::make('priority')
                    ->label('Orden inicial')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $files = $data['files'] ?? [];

        if (empty($files)) {
            Notification::make()
                ->title('Selecciona al menos un archivo.')
                ->warning()
                ->send();
            return;
        }

        $priorityBase = (int) ($data['priority'] ?? 0);
        $typeSelection = $data['type'] ?? 'auto';
        $orientationSelection = $data['orientation'] ?? 'auto';
        $isActive = (bool) ($data['is_active'] ?? true);
        $isFeatured = (bool) ($data['is_featured'] ?? false);

        DB::transaction(function () use ($files, $priorityBase, $typeSelection, $orientationSelection, $isActive, $isFeatured) {
            foreach (array_values($files) as $index => $path) {
                $fullPath = Storage::disk('public')->path($path);
                $mimeType = Storage::disk('public')->mimeType($path) ?? '';
                $filename = pathinfo($path, PATHINFO_FILENAME) ?: 'portfolio-item';
                $title = Str::headline(str_replace(['-', '_'], ' ', $filename));
                $slugBase = Str::slug($title ?: $filename);
                $slugBase = $slugBase !== '' ? $slugBase : 'portfolio-item';

                $type = $typeSelection === 'auto'
                    ? (str_starts_with($mimeType, 'video/') ? 'video' : 'photo')
                    : $typeSelection;

                $orientation = $orientationSelection === 'auto'
                    ? $this->detectOrientation($fullPath, $mimeType)
                    : $orientationSelection;

                $item = PortfolioItem::create([
                    'title' => $title ?: null,
                    'slug' => $this->makeUniqueSlug($slugBase),
                    'type' => $type,
                    'orientation' => $orientation,
                    'source' => 'upload',
                    'is_active' => $isActive,
                    'is_featured' => $isFeatured,
                    'priority' => $priorityBase + $index,
                ]);

                $item->addMediaFromDisk($path, 'public')
                    ->toMediaCollection('asset');

                Storage::disk('public')->delete($path);
            }
        });

        $this->form->fill([
            'type' => $typeSelection,
            'orientation' => $orientationSelection,
            'is_active' => $isActive,
            'is_featured' => $isFeatured,
            'priority' => $priorityBase,
            'files' => [],
        ]);

        Notification::make()
            ->title('Archivos cargados correctamente.')
            ->success()
            ->send();
    }

    protected function detectOrientation(string $fullPath, string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            $dimensions = @getimagesize($fullPath);
            if (is_array($dimensions) && isset($dimensions[0], $dimensions[1])) {
                return $dimensions[0] >= $dimensions[1] ? 'horizontal' : 'vertical';
            }
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'vertical';
        }

        return 'horizontal';
    }

    protected function makeUniqueSlug(string $base): string
    {
        $slug = $base;
        $suffix = 1;

        while (PortfolioItem::where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }
}
