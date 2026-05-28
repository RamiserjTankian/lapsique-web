import { ArrowRight, Plane } from 'lucide-react';
import { type ReactNode } from 'react';
import { AutoplayVideo } from '@/components/lapsique/AutoplayVideo';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { useIsMobileViewport } from '@/hooks/useMediaQuery';
import { useSectionEvent } from '@/hooks/useSectionEvent';
import { useTranslations } from '@/hooks/useTranslations';
import {
    CONTENT_DRONE_SHOTS,
    CONTENT_REEL_DURATION_SECONDS,
    getContentOfferOneLiner,
    LANDING_VIDEO_LOOP_SECONDS,
} from '@/data/contentOffer';
import { formatMxn } from '@/lib/utils';
import { videoSurfaceFrameClass } from '@/lib/videoSurface';
import type { LandingVideoEntry, PortfolioItemData } from '@/types';

function isPlayableLandingVideo(
    entry: LandingVideoEntry | null | undefined,
): entry is LandingVideoEntry {
    return Boolean(entry?.src?.trim());
}

function MetaLogoIcon({ className = 'h-3.5 w-3.5' }: { className?: string }) {
    return (
        <svg className={className} viewBox="0 0 36 15" fill="currentColor" aria-hidden="true">
            <path d="M8.02 5.49C7.4 4.33 7.03 3.51 7.03 2.67 7.03 1.16 8.23 0 9.76 0c1.12 0 1.99.62 2.76 1.67l.93 1.24 1.11-1.24C15.33.62 16.2 0 17.32 0c1.53 0 2.73 1.16 2.73 2.67 0 .84-.37 1.66-.99 2.82l-2.34 4.05c-.41.71-.68 1.15-1.02 1.44-.41.41-.91.66-1.5.66s-1.09-.25-1.5-.66c-.34-.29-.61-.73-1.02-1.44L9.77 6.32 7.49 11.4c-.41.71-.68 1.15-1.02 1.44-.41.41-.91.66-1.5.66s-1.09-.25-1.5-.66c-.34-.29-.61-.73-1.02-1.44l-2.34-4.05z" />
        </svg>
    );
}

function OfferPill({ icon, children }: { icon: ReactNode; children: ReactNode }) {
    return (
        <span className="inline-flex items-center gap-2 rounded-full border border-white/18 bg-black/35 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-white backdrop-blur">
            {icon}
            {children}
        </span>
    );
}

function ReelMosaicCell({
    video,
    className = '',
    eager = false,
    pauseWhenOffscreen = true,
}: {
    video: LandingVideoEntry;
    className?: string;
    eager?: boolean;
    pauseWhenOffscreen?: boolean;
}) {
    return (
        <div className={`relative ${videoSurfaceFrameClass} ${className}`} aria-hidden>
            <AutoplayVideo
                src={video.src}
                poster={video.poster}
                className="absolute inset-0 h-full w-full"
                videoClassName="h-full w-full object-cover opacity-90"
                eager={eager}
                pauseWhenOffscreen={pauseWhenOffscreen}
                loopSegmentSeconds={LANDING_VIDEO_LOOP_SECONDS}
            />
        </div>
    );
}

function ImageMosaicCell({
    image,
    className = '',
}: {
    image: PortfolioItemData;
    className?: string;
}) {
    const src = image.asset_url ?? image.poster_url;

    if (!src) {
        return <motionImageMosaicEmpty className={className} />;
    }

    return (
        <div className={`relative overflow-hidden bg-secondary ${className}`} aria-hidden>
            <img
                src={src}
                alt=""
                className="h-full w-full object-cover opacity-90"
                loading="lazy"
            />
        </div>
    );
}

function motionImageMosaicEmpty({ className }: { className?: string }) {
    return <div className={`bg-secondary ${className ?? ''}`} aria-hidden />;
}

function OfferMosaicBackdrop({
    isMobile,
    offerVideo,
    fallbackImage,
    equipmentReels,
    galleryImages,
    primaryEager,
}: {
    isMobile: boolean;
    offerVideo: LandingVideoEntry | null;
    fallbackImage?: PortfolioItemData;
    equipmentReels: LandingVideoEntry[];
    galleryImages: PortfolioItemData[];
    primaryEager: boolean;
}) {
    if (isMobile) {
        return (
            <div className="absolute inset-0" aria-hidden>
                {offerVideo ? (
                    <ReelMosaicCell video={offerVideo} className="absolute inset-0" eager={primaryEager} />
                ) : fallbackImage ? (
                    <ImageMosaicCell image={fallbackImage} className="absolute inset-0" />
                ) : (
                    <div className="absolute inset-0 bg-black" />
                )}
            </div>
        );
    }

    return (
        <div className="absolute inset-0 grid grid-cols-[1.35fr_0.65fr] grid-rows-2" aria-hidden>
            {offerVideo ? (
                <ReelMosaicCell
                    video={offerVideo}
                    className="col-span-1 row-span-2"
                    eager={primaryEager}
                    pauseWhenOffscreen={false}
                />
            ) : fallbackImage ? (
                <ImageMosaicCell image={fallbackImage} className="col-span-1 row-span-2" />
            ) : (
                <div className="col-span-1 row-span-2 bg-black" aria-hidden />
            )}

            {equipmentReels[0] ? (
                <ReelMosaicCell video={equipmentReels[0]} pauseWhenOffscreen />
            ) : galleryImages[1] ? (
                <ImageMosaicCell image={galleryImages[1]} />
            ) : (
                <div className="bg-black/80" aria-hidden />
            )}

            {equipmentReels[1] ? (
                <ReelMosaicCell video={equipmentReels[1]} pauseWhenOffscreen />
            ) : galleryImages[2] ? (
                <ImageMosaicCell image={galleryImages[2]} />
            ) : galleryImages[0] ? (
                <ImageMosaicCell image={galleryImages[0]} />
            ) : (
                <div className="bg-black/80" aria-hidden />
            )}
        </div>
    );
}

export function MetaOfferReelShowcase({
    price,
    images,
    landingOffer,
    equipmentVideos = [],
    onBook,
}: {
    price: number;
    images: PortfolioItemData[];
    landingOffer: LandingVideoEntry | null;
    equipmentVideos?: LandingVideoEntry[];
    onBook: () => void;
}) {
    const { t } = useTranslations();
    const ref = useSectionEvent('equipment_viewed', { section: 'equipment' });
    const isMobile = useIsMobileViewport();
    const offerOneLiner = getContentOfferOneLiner(t);

    const offerVideo = isPlayableLandingVideo(landingOffer) ? landingOffer : null;
    const equipmentReels = equipmentVideos.filter(isPlayableLandingVideo).slice(0, 2);

    const galleryImages = images
        .filter((item) => item.media_type === 'image' && Boolean(item.asset_url || item.poster_url))
        .slice(0, 3);
    const fallbackImage = galleryImages[0] ?? images[0];

    const hasMosaic = Boolean(offerVideo || equipmentReels.length > 0 || galleryImages.length > 0);
    const primaryEager = !isMobile;

    return (
        <article
            id="reels-negocio"
            ref={ref}
            className="scroll-mt-20 relative min-h-[min(380px,85svh)] overflow-hidden rounded-xl border border-primary/25 bg-black text-white shadow-[0_20px_60px_rgb(0_0_0/0.16)] md:min-h-[420px]"
        >
            {hasMosaic ? (
                <OfferMosaicBackdrop
                    isMobile={isMobile}
                    offerVideo={offerVideo}
                    fallbackImage={fallbackImage}
                    equipmentReels={equipmentReels}
                    galleryImages={galleryImages}
                    primaryEager={primaryEager}
                />
            ) : null}

            <div className="absolute inset-0 bg-gradient-to-r from-black via-black/88 to-black/55 md:via-black/82 md:to-black/35" />
            <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/25 to-black/40" />

            <div className="relative flex min-h-[min(380px,85svh)] flex-col justify-between p-5 md:min-h-[420px] md:p-6">
                <div className="flex flex-wrap gap-2">
                    <OfferPill icon={<MetaLogoIcon />}>
                        {t('funnel.meta_offer.pill_meta')}
                    </OfferPill>
                    <OfferPill icon={<Plane className="h-3.5 w-3.5" aria-hidden />}>
                        {t('funnel.meta_offer.drone_pill', { drone_shots: CONTENT_DRONE_SHOTS })}
                    </OfferPill>
                </div>

                <div className="max-w-xl">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                        {t('funnel.meta_offer.eyebrow')}
                    </p>
                    <h3 className="mt-2 font-display text-2xl font-bold leading-tight md:text-3xl">
                        {t('funnel.meta_offer.title_prefix')}{' '}
                        <span className="font-mono-tabular text-primary">{formatMxn(price)}</span>
                        {' '}
                        · {offerOneLiner}.
                    </h3>
                    <BookingCtaSection className="mt-4 pb-0 pt-0">
                        <BookingCtaButton type="button" onClick={onBook}>
                            {t('funnel.meta_offer.cta')}
                            <ArrowRight className="h-5 w-5" />
                        </BookingCtaButton>
                    </BookingCtaSection>
                    <p className="mt-3 max-w-lg text-sm leading-relaxed text-white/72">
                        {t('funnel.meta_offer.description', {
                            seconds: CONTENT_REEL_DURATION_SECONDS,
                            drone_shots: CONTENT_DRONE_SHOTS,
                        })}
                    </p>
                </div>
            </div>
        </article>
    );
}
