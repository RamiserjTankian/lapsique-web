import { useMemo } from 'react';
import { motion } from 'framer-motion';
import { BookingCtaButton } from '@/components/lapsique/BookingCtaButton';
import { BookingCtaSection } from '@/components/lapsique/BookingCtaSection';
import { Button } from '@/components/ui/button';
import { HeroPortfolioFloat } from '@/components/lapsique/HeroPortfolioFloat';
import { HeroTextAura } from '@/components/lapsique/HeroTextAura';
import { SpecBadge } from '@/components/lapsique/SpecBadge';
import { formatMxn } from '@/lib/utils';
import {
    CONTENT_DRONE_SHOTS,
    CONTENT_PHOTOS_COUNT,
    CONTENT_REEL_DURATION_SECONDS,
} from '@/data/contentOffer';
import { trackBookingEvent } from '@/hooks/useBookingAnalytics';
import { useTranslations } from '@/hooks/useTranslations';
import { fadeUp } from '@/lib/motion';
import { openBookingModal } from '@/lib/openBookingModal';
import type { PortfolioItemData } from '@/types';

interface CinematicHeroProps {
    title: string;
    subtitle: string;
    price: number;
    showAgendaCta?: boolean;
    portfolioItems?: PortfolioItemData[];
}

const heroStagger = {
    hidden: {},
    visible: {
        transition: { staggerChildren: 0.1, delayChildren: 0.05 },
    },
};

export function CinematicHero({
    title,
    subtitle,
    price,
    showAgendaCta = true,
    portfolioItems = [],
}: CinematicHeroProps) {
    const { t } = useTranslations();
    const specReplacements = useMemo(
        () => ({
            seconds: CONTENT_REEL_DURATION_SECONDS,
            drone_shots: CONTENT_DRONE_SHOTS,
            photos_count: CONTENT_PHOTOS_COUNT,
        }),
        [],
    );

    const openBooking = () => {
        openBookingModal({
            source: 'cinematic_hero',
            analyticsEvent: 'hero_cta_clicked',
        });
    };

    return (
        <motion.section
            variants={heroStagger}
            initial="hidden"
            animate="visible"
            className="relative overflow-x-hidden py-16 text-center cinematic-vignette md:py-24"
        >
            <HeroPortfolioFloat items={portfolioItems} />

            <motion.div variants={fadeUp} className="relative z-10">
                <span className="inline-block rounded-full border border-primary/30 bg-primary/10 px-4 py-1 text-xs font-medium uppercase tracking-widest text-primary">
                    {t('booking.hero.badge')}
                </span>
            </motion.div>

            <motion.div className="relative z-10 mx-auto mt-8 max-w-4xl px-4 backdrop-blur-[2px] sm:px-6">
                <HeroTextAura />
                <motion.h1
                    variants={fadeUp}
                    className="relative z-10 font-display text-4xl font-bold tracking-tight text-foreground drop-shadow-[0_2px_24px_var(--background)] md:text-6xl lg:text-7xl"
                >
                    {title}
                </motion.h1>
                <motion.p
                    variants={fadeUp}
                    className="relative z-10 mx-auto mt-6 max-w-3xl text-base leading-relaxed text-muted-foreground drop-shadow-[0_1px_16px_var(--background)] md:text-lg"
                >
                    {subtitle}
                </motion.p>
            </motion.div>

            <motion.div
                variants={fadeUp}
                className="relative z-10 mt-10 flex flex-wrap justify-center gap-2"
            >
                <SpecBadge highlight>{t('booking.hero.spec_sony')}</SpecBadge>
                <SpecBadge>{t('booking.hero.spec_reel', specReplacements)}</SpecBadge>
                <SpecBadge>{t('booking.hero.spec_drone', specReplacements)}</SpecBadge>
                <SpecBadge>{t('booking.hero.spec_photos', specReplacements)}</SpecBadge>
            </motion.div>

            <motion.div variants={fadeUp} className="relative z-10 mt-8">
                <p className="font-mono-tabular text-3xl font-semibold text-foreground md:text-4xl">
                    {formatMxn(price)}
                </p>
                <p className="mt-1 text-sm text-muted-foreground">
                    {t('booking.hero.price_subtitle')}
                </p>
            </motion.div>

            {showAgendaCta && (
                <motion.div variants={fadeUp} className="relative z-10 mt-10 px-4 sm:px-6">
                    <BookingCtaSection hero className="py-0">
                        <BookingCtaButton hero onClick={openBooking}>
                            {t('booking.hero.cta_book')}
                        </BookingCtaButton>
                    </BookingCtaSection>
                </motion.div>
            )}

            <motion.div
                variants={fadeUp}
                className="relative z-10 mt-6 flex justify-center px-4 sm:px-6"
            >
                <div className="w-full lg:max-w-md">
                    <Button variant="glass" size="xl" className="w-full" asChild>
                        <a
                            href="#que-incluye"
                            onClick={() =>
                                trackBookingEvent('hero_cta_clicked', { target: 'que_incluye' })
                            }
                        >
                            {t('booking.hero.cta_includes')}
                        </a>
                    </Button>
                </div>
            </motion.div>

            <motion.div
                variants={fadeUp}
                className="relative z-10 mx-auto mt-10 grid max-w-4xl gap-3 rounded-[2rem] border border-border/70 bg-secondary p-4 text-left backdrop-blur-md md:grid-cols-3 md:p-5"
            >
                <div>
                    <p className="text-[10px] uppercase tracking-[0.24em] text-primary/80">{t('booking.hero.promise_label')}</p>
                    <p className="mt-2 text-sm text-foreground">{t('booking.hero.promise')}</p>
                </div>
                <div>
                    <p className="text-[10px] uppercase tracking-[0.24em] text-primary/80">{t('booking.hero.ideal_for_label')}</p>
                    <p className="mt-2 text-sm text-foreground">{t('booking.hero.ideal_for')}</p>
                </div>
                <div>
                    <p className="text-[10px] uppercase tracking-[0.24em] text-primary/80">{t('booking.hero.conversion_label')}</p>
                    <p className="mt-2 text-sm text-foreground">{t('booking.hero.conversion_body')}</p>
                </div>
            </motion.div>
        </motion.section>
    );
}
