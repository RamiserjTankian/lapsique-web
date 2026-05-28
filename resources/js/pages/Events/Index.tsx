import { Head } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { EventTeaser } from '@/components/lapsique/EventTeaser';
import { useTranslations } from '@/hooks/useTranslations';
import type { EventItem } from '@/types';

interface EventsIndexProps {
    events: EventItem[];
}

export default function EventsIndex({ events }: EventsIndexProps) {
    const { t } = useTranslations();

    return (
        <SiteLayout>
            <Head title={t('pages.events.title')} />
            <GlassSection
                eyebrow={t('pages.events.index_eyebrow')}
                title={t('pages.events.index_title')}
                description={t('pages.events.index_description')}
            >
                <motionEventsList events={events} />
            </GlassSection>
        </SiteLayout>
    );
}

function motionEventsList({ events }: { events: EventItem[] }) {
    return (
        <div className="grid gap-3 md:grid-cols-2">
            {events.map((event) => (
                <EventTeaser key={event.id} event={event} />
            ))}
        </div>
    );
}
