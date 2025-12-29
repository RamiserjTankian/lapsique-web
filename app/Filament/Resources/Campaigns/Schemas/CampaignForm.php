<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la Campaña')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->helperText('Nombre descriptivo para identificar esta campaña'),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Descripción breve de la campaña (opcional)')
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Tipo de Campaña')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'whatsapp' => 'WhatsApp',
                        'multi_channel' => 'Multi-Canal',
                    ])
                    ->default('email')
                    ->required()
                    ->live()
                    ->helperText('Selecciona el canal de comunicación'),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'scheduled' => 'Programada',
                        'active' => 'Activa',
                        'paused' => 'Pausada',
                        'completed' => 'Completada',
                    ])
                    ->default('draft')
                    ->required()
                    ->disabled(fn ($record) => $record && in_array($record->status, ['active', 'completed']))
                    ->helperText('El estado se actualizará automáticamente al enviar'),

                // Contenido del Email
                TextInput::make('email_subject')
                    ->label('Asunto del Email')
                    ->required(fn ($get) => $get('type') === 'email' || $get('type') === 'multi_channel')
                    ->maxLength(255)
                    ->visible(fn ($get) => in_array($get('type'), ['email', 'multi_channel']))
                    ->columnSpanFull()
                    ->helperText('Asunto que verán los destinatarios'),

                RichEditor::make('email_body')
                    ->label('Contenido del Email')
                    ->required(fn ($get) => $get('type') === 'email' || $get('type') === 'multi_channel')
                    ->visible(fn ($get) => in_array($get('type'), ['email', 'multi_channel']))
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('campaign-attachments')
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
                        'undo',
                        'redo',
                    ])
                    ->columnSpanFull()
                    ->helperText('Escribe el contenido del email. Puedes usar enlaces que se trackearán automáticamente.'),

                TextInput::make('button_text')
                    ->label('Texto del Botón')
                    ->default('Ver Más')
                    ->maxLength(50)
                    ->visible(fn ($get) => in_array($get('type'), ['email', 'multi_channel']))
                    ->helperText('Texto del botón CTA (Call to Action)'),

                TextInput::make('button_url')
                    ->label('URL del Botón')
                    ->url()
                    ->maxLength(500)
                    ->visible(fn ($get) => in_array($get('type'), ['email', 'multi_channel']))
                    ->helperText('URL a la que llevará el botón (ej: link de Instagram post)')
                    ->columnSpanFull(),

                // Segmentación de Audiencia
                TagsInput::make('target_tags')
                    ->label('Tags de Clientes')
                    ->placeholder('Agregar tag (presiona Enter)')
                    ->helperText('Solo enviar a clientes con estos tags (opcional)')
                    ->columnSpanFull(),

                Select::make('target_lifecycle_stages')
                    ->label('Etapas del Ciclo de Vida')
                    ->multiple()
                    ->options([
                        'lead' => 'Lead',
                        'prospect' => 'Prospecto',
                        'customer' => 'Cliente',
                        'vip' => 'VIP',
                    ])
                    ->helperText('Filtrar por etapa del ciclo de vida (opcional)')
                    ->columnSpanFull(),

                Select::make('target_statuses')
                    ->label('Estados de Cliente')
                    ->multiple()
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'bounced' => 'Rebotado',
                    ])
                    ->helperText('Filtrar por estado del cliente (opcional)')
                    ->columnSpanFull(),

                // Scheduling
                DateTimePicker::make('starts_at')
                    ->label('Fecha de Envío')
                    ->timezone('America/Mexico_City')
                    ->helperText('Deja vacío para enviar inmediatamente al guardar y enviar')
                    ->columnSpanFull(),

                // Metadata
                Toggle::make('save_as_template')
                    ->label('Guardar como Plantilla')
                    ->default(false)
                    ->helperText('Guardar esta campaña como plantilla para reutilizar')
                    ->columnSpanFull(),
            ]);
    }
}
