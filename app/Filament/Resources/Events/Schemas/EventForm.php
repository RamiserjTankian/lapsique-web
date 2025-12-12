<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
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
                TextInput::make('headline')
                    ->label('Tagline')
                    ->maxLength(255)
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')
                    ->label('Fecha y hora')
                    ->timezone('America/Mexico_City')
                    ->required(),
                TextInput::make('venue')
                    ->label('Venue')
                    ->maxLength(255),
                TextInput::make('city')
                    ->label('Ciudad')
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(5)
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('cover')
                    ->label('Cover')
                    ->collection('cover')
                    ->image()
                    ->imageEditor()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('cover_vertical')
                    ->label('Flayer vertical')
                    ->collection('cover_vertical')
                    ->image()
                    ->imageEditor()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('has_vertical_poster', filled($state))),
                SpatieMediaLibraryFileUpload::make('cover_horizontal')
                    ->label('Flayer horizontal')
                    ->collection('cover_horizontal')
                    ->image()
                    ->imageEditor()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('has_horizontal_poster', filled($state))),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galería')
                    ->collection('gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->maxFiles(20),

                TextInput::make('youtube_url')
                    ->label('YouTube')
                    ->url()
                    ->maxLength(255),
                TextInput::make('ticket_url')
                    ->label('Ticket / RSVP')
                    ->url()
                    ->maxLength(255),
                Select::make('featured_poster')
                    ->label('Flayer destacado')
                    ->options([
                        'vertical' => 'Vertical',
                        'horizontal' => 'Horizontal',
                        'cover' => 'Cover original',
                    ])
                    ->default('horizontal'),
                Toggle::make('is_featured')
                    ->label('Destacado en inicio'),
                TextInput::make('priority')
                    ->label('Orden')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Select::make('djs')
                    ->label('Line up')
                    ->relationship('djs', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
