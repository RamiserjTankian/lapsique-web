import { Head } from '@inertiajs/react';
import SiteLayout from '@/layouts/SiteLayout';
import { GlassSection } from '@/components/lapsique/GlassSection';
import { EventTeaser } from '@/components/lapsique/EventTeaser';
import type { EventItem } from '@/types';

interface EventsIndexProps {
    events: EventItem[];
}

export default function EventsIndex({ events }: EventsIndexProps) {
    return (
        <SiteLayout>
            <Head title="Eventos" />
            <GlassSection
                eyebrow="Eventos"
                title="Próximos y pasados"
                description="Experiencias electrónicas en la Riviera Maya."
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
