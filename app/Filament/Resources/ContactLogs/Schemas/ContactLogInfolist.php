<?php

namespace App\Filament\Resources\ContactLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Contacto')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Cliente')
                                    ->placeholder('-'),
                                TextEntry::make('customer.email')
                                    ->label('Email')
                                    ->placeholder('-'),
                                TextEntry::make('event.title')
                                    ->label('Evento')
                                    ->placeholder('-'),
                                TextEntry::make('campaign.name')
                                    ->label('Campaña')
                                    ->placeholder('-'),
                                TextEntry::make('channel')
                                    ->label('Canal')
                                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '-'),
                                TextEntry::make('type')
                                    ->label('Tipo')
                                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '-'),
                                TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'sent' => 'info',
                                        'delivered' => 'success',
                                        'opened' => 'warning',
                                        'clicked' => 'success',
                                        'failed', 'bounced' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '-'),
                            ]),
                    ]),
                Section::make('Contenido')
                    ->schema([
                        TextEntry::make('subject')
                            ->label('Asunto')
                            ->placeholder('-'),
                        TextEntry::make('message')
                            ->label('Mensaje')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('Fechas y Estado')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sent_at')
                                    ->label('Enviado')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('delivered_at')
                                    ->label('Entregado')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('opened_at')
                                    ->label('Abierto')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('clicked_at')
                                    ->label('Click')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('failed_at')
                                    ->label('Fallido')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label('Creado')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('error_message')
                            ->label('Mensaje de Error')
                            ->default('Sin errores')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->error_message !== null),
                    ]),
                Section::make('Tracking de Email')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('emailTracking.opens_count')
                                    ->label('Aperturas')
                                    ->placeholder('0'),
                                TextEntry::make('emailTracking.clicks_count')
                                    ->label('Clicks')
                                    ->placeholder('0'),
                                TextEntry::make('emailTracking.device_type')
                                    ->label('Dispositivo')
                                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Unknown')
                                    ->placeholder('Unknown'),
                                TextEntry::make('emailTracking.ip_address')
                                    ->label('IP')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('emailTracking.clicked_links')
                            ->label('Links Clickeados')
                            ->formatStateUsing(function ($state) {
                                if (!$state) {
                                    return 'Ninguno';
                                }

                                if (is_string($state)) {
                                    return $state;
                                }

                                if (!is_array($state) || empty($state)) {
                                    return 'Ninguno';
                                }

                                $links = collect($state)->map(function ($link) {
                                    if (is_string($link)) {
                                        return $link;
                                    }

                                    if (!is_array($link)) {
                                        return null;
                                    }

                                    $url = $link['url'] ?? $link['link'] ?? $link['href'] ?? null;

                                    if (!$url) {
                                        return null;
                                    }

                                    $clickedAt = $link['clicked_at'] ?? null;

                                    if ($clickedAt) {
                                        try {
                                            $clickedAt = \Carbon\Carbon::parse($clickedAt)->format('d/m/Y H:i');
                                        } catch (\Throwable $e) {
                                            // Keep raw timestamp if parsing fails.
                                        }

                                        if ($clickedAt) {
                                            return $url . ' (' . $clickedAt . ')';
                                        }
                                    }

                                    return $url;
                                })->filter();

                                return $links->isEmpty() ? 'Ninguno' : $links->join("\n");
                            })
                            ->placeholder('Ninguno')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->emailTracking !== null),
                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('metadata')
                            ->label('Datos Adicionales')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'Sin metadata')
                            ->placeholder('Sin metadata')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
