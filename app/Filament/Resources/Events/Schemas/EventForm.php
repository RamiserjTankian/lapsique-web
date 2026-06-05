<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Dj;
use App\Models\Location;
use App\Support\EventLineup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->label('Slug / URL')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('headline')
                    ->label('Tagline')
                    ->maxLength(255)
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')
                    ->label('Fecha y hora')
                    ->timezone('America/Mexico_City')
                    ->required(),
                TextInput::make('venue')
                    ->label('Venue')
                    ->maxLength(255),
                TextInput::make('city')
                    ->label('Ciudad')
                    ->maxLength(255),
                
                Select::make('location_id')
                    ->label('Venue / Location')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(255),
                        TextInput::make('maps_url')
                            ->label('Google Maps URL')
                            ->url(),
                    ])
                    ->helperText('Selecciona el venue o crea uno nuevo'),
                
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(5)
                    ->columnSpanFull(),

                TagsInput::make('tags')
                    ->label('Tags del Evento')
                    ->placeholder('Agregar tag (presiona Enter)')
                    ->suggestions([
                        '🎥 Recording Party',
                        '🌟 Special Event',
                        '🎂 Anniversary',
                        '🎊 Opening',
                        '🏖️ Beach Party',
                        '🌃 Rooftop',
                        '🎭 Afterparty',
                    ])
                    ->helperText('Agrega tags como "🎥 Recording Party", "🌟 Special Event", etc.')
                    ->columnSpanFull(),

                Section::make('Contenido público Trascendental')
                    ->description('Clasifica si este evento aparece en trascendentalby.mx y cómo se muestra.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('trascendental_visible')
                            ->label('Mostrar en Trascendental')
                            ->helperText('Activa esto para que el evento alimente la landing pública.'),
                        Select::make('trascendental_kind')
                            ->label('Tipo en Trascendental')
                            ->options([
                                'produced' => 'Evento producido',
                                'roster_appearance' => 'Aparición del roster',
                                'announcement' => 'Anuncio / por confirmar',
                            ])
                            ->native(false),
                        TextInput::make('lineup_text')
                            ->label('Lineup público')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('public_image_path')
                            ->label('Imagen pública existente')
                            ->placeholder('/images/trascendental/events/flyer.webp')
                            ->maxLength(255)
                            ->helperText('Usa este campo para assets ya publicados cuando no quieras subir media nueva.')
                            ->columnSpanFull(),
                        TextInput::make('source_url')
                            ->label('Fuente / Resident Advisor')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('details_url')
                            ->label('Detalles / Instagram')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('cover')
                    ->label('Cover')
                    ->collection('cover')
                    ->image()
                    ->imageEditor()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('cover_vertical')
                    ->label('Flayer vertical')
                    ->collection('cover_vertical')
                    ->image()
                    ->imageEditor()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('has_vertical_poster', filled($state))),
                SpatieMediaLibraryFileUpload::make('cover_horizontal')
                    ->label('Flayer horizontal')
                    ->collection('cover_horizontal')
                    ->image()
                    ->imageEditor()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('has_horizontal_poster', filled($state))),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galería del Evento')
                    ->collection('gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->maxFiles(20),

                SpatieMediaLibraryFileUpload::make('venue_gallery')
                    ->label('Galería del Venue')
                    ->collection('venue_gallery')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->maxFiles(20)
                    ->helperText('Fotos del lugar donde será la fiesta')
                    ->columnSpanFull(),

                Repeater::make('technical_rider')
                    ->label('🎛️ Technical Rider - Equipamiento técnico disponible')
                    ->dehydrated()
                    ->reorderable()
                    ->columns(3)
                    ->schema([
                        Select::make('category')
                            ->label('Categoría')
                            ->options([
                                'cdj' => '💿 CDJ',
                                'mixer' => '🎚️ Mixer',
                                'sound_system' => '🔊 Sound System',
                                'other' => '🎵 Otro',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpan(1),
                        Select::make('brand_model')
                            ->label('Marca / Modelo')
                            ->options(function (callable $get) {
                                $category = $get('category');
                                
                                return match ($category) {
                                    'cdj' => [
                                        'cdj-3000' => '💿 Pioneer CDJ-3000',
                                        'cdj-2000nxs2' => '💿 Pioneer CDJ-2000 NXS2',
                                        'cdj-2000' => '💿 Pioneer CDJ-2000',
                                        'xdj-1000mk2' => '💿 Pioneer XDJ-1000 MK2',
                                        'sc6000' => '💿 Denon SC6000',
                                        'sc5000' => '💿 Denon SC5000',
                                    ],
                                    'mixer' => [
                                        'djm-v10' => '🎚️ Pioneer DJM-V10',
                                        'djm-a9' => '🎚️ Pioneer DJM-A9',
                                        'djm-900nxs2' => '🎚️ Pioneer DJM-900 NXS2',
                                        'xone-96' => '🎚️ Allen & Heath Xone:96',
                                        'xone-92' => '🎚️ Allen & Heath Xone:92',
                                        'model-1' => '🎚️ Richie Hawtin Model 1',
                                        'x1850' => '🎚️ Denon X1850',
                                    ],
                                    'sound_system' => [
                                        'l-acoustics' => '🔊 L-Acoustics',
                                        'funktion-one' => '🔊 Funktion One',
                                        'd&b' => '🔊 d&b audiotechnik',
                                        'void' => '🔊 VOID Acoustics',
                                        'martin-audio' => '🔊 Martin Audio',
                                        'meyer-sound' => '🔊 Meyer Sound',
                                        'kv2' => '🔊 KV2 Audio',
                                    ],
                                    default => [
                                        'other' => '🎵 Otro',
                                    ],
                                };
                            })
                            ->searchable()
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->columnSpan(1),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Agregar equipo')
                    ->collapsible()
                    ->collapsed(false)
                    ->columnSpanFull(),

                TextInput::make('youtube_url')
                    ->label('YouTube')
                    ->url()
                    ->maxLength(255),
                TextInput::make('ticket_url')
                    ->label('Ticket / RSVP')
                    ->url()
                    ->maxLength(255),
                Select::make('featured_poster')
                    ->label('Flayer destacado')
                    ->options([
                        'vertical' => 'Vertical',
                        'horizontal' => 'Horizontal',
                        'cover' => 'Cover original',
                    ])
                    ->default('horizontal'),
                Toggle::make('is_featured')
                    ->label('Destacado en inicio'),
                Section::make('Caso de éxito Trascendental')
                    ->description('Datos editoriales que alimentan la web de trascendentalby.mx.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_case_study')
                            ->label('Mostrar como caso de éxito'),
                        TextInput::make('case_sort')
                            ->label('Orden en casos')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('case_summary')
                            ->label('Resumen corto')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Repeater::make('case_metrics')
                            ->label('Métricas clave')
                            ->columns(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Dato')
                                    ->required()
                                    ->maxLength(80),
                                TextInput::make('value')
                                    ->label('Valor')
                                    ->required()
                                    ->maxLength(120),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Agregar métrica')
                            ->columnSpanFull(),
                        TagsInput::make('case_services')
                            ->label('Servicios entregados')
                            ->placeholder('Agregar servicio')
                            ->suggestions([
                                'Booking',
                                'Curaduría',
                                'Producción integral',
                                'Cashless',
                                'Ads',
                                'Contenido',
                                'Marketing',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                TextInput::make('priority')
                    ->label('Orden')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Repeater::make('lineup')
                    ->label('Line up')
                    ->dehydrated(false)
                    ->reorderable()
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('dj_id')
                            ->label('DJ')
                            ->options(fn () => Dj::query()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->columnSpan(3),
                        Toggle::make('is_b2b')
                            ->label('B2B')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state): void {
                                if (! $state) {
                                    $set('b2b_dj_id', null);
                                }
                            })
                            ->columnSpan(1),
                        Select::make('b2b_dj_id')
                            ->label('B2B con')
                            ->options(function (callable $get) {
                                $selectedDjId = (int) ($get('dj_id') ?? 0);

                                return Dj::query()
                                    ->when($selectedDjId > 0, fn ($query) => $query->where('id', '!=', $selectedDjId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->disabled(fn (callable $get): bool => ! (bool) $get('is_b2b'))
                            ->required(fn (callable $get): bool => (bool) $get('is_b2b'))
                            ->helperText('Selecciona el segundo DJ cuando actives B2B.')
                            ->columnSpan(3),
                        Select::make('role')
                            ->label('Rol')
                            ->options([
                                'headliner' => 'Headliner',
                                'warmup' => 'Warmup',
                                'local' => 'Local',
                            ])
                            ->default('warmup')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('time_slot')
                            ->label('Horario')
                            ->placeholder('ej: 11:00 PM - 12:30 AM')
                            ->maxLength(50)
                            ->columnSpan(2),
                        TextInput::make('guest_limit')
                            ->label('Límite Invitados')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Máximo de invitados para este DJ')
                            ->columnSpan(2),
                        Toggle::make('enabled')
                            ->label('Activo')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->afterStateHydrated(function (callable $set, $record) {
                        if (! $record) {
                            return;
                        }

                        $lineup = EventLineup::formStateFromDjs(
                            $record->djs()
                            ->orderBy('dj_event.position')
                            ->get()
                        );

                        $set('lineup', $lineup);
                    })
                    ->defaultItems(0)
                    ->helperText('Reordena para definir el orden en el cartel. Activa B2B para unir dos DJs en una sola tarjeta.')
                    ->collapsible(),
                Select::make('rps')
                    ->label('RPs del Evento')
                    ->relationship('rps', 'name', fn (Builder $query) => $query->where('status', 'active')->orderBy('name'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('RPs que trabajarán en este evento')
                    ->columnSpanFull(),
            ]);
    }
}
