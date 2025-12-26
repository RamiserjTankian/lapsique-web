<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label('Foto de perfil')
                    ->collection('avatar')
                    ->image()
                    ->imageEditor()
                    ->imageEditorMode(2)
                    ->imageEditorViewportWidth(400)
                    ->imageEditorViewportHeight(400)
                    ->imageEditorAspectRatios([
                        '1:1' => 'Cuadrado (1:1)',
                    ])
                    ->maxSize(10240)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    ->helperText('Foto de perfil del usuario. Máximo 10MB.')
                    ->columnSpanFull()
                    ->avatar(),

                TextInput::make('name')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(1),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->helperText(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord 
                        ? 'Deja en blanco para mantener la contraseña actual.' 
                        : 'Mínimo 8 caracteres.')
                    ->minLength(8)
                    ->columnSpan(1),

                TextInput::make('password_confirmation')
                    ->label('Confirmar contraseña')
                    ->password()
                    ->required(fn ($livewire, $get) => filled($get('password')))
                    ->same('password')
                    ->dehydrated(false)
                    ->columnSpan(1),

                DateTimePicker::make('email_verified_at')
                    ->label('Email verificado')
                    ->timezone('America/Mexico_City')
                    ->displayFormat('d/m/Y H:i')
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('Usuario activo')
                    ->default(true)
                    ->helperText('Desactiva este usuario para impedirle el acceso al panel.')
                    ->columnSpan(1),
            ]);
    }
}

