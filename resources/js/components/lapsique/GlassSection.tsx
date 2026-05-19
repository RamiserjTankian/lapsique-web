import { motion } from 'framer-motion';
import { cn } from '@/lib/utils';
import { fadeUp } from '@/lib/motion';

interface GlassSectionProps {
    eyebrow?: string;
    title: string;
    description?: string;
    children?: React.ReactNode;
    className?: string;
    action?: React.ReactNode;
}

export function GlassSection({
    eyebrow,
    title,
    description,
    children,
    className,
    action,
}: GlassSectionProps) {
    return (
        <motion.section
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, margin: '-80px' }}
            variants={fadeUp}
            className={cn('py-16 md:py-20', className)}
        >
            <div className="mb-8 flex flex-wrap items-end justify-between gap-4">
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
                        <p className="mt-2 max-w-md text-sm leading-relaxed text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
                {action}
            </div>
            {children}
        </motion.section>
    );
}
