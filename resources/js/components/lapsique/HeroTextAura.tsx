import { motion, useReducedMotion } from 'framer-motion';
import {
    Aperture,
    Camera,
    Clapperboard,
    FileImage,
    Film,
    FolderOpen,
    ImageIcon,
    Video,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

interface OrbitItem {
    Icon: LucideIcon;
    radius: number;
    duration: number;
    delay: number;
    size: number;
    initialAngle: number;
}

const orbitItems: OrbitItem[] = [
    { Icon: Video, radius: 118, duration: 34, delay: 0, size: 18, initialAngle: 0 },
    { Icon: Camera, radius: 142, duration: 40, delay: 0.8, size: 16, initialAngle: 52 },
    { Icon: ImageIcon, radius: 128, duration: 36, delay: 1.4, size: 17, initialAngle: 108 },
    { Icon: Film, radius: 155, duration: 44, delay: 0.3, size: 15, initialAngle: 165 },
    { Icon: FileImage, radius: 132, duration: 38, delay: 2, size: 16, initialAngle: 218 },
    { Icon: FolderOpen, radius: 148, duration: 42, delay: 1.1, size: 15, initialAngle: 275 },
    { Icon: Clapperboard, radius: 122, duration: 32, delay: 1.7, size: 17, initialAngle: 312 },
    { Icon: Aperture, radius: 138, duration: 46, delay: 0.5, size: 14, initialAngle: 38 },
];

function OrbitIcon({
    item,
    reducedMotion,
}: {
    item: OrbitItem;
    reducedMotion: boolean;
}) {
    const { Icon, radius, duration, delay, size, initialAngle } = item;

    if (reducedMotion) {
        return (
            <motion.div
                className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
                style={{
                    transform: `translate(calc(-50% + ${Math.cos((initialAngle * Math.PI) / 180) * radius}px), calc(-50% + ${Math.sin((initialAngle * Math.PI) / 180) * radius}px))`,
                }}
            >
                <span className="flex h-8 w-8 items-center justify-center rounded-lg border border-primary/20 bg-card/60 text-primary/70 shadow-sm backdrop-blur-sm">
                    <Icon style={{ width: size, height: size }} strokeWidth={1.5} />
                </span>
            </motion.div>
        );
    }

    return (
        <motion.div
            className="absolute left-1/2 top-1/2 h-0 w-0"
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
                animate={{ y: [0, -6, 4, 0] }}
                transition={{
                    duration: 4 + (delay % 3),
                    delay,
                    repeat: Infinity,
                    ease: 'easeInOut',
                }}
            >
                <span className="flex items-center justify-center rounded-lg border border-primary/25 bg-card/50 text-primary/80 shadow-[0_0_20px_oklch(0.78_0.14_75/0.2)] backdrop-blur-md dark:bg-card/30">
                    <span className="flex h-8 w-8 items-center justify-center md:h-9 md:w-9">
                        <Icon style={{ width: size, height: size }} strokeWidth={1.5} />
                    </span>
                </span>
            </motion.div>
        </motion.div>
    );
}

export function HeroTextAura() {
    const reducedMotion = useReducedMotion();

    return (
        <div
            className="pointer-events-none absolute inset-0 z-0 flex items-center justify-center"
            aria-hidden
        >
            {/* Glow orbs — moving gradients */}
            <motion.div
                className="absolute h-[min(420px,70vw)] w-[min(640px,95vw)] rounded-full opacity-60 blur-3xl"
                style={{
                    background:
                        'radial-gradient(ellipse 70% 60% at 40% 45%, var(--hero-radial-glow), transparent 68%)',
                }}
                animate={
                    reducedMotion
                        ? undefined
                        : {
                              x: ['-8%', '12%', '-4%', '-8%'],
                              y: ['-6%', '10%', '-2%', '-6%'],
                              scale: [1, 1.12, 0.94, 1],
                              opacity: [0.45, 0.7, 0.5, 0.45],
                          }
                }
                transition={{
                    duration: 14,
                    repeat: Infinity,
                    ease: 'easeInOut',
                }}
            />
            <motion.div
                className="absolute h-[min(320px,55vw)] w-[min(480px,80vw)] rounded-full opacity-50 blur-3xl"
                style={{
                    background:
                        'radial-gradient(ellipse 65% 55% at 60% 55%, oklch(0.72 0.1 200 / 0.35), transparent 70%)',
                }}
                animate={
                    reducedMotion
                        ? undefined
                        : {
                              x: ['10%', '-6%', '8%', '10%'],
                              y: ['4%', '-8%', '6%', '4%'],
                              scale: [0.95, 1.08, 1, 0.95],
                              opacity: [0.35, 0.55, 0.4, 0.35],
                          }
                }
                transition={{
                    duration: 18,
                    repeat: Infinity,
                    ease: 'easeInOut',
                    delay: 1.2,
                }}
            />
            <motion.div
                className="absolute h-48 w-72 rounded-full opacity-40 blur-2xl md:h-56 md:w-96"
                style={{
                    background:
                        'conic-gradient(from 120deg at 50% 50%, oklch(0.78 0.14 75 / 0.5), oklch(0.72 0.1 200 / 0.25), transparent 55%, oklch(0.78 0.14 75 / 0.35))',
                }}
                animate={
                    reducedMotion
                        ? undefined
                        : { rotate: [0, 360] }
                }
                transition={{
                    duration: 28,
                    repeat: Infinity,
                    ease: 'linear',
                }}
            />

            {/* Orbiting media icons */}
            <div className="relative mx-auto h-[min(260px,70vw)] w-full max-w-3xl scale-[0.82] sm:h-[min(340px,52vw)] sm:scale-100">
                {orbitItems.map((item, index) => (
                    <OrbitIcon key={index} item={item} reducedMotion={!!reducedMotion} />
                ))}
            </div>
        </div>
    );
}
