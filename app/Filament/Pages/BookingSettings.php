<?php

namespace App\Filament\Pages;

use App\Models\BookingSlot;
use App\Models\SiteSetting;
use App\Services\BookingSlotGeneratorService;
use App\Services\GoogleCalendarService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
            'booking_price' => $settings->booking_price ?? 5000,
            'booking_whatsapp' => $settings->booking_whatsapp ?? '',
            'booking_weeks_ahead' => $settings->booking_weeks_ahead ?? 4,
            'booking_advance_hours' => $settings->booking_advance_hours ?? 24,
            'booking_duration_minutes' => $settings->booking_duration_minutes ?? 120,
            'google_calendar_id' => $settings->google_calendar_id ?? 'primary',
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
                    ->description('Textos y precio que se muestran en /sesion-de-contenido')
                    ->columns(2)
                    ->schema([
                        TextInput::make('booking_title')
                            ->label('Título')
                            ->placeholder('Sesión de Contenido Profesional')
                            ->columnSpanFull(),

                        TextInput::make('booking_subtitle')
                            ->label('Subtítulo')
                            ->placeholder('2 Reels + 20 Fotos editadas en una sola sesión')
                            ->columnSpanFull(),

                        TextInput::make('booking_price')
                            ->label('Precio (MXN)')
                            ->numeric()
                            ->default(5000)
                            ->prefix('$')
                            ->suffix('MXN'),

                        TextInput::make('booking_whatsapp')
                            ->label('WhatsApp de contacto')
                            ->placeholder('5219841234567')
                            ->helperText('Con código de país, sin + ni espacios.'),
                    ]),

                Section::make('Parámetros de reserva')
                    ->description('Controlan cuándo y cuántos horarios se generan automáticamente.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('booking_weeks_ahead')
                            ->label('Semanas hacia adelante')
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(52)
                            ->suffix('sem.')
                            ->helperText('Cuántas semanas de horarios mostrar.'),

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
                            ->default(120)
                            ->minValue(30)
                            ->suffix('min')
                            ->helperText('Se usa para detectar conflictos en Google Calendar.'),
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
            'booking_price' => (int) ($data['booking_price'] ?? 5000),
            'booking_whatsapp' => $data['booking_whatsapp'] ?? null,
            'booking_weeks_ahead' => (int) ($data['booking_weeks_ahead'] ?? 4),
            'booking_advance_hours' => (int) ($data['booking_advance_hours'] ?? 24),
            'booking_duration_minutes' => (int) ($data['booking_duration_minutes'] ?? 120),
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
        BookingSlotGeneratorService $generator
    ): void {
        $settings = SiteSetting::current();
        $weeksAhead = $settings?->booking_weeks_ahead ?? 4;

        try {
            $result = $generator->generate($weeksAhead);

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
        BookingSlotGeneratorService $generator
    ): void {
        $settings = SiteSetting::current();
        $weeksAhead = $settings?->booking_weeks_ahead ?? 4;

        try {
            $deleted = $generator->clearFutureSlots();
            $result = $generator->generate($weeksAhead);

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
}
