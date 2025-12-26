<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Models\User;

class PostForm
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
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->label('Slug / URL')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
                Textarea::make('excerpt')
                    ->label('Extracto')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Resumen breve del post (opcional)')
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Contenido')
                    ->required()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('post-attachments')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'codeBlock',
                        'undo',
                        'redo',
                    ])
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('cover')
                    ->label('Imagen de portada')
                    ->collection('cover')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->required()
                    ->maxSize(51200)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galería de imágenes')
                    ->collection('gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->imageEditor()
                    ->maxFiles(20)
                    ->maxSize(51200)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    ->helperText('Puedes agregar hasta 20 imágenes. Máximo 50MB por imagen.')
                    ->columnSpanFull(),

                Select::make('author_id')
                    ->label('Autor')
                    ->options(fn () => User::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->default(fn () => auth()->id()),

                Toggle::make('is_published')
                    ->label('Publicado')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($state && !$get('published_at')) {
                            $set('published_at', now());
                        }
                    }),

                DateTimePicker::make('published_at')
                    ->label('Fecha de publicación')
                    ->timezone('America/Mexico_City')
                    ->visible(fn ($get) => $get('is_published'))
                    ->required(fn ($get) => $get('is_published'))
                    ->default(now())
                    ->helperText('El post será visible solo si la fecha es presente o pasada')
                    ->columnSpanFull(),
            ]);
    }
}

