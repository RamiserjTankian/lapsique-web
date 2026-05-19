<?php

namespace App\Filament\Resources\AybProducts\Schemas;

use App\Models\AybProduct;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AybProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $record): void {
                        if (! filled($state)) {
                            return;
                        }

                        $set('slug', AybProduct::generateUniqueSlug($state, $record?->id));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'beverage' => 'Bebida',
                        'food' => 'Alimento',
                    ])
                    ->required()
                    ->default('beverage'),
                TextInput::make('price')
                    ->label('Precio')
                    ->numeric()
                    ->required(),
                TextInput::make('currency')
                    ->label('Moneda')
                    ->maxLength(3)
                    ->default(config('pos.currency', 'MXN'))
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpan(2),
            ]);
    }
}
