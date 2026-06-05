<?php

namespace App\Filament\Pages;

use App\Models\BookingSlot;
use App\Models\SiteSetting;
use App\Services\BookingAvailabilityRuleService;
use App\Services\BookingSlotGeneratorService;
use App\Services\GoogleCalendarService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use UnitEnum;

class BookingSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Configuración de Booking';

    protected static ?string $title = 'Configuración de Booking';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar configuración')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->submit('save'),
        ];
    }

    public function getView(): string
    {
        return 'filament.pages.booking-settings';
    }

    public function mount(GoogleCalendarService $googleCalendar): void
    {
        $settings = SiteSetting::currentOrNew();

        $calendarOptions = [];
        if ($googleCalendar->isConnected()) {
            try {
                $calendars = $googleCalendar->listCalendars();
                $calendarOptions = collect($calendars)->pluck('name', 'id')->all();
            } catch (\Throwable $e) {
                Log::warning('BookingSettings: could not list calendars', ['error' => $e->getMessage()]);
            }
        }

        $this->schema->fill([
            'booking_title' => $settings->booking_title ?? '',
            'booking_subtitle' => $settings->booking_subtitle ?? '',
            'booking_price' => $settings->booking_price ?? (int) config('booking.content_price', 3000),
            'booking_whatsapp' => $settings->booking_whatsapp ?? '',
            'booking_team_name' => $settings->booking_team_name ?? '',
            'booking_team_bio' => $settings->booking_team_bio ?? '',
            'booking_availability_days' => $settings->bookingAvailabilityDays(),
            'booking_start_time' => $settings->bookingStartTime(),
            'booking_end_time' => $settings->bookingEndTime(),
            'booking_advance_hours' => $settings->booking_advance_hours ?? config('booking.default_advance_hours', 24),
            'booking_duration_minutes' => $settings->bookingDurationMinutes(),
            'google_calendar_id' => $settings->google_calendar_id ?? 'primary',
            'booking_calendar_notify_email' => $settings->booking_calendar_notify_email ?? '',
            'booking_studio_location' => $settings->booking_studio_location ?? '',
        ]);

        $this->calendarOptions = $calendarOptions;
    }

    public array $calendarOptions = [];

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Landing Page')
                    ->description('Textos y datos comerciales que se muestran en el home y en el funnel de agenda.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('booking_title')
                            ->label('Título')
                            ->placeholder('Producción ejecutiva / booking')
                            ->columnSpanFull(),

                        TextInput::make('booking_subtitle')
                            ->label('Subtítulo')
                            ->placeholder('1 reel + 10 fotos editadas en una sola sesión')
                            ->columnSpanFull(),

                        TextInput::make('booking_price')
                            ->label('Precio (MXN)')
                            ->numeric()
                            ->default(config('booking.content_price', 3000))
                            ->prefix('$')
                            ->suffix('MXN'),

                        TextInput::make('booking_whatsapp')
                            ->label('WhatsApp de contacto')
                            ->placeholder('5219841234567')
                            ->helperText('Con código de país, sin + ni espacios.'),

                        TextInput::make('booking_team_name')
                            ->label('Nombre del equipo / lead')
                            ->placeholder('Trascendental'),

                        Textarea::make('booking_team_bio')
                            ->label('Bio corta del equipo')
                            ->placeholder('Producción audiovisual boutique para marcas, artistas y negocios en Riviera Maya.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Parámetros de reserva')
                    ->description('Controlan cuándo y cuántos horarios se generan automáticamente.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('booking_availability_days')
                            ->label('Periodo para agendar')
                            ->numeric()
                            ->default(config('booking.availability_days', 11))
                            ->minValue(1)
                            ->maxValue(90)
                            ->suffix('días')
                            ->helperText('Default: 11 días, equivalente a 1.5 semanas.'),

                        TimePicker::make('booking_start_time')
                            ->label('Hora inicial')
                            ->default(config('booking.default_start_time', '14:00'))
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Default: 2:00 PM.'),

                        TimePicker::make('booking_end_time')
                            ->label('Hora final')
                            ->default(config('booking.default_end_time', '17:00'))
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Default: 5:00 PM.'),

                        TextInput::make('booking_advance_hours')
                            ->label('Anticipo mínimo')
                            ->numeric()
                            ->default(24)
                            ->minValue(0)
                            ->suffix('hrs')
                            ->helperText('Horas de anticipación mínima para reservar.'),

                        TextInput::make('booking_duration_minutes')
                            ->label('Duración de sesión')
                            ->numeric()
                            ->default(config('booking.default_duration_minutes', 120))
                            ->minValue(30)
                            ->suffix('min')
                            ->helperText('Se usa para detectar conflictos en Google Calendar.'),

                        TextInput::make('booking_calendar_notify_email')
                            ->label('Email interno (invitación calendario)')
                            ->email()
                            ->placeholder('produccion@trascendentalby.mx')
                            ->helperText('Recibe copia de la invitación de Google Calendar (Workspace).')
                            ->columnSpanFull(),

                        TextInput::make('booking_studio_location')
                            ->label('Ubicación del estudio')
                            ->placeholder('Playa del Carmen, Q.R.')
                            ->helperText('Aparece en el evento de Google Calendar.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(GoogleCalendarService $googleCalendar): void
    {
        $data = $this->schema->getState();

        $settings = SiteSetting::query()->firstOrCreate([]);
        $settings->update([
            'booking_title' => $data['booking_title'] ?? null,
            'booking_subtitle' => $data['booking_subtitle'] ?? null,
            'booking_price' => (int) ($data['booking_price'] ?? config('booking.content_price', 3000)),
            'booking_whatsapp' => $data['booking_whatsapp'] ?? null,
            'booking_team_name' => $data['booking_team_name'] ?? null,
            'booking_team_bio' => $data['booking_team_bio'] ?? null,
            'booking_weeks_ahead' => (int) ceil(((int) ($data['booking_availability_days'] ?? config('booking.availability_days', 11))) / 7),
            'booking_availability_days' => (int) ($data['booking_availability_days'] ?? config('booking.availability_days', 11)),
            'booking_start_time' => $data['booking_start_time'] ?? config('booking.default_start_time', '14:00'),
            'booking_end_time' => $data['booking_end_time'] ?? config('booking.default_end_time', '17:00'),
            'booking_advance_hours' => (int) ($data['booking_advance_hours'] ?? config('booking.default_advance_hours', 24)),
            'booking_duration_minutes' => (int) ($data['booking_duration_minutes'] ?? config('booking.default_duration_minutes', 120)),
            'booking_calendar_notify_email' => $data['booking_calendar_notify_email'] ?? null,
            'booking_studio_location' => $data['booking_studio_location'] ?? null,
        ]);

        // Reload calendar options after save
        if ($googleCalendar->isConnected()) {
            try {
                $calendars = $googleCalendar->listCalendars();
                $this->calendarOptions = collect($calendars)->pluck('name', 'id')->all();
            } catch (\Throwable $e) {
                // silent
            }
        }

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    public function saveCalendar(GoogleCalendarService $googleCalendar): void
    {
        $calendarId = request()->input('calendar_id', 'primary');

        SiteSetting::query()->firstOrCreate([])->update([
            'google_calendar_id' => $calendarId,
        ]);

        Notification::make()
            ->title('Calendario guardado')
            ->success()
            ->send();
    }

    public function disconnectGoogle(GoogleCalendarService $googleCalendar): void
    {
        $googleCalendar->disconnect();

        Notification::make()
            ->title('Google Calendar desconectado')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function generateSlots(
        GoogleCalendarService $googleCalendar,
        BookingSlotGeneratorService $generator,
        BookingAvailabilityRuleService $rulesService,
    ): void {
        $rulesService->ensureDefaultRules();

        $settings = SiteSetting::current();
        $availabilityDays = $settings?->bookingAvailabilityDays() ?? config('booking.availability_days', 11);

        try {
            $result = $generator->generate($availabilityDays);
            Cache::put('booking.last_slot_generation', array_merge($result, ['at' => now()->toIso8601String()]), now()->addDays(7));

            $msg = "Se crearon {$result['created']} horarios.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} omitidos — ya existían o muy pronto)";
            }
            if ($result['blocked_by_calendar'] > 0) {
                $msg .= " ({$result['blocked_by_calendar']} bloqueados por Google Calendar)";
            }

            Notification::make()
                ->title('Horarios generados')
                ->body($msg)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al generar horarios')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearAndRegenerate(
        GoogleCalendarService $googleCalendar,
        BookingSlotGeneratorService $generator,
        BookingAvailabilityRuleService $rulesService,
    ): void {
        $rulesService->ensureDefaultRules();

        $settings = SiteSetting::current();
        $availabilityDays = $settings?->bookingAvailabilityDays() ?? config('booking.availability_days', 11);

        try {
            $deleted = $generator->clearFutureSlots();
            $result = $generator->generate($availabilityDays);
            Cache::put('booking.last_slot_generation', array_merge($result, ['at' => now()->toIso8601String()]), now()->addDays(7));

            Notification::make()
                ->title('Horarios regenerados')
                ->body("Se eliminaron {$deleted} horarios sin reservas. Se crearon {$result['created']} nuevos.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getCalendarInfo(GoogleCalendarService $googleCalendar): array
    {
        return $googleCalendar->getConnectionSummary();
    }

    public function getUpcomingSlotCount(): int
    {
        return BookingSlot::where('date', '>=', today())
            ->where('is_active', true)
            ->count();
    }

    public function getGoogleClientConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    /**
     * @return array<string, int>
     */
    public function getAvailableSlotsByTime(): array
    {
        return BookingSlot::query()
            ->available()
            ->where('date', '>=', today())
            ->selectRaw('time_value, count(*) as total')
            ->groupBy('time_value')
            ->orderBy('time_value')
            ->pluck('total', 'time_value')
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastSlotGeneration(): ?array
    {
        $cached = Cache::get('booking.last_slot_generation');

        return is_array($cached) ? $cached : null;
    }
}
