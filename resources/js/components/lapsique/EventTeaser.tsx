import { motion } from 'framer-motion';
import { glassCardVariants } from '@/lib/variants';
import { useTranslations } from '@/hooks/useTranslations';
import { getDateFnsLocale } from '@/lib/dateLocale';
import { cn } from '@/lib/utils';
import { route } from '@/lib/route';
import { Link, usePage } from '@inertiajs/react';
import { fadeUp } from '@/lib/motion';
import type { EventItem, PageProps } from '@/types';
import { format, parseISO } from 'date-fns';
import { Calendar } from 'lucide-react';

interface EventTeaserProps {
    event: EventItem;
    index?: number;
}

export function EventTeaser({ event, index = 0 }: EventTeaserProps) {
    const { ziggy } = usePage<PageProps>().props;
    const { locale } = useTranslations();
    const dateLabel = event.starts_at
        ? format(parseISO(event.starts_at), 'd MMM yyyy', { locale: getDateFnsLocale(locale) })
        : null;

    return (
        <motion.div
            variants={fadeUp}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            custom={index}
        >
            <Link
                href={route('events.show', { event: event.slug }, false, ziggy)}
                className={cn(
                    glassCardVariants(),
                    'group flex gap-4 overflow-hidden p-3 transition-all duration-300',
                    'hover:shadow-[0_8px_32px_oklch(0_0_0/0.35)] hover:border-primary/20',
                )}
            >
                {event.cover_url ? (
                    <div className="relative h-20 w-20 shrink-0 overflow-hidden rounded-lg">
                        <img
                            src={event.cover_url}
                            alt=""
                            className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    </div>
                ) : (
                    <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-muted/30">
                        <Calendar className="h-6 w-6 text-muted-foreground" />
                    </div>
                )}
                <div className="min-w-0 flex-1 py-1">
                    {dateLabel && (
                        <p className="font-mono text-[10px] uppercase tracking-wider text-primary">
                            {dateLabel}
                        </p>
                    )}
                    <h3 className="mt-1 line-clamp-2 font-semibold text-foreground transition-colors group-hover:text-primary">
                        {event.title}
                    </h3>
                    {event.location_name && (
                        <p className="mt-1 text-xs text-muted-foreground">{event.location_name}</p>
                    )}
                </div>
            </Link>
        </motion.div>
    );
}
