<?php

namespace App\Filament\Resources\PageContentBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageContentBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Ubicación')
                    ->columns(2)
                    ->schema([
                        Select::make('site')
                            ->label('Sitio')
                            ->options([
                                'trascendental' => 'Trascendental',
                            ])
                            ->default('trascendental')
                            ->required(),
                        Select::make('locale')
                            ->label('Idioma')
                            ->options([
                                'en' => 'English',
                                'es' => 'Español',
                            ])
                            ->default('en')
                            ->required(),
                        TextInput::make('page')
                            ->label('Página')
                            ->placeholder('home')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('section')
                            ->label('Sección')
                            ->placeholder('hero')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('key')
                            ->label('Clave')
                            ->placeholder('headline')
                            ->required()
                            ->maxLength(80),
                        TextInput::make('priority')
                            ->label('Orden')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columnSpanFull(),

                Section::make('Contenido')
                    ->columns(2)
                    ->schema([
                        TextInput::make('eyebrow')
                            ->label('Eyebrow')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Título')
                            ->maxLength(255),
                        Textarea::make('body')
                            ->label('Cuerpo')
                            ->rows(6)
                            ->columnSpanFull(),
                        TextInput::make('asset_path')
                            ->label('Asset público')
                            ->placeholder('/images/trascendental/...')
                            ->maxLength(255)
                            ->helperText('Ruta o URL de una imagen/video ya publicado.'),
                        TextInput::make('cta_label')
                            ->label('Texto CTA')
                            ->maxLength(255),
                        TextInput::make('cta_url')
                            ->label('URL CTA')
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
