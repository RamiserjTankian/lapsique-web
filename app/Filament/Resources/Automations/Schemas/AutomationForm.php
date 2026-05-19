<?php

namespace App\Filament\Resources\Automations\Schemas;

use App\Models\Campaign;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AutomationForm
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
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activa',
                        'paused' => 'Pausada',
                        'archived' => 'Archivada',
                    ])
                    ->default('active')
                    ->required(),

                Select::make('trigger_type')
                    ->label('Trigger')
                    ->options([
                        'signup' => 'Registro',
                        'event_registration' => 'Registro a Evento',
                        'event_reminder' => 'Recordatorio de Evento',
                        'abandoned_cart' => 'Carrito Abandonado',
                        'birthday' => 'Cumpleaños',
                        'anniversary' => 'Aniversario',
                        'tag_added' => 'Tag Agregado',
                        'lifecycle_change' => 'Cambio de Lifecycle',
                        'score_threshold' => 'Score Threshold',
                        'email_opened' => 'Apertura de Email',
                    ])
                    ->required()
                    ->live(),

                Select::make('trigger_config.campaign_id')
                    ->label('Campaña que dispara el flujo')
                    ->options(fn () => Campaign::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn ($get) => $get('trigger_type') === 'email_opened')
                    ->helperText('Solo se dispara cuando se abre esta campaña.')
                    ->columnSpanFull(),

                Repeater::make('steps')
                    ->label('Pasos del flujo')
                    ->defaultItems(1)
                    ->minItems(1)
                    ->schema([
                        Select::make('action')
                            ->label('Acción')
                            ->options([
                                'send_campaign' => 'Enviar campaña',
                            ])
                            ->required()
                            ->live(),
                        Select::make('campaign_id')
                            ->label('Campaña a enviar')
                            ->options(fn () => Campaign::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->visible(fn ($get) => $get('action') === 'send_campaign'),
                        TextInput::make('delay_minutes')
                            ->label('Delay (min)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
