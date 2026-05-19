<?php

namespace App\Filament\Resources\SessionCustomers\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class SessionCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /**
     * @return array<Step>
     */
    public static function steps(): array
    {
        return [
            Step::make('Contacto')
                ->icon(Heroicon::OutlinedUser)
                ->description('Identidad y canales de comunicación del cliente.')
                ->columns(2)
                ->schema(self::contactFields()),

            Step::make('Facturación')
                ->icon(Heroicon::OutlinedDocumentText)
                ->description('Datos fiscales para facturar sesiones (CFDI México).')
                ->columns(2)
                ->schema(self::fiscalFields()),

            Step::make('Operación')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->description('Notas internas del equipo y preferencias de contacto.')
                ->schema(self::operationsFields()),

            Step::make('Revisión')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->description('Confirma que la información esté correcta antes de guardar.')
                ->schema(self::reviewFields()),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function contactFields(): array
    {
        return [
            TextInput::make('name')
                ->label('Nombre completo')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email principal')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('phone')
                ->label('WhatsApp / teléfono')
                ->tel()
                ->maxLength(255)
                ->helperText('Con código de país, sin espacios.'),
            TextInput::make('whatsapp')
                ->label('WhatsApp alterno')
                ->tel()
                ->maxLength(255),
            TextInput::make('instagram_handle')
                ->label('Instagram')
                ->prefix('@')
                ->maxLength(255),
            Select::make('status')
                ->label('Estado CRM')
                ->options([
                    'lead' => 'Lead',
                    'prospect' => 'Prospecto',
                    'customer' => 'Cliente',
                    'inactive' => 'Inactivo',
                ])
                ->default('customer')
                ->required()
                ->native(false),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function fiscalFields(): array
    {
        return [
            TextInput::make('fiscal_legal_name')
                ->label('Razón social')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('fiscal_rfc')
                ->label('RFC')
                ->maxLength(13)
                ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                ->helperText('13 caracteres para persona moral; 12 o 13 para física.'),
            Select::make('fiscal_regime')
                ->label('Régimen fiscal')
                ->options([
                    '601' => '601 — General de Ley Personas Morales',
                    '603' => '603 — Personas Morales con Fines no Lucrativos',
                    '605' => '605 — Sueldos y Salarios',
                    '612' => '612 — Personas Físicas con Actividades Empresariales',
                    '616' => '616 — Sin obligaciones fiscales',
                    '626' => '626 — Régimen Simplificado de Confianza',
                ])
                ->searchable()
                ->native(false),
            Select::make('fiscal_cfdi_use')
                ->label('Uso de CFDI')
                ->options([
                    'G01' => 'G01 — Adquisición de mercancías',
                    'G03' => 'G03 — Gastos en general',
                    'S01' => 'S01 — Sin efectos fiscales',
                    'CP01' => 'CP01 — Pagos',
                ])
                ->searchable()
                ->native(false),
            TextInput::make('fiscal_email')
                ->label('Email de facturación')
                ->email()
                ->maxLength(255),
            TextInput::make('fiscal_zip')
                ->label('Código postal')
                ->maxLength(10),
            TextInput::make('fiscal_address')
                ->label('Calle y número')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('fiscal_city')
                ->label('Ciudad')
                ->maxLength(255),
            TextInput::make('fiscal_state')
                ->label('Estado')
                ->maxLength(255),
            TextInput::make('fiscal_country')
                ->label('País (ISO)')
                ->default('MX')
                ->maxLength(2),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function operationsFields(): array
    {
        return [
            Textarea::make('notes')
                ->label('Notas internas')
                ->rows(6)
                ->placeholder('Brief, acuerdos, pendientes de producción, etc.')
                ->columnSpanFull(),
            Toggle::make('subscribed_whatsapp')
                ->label('Puede recibir WhatsApp')
                ->default(true),
            Toggle::make('subscribed_newsletter')
                ->label('Suscrito a newsletter')
                ->default(true),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function reviewFields(): array
    {
        return [
            Placeholder::make('review_summary')
                ->label('Resumen')
                ->content(fn ($get) => new HtmlString(self::buildReviewHtml($get)))
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  callable(string): mixed  $get
     */
    protected static function buildReviewHtml(callable $get): string
    {
        $lines = [
            '<strong>Contacto</strong>',
            e((string) $get('name')).' · '.e((string) $get('email')),
            'Tel: '.e((string) ($get('phone') ?: '—')),
            'Instagram: '.e($get('instagram_handle') ? '@'.$get('instagram_handle') : '—'),
            '<br><strong>Facturación</strong>',
            'RFC: '.e((string) ($get('fiscal_rfc') ?: 'Sin registrar')),
            e((string) ($get('fiscal_legal_name') ?: '—')),
            'Uso CFDI: '.e((string) ($get('fiscal_cfdi_use') ?: '—')),
        ];

        return '<div class="space-y-1 text-sm text-muted-foreground">'.implode('<br>', $lines).'</div>';
    }
}
