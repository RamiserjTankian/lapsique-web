<?php

namespace App\Filament\Resources\StripeSettings\Schemas;

use App\Models\StripeSetting;
use App\Services\StripeIntegrationService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class StripeSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Estado')
                ->description('Activa o desactiva Stripe en checkout. Las llaves del panel tienen prioridad sobre el .env.')
                ->schema([
                    Toggle::make('is_enabled')
                        ->label('Stripe habilitado')
                        ->default(true),

                    Placeholder::make('connection_summary')
                        ->label('Resumen de conexión')
                        ->content(function (?StripeSetting $record): HtmlString {
                            if (! $record?->exists) {
                                return new HtmlString('<span class="text-sm text-gray-500">Guarda la configuración y usa «Verificar conexión» para comprobar las llaves.</span>');
                            }

                            $summary = app(StripeIntegrationService::class)->getConnectionSummary($record);

                            $status = e((string) ($summary['connection_status_label'] ?? 'Sin verificar'));
                            $verified = $summary['last_verified_at']
                                ? e($summary['last_verified_at']->timezone(config('app.timezone'))->format('d/m/Y H:i'))
                                : '—';
                            $error = filled($summary['last_error_message'] ?? null)
                                ? '<p class="mt-1 text-sm text-danger-600">'.e((string) $summary['last_error_message']).'</p>'
                                : '';
                            $webhookUrl = filled($summary['webhook_url'] ?? null)
                                ? '<p class="mt-2 text-xs text-gray-500">Webhook: <code class="text-xs">'.e((string) $summary['webhook_url']).'</code></p>'
                                : '';

                            return new HtmlString(
                                '<p class="text-sm"><strong>Estado:</strong> '.$status.' · <strong>Última verificación:</strong> '.$verified.'</p>'
                                .$error
                                .$webhookUrl
                            );
                        })
                        ->visible(fn ($livewire): bool => $livewire instanceof EditRecord),
                ]),

            Section::make('Llaves de API')
                ->schema([
                    TextInput::make('secret_key')
                        ->label('Secret key')
                        ->password()
                        ->revealable()
                        ->required(fn ($livewire): bool => $livewire instanceof CreateRecord)
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText(fn ($livewire): string => $livewire instanceof EditRecord
                            ? 'Dejar vacío para conservar la clave actual.'
                            : 'sk_test_… o sk_live_…'),

                    TextInput::make('publishable_key')
                        ->label('Publishable key')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText(fn ($livewire): string => $livewire instanceof EditRecord
                            ? 'Dejar vacío para conservar.'
                            : 'pk_test_… o pk_live_… (opcional en servidor, útil para referencia).'),

                    TextInput::make('webhook_secret')
                        ->label('Webhook signing secret')
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText(fn ($livewire): string => $livewire instanceof EditRecord
                            ? 'Dejar vacío para conservar. whsec_… del endpoint en Stripe Dashboard.'
                            : 'whsec_… del endpoint configurado en Stripe Dashboard.'),
                ]),

            Section::make('Preferencias')
                ->schema([
                    Select::make('currency')
                        ->label('Moneda por defecto')
                        ->options([
                            'MXN' => 'MXN — Peso mexicano',
                            'USD' => 'USD — Dólar',
                            'EUR' => 'EUR — Euro',
                        ])
                        ->required()
                        ->native(false)
                        ->default('MXN'),

                    TextInput::make('webhook_tolerance_seconds')
                        ->label('Tolerancia firma webhook (segundos)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue(86400)
                        ->default(300)
                        ->helperText('0 desactiva la comprobación de antigüedad del timestamp.'),
                ]),
        ]);
    }
}
