import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, Check, Mail, Volume2, VolumeX, X } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent, type ReactNode } from 'react';
import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { route } from '@/lib/route';
import type { PageProps } from '@/types';
import { type ProducedEvent, type TrascendentalCase, type TrascendentalTour } from './Partials';

interface HomeProps {
    cases: TrascendentalCase[];
    tours: TrascendentalTour[];
    producedEvents: ProducedEvent[];
}

type Locale = 'en' | 'es';

type JoinFormState = {
    name: string;
    email: string;
    whatsapp: string;
    company_website: string;
};

const initialJoinForm: JoinFormState = {
    name: '',
    email: '',
    whatsapp: '',
    company_website: '',
};

const heroVideos = [
    '/videos/trascendental/traumer-shonky-drop-1.mp4',
    '/videos/trascendental/traumer-shonky-drop-2.mp4',
];

const copy = {
    en: {
        heroEyebrow: 'International booking / Executive production / Culture',
        heroTitle: 'ARTISTS. EVENTS. CULTURE.',
        heroBody: '',
        book: 'SOLICITAR BOOKING',
        start: 'INICIAR PROYECTO',
        join: 'JOIN THE LIST',
        soundOff: 'Enable sound',
        soundOn: 'Mute',
        servicesEyebrow: 'Services',
        impactTitle: 'IMPACT',
        presenceTitle: 'INTERNATIONAL PRESENCE',
        presenceRegion: 'Latin America · Europe · Middle East',
        presenceBody: 'A network built through long-term relationships with artists, venues and promoters.',
        artistsTitle: 'ARTISTS',
        artistsBody: 'A curated roster of artists available for bookings, showcases and special projects.',
        viewRoster: 'VIEW ROSTER',
        selectedBody: '',
        joinTitle: 'JOIN THE LIST',
        joinBody: 'Early access to events, announcements and special projects.',
        name: 'Name',
        email: 'Email Address',
        whatsapp: 'WhatsApp (optional)',
        joinSubmit: 'JOIN',
        joinSuccess: 'You are on the list.',
        joinError: 'Could not join the list. Check the fields and try again.',
        contactTitle: 'CONTACT',
        contactBody: 'For bookings, projects, partnerships and press, send the context and we will route it to the right conversation.',
        contactCta: 'CONTACT',
    },
    es: {
        heroEyebrow: 'Booking internacional / Produccion ejecutiva / Cultura',
        heroTitle: 'ARTISTAS. EVENTOS. CULTURA.',
        heroBody: '',
        book: 'SOLICITAR BOOKING',
        start: 'INICIAR PROYECTO',
        join: 'JOIN THE LIST',
        soundOff: 'Activar sonido',
        soundOn: 'Silenciar',
        servicesEyebrow: 'Servicios',
        impactTitle: 'IMPACTO',
        presenceTitle: 'PRESENCIA INTERNACIONAL',
        presenceRegion: 'Latinoamerica · Europa · Medio Oriente',
        presenceBody: 'Una red construida a traves de relaciones de largo plazo con artistas, venues y promotores.',
        artistsTitle: 'ARTISTAS',
        artistsBody: 'Roster curado de artistas disponibles para bookings, showcases y proyectos especiales.',
        viewRoster: 'VER ROSTER',
        selectedBody: '',
        joinTitle: 'JOIN THE LIST',
        joinBody: 'Acceso anticipado a eventos, anuncios y proyectos especiales.',
        name: 'Nombre',
        email: 'Correo electronico',
        whatsapp: 'WhatsApp (opcional)',
        joinSubmit: 'JOIN',
        joinSuccess: 'Ya estas en la lista.',
        joinError: 'No se pudo registrar. Revisa los campos e intenta de nuevo.',
        contactTitle: 'CONTACTO',
        contactBody: 'Para booking, proyectos, partnerships y prensa, envia el contexto y lo llevamos a la conversacion correcta.',
        contactCta: 'CONTACTO',
    },
} satisfies Record<Locale, Record<string, string>>;

const services = {
    en: [
        ['BOOKING', 'Representation, contracting and opportunity development for national and international artists.'],
        ['EVENTS', 'Concept, production and execution for clubs, festivals and brands.'],
        ['COMMUNITY', 'Access to events, experiences and projects selected by Trascendental.'],
    ],
    es: [
        ['BOOKING', 'Representacion, contratacion y desarrollo de oportunidades para artistas nacionales e internacionales.'],
        ['EVENTS', 'Concepto, produccion y ejecucion para clubes, festivales y marcas.'],
        ['COMMUNITY', 'Acceso a eventos, experiencias y proyectos seleccionados por Trascendental.'],
    ],
} satisfies Record<Locale, Array<[string, string]>>;

const impact = [
    ['300+', 'Events Produced'],
    ['30+', 'International Bookings'],
    ['30+', 'Venues Operated'],
    ['12+', 'Countries Connected'],
    ['Since 2014', 'Continuous Operations'],
];

const artists = [
    {
        name: 'Crihan',
        alias: 'Discret Popescu',
        image: '/images/trascendental/artists/crihan-portrait.jpeg',
        instagram: '@discret_popescu',
        soundcloud: 'https://on.soundcloud.com/zPmi7kTiXJ802hmL8P',
        dates: 'SOLD OUT',
        markets: 'Romanian · Discret Popescu',
    },
    {
        name: 'Jay Tripwire',
        alias: 'Witching Hour',
        image: '/images/trascendental/artists/jay-tripwire-live.jpeg',
        instagram: '@jaytripwire',
        soundcloud: 'https://on.soundcloud.com/aujhnGUYV96wiRavZk',
        dates: 'LAST DATES',
        markets: 'Canadian · Witching Hour',
    },
    {
        name: 'Mike.D',
        alias: 'Cadenza Music',
        image: '/images/trascendental/artists/mike-d-01.jpeg',
        instagram: '@mikedubssss',
        soundcloud: 'https://on.soundcloud.com/8rF8kxHjll1z6cpTEf',
        dates: 'OPEN DATES',
        markets: 'Mexican · Cadenza Music',
    },
    {
        name: 'Zone+',
        alias: 'All Day I Dream',
        image: '/images/trascendental/artists/zone-plus.jpeg',
        instagram: '@z0neplus',
        soundcloud: 'https://on.soundcloud.com/X14HzDlO4rAL1aqG8r',
        dates: 'LAST DATES',
        markets: 'South Arabia · All Day I Dream',
    },
    {
        name: 'Barry Sound',
        alias: 'House Groove',
        image: '/images/trascendental/artists/barry-sound.jpeg',
        instagram: '@barrysound_music',
        soundcloud: 'https://on.soundcloud.com/pjxWil9vE9Wf8Hgih2',
        dates: 'OPEN DATES',
        markets: 'Mexican · House Groove',
    },
    {
        name: 'Gala',
        alias: 'Boogie Room Records',
        image: '/images/trascendental/artists/gala.jpeg',
        instagram: '@galamx__',
        soundcloud: 'https://on.soundcloud.com/sJFAWimFvO7IRCZJJs',
        dates: 'OPEN DATES',
        markets: 'Mexican · Boogie Room Records',
    },
];

export default function Home({ producedEvents }: HomeProps) {
    const { ziggy, locale, site } = usePage<PageProps>().props;
    const activeLocale: Locale = locale === 'en' ? 'en' : 'es';
    const c = copy[activeLocale];
    const videoRef = useRef<HTMLVideoElement>(null);
    const [heroVideo] = useState(() => heroVideos[Math.floor(Math.random() * heroVideos.length)]);
    const [soundEnabled, setSoundEnabled] = useState(false);
    const [joinOpen, setJoinOpen] = useState(false);
    const selectedProjects = producedEvents.slice(0, 6);

    useEffect(() => {
        if (typeof window === 'undefined' || window.sessionStorage.getItem('trascendental_join_popup_seen')) {
            return;
        }

        const timer = window.setTimeout(() => {
            window.sessionStorage.setItem('trascendental_join_popup_seen', '1');
            setJoinOpen(true);
        }, 14000);

        return () => window.clearTimeout(timer);
    }, []);

    const toggleSound = () => {
        const video = videoRef.current;

        if (!video) {
            return;
        }

        const nextValue = !soundEnabled;
        video.muted = !nextValue;
        video.volume = nextValue ? 0.72 : 0;
        void video.play();
        setSoundEnabled(nextValue);
    };

    return (
        <TrascendentalLayout>
            <section className="relative overflow-hidden bg-black text-white">
                <video
                    ref={videoRef}
                    className="absolute inset-0 h-full w-full object-cover"
                    src={heroVideo}
                    poster="/images/trascendental/traumer-shonky-poster.jpg"
                    autoPlay
                    muted
                    loop
                    playsInline
                    preload="metadata"
                    aria-hidden="true"
                />
                <div className="absolute inset-0 bg-black/62" />
                <div className="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black via-black/40 to-transparent" />
                <div className="relative px-4 pb-8 pt-7 sm:px-6 lg:px-8">
                    <div className="mx-auto grid min-h-[min(680px,calc(100svh-5rem))] max-w-[1500px] content-between gap-12">
                        <div className="flex items-center justify-end gap-4 border-b border-white/20 pb-4 text-[0.7rem] font-bold uppercase text-white/68">
                            <button type="button" onClick={toggleSound} className="inline-flex items-center gap-2">
                                {soundEnabled ? <Volume2 className="h-4 w-4" /> : <VolumeX className="h-4 w-4" />}
                                {soundEnabled ? c.soundOn : c.soundOff}
                            </button>
                        </div>

                        <div className="max-w-5xl">
                            <h1 className="max-w-4xl text-[clamp(3rem,12vw,8.5rem)] font-black uppercase leading-[0.83]">
                                {c.heroTitle}
                            </h1>
                            {c.heroBody ? (
                                <p className="mt-6 max-w-xl text-base leading-relaxed text-white/76 sm:text-lg">
                                    {c.heroBody}
                                </p>
                            ) : null}
                            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                <Link href={route('trascendental.tours', undefined, false, ziggy)} className="tdl-button tdl-button-dark">
                                    {c.book}
                                    <ArrowUpRight className="h-4 w-4" />
                                </Link>
                                <Link href={route('trascendental.contact', undefined, false, ziggy)} className="tdl-button tdl-button-light">
                                    {c.start}
                                    <ArrowUpRight className="h-4 w-4" />
                                </Link>
                                <button type="button" onClick={() => setJoinOpen(true)} className="tdl-button border-white/50 text-white">
                                    {c.join}
                                    <Mail className="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section className="tdl-section">
                <div className="tdl-wrap">
                    <p className="tdl-eyebrow">{c.servicesEyebrow}</p>
                    <div className="mt-8 grid gap-8 md:grid-cols-3">
                        {services[activeLocale].map(([title, text]) => (
                            <article key={title} className="border-t border-black pt-4">
                                <h2 className="text-3xl font-black uppercase leading-none">{title}</h2>
                                <p className="mt-4 max-w-sm text-sm leading-relaxed text-black/65">{text}</p>
                                {title === 'COMMUNITY' ? (
                                    <div className="mt-5 flex flex-wrap gap-2 text-xs font-bold uppercase">
                                        {site.instagramUrl ? (
                                            <a href={site.instagramUrl} target="_blank" rel="noopener noreferrer" className="border border-black px-3 py-2">
                                                Instagram
                                            </a>
                                        ) : null}
                                        {site.facebookUrl ? (
                                            <a href={site.facebookUrl} target="_blank" rel="noopener noreferrer" className="border border-black px-3 py-2">
                                                Facebook
                                            </a>
                                        ) : null}
                                    </div>
                                ) : null}
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            <section className="bg-black px-4 py-14 text-white sm:px-6 lg:px-8">
                <div className="mx-auto max-w-[1500px]">
                    <p className="tdl-eyebrow text-white/50">{c.impactTitle}</p>
                    <div className="mt-8 grid gap-px bg-white/20 sm:grid-cols-2 lg:grid-cols-5">
                        {impact.map(([value, label]) => (
                            <div key={`${value}-${label}`} className="bg-black p-5 sm:p-6">
                                <p className="text-4xl font-black uppercase leading-none sm:text-5xl">{value}</p>
                                <p className="mt-3 text-xs font-bold uppercase leading-relaxed text-white/58">{label}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            <section className="tdl-section">
                <div className="tdl-wrap grid gap-8 lg:grid-cols-[0.84fr_1.16fr]">
                    <div>
                        <p className="tdl-eyebrow">{c.presenceRegion}</p>
                        <h2 className="tdl-heading">{c.presenceTitle}</h2>
                    </div>
                    <p className="max-w-2xl self-end text-2xl font-black uppercase leading-tight text-black/78 sm:text-3xl">
                        {c.presenceBody}
                    </p>
                </div>
            </section>

            <section id="artists" className="tdl-section border-t border-black/10">
                <div className="tdl-wrap">
                    <div className="mb-9 grid gap-5 md:grid-cols-[1fr_auto] md:items-end">
                        <div>
                            <p className="tdl-eyebrow">Roster</p>
                            <h2 className="tdl-heading">{c.artistsTitle}</h2>
                            <p className="mt-4 max-w-xl text-base leading-relaxed text-black/62">{c.artistsBody}</p>
                        </div>
                        <Link href={route('trascendental.tours', undefined, false, ziggy)} className="tdl-button border-black text-black">
                            {c.viewRoster}
                            <ArrowUpRight className="h-4 w-4" />
                        </Link>
                    </div>
                    <div className="grid gap-x-5 gap-y-8 sm:grid-cols-2">
                        {artists.map((artist) => (
                            <article key={artist.name} className="grid gap-4 border-t border-black pt-4 md:grid-cols-[0.48fr_0.52fr]">
                                <img src={artist.image} alt={`${artist.name} booking visual`} className="aspect-[4/5] w-full bg-black object-contain" loading="lazy" />
                                <div className="flex min-h-full flex-col justify-between gap-6">
                                    <div>
                                        <p className="text-xs font-bold uppercase text-black/45">{artist.alias}</p>
                                        <h3 className="mt-2 text-4xl font-black uppercase leading-none">{artist.name}</h3>
                                        <p className="mt-4 text-sm font-bold uppercase leading-relaxed text-black/62">{artist.markets}</p>
                                        <p className="mt-3 text-sm leading-relaxed text-black/58">{artist.dates}</p>
                                    </div>
                                    <div className="grid gap-2 text-xs font-bold uppercase text-black/72">
                                        <a href={`https://www.instagram.com/${artist.instagram.replace('@', '')}/`} target="_blank" rel="noopener noreferrer" className="border-t border-black/15 pt-2">
                                            Instagram {artist.instagram}
                                        </a>
                                        <a href={artist.soundcloud} target="_blank" rel="noopener noreferrer" className="border-t border-black/15 pt-2">
                                            SoundCloud
                                        </a>
                                    </div>
                                </div>
                            </article>
                        ))}
                    </div>
                </div>
            </section>

            {selectedProjects.length > 0 ? (
                <section className="tdl-section border-t border-black/10">
                    <div className="tdl-wrap">
                        <div className="grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                            {selectedProjects.map((project) => (
                                <article key={`${project.title}-${project.date}`} className="border-t border-black pt-4">
                                    <img src={project.image} alt={`${project.title} visual`} className="aspect-[4/5] w-full object-cover" loading="lazy" />
                                    <p className="mt-4 text-xs font-bold uppercase text-black/45">{project.date} / {project.city}</p>
                                    <h3 className="mt-2 text-2xl font-black uppercase leading-none">{project.title}</h3>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            ) : null}

            <section className="tdl-section bg-[#eeeeec]" id="join-list">
                <div className="tdl-wrap grid gap-9 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                    <div>
                        <p className="tdl-eyebrow">Registration</p>
                        <h2 className="tdl-heading">{c.joinTitle}</h2>
                        <p className="mt-5 max-w-md text-base leading-relaxed text-black/62">{c.joinBody}</p>
                        <button type="button" onClick={() => setJoinOpen(true)} className="tdl-button mt-7 border-black bg-black text-white">
                            {c.join}
                            <Mail className="h-4 w-4" />
                        </button>
                    </div>
                    <JoinListForm labels={c} mode="section" />
                </div>
            </section>

            <section className="tdl-section border-t border-black/10">
                <div className="tdl-wrap grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-end">
                    <div>
                        <p className="tdl-eyebrow">Booking / Projects / Partnerships / Press</p>
                        <h2 className="tdl-heading">{c.contactTitle}</h2>
                    </div>
                    <div>
                        <p className="max-w-xl text-base leading-relaxed text-black/62">{c.contactBody}</p>
                        <div className="mt-6 flex flex-wrap gap-3">
                            {['BOOKING', 'PROJECTS', 'PARTNERSHIPS', 'PRESS'].map((item) => (
                                <span key={item} className="border border-black px-3 py-2 text-xs font-bold uppercase text-black">
                                    {item}
                                </span>
                            ))}
                        </div>
                        <Link href={route('trascendental.contact', undefined, false, ziggy)} className="tdl-button mt-7 border-black text-black">
                            {c.contactCta}
                            <ArrowUpRight className="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </section>

            <JoinListPopup open={joinOpen} onClose={() => setJoinOpen(false)} labels={c} />
        </TrascendentalLayout>
    );
}

function JoinListForm({ labels, mode }: { labels: Record<string, string>; mode: 'section' | 'popup' }) {
    const { ziggy, locale } = usePage<PageProps>().props;
    const [form, setForm] = useState<JoinFormState>(initialJoinForm);
    const [status, setStatus] = useState<'idle' | 'sending' | 'success' | 'error'>('idle');

    const update = (key: keyof JoinFormState, value: string) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setStatus('sending');

        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
        const response = await fetch(route('trascendental.leads.store', undefined, false, ziggy), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({
                lead_type: 'join_list',
                service_type: 'booking',
                name: form.name || 'Join The List',
                email: form.email,
                phone: form.whatsapp,
                city: 'Join The List',
                budget: 'Join The List',
                message: 'Early access to events, announcements and special projects.',
                locale,
                current_url: window.location.href,
                privacy_accepted: true,
                captcha_answer: '11',
                company_website: form.company_website,
            }),
        });

        if (response.ok) {
            setStatus('success');
            setForm(initialJoinForm);
            return;
        }

        setStatus('error');
    };

    return (
        <form onSubmit={submit} className={mode === 'popup' ? 'grid gap-5' : 'grid gap-6 border-y border-black/15 py-8'}>
            <input
                type="text"
                name="company_website"
                value={form.company_website}
                onChange={(event) => update('company_website', event.target.value)}
                className="hidden"
                tabIndex={-1}
                autoComplete="off"
            />
            <Field label={labels.name}>
                <input value={form.name} onChange={(event) => update('name', event.target.value)} className="contact-input" autoComplete="name" />
            </Field>
            <Field label={labels.email}>
                <input value={form.email} onChange={(event) => update('email', event.target.value)} className="contact-input" type="email" autoComplete="email" required />
            </Field>
            <Field label={labels.whatsapp}>
                <input value={form.whatsapp} onChange={(event) => update('whatsapp', event.target.value)} className="contact-input" autoComplete="tel" />
            </Field>
            <button type="submit" disabled={status === 'sending'} className="tdl-button justify-center border-black bg-black text-white disabled:opacity-60">
                {status === 'success' ? <Check className="h-4 w-4" /> : <Mail className="h-4 w-4" />}
                {labels.joinSubmit}
            </button>
            {status === 'success' ? <p className="text-sm font-bold uppercase text-black">{labels.joinSuccess}</p> : null}
            {status === 'error' ? <p className="text-sm font-bold uppercase text-red-700">{labels.joinError}</p> : null}
        </form>
    );
}

function JoinListPopup({ open, onClose, labels }: { open: boolean; onClose: () => void; labels: Record<string, string> }) {
    useEffect(() => {
        if (!open) {
            return;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', handleKeyDown);

        return () => document.removeEventListener('keydown', handleKeyDown);
    }, [onClose, open]);

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 grid place-items-center bg-black/70 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="join-list-popup-title" onMouseDown={onClose}>
            <div className="w-full max-w-xl border border-white/18 bg-[#f6f6f3] p-5 text-black shadow-2xl sm:p-7" onMouseDown={(event) => event.stopPropagation()}>
                <div className="mb-7 flex items-start justify-between gap-5 border-b border-black/15 pb-5">
                    <div>
                        <p className="tdl-eyebrow">TRASCENDENTAL.</p>
                        <h2 id="join-list-popup-title" className="mt-3 text-4xl font-black uppercase leading-none">
                            {labels.joinTitle}
                        </h2>
                        <p className="mt-4 max-w-md text-sm leading-relaxed text-black/62">{labels.joinBody}</p>
                    </div>
                    <button type="button" onClick={onClose} className="inline-flex h-10 w-10 shrink-0 items-center justify-center border border-black text-black" aria-label="Close join list popup">
                        <X className="h-5 w-5" />
                    </button>
                </div>
                <JoinListForm labels={labels} mode="popup" />
            </div>
        </div>
    );
}

function Field({ label, children }: { label: string; children: ReactNode }) {
    return (
        <label className="grid gap-2">
            <span className="text-xs font-bold uppercase text-black/45">{label}</span>
            {children}
        </label>
    );
}
