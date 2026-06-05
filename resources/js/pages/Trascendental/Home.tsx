import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, Check, LoaderCircle, Mail, MessageCircle, Volume2, VolumeX, X } from 'lucide-react';
import { useEffect, useRef, useState, type FormEvent, type ReactNode } from 'react';
import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { useTranslations } from '@/hooks/useTranslations';
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
};

const initialJoinForm: JoinFormState = {
    name: '',
    email: '',
    whatsapp: '',
};

const heroVideos = [
    '/videos/trascendental/traumer-shonky-drop-1.mp4',
    '/videos/trascendental/traumer-shonky-drop-2.mp4',
];

const copy = {
    en: {
        heroEyebrow: 'International booking / Executive production / Culture',
        heroTitle: 'EVENTS.\nARTISTS.\nCULTURE.',
        heroBody: '',
        book: 'REQUEST BOOKING',
        start: 'START A PROJECT',
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
        joinBody: 'Early access to events, artist announcements and selected projects before public release.',
        name: 'Name',
        email: 'Email Address',
        whatsapp: 'WhatsApp (optional)',
        joinSubmit: 'JOIN',
        joinSending: 'SENDING',
        joinSuccess: 'Thank you. You are on the list. Your registration was saved and we will contact you through Trascendental channels.',
        joinError: 'Could not join the list. Check the fields and try again.',
        joinClose: 'Close join list popup',
        contactTitle: 'CONTACT',
        contactBody: 'For bookings, projects, partnerships and press, send the context and we will route it to the right conversation.',
        contactCta: 'CONTACT',
    },
    es: {
        heroEyebrow: 'Booking internacional / Produccion ejecutiva / Cultura',
        heroTitle: 'EVENTS.\nARTISTS.\nCULTURE.',
        heroBody: '',
        book: 'SOLICITAR BOOKING',
        start: 'INICIAR PROYECTO',
        join: 'UNIRME A LA LISTA',
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
        joinTitle: 'UNIRME A LA LISTA',
        joinBody: 'Acceso anticipado a eventos, anuncios de artistas y proyectos seleccionados antes de su lanzamiento publico.',
        name: 'Nombre',
        email: 'Correo electronico',
        whatsapp: 'WhatsApp (opcional)',
        joinSubmit: 'UNIRME',
        joinSending: 'ENVIANDO',
        joinSuccess: 'Gracias. Ya estas en la lista. Tu registro quedo guardado y te contactaremos por los canales de Trascendental.',
        joinError: 'No se pudo registrar. Revisa los campos e intenta de nuevo.',
        joinClose: 'Cerrar popup de registro',
        contactTitle: 'CONTACTO',
        contactBody: 'Para booking, proyectos, partnerships y prensa, envia el contexto y lo llevamos a la conversacion correcta.',
        contactCta: 'CONTACTO',
    },
} satisfies Record<Locale, Record<string, string>>;

const impactValues = ['300+', '30+', '30+', '12+', null] as const;
const impactLabelKeys = ['events', 'bookings', 'venues', 'countries', 'operations'] as const;

export default function Home({ tours, producedEvents }: HomeProps) {
    const { ziggy, locale, site } = usePage<PageProps>().props;
    const { t } = useTranslations();
    const activeLocale: Locale = locale === 'en' ? 'en' : 'es';
    const c = copy[activeLocale];
    const serviceCards = [
        [t('trascendental.home.services.booking'), t('trascendental.home.services.booking_text'), 'booking'],
        [t('trascendental.home.services.events'), t('trascendental.home.services.events_text'), 'events'],
        [t('trascendental.home.services.community'), t('trascendental.home.services.community_text'), 'community'],
    ];
    const videoRef = useRef<HTMLVideoElement>(null);
    const [heroVideo] = useState(() => heroVideos[Math.floor(Math.random() * heroVideos.length)]);
    const [soundEnabled, setSoundEnabled] = useState(false);
    const [joinOpen, setJoinOpen] = useState(false);
    const [heroCopyVisible, setHeroCopyVisible] = useState(true);
    const [typedHeroTitle, setTypedHeroTitle] = useState('');
    const selectedProjects = producedEvents.slice(0, 6);
    const whatsappCommunityHref = site.whatsappCommunityUrl;
    const heroTitleComplete = typedHeroTitle.length >= c.heroTitle.length;

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

    useEffect(() => {
        if (!heroCopyVisible) {
            setTypedHeroTitle('');
            return;
        }

        let index = 0;
        const title = c.heroTitle;
        setTypedHeroTitle('');

        const timer = window.setInterval(() => {
            index += 1;
            setTypedHeroTitle(title.slice(0, index));

            if (index >= title.length) {
                window.clearInterval(timer);
            }
        }, 88);

        return () => window.clearInterval(timer);
    }, [c.heroTitle, heroCopyVisible]);

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
            <section
                className="relative overflow-hidden bg-black text-white"
                onMouseEnter={() => setHeroCopyVisible(true)}
                onMouseLeave={() => setHeroCopyVisible(false)}
            >
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
                <div className="pointer-events-none absolute inset-x-0 bottom-0 h-[46%] bg-gradient-to-t from-black/80 via-black/40 to-transparent" />
                <div className="relative px-4 pb-8 pt-7 sm:px-6 lg:px-8">
                    <div className="mx-auto grid min-h-[min(680px,calc(100svh-5rem))] max-w-[1500px] content-between gap-12">
                        <div className="flex items-center justify-end gap-4 border-b border-white/20 pb-4 text-[0.7rem] font-bold uppercase text-white/68">
                            <button type="button" onClick={toggleSound} className="inline-flex items-center gap-2">
                                {soundEnabled ? <Volume2 className="h-4 w-4" /> : <VolumeX className="h-4 w-4" />}
                                {soundEnabled ? c.soundOn : c.soundOff}
                            </button>
                        </div>

                        <div className="max-w-5xl">
                            <h1
                                className={`min-h-[clamp(10rem,25vw,22rem)] max-w-4xl whitespace-pre-line text-[clamp(3rem,12vw,8.5rem)] font-black uppercase leading-[0.83] transition-opacity duration-300 ${heroCopyVisible ? 'opacity-100' : 'opacity-0'}`}
                                aria-label={c.heroTitle}
                            >
                                <span aria-hidden="true">
                                    {typedHeroTitle}
                                    <span className={`ml-2 inline-block h-[0.11em] w-[0.11em] rounded-full bg-white align-baseline ${heroCopyVisible && heroTitleComplete ? 'animate-pulse' : 'hidden'}`} />
                                </span>
                            </h1>
                            {c.heroBody ? (
                                <p className={`mt-6 max-w-xl text-base leading-relaxed text-white/76 transition-opacity duration-300 sm:text-lg ${heroCopyVisible ? 'opacity-100' : 'opacity-0'}`}>
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
                        {serviceCards.map(([title, text, key]) => (
                            <article key={key} className="border-t border-black pt-4">
                                <h2 className="text-3xl font-black uppercase leading-none">{title}</h2>
                                <p className="mt-4 max-w-sm text-sm leading-relaxed text-black/65">{text}</p>
                                {key === 'community' ? (
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
                                        {whatsappCommunityHref ? (
                                            <a href={whatsappCommunityHref} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-2 border border-black px-3 py-2">
                                                {t('trascendental.whatsapp.community_cta')}
                                                <MessageCircle className="h-3.5 w-3.5" />
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
                        {impactLabelKeys.map((key, index) => (
                            <div key={key} className="bg-black p-5 sm:p-6">
                                <p className="text-4xl font-black uppercase leading-none sm:text-5xl">{impactValues[index] ?? t('trascendental.home.impact.since')}</p>
                                <p className="mt-3 text-xs font-bold uppercase leading-relaxed text-white/58">{t(`trascendental.home.impact.${key}`)}</p>
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
                            <p className="tdl-eyebrow">{t('trascendental.home.roster')}</p>
                            <h2 className="tdl-heading">{c.artistsTitle}</h2>
                            <p className="mt-4 max-w-xl text-base leading-relaxed text-black/62">{c.artistsBody}</p>
                        </div>
                        <Link href={route('trascendental.tours', undefined, false, ziggy)} className="tdl-button border-black text-black">
                            {c.viewRoster}
                            <ArrowUpRight className="h-4 w-4" />
                        </Link>
                    </div>
                    <div className="grid gap-x-5 gap-y-8 sm:grid-cols-2">
                        {tours.map((artist) => (
                            <article key={artist.artist} className="grid gap-4 border-t border-black pt-4 md:grid-cols-[0.48fr_0.52fr]">
                                <img src={artist.image} alt={t('trascendental.tours_card.booking_portrait', { artist: artist.artist })} className="aspect-[4/5] w-full bg-black object-contain" loading="lazy" />
                                <div className="flex min-h-full flex-col justify-between gap-6">
                                    <div>
                                        {artist.label ? <p className="text-xs font-bold uppercase text-black/45">{artist.label}</p> : null}
                                        <h3 className="mt-2 text-4xl font-black uppercase leading-none">{artist.artist}</h3>
                                        <p className="mt-4 text-sm font-bold uppercase leading-relaxed text-black/62">{t(`trascendental.tours_card.nationalities.${artist.nationality}`)} · {artist.label}</p>
                                        <p className="mt-3 text-sm leading-relaxed text-black/58">{artistStatusLabel(artist.status, t)}</p>
                                    </div>
                                    <div className="grid gap-2 text-xs font-bold uppercase text-black/72">
                                        <a href={artist.instagram_url} target="_blank" rel="noopener noreferrer" className="border-t border-black/15 pt-2">
                                            Instagram {artist.instagram}
                                        </a>
                                        <a href={artist.soundcloud_url} target="_blank" rel="noopener noreferrer" className="border-t border-black/15 pt-2">
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
                                    <img src={project.image} alt={t('trascendental.produced.flyer_alt', { title: project.title })} className="aspect-[4/5] w-full bg-black/5 object-contain" loading="lazy" />
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
                        <p className="tdl-eyebrow">{t('trascendental.home.registration')}</p>
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
                        <p className="tdl-eyebrow">{t('trascendental.home.contact_eyebrow')}</p>
                        <h2 className="tdl-heading">{c.contactTitle}</h2>
                    </div>
                    <div>
                        <p className="max-w-xl text-base leading-relaxed text-black/62">{c.contactBody}</p>
                        <div className="mt-6 flex flex-wrap gap-3">
                            {['booking', 'projects', 'partnerships', 'press'].map((item) => (
                                <span key={item} className="border border-black px-3 py-2 text-xs font-bold uppercase text-black">
                                    {t(`trascendental.home.contact_chip_${item}`)}
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

function artistStatusLabel(status: string, t: (key: string) => string) {
    const key = {
        'SOLD OUT': 'sold_out',
        'LAST DATES': 'last_dates',
        'OPEN DATES': 'open_dates',
    }[status];

    return key ? t(`trascendental.home.artist_status.${key}`) : status;
}

function JoinListForm({ labels, mode }: { labels: Record<string, string>; mode: 'section' | 'popup' }) {
    const { ziggy, locale } = usePage<PageProps>().props;
    const [form, setForm] = useState<JoinFormState>(initialJoinForm);
    const [status, setStatus] = useState<'idle' | 'sending' | 'success' | 'error'>('idle');
    const [statusMessage, setStatusMessage] = useState<string | null>(null);
    const [successVisible, setSuccessVisible] = useState(false);
    const [typedSuccessMessage, setTypedSuccessMessage] = useState('');

    useEffect(() => {
        if (status !== 'success' || !statusMessage) {
            setSuccessVisible(false);
            setTypedSuccessMessage('');
            return;
        }

        setSuccessVisible(false);
        setTypedSuccessMessage('');

        const revealTimer = window.setTimeout(() => setSuccessVisible(true), 40);
        let index = 0;
        const typeTimer = window.setInterval(() => {
            index += 1;
            setTypedSuccessMessage(statusMessage.slice(0, index));

            if (index >= statusMessage.length) {
                window.clearInterval(typeTimer);
            }
        }, 18);

        return () => {
            window.clearTimeout(revealTimer);
            window.clearInterval(typeTimer);
        };
    }, [status, statusMessage]);

    const update = (key: keyof JoinFormState, value: string) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const submit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        setStatus('sending');
        setStatusMessage(null);
        setSuccessVisible(false);
        setTypedSuccessMessage('');

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
            }),
        });

        const data = (await response.json().catch(() => null)) as { message?: string } | null;

        if (response.ok) {
            setStatus('success');
            setStatusMessage(data?.message ?? labels.joinSuccess);
            setForm(initialJoinForm);
            return;
        }

        setStatus('error');
        setStatusMessage(data?.message ?? labels.joinError);
    };

    return (
        <form onSubmit={submit} className={mode === 'popup' ? 'grid gap-5' : 'grid gap-6 border-y border-black/15 py-8'} aria-busy={status === 'sending'}>
            <Field label={labels.name}>
                <input value={form.name} onChange={(event) => update('name', event.target.value)} className="contact-input" autoComplete="name" disabled={status === 'sending'} />
            </Field>
            <Field label={labels.email}>
                <input value={form.email} onChange={(event) => update('email', event.target.value)} className="contact-input" type="email" autoComplete="email" required disabled={status === 'sending'} />
            </Field>
            <Field label={labels.whatsapp}>
                <input value={form.whatsapp} onChange={(event) => update('whatsapp', event.target.value)} className="contact-input" autoComplete="tel" disabled={status === 'sending'} />
            </Field>
            <button type="submit" disabled={status === 'sending'} className="tdl-button justify-center border-black bg-black text-white disabled:opacity-60">
                {status === 'sending' ? <LoaderCircle className="h-4 w-4 animate-spin" /> : status === 'success' ? <Check className="h-4 w-4" /> : <Mail className="h-4 w-4" />}
                {status === 'sending' ? labels.joinSending : labels.joinSubmit}
            </button>
            {status === 'success' ? (
                <div
                    className={`border border-black bg-white px-4 py-3 text-sm font-bold uppercase leading-relaxed text-black transition-all duration-700 ${successVisible ? 'translate-y-0 opacity-100' : 'translate-y-2 opacity-0'}`}
                    aria-live="polite"
                >
                    {typedSuccessMessage}
                    <span className={`ml-1 inline-block h-[0.75em] w-[0.75em] rounded-full bg-black align-baseline ${typedSuccessMessage.length >= (statusMessage?.length ?? 0) ? 'animate-pulse' : ''}`} />
                </div>
            ) : null}
            {status === 'error' ? <p className="text-sm font-bold uppercase text-red-700">{statusMessage ?? labels.joinError}</p> : null}
        </form>
    );
}

function JoinListPopup({ open, onClose, labels }: { open: boolean; onClose: () => void; labels: Record<string, string> }) {
    useEffect(() => {
        if (!open) {
            return;
        }

        const { overflow, paddingRight, position, top, width } = document.body.style;
        const scrollY = window.scrollY;
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.width = '100%';
        if (scrollbarWidth > 0) {
            document.body.style.paddingRight = `${scrollbarWidth}px`;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.body.style.overflow = overflow;
            document.body.style.paddingRight = paddingRight;
            document.body.style.position = position;
            document.body.style.top = top;
            document.body.style.width = width;
            window.scrollTo(0, scrollY);
            document.removeEventListener('keydown', handleKeyDown);
        };
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
                    <button type="button" onClick={onClose} className="inline-flex h-10 w-10 shrink-0 items-center justify-center border border-black text-black" aria-label={labels.joinClose}>
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
