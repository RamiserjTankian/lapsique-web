<?php

namespace App\Filament\Resources\BookingLandingAnalytics\Tables;

use App\Services\BookingLandingAnalyticsService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingLandingAnalyticsTable
{
    public static function configure(Table $table): Table
    {
        $analytics = app(BookingLandingAnalyticsService::class);

        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('source_type')
                    ->label('Canal')
                    ->formatStateUsing(fn ($record) => $analytics->channelLabel($record))
                    ->badge()
                    ->color('primary'),

                TextColumn::make('utm_source')
                    ->label('Origen')
                    ->formatStateUsing(fn ($record) => $analytics->sourceLabel($record))
                    ->searchable(),

                TextColumn::make('landing_path')
                    ->label('Entrada')
                    ->formatStateUsing(fn ($record) => $record->landing_path ?: BookingLandingAnalyticsService::LANDING_PATH)
                    ->description(fn ($record) => $record->referrer_domain ?: 'Sin referrer')
                    ->wrap(),

                TextColumn::make('device_type')
                    ->label('Dispositivo')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('booking_funnel_stage')
                    ->label('Etapa')
                    ->state(fn ($record) => $analytics->sessionStageSummary($record)['label'])
                    ->badge()
                    ->color(fn ($record) => $analytics->sessionStageSummary($record)['color']),

                TextColumn::make('booking_pageviews_count')
                    ->label('PV')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('booking_events_count')
                    ->label('Eventos')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('video_plays_count')
                    ->label('Plays')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('contact_events_count')
                    ->label('Contactos')
                    ->numeric()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('form_starts_count')
                    ->label('Formularios')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('last_seen_at')
                    ->label('Ultima actividad')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn ($record) => $analytics->durationSeconds($record) . 's en sesión')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('device_type')
                    ->label('Dispositivo')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                    ]),
                SelectFilter::make('source_type')
                    ->label('Canal base')
                    ->options([
                        'social' => 'Social',
                        'campaign' => 'Campaign',
                        'search' => 'Search',
                        'referral' => 'Referral',
                        'direct' => 'Direct',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
