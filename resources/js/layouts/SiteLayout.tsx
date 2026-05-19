import type { ReactNode } from 'react';
import { SiteHeader } from '@/components/lapsique/SiteHeader';
import { SiteFooter } from '@/components/lapsique/SiteFooter';
import { WhatsAppFab } from '@/components/lapsique/WhatsAppFab';

interface SiteLayoutProps {
    children: ReactNode;
}

export default function SiteLayout({ children }: SiteLayoutProps) {
    return (
        <MotionSiteLayout>
            <SiteHeader />
            <main className="mx-auto max-w-6xl px-4 pb-28 sm:px-6">{children}</main>
            <SiteFooter />
            <WhatsAppFab />
        </MotionSiteLayout>
    );
}

function MotionSiteLayout({ children }: { children: ReactNode }) {
    return <div className="relative min-h-screen grain-overlay">{children}</div>;
}
