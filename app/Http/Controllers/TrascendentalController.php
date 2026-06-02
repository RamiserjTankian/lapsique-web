<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrascendentalCaseResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class TrascendentalController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Trascendental/Home', [
            'cases' => $this->caseStudies(limit: 2),
            'tours' => $this->tours(),
            'producedEvents' => $this->producedEvents(),
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('Trascendental/Services');
    }

    public function cases(): Response
    {
        return Inertia::render('Trascendental/Cases', [
            'cases' => $this->caseStudies(),
            'producedEvents' => $this->producedEvents(),
        ]);
    }

    public function events(): Response
    {
        $events = $this->producedEvents();
        $perPage = 12;
        $lastPage = max(1, (int) ceil(count($events) / $perPage));
        $page = min($lastPage, max(1, (int) request()->query('page', 1)));

        return Inertia::render('Trascendental/Events', [
            'events' => array_slice($events, ($page - 1) * $perPage, $perPage),
            'upcomingEvents' => $this->upcomingEvents(),
            'pagination' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
                'perPage' => $perPage,
                'total' => count($events),
            ],
        ]);
    }

    public function tours(): Response|array
    {
        if (request()->routeIs('trascendental.tours')) {
            return Inertia::render('Trascendental/Tours', [
                'tours' => $this->tourData(),
            ]);
        }

        return $this->tourData();
    }

    public function about(): Response
    {
        return Inertia::render('Trascendental/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Trascendental/Contact');
    }

    private function caseStudies(?int $limit = null): array
    {
        $query = Event::query()
            ->where('is_case_study', true)
            ->with('media')
            ->orderBy('case_sort')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return TrascendentalCaseResource::collection($query->get())->resolve();
    }

    private function tourData(): array
    {
        return [
            [
                'artist' => 'Crihan',
                'status' => 'SOLD OUT',
                'nationality' => 'Romanian',
                'label' => 'Into The Woods',
                'instagram' => '@discret_popescu',
                'instagram_url' => 'https://www.instagram.com/discret_popescu/',
                'soundcloud_url' => 'https://on.soundcloud.com/zPmi7kTiXJ802hmL8P',
                'bio' => 'Based in Bucharest and active for over two decades, Alin Crihan is one of Romania\'s most respected underground artists. With more than 60 vinyl releases across multiple aliases, his sound has taken him from Sunwaves and Guesthouse to clubs and festivals throughout Europe, the Americas and beyond.',
                'image' => asset('images/trascendental/artists/crihan-portrait.jpeg'),
            ],
            [
                'artist' => 'Jay Tripwire',
                'status' => 'LAST DATES',
                'nationality' => 'Canadian',
                'label' => 'Rawax Music',
                'instagram' => '@jaytripwire',
                'instagram_url' => 'https://www.instagram.com/jaytripwire/',
                'soundcloud_url' => 'https://on.soundcloud.com/aujhnGUYV96wiRavZk',
                'bio' => 'A cornerstone of the global underground for more than 30 years, Jay Tripwire has performed in over 200 cities worldwide and released more than 400 vinyl records. Known for his deep hypnotic sound and uncompromising approach behind the decks, he remains one of Canada\'s most enduring electronic music artists.',
                'image' => asset('images/trascendental/artists/jay-tripwire-live.jpeg'),
            ],
            [
                'artist' => 'Mike.D',
                'status' => 'OPEN DATES',
                'nationality' => 'Mexican',
                'label' => 'Cadenza Music',
                'instagram' => '@mikedubssss',
                'instagram_url' => 'https://www.instagram.com/mikedubssss/',
                'soundcloud_url' => 'https://on.soundcloud.com/8rF8kxHjll1z6cpTEf',
                'bio' => 'Born in Ciudad Juarez, Mike.D has established himself as one of Mexico\'s most promising minimal and underground producers. His music has been released on labels including Cadenza, Vatos Locos and Kanja Records, earning support from artists such as Arapu, Priku, Gescu and Reboot.',
                'image' => asset('images/trascendental/artists/mike-d-01.jpeg'),
            ],
            [
                'artist' => 'Barry Sound',
                'status' => 'OPEN DATES',
                'nationality' => 'Mexican',
                'label' => 'House Cookin',
                'instagram' => '@barrysound_music',
                'instagram_url' => 'https://www.instagram.com/barrysound_music/',
                'soundcloud_url' => 'https://on.soundcloud.com/pjxWil9vE9Wf8Hgih2',
                'bio' => 'Hailing from Merida, Mexico, Barry Sound blends deep house, jazz influences and minimal grooves into elegant and atmospheric sets. With releases on international labels and a growing vinyl-focused profile, he has become a rising name within Mexico\'s underground electronic music scene.',
                'image' => asset('images/trascendental/artists/barry-sound.jpeg'),
            ],
            [
                'artist' => 'Gala',
                'status' => 'OPEN DATES',
                'nationality' => 'Mexican',
                'label' => 'Boogie Room Records',
                'instagram' => '@galamx__',
                'instagram_url' => 'https://www.instagram.com/galamx__/',
                'soundcloud_url' => 'https://on.soundcloud.com/sJFAWimFvO7IRCZJJs',
                'bio' => 'Originally from Merida, Mexico, Gala is an emerging force within the underground house scene. Her versatile selections move between house, deep house, minimal and breaks, taking her from clubs in Mexico to venues in France while sharing lineups with artists such as Priku and Olga Korol.',
                'image' => asset('images/trascendental/artists/gala.jpeg'),
            ],
            [
                'artist' => 'Zone+',
                'status' => 'LAST DATES',
                'nationality' => 'Bahrain',
                'label' => 'All Day I Dream',
                'instagram' => '@z0neplus',
                'instagram_url' => 'https://www.instagram.com/z0neplus/',
                'soundcloud_url' => 'https://on.soundcloud.com/X14HzDlO4rAL1aqG8r',
                'bio' => 'Based in Bahrain, Zone+ is known for his deep and hypnotic approach to electronic music. His journey has led him to renowned events including Cercle Bali, Sunwaves, Burning Man and Sandbox Festival, while releases on labels such as All Day I Dream and Anjunadeep continue to expand his international presence.',
                'image' => asset('images/trascendental/artists/zone-plus.jpeg'),
            ],
        ];
    }

    private function producedEvents(): array
    {
        return [
            [
                'title' => 'Rebolledo at Zal Marina',
                'date' => 'Apr 4, 2026',
                'venue' => 'Zal Marina',
                'city' => 'Progreso, Yucatan',
                'lineup' => 'Rebolledo, Gerard Maya + Michelle Griffin, Golden Hour',
                'summary' => 'Produccion integral, cashless, pauta y operacion para una fecha sold out.',
                'image' => asset('images/trascendental/events/rebolledo-zal-marina-flyer.jpg'),
                'source_url' => null,
            ],
            [
                'title' => 'TDL & Atypical invites Lizz',
                'date' => 'Mar 14, 2026',
                'venue' => 'Private location',
                'city' => 'Merida, Yucatan',
                'lineup' => 'Andru, C.C (TDL), Lizz, J.Rojas, Never Alone In A Dark Room',
                'summary' => 'Fecha de house y minimal techno con Lizz y residentes TDL para una noche de club extendida.',
                'image' => asset('images/trascendental/events/tdl-atypical-lizz.jpg'),
                'source_url' => 'https://ra.co/events/2384899',
            ],
            [
                'title' => 'UMi Fest presents Iluminal w/ Olga Korol / Alci',
                'date' => 'Jan 5, 2026',
                'venue' => 'UMi Tulum',
                'city' => 'Tulum, Quintana Roo',
                'lineup' => 'Olga Korol, Alci, Andu, C.C (TDL), Franco, Golden Pineapple, M Lee',
                'summary' => 'Apertura de temporada con 12 horas de house y minimal, curada para un flujo de dia a noche.',
                'image' => asset('images/trascendental/events/umi-iluminal-ii-original.jpg'),
                'source_url' => 'https://ra.co/events/2305699',
            ],
            [
                'title' => 'UMi Fest presents - Priku',
                'date' => 'Jan 6, 2026',
                'venue' => 'UMi Tulum',
                'city' => 'Tulum, Quintana Roo',
                'lineup' => 'Priku, Andru, Alvaro C, Gala, J.Rojas, San-D + Miguel',
                'summary' => 'Extended set de Priku entre jungle y playa, con narrativa musical de dia completo.',
                'image' => asset('images/trascendental/events/umi-priku-original.jpg'),
                'source_url' => 'https://ra.co/events/2325258',
            ],
            [
                'title' => 'UMi Fest presents Mirrors w/ Nu Zau & Sepp',
                'date' => 'Jan 12, 2026',
                'venue' => 'UMi Tulum',
                'city' => 'Tulum, Quintana Roo',
                'lineup' => 'Nu Zau, Sepp, Al-Saad + ABadillo, Cicibi Halim, John Pavas, Lagunes Jr + Kapi, Monoe',
                'summary' => 'Sesion larga de minimal y house con crews locales e invitados internacionales en una progresion continua.',
                'image' => asset('images/trascendental/events/umi-mirrors.webp'),
                'source_url' => 'https://ra.co/events/2308077',
            ],
            [
                'title' => 'Iluminal II Closing Party w/ Shonky & Traumer',
                'date' => 'Jan 14, 2026',
                'venue' => 'UMi Tulum',
                'city' => 'Tulum, Quintana Roo',
                'lineup' => 'Traumer, Shonky, C.C (TDL) + J.Rojas, Etienne, Lagunes Jr, M Lee',
                'summary' => 'Cierre de temporada con Traumer y Shonky en formato de larga duracion.',
                'image' => asset('images/trascendental/events/umi-closing-party-original.jpg'),
                'source_url' => 'https://ra.co/events/2252294',
            ],
            [
                'title' => 'TDL: Dia de Muertos',
                'date' => 'Nov 1, 2025',
                'venue' => 'Nakuh',
                'city' => 'Merida',
                'lineup' => 'Ray Mono, EM2K, J.Rojas + C.C (TDL) + Halim, Gerard, Basualdo B2B Maryfer, The Felina',
                'summary' => 'Dos ambientes en Merida con Deep House, Electronica y una narrativa conectada a Dia de Muertos.',
                'image' => asset('images/trascendental/events/tdl-dia-de-muertos.webp'),
                'source_url' => 'https://ra.co/events/2273122',
            ],
            [
                'title' => 'Sala Sala & TDL: Josefina Tapia',
                'date' => 'Sep 25, 2025',
                'venue' => 'Proyecto Casa 459',
                'city' => 'Merida',
                'lineup' => 'Josefina Tapia, J.Rojas, C.C (TDL)',
                'summary' => 'Colaboracion de club con house clasico, minimal europeo, breaks y electro de alto detalle.',
                'image' => asset('images/trascendental/events/josefina-tapia.webp'),
                'source_url' => 'https://ra.co/events/2250398',
            ],
            [
                'title' => 'Trascendental & Kasa Kuro invites Lizz',
                'date' => 'Jun 6, 2025',
                'venue' => 'Ajeno',
                'city' => 'Mexico City',
                'lineup' => 'Alonso Del Rio, C.C (TDL), Garibye, InFlamme, J.Rojas, Lizz, Mila Clec, Panartezza',
                'summary' => 'Encuentro en CDMX con Lizz y colaboraciones locales para una noche de house y minimal.',
                'image' => asset('images/trascendental/events/lizz-cdmx.webp'),
                'source_url' => 'https://ra.co/events/2176587',
            ],
            [
                'title' => "TDL + Make It 'Till Monday takes over Radio28",
                'date' => 'Feb 28, 2025',
                'venue' => 'Radio28',
                'city' => 'Puebla',
                'lineup' => 'Alonso Tapia, C.C (TDL), Techu',
                'summary' => 'Takeover de TDL en Puebla con house y minimal techno en formato club.',
                'image' => asset('images/trascendental/events/radio28.webp'),
                'source_url' => 'https://ra.co/events/2103187',
            ],
            [
                'title' => 'Trascendental AT THE BEACH',
                'date' => 'Mar 23, 2024',
                'venue' => 'TBA - San Bruno',
                'city' => 'Yucatan',
                'lineup' => 'Miroloja, J.Rojas b2b Basualdo, C.C (TDL) + Povedano',
                'summary' => 'Experiencia de playa con day pass, artistas residentes e invitados de la escena underground parisina.',
                'image' => asset('images/trascendental/events/trascendental-at-the-beach.webp'),
                'source_url' => 'https://ra.co/events/1880883',
            ],
            [
                'title' => 'Trascendental: Konstantin',
                'date' => 'Apr 11, 2024',
                'venue' => 'G.O.A.T.',
                'city' => 'Merida',
                'lineup' => 'Konstantin, J.Rojas + C.C (TDL), Banks b2b BLKPND, Carlo Gerardo',
                'summary' => 'Booking internacional de Giegling con soporte local para una noche de minimal y deep house.',
                'image' => asset('images/trascendental/events/konstantin.webp'),
                'source_url' => 'https://ra.co/events/1903253',
            ],
            [
                'title' => 'Trascendental x White Deer Records: Youandewan',
                'date' => 'Mar 14, 2024',
                'venue' => 'Ignoto',
                'city' => 'Merida',
                'lineup' => 'Youandewan, J.Rojas + Duprat, Alfredo Avila, C.C (TDL)',
                'summary' => 'Colaboracion con White Deer Records enfocada en house y minimal para abrir pista al productor de Berlin.',
                'image' => asset('images/trascendental/events/youandewan.webp'),
                'source_url' => 'https://ra.co/events/1873625',
            ],
            [
                'title' => 'Trascendental Takeover',
                'date' => 'Jun 27, 2024',
                'venue' => 'Chembech Listening Club',
                'city' => 'Merida',
                'lineup' => 'J.Rojas b2b Duprat, C.C (TDL)',
                'summary' => 'Takeover de capacidad limitada con audio Funktion One y formato de escucha cercano.',
                'image' => asset('images/trascendental/events/trascendental-takeover.webp'),
                'source_url' => 'https://ra.co/events/1953959',
            ],
            [
                'title' => 'TDL presents: Cosmjn',
                'date' => 'Nov 16, 2024',
                'venue' => 'Salon Gallos',
                'city' => 'Merida',
                'lineup' => 'Cosmjn, J.Rojas, C.C (TDL), The AM, Andru b2b NB, Dolphin, Basualdo',
                'summary' => 'Noche en Salon Gallos alrededor del sonido minimal rumano y el circuito local de Merida.',
                'image' => asset('images/trascendental/events/tdl-cosmjn.webp'),
                'source_url' => 'https://ra.co/events/2025885',
            ],
            [
                'title' => 'TDL presents: Game at Salon Gallos',
                'date' => 'Sep 28, 2024',
                'venue' => 'Salon Gallos',
                'city' => 'Merida',
                'lineup' => 'Game, Gerard, Lucrecia, Gala, J.Rojas + Povedano, Diegual + C.C (TDL)',
                'summary' => 'Programacion de dos salas con techno, electronica y una seleccion local enfocada al dancefloor.',
                'image' => asset('images/trascendental/events/tdl-game.webp'),
                'source_url' => 'https://ra.co/events/1997852',
            ],
        ];
    }

    private function upcomingEvents(): array
    {
        return [
            [
                'category' => 'produced',
                'title' => 'Produced by Trascendental',
                'date' => 'TBA',
                'city' => '',
                'venue' => '',
                'lineup' => '',
                'details_url' => null,
                'tickets_url' => null,
            ],
            [
                'category' => 'announce',
                'title' => 'To be announced',
                'date' => 'TBA',
                'city' => '',
                'venue' => '',
                'lineup' => '',
                'details_url' => null,
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Crihan - Besarabia Aniversario 4',
                'date' => 'Jun 12, 2026',
                'city' => 'Buenos Aires, Argentina',
                'venue' => '@dunepark',
                'lineup' => 'Discret Popescu',
                'details_url' => null,
                'tickets_url' => 'https://events.flashpass.com.ar/eventos/besarabia-aniversario-vol4-6306',
            ],
            [
                'category' => 'roster',
                'title' => 'Crihan - Moonlight',
                'date' => 'Jun 13, 2026',
                'city' => 'Asuncion, Paraguay',
                'venue' => '@zolua',
                'lineup' => 'Discret Popescu',
                'image' => asset('images/trascendental/events/crihan-moonlight-2026.webp'),
                'details_url' => null,
                'tickets_url' => 'https://ticketea.com.py/events/moonlight-2026-06-13-23-00-00-0300',
            ],
            [
                'category' => 'roster',
                'title' => 'Crihan - Insight',
                'date' => 'Jun 19, 2026',
                'city' => 'Lima, Peru',
                'venue' => '@habemusclub',
                'lineup' => 'Discret Popescu',
                'details_url' => 'https://www.instagram.com/in.sight____?igsh=ZmF5OXp4MWx3Zmw0',
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Crihan - ENQA + Black 808',
                'date' => 'Jun 20, 2026',
                'city' => 'Cuzco, Peru',
                'venue' => '@electroniclub_cusco',
                'lineup' => 'Discret Popescu',
                'details_url' => 'https://www.instagram.com/electroniclub_cusco?igsh=dWtyemo0bmxwNDV3',
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Crihan - Diez Cero Uno',
                'date' => 'Jun 26, 2026',
                'city' => 'Puebla, Mexico',
                'venue' => '@diezcerounoo',
                'lineup' => 'Discret Popescu',
                'details_url' => 'https://www.instagram.com/diezcerounoo?igsh=YWo2aDk3Z2phZjV1',
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Crihan - Tempo Club',
                'date' => 'Jun 27, 2026',
                'city' => 'Queretaro, Mexico',
                'venue' => '@tempoclub_mx',
                'lineup' => 'Discret Popescu',
                'details_url' => 'https://www.instagram.com/tempoclub_mx?igsh=em1hbXl3aTl3ZXdo',
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Jay Tripwire - Microdot',
                'date' => 'Aug 14, 2026',
                'city' => 'Sao Paulo, Brasil',
                'venue' => '@ephigeniasp',
                'lineup' => 'Jay Tripwire',
                'details_url' => 'https://www.instagram.com/ephigeniasp?igsh=MWl2dGdyZHpydms3eg==',
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Jay Tripwire - Torreon del Monje',
                'date' => 'Aug 22, 2026',
                'city' => 'Mar del Plata, Argentina',
                'venue' => '@glasidum',
                'lineup' => 'Jay Tripwire',
                'details_url' => 'https://www.instagram.com/glasidum?igsh=MWhqcnF1aDVxNnVrbw==',
                'tickets_url' => null,
            ],
            [
                'category' => 'roster',
                'title' => 'Jay Tripwire - Tempo Club',
                'date' => 'Aug 29, 2026',
                'city' => 'Queretaro, Mexico',
                'venue' => '@tempoclub_mx',
                'lineup' => 'Jay Tripwire',
                'details_url' => 'https://www.instagram.com/tempoclub_mx?igsh=em1hbXl3aTl3ZXdo',
                'tickets_url' => null,
            ],
        ];
    }
}
