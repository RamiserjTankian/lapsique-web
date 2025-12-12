<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                TextInput::make('youtube_id')
                    ->label('YouTube ID')
                    ->required()
                    ->maxLength(50),
                TextInput::make('youtube_url')
                    ->label('URL de YouTube')
                    ->url()
                    ->required()
                    ->maxLength(255),
                TextInput::make('thumbnail_url')
                    ->label('Miniatura')
                    ->url()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('location')
                    ->label('Ubicación')
                    ->maxLength(255),
                TextInput::make('maps_url')
                    ->label('Link de Maps')
                    ->url()
                    ->maxLength(255)
                    ->helperText('Enlace de Google Maps del spot de grabación'),
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
