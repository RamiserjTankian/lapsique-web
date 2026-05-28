import { motion } from 'framer-motion';
import {
    landingSectionInnerPaddingClass,
    landingSectionShellClass,
    landingSectionSurfaceClass,
} from '@/lib/landingSection';
import { cn } from '@/lib/utils';
import { fadeUp } from '@/lib/motion';

interface GlassSectionProps {
    eyebrow?: string;
    title: string;
    description?: string;
    children?: React.ReactNode;
    className?: string;
    action?: React.ReactNode;
    /** When false, only the surface wrapper is rendered (no title block). */
    showHeader?: boolean;
    id?: string;
}

export function GlassSection({
    eyebrow,
    title,
    description,
    children,
    className,
    action,
    showHeader = true,
    id,
}: GlassSectionProps) {
    return (
        <motion.section
            id={id}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: '-80px' }}
            variants={fadeUp}
            className={cn(landingSectionShellClass, className)}
        >
            <div className={cn(landingSectionSurfaceClass, landingSectionInnerPaddingClass)}>
                {showHeader ? (
                    <div className="relative z-10 mb-6 flex flex-wrap items-end justify-between gap-4 md:mb-8">
                        <div>
                            {eyebrow && (
                                <span className="inline-block rounded-full border border-accent/30 bg-accent/10 px-3 py-0.5 text-xs font-medium uppercase tracking-widest text-accent">
                                    {eyebrow}
                                </span>
                            )}
                            <h2 className="font-display mt-3 text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                                {title}
                            </h2>
                            {description && (
                                <p className="mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground md:text-base">
                                    {description}
                                </p>
                            )}
                        </div>
                        {action}
                    </div>
                ) : null}
                {children}
            </div>
        </motion.section>
    );
}
