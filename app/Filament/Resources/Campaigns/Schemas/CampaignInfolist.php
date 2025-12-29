<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CampaignInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Información de la Campaña')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nombre'),
                                TextEntry::make('type')
                                    ->label('Tipo')
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'email' => 'primary',
                                        'sms' => 'success',
                                        'whatsapp' => 'info',
                                        'multi_channel' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn ($state) => match($state) {
                                        'draft' => 'secondary',
                                        'scheduled' => 'warning',
                                        'active' => 'success',
                                        'paused' => 'danger',
                                        'completed' => 'gray',
                                        default => 'gray',
                                    }),
                                TextEntry::make('starts_at')
                                    ->label('Fecha de Envío')
                                    ->dateTime('M d, Y H:i')
                                    ->placeholder('Inmediato'),
                            ]),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Métricas de Envío')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_recipients')
                                    ->label('Total Destinatarios')
                                    ->numeric()
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('sent_count')
                                    ->label('Enviados')
                                    ->numeric()
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('delivered_count')
                                    ->label('Entregados')
                                    ->numeric()
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('bounced_count')
                                    ->label('Rebotados')
                                    ->numeric()
                                    ->badge()
                                    ->color('danger'),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Métricas de Engagement')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('opened_count')
                                    ->label('Aperturas')
                                    ->numeric()
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('clicked_count')
                                    ->label('Clicks')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('open_rate')
                                    ->label('Tasa de Apertura')
                                    ->formatStateUsing(fn ($record) => $record->open_rate . '%')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('click_rate')
                                    ->label('Tasa de Clicks')
                                    ->formatStateUsing(fn ($record) => $record->click_rate . '%')
                                    ->badge()
                                    ->color('warning'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('click_to_open_rate')
                                    ->label('Click-to-Open Rate')
                                    ->formatStateUsing(fn ($record) => $record->click_to_open_rate . '%')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('conversion_count')
                                    ->label('Conversiones')
                                    ->numeric()
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Contenido')
                    ->schema([
                        TextEntry::make('content.email.subject')
                            ->label('Asunto')
                            ->visible(fn ($record) => !empty($record->content['email']['subject'] ?? null)),
                        TextEntry::make('content.email.body')
                            ->label('Contenido')
                            ->html()
                            ->visible(fn ($record) => !empty($record->content['email']['body'] ?? null))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->content ?? [])),
            ]);
    }
}

