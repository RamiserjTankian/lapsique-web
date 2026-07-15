import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { MediaAutoplayUnlock } from '@/components/lapsique/MediaAutoplayUnlock';
import { SiteHeader } from '@/components/lapsique/SiteHeader';
import { SiteFooter } from '@/components/lapsique/SiteFooter';
import { WhatsAppFab } from '@/components/lapsique/WhatsAppFab';
import { TrascendentalLayout } from '@/layouts/TrascendentalLayout';
import type { PageProps } from '@/types';

interface SiteLayoutProps {
    children: ReactNode;
}

export default function SiteLayout({ children }: SiteLayoutProps) {
    const { site } = usePage<PageProps>().props;

    if (site.isTrascendental) {
        return (
            <TrascendentalLayout>
                <div className="mx-auto max-w-6xl px-4 pb-28 sm:px-6">{children}</div>
            </TrascendentalLayout>
        );
    }

    return (
        <MotionSiteLayout>
            <MediaAutoplayUnlock />
            <SiteHeader />
            <main className="mx-auto max-w-6xl px-4 pb-28 sm:px-6">{children}</main>
            <SiteFooter />
            <WhatsAppFab />
        </MotionSiteLayout>
    );
}

function MotionSiteLayout({ children }: { children: ReactNode }) {
    return <div className="relative min-h-screen overflow-x-hidden grain-overlay">{children}</div>;
}
