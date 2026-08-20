import { useCallback, useEffect, useMemo, useRef, useState, type PointerEvent } from 'react';
import { ChevronLeft, ChevronRight, ExternalLink, Pause, Play, SlidersHorizontal } from 'lucide-react';
import SiteLayout from '@/layouts/SiteLayout';
import { SeoHead } from '@/components/lapsique/SeoHead';

const DURATION = 8_751;
const AUDIO_URL = '/audio/traumer-shonky/session.m4a';
const PEAKS_URL = '/audio/traumer-shonky/peaks.json';
const TRACKS_URL = '/audio/traumer-shonky/tracks.json';

const BANDS = [
    { frequency: 60, label: '60 Hz' },
    { frequency: 170, label: '170 Hz' },
    { frequency: 350, label: '350 Hz' },
    { frequency: 1_000, label: '1 kHz' },
    { frequency: 3_500, label: '3.5 kHz' },
    { frequency: 10_000, label: '10 kHz' },
] as const;

const PRESETS = {
    Original: [0, 0, 0, 0, 0, 0],
    Cálido: [2.5, 1.5, 0.5, -0.5, -1, -1.5],
    Club: [3, 1.5, -1, -0.5, 1.5, 2],
    Noche: [1.5, 0.5, -1, -0.5, -2, -3],
} as const;

type PresetName = keyof typeof PRESETS;
type Track = {
    offset: number;
    title: string;
    artist: string;
    url?: string;
    cover?: string;
    verifiedBy: 'Shazam';
};

const GALLERY = [
    { src: '/images/traumer-shonky/gallery/foto-157.webp', alt: 'Público frente a la cabina durante Traumer b2b Shonky', shape: 'wide' },
    { src: '/images/traumer-shonky/gallery/foto-004.webp', alt: 'Traumer seleccionando un vinilo en la cabina', shape: 'portrait' },
    { src: '/images/traumer-shonky/gallery/foto-014.webp', alt: 'Shonky concentrado durante el set', shape: 'portrait' },
    { src: '/images/traumer-shonky/gallery/foto-028.webp', alt: 'Detalle de la selección en tornamesa', shape: 'portrait' },
    { src: '/images/traumer-shonky/gallery/foto-060.webp', alt: 'Vista completa de la cabina antes de la noche', shape: 'landscape' },
    { src: '/images/traumer-shonky/gallery/foto-063.webp', alt: 'Traumer y Shonky compartiendo la mezcla', shape: 'portrait' },
    { src: '/images/traumer-shonky/gallery/foto-077.webp', alt: 'Artista mezclando vinilos desde la cabina', shape: 'portrait' },
    { src: '/images/traumer-shonky/gallery/foto-088.webp', alt: 'Primeras horas de baile frente al escenario', shape: 'landscape' },
    { src: '/images/traumer-shonky/gallery/foto-108.webp', alt: 'La pista bajo luces rojas durante la sesión', shape: 'landscape' },
    { src: '/images/traumer-shonky/gallery/foto-120.webp', alt: 'Público levantando las manos frente a los DJs', shape: 'landscape' },
    { src: '/images/traumer-shonky/gallery/foto-168.webp', alt: 'Bailarina dentro del público durante la noche', shape: 'portrait' },
    { src: '/images/traumer-shonky/gallery/foto-194.webp', alt: 'Traumer y Shonky detrás de la cabina al cierre', shape: 'landscape' },
] as const;

export default function TraumerShonkyRadio() {
    const audioRef = useRef<HTMLAudioElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const audioContextRef = useRef<AudioContext | null>(null);
    const filtersRef = useRef<BiquadFilterNode[]>([]);
    const gainRef = useRef<GainNode | null>(null);
    const [peaks, setPeaks] = useState<number[]>([]);
    const [tracks, setTracks] = useState<Track[]>([]);
    const [playing, setPlaying] = useState(false);
    const [currentTime, setCurrentTime] = useState(0);
    const [volume, setVolume] = useState(0.85);
    const [eqEnabled, setEqEnabled] = useState(true);
    const [eqValues, setEqValues] = useState<number[]>([...PRESETS.Original]);
    const [activePreset, setActivePreset] = useState<PresetName | null>('Original');
    const [dataError, setDataError] = useState(false);
    const [galleryIndex, setGalleryIndex] = useState(0);
    const [galleryPaused, setGalleryPaused] = useState(false);
    const [galleryInteracting, setGalleryInteracting] = useState(false);

    useEffect(() => {
        let mounted = true;
        Promise.all([
            fetch(PEAKS_URL).then((response) => response.ok ? response.json() : Promise.reject()),
            fetch(TRACKS_URL).then((response) => response.ok ? response.json() : Promise.reject()),
        ]).then(([nextPeaks, nextTracks]) => {
            if (!mounted) return;
            setPeaks(nextPeaks);
            setTracks(nextTracks);
        }).catch(() => mounted && setDataError(true));

        return () => { mounted = false; };
    }, []);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas || peaks.length === 0) return;

        const draw = () => {
            const rect = canvas.getBoundingClientRect();
            const ratio = window.devicePixelRatio || 1;
            canvas.width = Math.max(1, Math.floor(rect.width * ratio));
            canvas.height = Math.max(1, Math.floor(rect.height * ratio));
            const context = canvas.getContext('2d');
            if (!context) return;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, rect.width, rect.height);
            context.fillStyle = 'rgb(255 255 255 / 0.58)';
            const center = rect.height / 2;
            const columns = Math.max(1, Math.floor(rect.width));
            for (let x = 0; x < columns; x += 1) {
                const start = Math.floor((x / columns) * peaks.length);
                const end = Math.max(start + 1, Math.floor(((x + 1) / columns) * peaks.length));
                let peak = 0;
                for (let index = start; index < end; index += 1) peak = Math.max(peak, peaks[index] ?? 0);
                const amplitude = Math.max(1, peak * rect.height * 0.44);
                context.fillRect(x, center - amplitude, 1, amplitude * 2);
            }
        };

        draw();
        const observer = new ResizeObserver(draw);
        observer.observe(canvas);
        return () => observer.disconnect();
    }, [peaks]);

    useEffect(() => {
        filtersRef.current.forEach((filter, index) => {
            filter.gain.value = eqEnabled ? eqValues[index] ?? 0 : 0;
        });
    }, [eqEnabled, eqValues]);

    useEffect(() => {
        if (gainRef.current) gainRef.current.gain.value = volume;
    }, [volume]);

    useEffect(() => {
        if (galleryPaused || galleryInteracting || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const interval = window.setInterval(() => {
            setGalleryIndex((index) => (index + 1) % GALLERY.length);
        }, 5_000);
        return () => window.clearInterval(interval);
    }, [galleryInteracting, galleryPaused]);

    const setupAudio = useCallback(async () => {
        const audio = audioRef.current;
        if (!audio) return;
        if (!audioContextRef.current) {
            const context = new AudioContext();
            const source = context.createMediaElementSource(audio);
            const filters = BANDS.map((band, index) => {
                const filter = context.createBiquadFilter();
                filter.type = 'peaking';
                filter.frequency.value = band.frequency;
                filter.Q.value = 1;
                filter.gain.value = eqEnabled ? eqValues[index] ?? 0 : 0;
                return filter;
            });
            const gain = context.createGain();
            gain.gain.value = volume;
            const chain: AudioNode[] = [source, ...filters, gain, context.destination];
            chain.slice(0, -1).forEach((node, index) => node.connect(chain[index + 1]));
            audioContextRef.current = context;
            filtersRef.current = filters;
            gainRef.current = gain;
        }
        if (audioContextRef.current.state === 'suspended') await audioContextRef.current.resume();
    }, [eqEnabled, eqValues, volume]);

    const togglePlayback = async () => {
        const audio = audioRef.current;
        if (!audio) return;
        await setupAudio();
        if (audio.paused) await audio.play();
        else audio.pause();
    };

    const seek = useCallback((seconds: number) => {
        const audio = audioRef.current;
        if (!audio) return;
        const next = Math.max(0, Math.min(DURATION, seconds));
        audio.currentTime = next;
        setCurrentTime(next);
    }, []);

    const seekFromPointer = (event: PointerEvent<HTMLDivElement>) => {
        const rect = event.currentTarget.getBoundingClientRect();
        seek(((event.clientX - rect.left) / rect.width) * DURATION);
    };

    const activeTrack = useMemo(
        () => [...tracks].reverse().find((track) => track.offset <= currentTime),
        [currentTime, tracks],
    );

    const setPreset = (name: PresetName) => {
        setEqValues([...PRESETS[name]]);
        setActivePreset(name);
    };

    return (
        <SiteLayout>
            <SeoHead />
            <div className="dark relative left-1/2 w-screen -translate-x-1/2 bg-background text-foreground">
                <section className="mx-auto max-w-6xl px-4 pb-16 pt-16 sm:px-6 sm:pt-24">
                    <p className="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-primary">Lapsique Radio · Archivo 001</p>
                    <div className="mt-5 grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_15rem]">
                        <div>
                            <h1 className="max-w-[10ch] text-balance font-display text-[clamp(4rem,11vw,9.5rem)] font-black uppercase leading-[0.82] tracking-[-0.055em] text-white">
                                Traumer <span className="block text-primary">b2b Shonky</span>
                            </h1>
                            <p className="mt-8 max-w-2xl text-pretty text-base leading-relaxed text-white/72 sm:text-lg">
                                Dos horas y veinticinco minutos recuperados de la toma estéreo de Zoom H4. Audio normalizado, ecualización en tiempo real y 24 tracks identificados durante la noche.
                            </p>
                        </div>
                        <dl className="grid grid-cols-2 gap-5 border-l border-white/15 pl-5 font-mono text-xs uppercase tracking-[0.12em] text-white/45 lg:grid-cols-1">
                            <div><dt>Fecha</dt><dd className="mt-2 text-sm text-white">14.01.26</dd></div>
                            <div><dt>Duración</dt><dd className="mt-2 text-sm tabular-nums text-white">02:25:51</dd></div>
                            <div><dt>Fuente</dt><dd className="mt-2 text-sm text-white">Zoom H4</dd></div>
                        </dl>
                    </div>

                    <button type="button" data-lightbox-trigger="true" className="group relative mt-12 block w-full overflow-hidden bg-black text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-4 focus-visible:ring-offset-[#07090b]" aria-label="Abrir fotografía del público durante Traumer b2b Shonky">
                        <img src={GALLERY[0].src} alt={GALLERY[0].alt} className="aspect-[16/8] w-full object-cover opacity-85 outline outline-1 outline-white/10 transition-[filter,opacity] duration-150 group-hover:opacity-100" fetchPriority="high" />
                        <span className="absolute inset-x-0 bottom-0 flex justify-between bg-gradient-to-t from-black/90 to-transparent px-5 pb-5 pt-20 font-mono text-[0.65rem] uppercase tracking-[0.16em] text-white/65 sm:px-8 sm:pb-7">
                            <span>Sesión completa</span><span>Fotografía · Lapsique</span>
                        </span>
                    </button>
                </section>

                <section className="border-y border-white/12 bg-card" aria-labelledby="radio-heading">
                    <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
                        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p className="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-primary">Escucha la sesión</p>
                                <h2 id="radio-heading" className="mt-3 font-display text-4xl font-black uppercase tracking-[-0.035em] text-white sm:text-6xl">Radio de la noche</h2>
                            </div>
                            <p className="max-w-sm text-sm leading-relaxed text-white/50">AAC estéreo · 48 kHz · −14 LUFS · pico real −1 dBTP</p>
                        </div>

                        <audio ref={audioRef} src={AUDIO_URL} preload="metadata" onPlay={() => setPlaying(true)} onPause={() => setPlaying(false)} onTimeUpdate={(event) => setCurrentTime(event.currentTarget.currentTime)} onEnded={() => setPlaying(false)} />

                        <div className="mt-10 grid gap-6 sm:grid-cols-[5.5rem_minmax(0,1fr)] sm:items-center">
                            <button type="button" onClick={togglePlayback} className="flex size-[5.5rem] items-center justify-center rounded-full bg-primary text-white transition-transform duration-150 active:scale-[0.96] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-4 focus-visible:ring-offset-[#0b0d0f]" aria-label={playing ? 'Pausar sesión' : 'Reproducir sesión'}>
                                {playing ? <Pause className="size-7 fill-current" aria-hidden="true" /> : <Play className="ml-1 size-7 fill-current" aria-hidden="true" />}
                            </button>
                            <div className="min-w-0">
                                <p className="font-mono text-[0.65rem] uppercase tracking-[0.16em] text-white/40">Ahora</p>
                                <p className="mt-2 [overflow-wrap:anywhere] font-ui-display text-xl font-bold text-white sm:text-2xl">{activeTrack ? `${activeTrack.title} — ${activeTrack.artist}` : 'Traumer b2b Shonky'}</p>
                                <p className="mt-2 font-mono text-xs tabular-nums text-white/45">{formatTime(currentTime)} / {formatTime(DURATION)}</p>
                            </div>
                        </div>

                        <div className="mt-10">
                            <div role="slider" tabIndex={0} aria-label="Línea de tiempo de la sesión" aria-valuemin={0} aria-valuemax={DURATION} aria-valuenow={Math.floor(currentTime)} aria-valuetext={`${formatTime(currentTime)} de ${formatTime(DURATION)}`} onPointerDown={seekFromPointer} onKeyDown={(event) => {
                                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
                                event.preventDefault();
                                if (event.key === 'Home') seek(0);
                                else if (event.key === 'End') seek(DURATION);
                                else seek(currentTime + (event.key === 'ArrowRight' ? 10 : -10));
                            }} className="relative h-52 cursor-crosshair overflow-hidden border-y border-white/12 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                <canvas ref={canvasRef} className="block h-full w-full" aria-hidden="true" />
                                <div className="pointer-events-none absolute inset-y-0 left-0 border-r border-primary bg-gradient-to-r from-primary/20 to-transparent" style={{ width: `${(currentTime / DURATION) * 100}%` }} />
                                <div className="pointer-events-none absolute inset-0" aria-hidden="true">
                                    {tracks.map((track) => <span key={`${track.offset}-${track.title}`} className="absolute bottom-0 h-[22%] w-px bg-primary/55" style={{ left: `${(track.offset / DURATION) * 100}%` }} />)}
                                </div>
                            </div>
                            <div className="mt-3 flex justify-between font-mono text-[0.62rem] tabular-nums text-white/35"><span>00:00</span><span>48:37</span><span>01:37:14</span><span>02:25:51</span></div>
                        </div>

                        <div className="mt-8 flex flex-col gap-5 border-t border-white/12 pt-7 sm:flex-row sm:items-center sm:justify-between">
                            <label className="flex min-h-11 items-center gap-4 text-sm text-white/60"><span>Volumen</span><input type="range" min="0" max="1" step="0.01" value={volume} onChange={(event) => setVolume(Number(event.target.value))} className="w-44 accent-primary" aria-label="Volumen" /></label>
                            <p className="font-mono text-[0.62rem] uppercase tracking-[0.14em] text-white/35">24 coincidencias verificadas con Shazam</p>
                        </div>
                    </div>
                </section>

                <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24" aria-labelledby="equalizer-heading">
                    <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="flex items-center gap-2 font-mono text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-primary"><SlidersHorizontal className="size-4" aria-hidden="true" /> Ajusta la escucha</p>
                            <h2 id="equalizer-heading" className="mt-3 font-display text-4xl font-black uppercase tracking-[-0.035em] text-white sm:text-6xl">Ecualizador</h2>
                        </div>
                        <button type="button" role="switch" aria-checked={eqEnabled} onClick={() => setEqEnabled((enabled) => !enabled)} className="flex min-h-11 items-center gap-3 self-start font-ui-display text-sm font-bold uppercase tracking-[0.08em] text-white/65 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary sm:self-auto">
                            <span className={`relative h-6 w-11 rounded-full transition-colors duration-150 ${eqEnabled ? 'bg-primary' : 'bg-white/25'}`} aria-hidden="true"><span className={`absolute top-1 size-4 rounded-full bg-white transition-transform duration-150 ${eqEnabled ? 'translate-x-6' : 'translate-x-1'}`} /></span>
                            EQ {eqEnabled ? 'activo' : 'desactivado'}
                        </button>
                    </div>
                    <div className="mt-9 flex gap-3 overflow-x-auto pb-3" role="group" aria-label="Presets del ecualizador">
                        {(Object.keys(PRESETS) as PresetName[]).map((name) => <button key={name} type="button" aria-pressed={activePreset === name} onClick={() => setPreset(name)} className={`min-h-11 shrink-0 rounded-full border px-5 text-sm transition-[background-color,border-color,color,transform] duration-150 active:scale-[0.96] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${activePreset === name ? 'border-white bg-white text-black' : 'border-white/18 text-white/55 hover:border-primary hover:text-white'}`}>{name}</button>)}
                    </div>
                    <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        {BANDS.map((band, index) => <label key={band.frequency} className="grid gap-4 border-t border-white/12 pt-5">
                            <span className="flex justify-between font-mono text-xs"><span className="text-white/50">{band.label}</span><span className="tabular-nums text-white">{formatDb(eqValues[index] ?? 0)}</span></span>
                            <input type="range" min="-6" max="6" step="0.5" value={eqValues[index]} onChange={(event) => {
                                const next = [...eqValues];
                                next[index] = Number(event.target.value);
                                setEqValues(next);
                                setActivePreset(null);
                            }} className="h-11 w-full accent-primary" aria-label={band.label} />
                        </label>)}
                    </div>
                    <p className="mt-8 max-w-2xl text-sm leading-relaxed text-white/45">Los ajustes se aplican únicamente en tu navegador. El archivo original y la mezcla normalizada permanecen intactos.</p>
                </section>

                <section className="border-y border-white/12 bg-card" aria-labelledby="tracks-heading">
                    <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div><p className="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-primary">Timeline verificada</p><h2 id="tracks-heading" className="mt-3 font-display text-4xl font-black uppercase tracking-[-0.035em] text-white sm:text-6xl">Tracks de la noche</h2></div>
                            <p role="status" className="font-mono text-xs uppercase tracking-[0.12em] text-white/40">{dataError ? 'No fue posible cargar la lista' : `${tracks.length} coincidencias`}</p>
                        </div>
                        <ol className="mt-10 grid gap-x-10 md:grid-cols-2">
                            {tracks.map((track) => <li key={`${track.offset}-${track.title}`} className="grid grid-cols-[4.5rem_3.75rem_minmax(0,1fr)_2.75rem] items-center gap-3 border-t border-white/12 py-4">
                                <button type="button" onClick={() => seek(track.offset)} className="min-h-11 text-left font-mono text-xs tabular-nums text-primary underline decoration-white/15 underline-offset-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{formatTime(track.offset)}</button>
                                {track.cover ? <img src={track.cover} alt="" loading="lazy" decoding="async" className="size-[3.75rem] bg-white/5 object-cover outline outline-1 outline-white/12" /> : <span className="flex size-[3.75rem] items-center justify-center bg-primary/15 font-mono text-xs font-bold text-primary outline outline-1 outline-primary/25" aria-hidden="true">LR</span>}
                                <div className="min-w-0"><p className="[overflow-wrap:anywhere] font-ui-display text-sm font-bold text-white">{track.title}</p><p className="mt-1 text-xs text-white/45">{track.artist}</p></div>
                                {track.url ? <a href={track.url} target="_blank" rel="noreferrer" className="flex size-11 items-center justify-center text-white/45 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" aria-label={`Abrir ${track.title} en Shazam`}><ExternalLink className="size-4" aria-hidden="true" /></a> : null}
                            </li>)}
                        </ol>
                    </div>
                </section>

                <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24" aria-labelledby="gallery-heading">
                    <p className="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-primary">Archivo fotográfico · Lapsique</p>
                    <h2 id="gallery-heading" className="mt-3 max-w-4xl text-balance font-display text-4xl font-black uppercase leading-[0.95] tracking-[-0.035em] text-white sm:text-7xl">Cabina, público y cierre de una misma noche.</h2>
                    <p className="mt-6 max-w-2xl text-pretty text-base leading-relaxed text-white/55">Una selección del archivo completo: luz de tarde, vinilos, transición a la noche y la pista llena.</p>
                    <div className="mt-12" onFocusCapture={() => setGalleryInteracting(true)} onBlurCapture={(event) => {
                        if (!event.currentTarget.contains(event.relatedTarget)) setGalleryInteracting(false);
                    }}>
                        <div className="overflow-hidden bg-card outline outline-1 outline-white/12">
                            <div className="flex transition-transform duration-700 ease-out motion-reduce:transition-none" style={{ transform: `translateX(-${galleryIndex * 100}%)` }}>
                                {GALLERY.map((photo, index) => <div key={photo.src} className="min-w-full" aria-hidden={index !== galleryIndex}>
                                    <button type="button" tabIndex={index === galleryIndex ? 0 : -1} data-lightbox-trigger="true" className="group relative block w-full overflow-hidden text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary" aria-label={`Abrir fotografía: ${photo.alt}`}>
                                        <img src={photo.src} alt={photo.alt} loading={index < 2 ? 'eager' : 'lazy'} decoding="async" className="aspect-[16/9] w-full object-cover opacity-90 transition-[filter,opacity,transform] duration-700 group-hover:scale-[1.01] group-hover:opacity-100 motion-reduce:transform-none motion-reduce:transition-none" />
                                    </button>
                                </div>)}
                            </div>
                        </div>
                        <div className="mt-5 flex flex-wrap items-center justify-between gap-4">
                            <p className="font-mono text-xs uppercase tracking-[0.14em] text-white/55" aria-live="polite">{String(galleryIndex + 1).padStart(2, '0')} / {String(GALLERY.length).padStart(2, '0')}</p>
                            <div className="flex items-center gap-2">
                                <button type="button" onClick={() => setGalleryIndex((index) => (index - 1 + GALLERY.length) % GALLERY.length)} className="flex size-11 items-center justify-center border border-white/18 text-white transition-colors hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" aria-label="Fotografía anterior"><ChevronLeft className="size-5" aria-hidden="true" /></button>
                                <button type="button" onClick={() => setGalleryPaused((paused) => !paused)} className="flex min-h-11 items-center gap-2 border border-white/18 px-4 text-xs font-bold uppercase tracking-[0.08em] text-white transition-colors hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" aria-label={galleryPaused ? 'Reanudar galería automática' : 'Pausar galería automática'}>
                                    {galleryPaused ? <Play className="size-4 fill-current" aria-hidden="true" /> : <Pause className="size-4 fill-current" aria-hidden="true" />}{galleryPaused ? 'Reanudar' : 'Pausar'}
                                </button>
                                <button type="button" onClick={() => setGalleryIndex((index) => (index + 1) % GALLERY.length)} className="flex size-11 items-center justify-center border border-white/18 text-white transition-colors hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" aria-label="Fotografía siguiente"><ChevronRight className="size-5" aria-hidden="true" /></button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </SiteLayout>
    );
}

function formatTime(seconds: number): string {
    const safe = Math.max(0, Math.floor(Number(seconds) || 0));
    const hours = Math.floor(safe / 3_600);
    const minutes = Math.floor((safe % 3_600) / 60);
    const remainder = safe % 60;
    return [hours, minutes, remainder].map((value) => String(value).padStart(2, '0')).join(':');
}

function formatDb(value: number): string {
    return `${value > 0 ? '+' : ''}${value.toFixed(1)} dB`;
}
