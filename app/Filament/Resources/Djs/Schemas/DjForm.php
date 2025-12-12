<?php

namespace App\Filament\Resources\Djs\Schemas;

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
                    ->required(),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galería')
                    ->collection('gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->maxFiles(12)
                    ->columnSpan(1),

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
                Toggle::make('is_featured')
                    ->label('Destacado en portada'),
                TextInput::make('priority')
                    ->label('Orden de prioridad')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
