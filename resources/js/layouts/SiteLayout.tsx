import type { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import { CreatorTrustSection } from '@/components/lapsique/CreatorTrustSection';
import { MediaAutoplayUnlock } from '@/components/lapsique/MediaAutoplayUnlock';
import { SiteHeader } from '@/components/lapsique/SiteHeader';
import { SiteFooter } from '@/components/lapsique/SiteFooter';
import { WhatsAppFab } from '@/components/lapsique/WhatsAppFab';
import { SiteImageViewer } from '@/components/lapsique/SiteImageViewer';
import type { PageProps } from '@/types';

interface SiteLayoutProps {
    children: ReactNode;
}

export default function SiteLayout({ children }: SiteLayoutProps) {
    const { url } = usePage<PageProps>();
    const showCreatorTrust = shouldShowCreatorTrust(url);

    return (
        <MotionSiteLayout>
            <MediaAutoplayUnlock />
            <SiteHeader />
            <main className="mx-auto max-w-6xl px-4 sm:px-6">{children}</main>
            {showCreatorTrust ? <CreatorTrustSection /> : null}
            <SiteFooter />
            <WhatsAppFab />
            <SiteImageViewer />
        </MotionSiteLayout>
    );
}

function MotionSiteLayout({ children }: { children: ReactNode }) {
    return <div className="lapsique-site relative min-h-screen overflow-x-hidden grain-overlay">{children}</div>;
}

function shouldShowCreatorTrust(url: string): boolean {
    const path = normalizePath(url);

    return path === '/' || path === '/dj-set' || path === '/djset';
}

function normalizePath(url: string): string {
    if (!url) {
        return '/';
    }

    try {
        if (url.startsWith('http')) {
            return new URL(url).pathname;
        }
    } catch {
        return '/';
    }

    return url.split('?')[0] || '/';
}
