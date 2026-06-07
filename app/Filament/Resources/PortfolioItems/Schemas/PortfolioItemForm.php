<?php

namespace App\Filament\Resources\PortfolioItems\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PortfolioItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->label('Slug / URL')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'photo' => 'Fotografía',
                        'video' => 'Reel / Aftermovie',
                    ])
                    ->required()
                    ->default('photo')
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state === 'photo') {
                            $set('source', 'upload');
                            $set('youtube_url', null);
                            $set('youtube_id', null);
                        }
                    }),
                Select::make('orientation')
                    ->label('Orientación')
                    ->options([
                        'horizontal' => 'Horizontal',
                        'vertical' => 'Vertical',
                    ])
                    ->required()
                    ->default('horizontal'),
                Select::make('source')
                    ->label('Fuente de video')
                    ->options([
                        'upload' => 'Subir archivo',
                        'youtube' => 'YouTube',
                    ])
                    ->default('upload')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state === 'youtube') {
                            $set('type', 'video');
                            return;
                        }

                        $set('youtube_url', null);
                        $set('youtube_id', null);
                    }),
                TextInput::make('youtube_url')
                    ->label('URL de YouTube')
                    ->url()
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('source') === 'youtube')
                    ->required(fn (Get $get) => $get('source') === 'youtube')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if (! $state) {
                            return;
                        }

                        $youtubeId = null;
                        $isShorts = false;
                        
                        if (preg_match('/youtube\.com\/shorts\/([^?]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                            $isShorts = true;
                        } elseif (preg_match('/[?&]v=([^&]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                        } elseif (preg_match('/youtu\.be\/([^?]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                        } elseif (preg_match('/youtube\.com\/embed\/([^?]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                        }

                        if ($youtubeId) {
                            $set('youtube_id', $youtubeId);
                            
                            // Si es Shorts, establecer orientación vertical automáticamente
                            if ($isShorts) {
                                $set('orientation', 'vertical');
                            }
                        }
                    })
                    ->helperText('Pega la URL del video y se extraerá el ID automáticamente. Los Shorts se detectarán como verticales.'),
                TextInput::make('youtube_id')
                    ->label('YouTube ID')
                    ->maxLength(50)
                    ->readOnly()
                    ->visible(fn (Get $get) => $get('source') === 'youtube'),
                Textarea::make('caption')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpanFull(),
                CheckboxList::make('tags')
                    ->label('Giros de negocio / Tags')
                    ->options([
                        'barbershop' => '💈 Barbershop',
                        'food' => '🍔 Food & Restaurant',
                        'nightlife' => '🌃 Nightlife',
                        'events' => '🎉 Events',
                        'fitness' => '💪 Fitness & Gym',
                        'beauty' => '💄 Beauty & Spa',
                        'fashion' => '👔 Fashion',
                        'music' => '🎵 Music',
                        'art' => '🎨 Art & Culture',
                        'travel' => '✈️ Travel',
                        'lifestyle' => '🌟 Lifestyle',
                        'automotive' => '🚗 Automotive',
                        'real-estate' => '🏠 Real Estate',
                        'tech' => '💻 Tech',
                        'sports' => '⚽ Sports',
                        'wedding' => '💍 Wedding',
                        'corporate' => '🏢 Corporate',
                        'hospitality' => '🏨 Hospitality',
                    ])
                    ->columns(2)
                    ->gridDirection('row')
                    ->helperText('Selecciona los giros de negocio o categorías que aplican a esta pieza')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Visible en la web')
                    ->default(true),
                Toggle::make('is_featured')
                    ->label('Destacado'),
                TextInput::make('priority')
                    ->label('Orden')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Section::make('Archivo')
                    ->description('Sube una pieza nueva o apunta a un archivo público ya existente.')
                    ->schema([
                        TextInput::make('asset_path')
                            ->label('Ruta pública del archivo')
                            ->placeholder('/videos/trascendental/reel.mp4')
                            ->maxLength(255)
                            ->helperText('Úsalo para fotos o videos que ya viven en public/images o public/videos.')
                            ->visible(fn (Get $get) => $get('source') !== 'youtube')
                            ->columnSpanFull(),
                        TextInput::make('poster_path')
                            ->label('Ruta pública del poster')
                            ->placeholder('/images/trascendental/projects/poster.webp')
                            ->maxLength(255)
                            ->helperText('Opcional. Sirve como portada cuando el archivo principal es video.')
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('asset')
                            ->label('Archivo')
                            ->collection('asset')
                            ->visible(fn (Get $get) => $get('source') !== 'youtube')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/jpg',
                                'video/mp4',
                                'video/quicktime',
                                'video/webm',
                            ])
                            ->maxSize(256000)
                            ->helperText('Opcional. Admite JPG, PNG, WEBP y videos MP4/MOV/WEBM hasta 256MB.')
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('poster')
                            ->label('Poster (opcional)')
                            ->collection('poster')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                            ->imageEditor()
                            ->helperText('Imagen de portada para los reels o aftermovies.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
