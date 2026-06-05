<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->label('Slug / URL')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('youtube_url')
                    ->label('URL de YouTube')
                    ->url()
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if (!$state) {
                            return;
                        }
                        
                        // Extract YouTube ID from various URL formats
                        $youtubeId = null;
                        
                        // Pattern 1: https://www.youtube.com/watch?v=VIDEO_ID
                        if (preg_match('/[?&]v=([^&]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                        }
                        // Pattern 2: https://youtu.be/VIDEO_ID
                        elseif (preg_match('/youtu\.be\/([^?]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                        }
                        // Pattern 3: https://www.youtube.com/embed/VIDEO_ID
                        elseif (preg_match('/youtube\.com\/embed\/([^?]+)/', $state, $matches)) {
                            $youtubeId = $matches[1];
                        }
                        
                        if ($youtubeId) {
                            $set('youtube_id', $youtubeId);
                            $set('thumbnail_url', "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg");
                        }
                    })
                    ->helperText('Pega la URL del video de YouTube y se extraerá automáticamente el ID y thumbnail'),
                TextInput::make('youtube_id')
                    ->label('YouTube ID')
                    ->required()
                    ->maxLength(50)
                    ->readOnly()
                    ->helperText('Se genera automáticamente desde la URL'),
                Section::make('Miniatura del Video')
                    ->description('Puedes subir una miniatura personalizada o usar la de YouTube')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('thumbnail')
                            ->label('Subir Miniatura Personalizada')
                            ->collection('thumbnail')
                            ->image()
                            ->maxSize(5120)
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                            ])
                            ->helperText('Sube una imagen personalizada (16:9). Si no subes nada, se usará la miniatura de YouTube.')
                            ->columnSpanFull(),
                        TextInput::make('thumbnail_url')
                            ->label('URL de Miniatura de YouTube')
                            ->url()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Se genera automáticamente desde YouTube')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                TextInput::make('location')
                    ->label('Ubicación')
                    ->maxLength(255),
                TextInput::make('maps_url')
                    ->label('Link de Maps')
                    ->url()
                    ->maxLength(255)
                    ->helperText('Enlace de Google Maps del spot de grabación'),
                Select::make('djs')
                    ->label('DJs en el video')
                    ->multiple()
                    ->relationship('djs', 'name')
                    ->preload()
                    ->searchable()
                    ->helperText('Selecciona los DJs que aparecen en este set'),
                CheckboxList::make('tags')
                    ->label('Tags')
                    ->options([
                        'psique-originals' => 'TRASCENDENTAL ORIGINALS (Producción propia)',
                        'youtube' => 'YOUTUBE (Video de referencia del DJ)',
                    ])
                    ->columns(1)
                    ->helperText('Selecciona el tipo de video: producción propia o video de referencia del artista')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->label('Publicado')
                    ->timezone('America/Mexico_City'),
                Toggle::make('is_featured')
                    ->label('Destacado'),
                TextInput::make('priority')
                    ->label('Orden')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
