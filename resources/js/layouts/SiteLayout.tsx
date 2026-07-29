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
    const { url, props } = usePage<PageProps>();
    const showCreatorTrust = shouldShowCreatorTrust(url);
    const skipLabel = props.locale === 'en' ? 'Skip to main content' : 'Saltar al contenido principal';

    return (
        <MotionSiteLayout>
            <MediaAutoplayUnlock />
            <a
                href="#main-content"
                className="fixed left-4 top-4 z-[250] -translate-y-24 border border-foreground bg-background px-4 py-3 text-sm font-bold uppercase tracking-[0.08em] text-foreground opacity-0 transition-[transform,opacity] duration-150 focus:translate-y-0 focus:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 motion-reduce:transition-none"
            >
                {skipLabel}
            </a>
            <SiteHeader />
            <main id="main-content" tabIndex={-1} className="mx-auto max-w-6xl px-4 outline-none sm:px-6">{children}</main>
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
