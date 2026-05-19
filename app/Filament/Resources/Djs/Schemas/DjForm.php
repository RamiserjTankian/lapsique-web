<?php

namespace App\Filament\Resources\Djs\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DjForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre artístico')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->label('Slug / URL')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Se usará en los links públicos.'),
                Textarea::make('bio')
                    ->label('Biografía')
                    ->rows(5)
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('profile')
                    ->label('Foto principal')
                    ->collection('profile')
                    ->image()
                    ->imageEditor()
                    ->imageEditorMode(2)
                    ->imageEditorViewportWidth(1600)
                    ->imageEditorViewportHeight(900)
                    ->imageEditorAspectRatios([
                        '16:9' => 'Card / Hero (16:9)',
                        '1:1' => 'Cuadrado (avatar)',
                    ])
                    ->required()
                    ->maxSize(51200)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    ->helperText('Ajusta el recorte en 16:9 para que se vea perfecto en las cards y hero. Máximo 50MB.')
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galería')
                    ->collection('gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->maxFiles(20)
                    ->maxSize(51200)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    ->helperText('Puedes agregar hasta 20 fotos. Máximo 50MB por imagen.')
                    ->columnSpanFull(),

                TextInput::make('instagram_handle')
                    ->label('Instagram')
                    ->prefix('@')
                    ->maxLength(255),
                TextInput::make('youtube_url')
                    ->label('Enlace a YouTube')
                    ->url()
                    ->maxLength(255),
                TextInput::make('soundcloud_url')
                    ->label('SoundCloud')
                    ->url()
                    ->maxLength(255),
                TextInput::make('website_url')
                    ->label('Sitio web')
                    ->url()
                    ->maxLength(255)
                    ->columnSpanFull(),

                CheckboxList::make('tags')
                    ->label('Tags del DJ')
                    ->options([
                        'new' => '🆕 NEW - Nuevo en la escena',
                        'trending' => '📈 TRENDING - En tendencia',
                        'hot' => '🔥 HOT - Lo más caliente',
                        'star' => '⭐ STAR - Artista estrella',
                        'producer' => '🎛️ PRODUCER - Productor',
                        'resident' => '🏠 RESIDENT - Residente',
                        'international' => '🌎 INTERNATIONAL - Internacional',
                        'local' => '📍 LOCAL - Talento local',
                        'dj' => '🎧 DJ - Disc Jockey',
                        'live' => '🎹 LIVE - Performance en vivo',
                    ])
                    ->descriptions([
                        'new' => 'Para artistas nuevos o recién llegados',
                        'trending' => 'Para artistas que están en tendencia',
                        'hot' => 'Para los artistas más populares del momento',
                        'star' => 'Para artistas destacados o headliners',
                        'producer' => 'Para productores musicales',
                        'resident' => 'Para DJs residentes',
                        'international' => 'Para artistas internacionales',
                        'local' => 'Para talento local de la región',
                        'dj' => 'Para DJs tradicionales',
                        'live' => 'Para artistas que hacen performance en vivo',
                    ])
                    ->columns(2)
                    ->gridDirection('row')
                    ->helperText('Selecciona uno o más tags que describan al DJ')
                    ->columnSpanFull(),

                Repeater::make('technical_rider')
                    ->label('Rider técnico')
                    ->schema([
                        TextInput::make('label')
                            ->label('Equipo / requisito')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('value')
                            ->label('Detalle')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->columnSpanFull(),

                Toggle::make('is_featured')
                    ->label('Destacado en portada')
                    ->helperText('Aparecerá en la sección principal del sitio'),
                Toggle::make('is_highlighted')
                    ->label('DJ Destacado (Prioridad Máxima)')
                    ->helperText('Este DJ se mostrará como destacado con glow dorado y superpuesto sobre todos los demás en el inicio'),
                TextInput::make('priority')
                    ->label('Orden de prioridad')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Número menor = mayor prioridad (0 es el más alto)'),
            ]);
    }
}
