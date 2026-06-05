<?php

namespace Database\Seeders;

use App\Models\Dj;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TrascendentalPublicContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedArtists();
        $this->seedProducedEvents();
        $this->seedRosterAppearances();
    }

    private function seedArtists(): void
    {
        $artists = [
            [
                'name' => 'Crihan',
                'slug' => 'crihan',
                'booking_status' => 'SOLD OUT',
                'nationality' => 'Romanian',
                'record_label' => 'Into The Woods',
                'instagram_handle' => 'discret_popescu',
                'instagram_url' => 'https://www.instagram.com/discret_popescu/',
                'soundcloud_url' => 'https://on.soundcloud.com/zPmi7kTiXJ802hmL8P',
                'bio' => 'Romanian selector and producer associated with the Discret Popescu alias, known for detailed minimal and house sets.',
                'public_image_path' => '/images/trascendental/artists/crihan-portrait.jpeg',
                'priority' => 0,
            ],
            [
                'name' => 'Jay Tripwire',
                'slug' => 'jay-tripwire',
                'booking_status' => 'LAST DATES',
                'nationality' => 'Canadian',
                'record_label' => 'Rawax Music',
                'instagram_handle' => 'jaytripwire',
                'instagram_url' => 'https://www.instagram.com/jaytripwire/',
                'soundcloud_url' => 'https://on.soundcloud.com/aujhnGUYV96wiRavZk',
                'bio' => 'Canadian house and techno artist with a long-running catalog and a deep connection to underground club culture.',
                'public_image_path' => '/images/trascendental/artists/jay-tripwire-live.jpeg',
                'priority' => 1,
            ],
            [
                'name' => 'Mike.D',
                'slug' => 'mike-d',
                'booking_status' => 'OPEN DATES',
                'nationality' => 'Mexican',
                'record_label' => 'Cadenza Music',
                'instagram_handle' => 'mikedubssss',
                'instagram_url' => 'https://www.instagram.com/mikedubssss/',
                'soundcloud_url' => 'https://on.soundcloud.com/8rF8kxHjll1z6cpTEf',
                'bio' => 'Mexican artist with a refined house and minimal approach for club nights, showcases and curated rooms.',
                'public_image_path' => '/images/trascendental/artists/mike-d-01.jpeg',
                'priority' => 2,
            ],
            [
                'name' => 'Barry Sound',
                'slug' => 'barry-sound',
                'booking_status' => 'OPEN DATES',
                'nationality' => 'Mexican',
                'record_label' => 'House Cookin',
                'instagram_handle' => 'barrysound_music',
                'instagram_url' => 'https://www.instagram.com/barrysound_music/',
                'soundcloud_url' => 'https://on.soundcloud.com/pjxWil9vE9Wf8Hgih2',
                'bio' => 'Mexican DJ focused on warm house selections and community-driven dancefloor sessions.',
                'public_image_path' => '/images/trascendental/artists/barry-sound.jpeg',
                'priority' => 3,
            ],
            [
                'name' => 'Gala',
                'slug' => 'gala',
                'booking_status' => 'OPEN DATES',
                'nationality' => 'Mexican',
                'record_label' => 'Boogie Room Records',
                'instagram_handle' => 'galamx__',
                'instagram_url' => 'https://www.instagram.com/galamx__/',
                'soundcloud_url' => 'https://on.soundcloud.com/sJFAWimFvO7IRCZJJs',
                'bio' => 'Mexican selector connected to house groove, club programming and local scene development.',
                'public_image_path' => '/images/trascendental/artists/gala.jpeg',
                'priority' => 4,
            ],
            [
                'name' => 'Zone+',
                'slug' => 'zone-plus',
                'booking_status' => 'LAST DATES',
                'nationality' => 'Bahrain',
                'record_label' => 'All Day I Dream',
                'instagram_handle' => 'z0neplus',
                'instagram_url' => 'https://www.instagram.com/z0neplus/',
                'soundcloud_url' => 'https://on.soundcloud.com/X14HzDlO4rAL1aqG8r',
                'bio' => 'Bahrain-born artist known for melodic house, extended journeys and internationally connected showcases.',
                'public_image_path' => '/images/trascendental/artists/zone-plus.jpeg',
                'priority' => 5,
            ],
        ];

        foreach ($artists as $artist) {
            Dj::query()->updateOrCreate(
                ['slug' => $artist['slug']],
                [
                    ...$artist,
                    'trascendental_roster' => true,
                    'is_featured' => true,
                ],
            );
        }
    }

    private function seedProducedEvents(): void
    {
        $events = [
            ['rebolledo-zal-marina', 'Rebolledo - Zal Marina', '2026-04-04', 'Zal Marina', 'Progreso, Yucatan', 'Rebolledo, Gerard Maya + Michelle Griffin, Golden Hour', 'Produccion integral, cashless, pauta y operacion para una fecha sold out.', '/images/trascendental/events/rebolledo-zal-marina-flyer.jpg', null],
            ['tdl-atypical-invites-lizz', 'TDL & Atypical invites Lizz', '2026-03-14', 'Private location', 'Merida, Yucatan', 'Andru, C.C (TDL), Lizz, J.Rojas, Never Alone In A Dark Room', 'Fecha de house y minimal techno con Lizz y residentes TDL para una noche de club extendida.', '/images/trascendental/events/tdl-atypical-lizz.jpg', 'https://ra.co/events/2384899'],
            ['umi-fest-iluminal-olga-korol-alci', 'UMi Fest presents Iluminal w/ Olga Korol / Alci', '2026-01-05', 'UMi Tulum', 'Tulum, Quintana Roo', 'Olga Korol, Alci, Andu, C.C (TDL), Franco, Golden Pineapple, M Lee', 'Apertura de temporada con 12 horas de house y minimal, curada para un flujo de dia a noche.', '/images/trascendental/events/umi-iluminal-ii-original.jpg', 'https://ra.co/events/2305699'],
            ['umi-fest-priku', 'UMi Fest presents - Priku', '2026-01-06', 'UMi Tulum', 'Tulum, Quintana Roo', 'Priku, Andru, Alvaro C, Gala, J.Rojas, San-D + Miguel', 'Extended set de Priku entre jungle y playa, con narrativa musical de dia completo.', '/images/trascendental/events/umi-priku-original.jpg', 'https://ra.co/events/2325258'],
            ['umi-fest-mirrors-nu-zau-sepp', 'UMi Fest presents Mirrors w/ Nu Zau & Sepp', '2026-01-12', 'UMi Tulum', 'Tulum, Quintana Roo', 'Nu Zau, Sepp, Al-Saad + ABadillo, Cicibi Halim, John Pavas, Lagunes Jr + Kapi, Monoe', 'Sesion larga de minimal y house con crews locales e invitados internacionales en una progresion continua.', '/images/trascendental/events/umi-mirrors.webp', 'https://ra.co/events/2308077'],
            ['iluminal-closing-shonky-traumer', 'Iluminal II Closing Party w/ Shonky & Traumer', '2026-01-14', 'UMi Tulum', 'Tulum, Quintana Roo', 'Traumer, Shonky, C.C (TDL) + J.Rojas, Etienne, Lagunes Jr, M Lee', 'Cierre de temporada con Traumer y Shonky en formato de larga duracion.', '/images/trascendental/events/umi-closing-party-original.jpg', 'https://ra.co/events/2252294'],
            ['tdl-dia-de-muertos', 'TDL: Dia de Muertos', '2025-11-01', 'Nakuh', 'Merida', 'Ray Mono, EM2K, J.Rojas + C.C (TDL) + Halim, Gerard, Basualdo B2B Maryfer, The Felina', 'Dos ambientes en Merida con Deep House, Electronica y una narrativa conectada a Dia de Muertos.', '/images/trascendental/events/tdl-dia-de-muertos.webp', 'https://ra.co/events/2273122'],
            ['sala-sala-tdl-josefina-tapia', 'Sala Sala & TDL: Josefina Tapia', '2025-09-25', 'Proyecto Casa 459', 'Merida', 'Josefina Tapia, J.Rojas, C.C (TDL)', 'Colaboracion de club con house clasico, minimal europeo, breaks y electro de alto detalle.', '/images/trascendental/events/josefina-tapia.webp', 'https://ra.co/events/2250398'],
            ['trascendental-kasa-kuro-lizz', 'Trascendental & Kasa Kuro invites Lizz', '2025-06-06', 'Ajeno', 'Mexico City', 'Alonso Del Rio, C.C (TDL), Garibye, InFlamme, J.Rojas, Lizz, Mila Clec, Panartezza', 'Encuentro en CDMX con Lizz y colaboraciones locales para una noche de house y minimal.', '/images/trascendental/events/lizz-cdmx.webp', 'https://ra.co/events/2176587'],
            ['tdl-make-it-till-monday-radio28', "TDL + Make It 'Till Monday takes over Radio28", '2025-02-28', 'Radio28', 'Puebla', 'Alonso Tapia, C.C (TDL), Techu', 'Takeover de TDL en Puebla con house y minimal techno en formato club.', '/images/trascendental/events/radio28.webp', 'https://ra.co/events/2103187'],
            ['trascendental-at-the-beach', 'Trascendental AT THE BEACH', '2024-03-23', 'TBA - San Bruno', 'Yucatan', 'Miroloja, J.Rojas b2b Basualdo, C.C (TDL) + Povedano', 'Experiencia de playa con day pass, artistas residentes e invitados de la escena underground parisina.', '/images/trascendental/events/trascendental-at-the-beach.webp', 'https://ra.co/events/1880883'],
            ['trascendental-konstantin', 'Trascendental: Konstantin', '2024-04-11', 'G.O.A.T.', 'Merida', 'Konstantin, J.Rojas + C.C (TDL), Banks b2b BLKPND, Carlo Gerardo', 'Booking internacional de Giegling con soporte local para una noche de minimal y deep house.', '/images/trascendental/events/konstantin.webp', 'https://ra.co/events/1903253'],
            ['trascendental-white-deer-youandewan', 'Trascendental x White Deer Records: Youandewan', '2024-03-14', 'Ignoto', 'Merida', 'Youandewan, J.Rojas + Duprat, Alfredo Avila, C.C (TDL)', 'Colaboracion con White Deer Records enfocada en house y minimal para abrir pista al productor de Berlin.', '/images/trascendental/events/youandewan.webp', 'https://ra.co/events/1873625'],
            ['trascendental-takeover', 'Trascendental Takeover', '2024-06-27', 'Chembech Listening Club', 'Merida', 'J.Rojas b2b Duprat, C.C (TDL)', 'Takeover de capacidad limitada con audio Funktion One y formato de escucha cercano.', '/images/trascendental/events/trascendental-takeover.webp', 'https://ra.co/events/1953959'],
            ['tdl-cosmjn-salon-gallos', 'TDL presents: Cosmjn', '2024-11-16', 'Salon Gallos', 'Merida', 'Cosmjn, J.Rojas, C.C (TDL), The AM, Andru b2b NB, Dolphin, Basualdo', 'Noche en Salon Gallos alrededor del sonido minimal rumano y el circuito local de Merida.', '/images/trascendental/events/tdl-cosmjn.webp', 'https://ra.co/events/2025885'],
            ['tdl-game-salon-gallos', 'TDL presents: Game at Salon Gallos', '2024-09-28', 'Salon Gallos', 'Merida', 'Game, Gerard, Lucrecia, Gala, J.Rojas + Povedano, Diegual + C.C (TDL)', 'Programacion de dos salas con techno, electronica y una seleccion local enfocada al dancefloor.', '/images/trascendental/events/tdl-game.webp', 'https://ra.co/events/1997852'],
        ];

        foreach ($events as $index => [$slug, $title, $date, $venue, $city, $lineup, $summary, $image, $sourceUrl]) {
            Event::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'starts_at' => $this->date($date),
                    'venue' => $venue,
                    'city' => $city,
                    'lineup_text' => $lineup,
                    'description' => $summary,
                    'public_image_path' => $image,
                    'source_url' => $sourceUrl,
                    'trascendental_kind' => 'produced',
                    'trascendental_visible' => true,
                    'is_featured' => $index < 6,
                    'priority' => $index,
                ],
            );
        }
    }

    private function seedRosterAppearances(): void
    {
        $events = [
            ['crihan-besarabia-aniversario-4-2026', 'Crihan - Besarabia Aniversario 4', '2026-06-12', '@dunepark', 'Buenos Aires, Argentina', 'Discret Popescu', '/images/trascendental/events/crihan-besarabia-aniversario-4-2026.webp', null, 'https://events.flashpass.com.ar/eventos/besarabia-aniversario-vol4-6306'],
            ['crihan-moonlight-2026', 'Crihan - Moonlight', '2026-06-13', '@zolua', 'Asuncion, Paraguay', 'Discret Popescu', '/images/trascendental/events/crihan-moonlight-2026.webp', null, 'https://ticketea.com.py/events/moonlight-2026-06-13-23-00-00-0300'],
            ['crihan-insight-2026', 'Crihan - Insight', '2026-06-19', '@habemusclub', 'Lima, Peru', 'Discret Popescu', '/images/trascendental/events/crihan-insight-2026.webp', null, 'https://www.passline.com/eventos/insight-pres-crihan-rumania'],
            ['crihan-enqa-black-808-2026', 'Crihan - ENQA + Black 808', '2026-06-20', '@electroniclub_cusco', 'Cuzco, Peru', 'Discret Popescu', null, 'https://www.instagram.com/electroniclub_cusco?igsh=dWtyemo0bmxwNDV3', null],
            ['crihan-diez-cero-uno-2026', 'Crihan - Diez Cero Uno', '2026-06-26', '@diezcerounoo', 'Puebla, Mexico', 'Discret Popescu', '/images/trascendental/events/crihan-diez-cero-uno-2026.webp', 'https://www.instagram.com/diezcerounoo?igsh=YWo2aDk3Z2phZjV1', null],
            ['crihan-tempo-club-2026', 'Crihan - Tempo Club', '2026-06-27', '@tempoclub_mx', 'Queretaro, Mexico', 'Discret Popescu', null, 'https://www.instagram.com/tempoclub_mx?igsh=em1hbXl3aTl3ZXdo', null],
            ['jay-tripwire-microdot-2026', 'Jay Tripwire - Microdot', '2026-08-14', '@ephigeniasp', 'Sao Paulo, Brasil', 'Jay Tripwire', null, 'https://www.instagram.com/ephigeniasp?igsh=MWl2dGdyZHpydms3eg==', null],
            ['jay-tripwire-torreon-del-monje-2026', 'Jay Tripwire - Torreon del Monje', '2026-08-22', '@glasidum', 'Mar del Plata, Argentina', 'Jay Tripwire', null, 'https://www.instagram.com/glasidum?igsh=MWhqcnF1aDVxNnVrbw==', null],
            ['jay-tripwire-tempo-club-2026', 'Jay Tripwire - Tempo Club', '2026-08-29', '@tempoclub_mx', 'Queretaro, Mexico', 'Jay Tripwire', null, 'https://www.instagram.com/tempoclub_mx?igsh=em1hbXl3aTl3ZXdo', null],
        ];

        foreach ($events as $index => [$slug, $title, $date, $venue, $city, $lineup, $image, $detailsUrl, $ticketUrl]) {
            Event::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'starts_at' => $this->date($date),
                    'venue' => $venue,
                    'city' => $city,
                    'lineup_text' => $lineup,
                    'public_image_path' => $image,
                    'details_url' => $detailsUrl,
                    'ticket_url' => $ticketUrl,
                    'trascendental_kind' => 'roster_appearance',
                    'trascendental_visible' => true,
                    'priority' => $index,
                ],
            );
        }
    }

    private function date(string $date): Carbon
    {
        return Carbon::parse($date . ' 20:00:00', config('app.timezone'));
    }
}
