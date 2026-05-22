import {
    SONY_BRAND_LOGO,
    SONY_CAMERA_MODELS,
    SONY_EQUIPMENT_DESCRIPTION,
    SONY_EQUIPMENT_EYEBROW,
    SONY_EQUIPMENT_HEADLINE,
} from '@/data/sonyEquipment';
import { useSectionEvent } from '@/hooks/useSectionEvent';

export function OfferEquipmentShowcase() {
    const ref = useSectionEvent('equipment_viewed', { section: 'equipment' });

    return (
        <article
            ref={ref}
            className="overflow-hidden rounded-xl border border-primary/25 bg-gradient-to-br from-zinc-950 via-[#121218] to-black text-white shadow-[0_28px_90px_rgb(0_0_0/0.2)]"
        >
            <div className="p-6 md:p-8 lg:p-10">
                <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-10">
                    <div className="flex items-center gap-4 lg:flex-col lg:items-start lg:gap-3">
                        <img
                            src={SONY_BRAND_LOGO.imageSrc}
                            alt={SONY_BRAND_LOGO.imageAlt}
                            className="h-7 w-auto object-contain md:h-9"
                            width={160}
                            height={40}
                        />
                        <p className="text-[10px] font-semibold uppercase tracking-[0.24em] text-primary">
                            {SONY_EQUIPMENT_EYEBROW}
                        </p>
                    </div>

                    <div className="min-w-0 flex-1 lg:max-w-3xl">
                        <h3 className="font-display text-2xl font-bold leading-tight md:text-3xl lg:text-4xl">
                            {SONY_EQUIPMENT_HEADLINE}
                        </h3>
                        <p className="mt-4 max-w-2xl text-sm leading-relaxed text-white/72 md:text-base">
                            {SONY_EQUIPMENT_DESCRIPTION}
                        </p>
                    </div>
                </div>

                <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5">
                    {SONY_CAMERA_MODELS.map((camera) => (
                        <figure
                            key={camera.id}
                            className="group overflow-hidden rounded-xl border border-white/12 bg-white/[0.04] transition duration-300 hover:border-primary/35"
                        >
                            <div className="relative aspect-[5/4] overflow-hidden bg-black/40 sm:aspect-[4/3]">
                                <img
                                    src={camera.imageSrc}
                                    alt={camera.imageAlt}
                                    className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]"
                                    loading="lazy"
                                />
                                <span className="absolute left-3 top-3 rounded-full border border-white/15 bg-black/55 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.2em] text-white/90 backdrop-blur">
                                    {camera.pillLabel}
                                </span>
                            </div>
                            <figcaption className="border-t border-white/10 px-4 py-4 md:px-5 md:py-5">
                                <p className="font-display text-lg font-bold text-white">{camera.name}</p>
                                <p className="mt-1 text-xs text-white/60 md:text-sm">{camera.specLine}</p>
                            </figcaption>
                        </figure>
                    ))}
                </div>

                <p className="mt-6 text-center text-xs text-white/50 md:text-sm">
                    Sony a7 V, a7 IV y a6700 disponibles en set según la producción.
                </p>
            </div>
        </article>
    );
}
