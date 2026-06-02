import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import { FinalCta, PageShell } from './Partials';

const impact = [
    ['300+', 'Events Produced'],
    ['30+', 'International Bookings'],
    ['30+', 'Venues Operated'],
    ['12+', 'Countries Connected'],
    ['Since 2014', 'Continuous Operations'],
];

export default function About() {
    return (
        <TrascendentalLayout>
            <PageShell
                title="FROM LOCAL ROOTS TO INTERNATIONAL PROJECTS"
                intro="Trascendental was built through years of work across events, artist development and cultural projects."
            >
                <div className="grid gap-10 border-y border-black/15 py-10 lg:grid-cols-[1fr_1fr]">
                    <p className="break-words text-3xl font-black uppercase leading-none sm:text-4xl">
                        Originally connected to the team behind Jaguar House, the project evolved into an independent platform focused on artist representation, international bookings and event production.
                    </p>
                    <div className="space-y-6 text-lg leading-relaxed text-black/70">
                        <p>
                            Today, Trascendental operates across Latin America, Europe and the Middle East through a network of artists, venues and long-term collaborators.
                        </p>
                        <p>
                            The platform connects local roots with international projects through booking, production, long-term relationships and cultural continuity.
                        </p>
                    </div>
                </div>
                <section className="mt-10 bg-black p-5 text-white sm:p-7">
                    <p className="text-xs font-bold uppercase text-white/50">Impact</p>
                    <div className="mt-6 grid gap-px bg-white/20 sm:grid-cols-2 lg:grid-cols-5">
                        {impact.map(([value, label]) => (
                            <div key={`${value}-${label}`} className="bg-black p-4">
                                <p className="text-3xl font-black uppercase leading-none">{value}</p>
                                <p className="mt-3 text-xs font-bold uppercase leading-relaxed text-white/58">{label}</p>
                            </div>
                        ))}
                    </div>
                </section>
                <section className="mt-10 grid gap-6 border-y border-black/15 py-8 lg:grid-cols-[0.55fr_0.45fr] lg:items-end">
                    <div>
                        <p className="text-xs font-bold uppercase text-black/45">International presence</p>
                        <h2 className="mt-3 text-5xl font-black uppercase leading-none">Latin America · Europe · Middle East</h2>
                    </div>
                    <p className="text-lg leading-relaxed text-black/65">
                        A network built through long-term relationships with artists, venues and promoters.
                    </p>
                </section>
                <div className="mt-10 grid gap-3 md:grid-cols-[1.15fr_0.85fr]">
                    <img
                        src="/images/trascendental/about/jaguar-house-wide.jpeg"
                        alt="Jaguar House event produced by the team behind Trascendental"
                        className="aspect-[16/10] h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div className="grid gap-3 sm:grid-cols-2 md:grid-cols-1">
                        <img
                            src="/images/trascendental/about/jaguar-house-crowd.jpeg"
                            alt="Crowd at Jaguar House"
                            className="aspect-[16/10] h-full w-full object-cover"
                            loading="lazy"
                        />
                        <img
                            src="/images/trascendental/cases/rebolledo-lasers.webp"
                            alt="Trascendental stage production"
                            className="aspect-[16/10] h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                </div>
            </PageShell>
            <FinalCta />
        </TrascendentalLayout>
    );
}
