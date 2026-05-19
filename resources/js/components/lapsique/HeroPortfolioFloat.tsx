import { useMemo } from 'react';
import { motion, useReducedMotion } from 'framer-motion';
import type { PortfolioItemData } from '@/types';

interface OrbitPhotoConfig {
    radius: number;
    duration: number;
    delay: number;
    initialAngle: number;
}

const orbitPhotoConfigs: OrbitPhotoConfig[] = [
    { radius: 168, duration: 38, delay: 0, initialAngle: 12 },
    { radius: 195, duration: 44, delay: 0.6, initialAngle: 58 },
    { radius: 178, duration: 40, delay: 1.2, initialAngle: 102 },
    { radius: 210, duration: 48, delay: 0.4, initialAngle: 148 },
    { radius: 185, duration: 42, delay: 1.8, initialAngle: 198 },
    { radius: 202, duration: 46, delay: 0.9, initialAngle: 248 },
    { radius: 172, duration: 36, delay: 1.5, initialAngle: 292 },
    { radius: 188, duration: 50, delay: 0.2, initialAngle: 332 },
];

const MAX_FLOAT_PHOTOS = 8;

function pickHeroImages(items: PortfolioItemData[]): Array<PortfolioItemData & { imageUrl: string }> {
    return items
        .filter((item) => item.media_type === 'image')
        .map((item) => ({
            ...item,
            imageUrl: item.asset_url ?? item.poster_url ?? '',
        }))
        .filter((item) => item.imageUrl.length > 0)
        .slice(0, MAX_FLOAT_PHOTOS);
}

function OrbitPhoto({
    imageUrl,
    alt,
    config,
    reducedMotion,
    eager,
}: {
    imageUrl: string;
    alt: string;
    config: OrbitPhotoConfig;
    reducedMotion: boolean;
    eager: boolean;
}) {
    const { radius, duration, delay, initialAngle } = config;

    const frame = (
        <span className="block overflow-hidden rounded-xl border border-primary/20 bg-card/40 shadow-[0_8px_32px_oklch(0_0_0/0.25)] backdrop-blur-sm dark:border-primary/15 dark:bg-card/25">
            <img
                src={imageUrl}
                alt={alt}
                className="h-28 w-20 object-cover opacity-90 md:h-32 md:w-24"
                loading={eager ? 'eager' : 'lazy'}
                decoding="async"
            />
        </span>
    );

    if (reducedMotion) {
        return (
            <div
                className="absolute left-1/2 top-1/2 opacity-45"
                style={{
                    transform: `translate(calc(-50% + ${Math.cos((initialAngle * Math.PI) / 180) * radius}px), calc(-50% + ${Math.sin((initialAngle * Math.PI) / 180) * radius}px))`,
                }}
            >
                {frame}
            </div>
        );
    }

    return (
        <motion.div
            className="absolute left-1/2 top-1/2 h-0 w-0 opacity-50 md:opacity-55"
            style={{ rotate: initialAngle }}
            animate={{ rotate: initialAngle + 360 }}
            transition={{
                duration,
                delay,
                repeat: Infinity,
                ease: 'linear',
            }}
        >
            <motion.div
                className="absolute"
                style={{ left: radius, top: 0, x: '-50%', y: '-50%' }}
                animate={{ y: [0, -8, 5, 0] }}
                transition={{
                    duration: 4.5 + (delay % 3),
                    delay,
                    repeat: Infinity,
                    ease: 'easeInOut',
                }}
            >
                {frame}
            </motion.div>
        </motion.div>
    );
}

interface HeroPortfolioFloatProps {
    items: PortfolioItemData[];
}

export function HeroPortfolioFloat({ items }: HeroPortfolioFloatProps) {
    const reducedMotion = useReducedMotion();
    const photos = useMemo(() => pickHeroImages(items), [items]);

    if (photos.length === 0) {
        return null;
    }

    return (
        <motion.div
            className="pointer-events-none absolute inset-0 z-0 flex items-center justify-center overflow-hidden"
            aria-hidden
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.8, ease: [0.22, 1, 0.36, 1] }}
        >
            <motion.div
                className="relative mx-auto h-[min(280px,72vw)] w-full max-w-4xl scale-[0.82] sm:h-[min(360px,54vw)] sm:scale-100"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.6, delay: 0.1 }}
            >
                {photos.map((photo, index) => (
                    <OrbitPhoto
                        key={photo.id}
                        imageUrl={photo.imageUrl}
                        alt=""
                        config={orbitPhotoConfigs[index % orbitPhotoConfigs.length]}
                        reducedMotion={!!reducedMotion}
                        eager={index < 4}
                    />
                ))}
            </motion.div>

            {/* Central scrim — keeps title readable over photos */}
            <motion.div
                className="absolute inset-0 bg-[radial-gradient(ellipse_70%_55%_at_50%_42%,var(--background)_0%,color-mix(in_oklch,var(--background)_72%,transparent)_45%,transparent_72%)]"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5, delay: 0.15 }}
            />
            <motion.div
                className="absolute inset-0 bg-[radial-gradient(ellipse_95%_80%_at_50%_50%,transparent_0%,color-mix(in_oklch,var(--background)_35%,transparent)_100%)]"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                transition={{ duration: 0.5, delay: 0.2 }}
            />
        </motion.div>
    );
}
