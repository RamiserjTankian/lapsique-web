import '../css/app.css';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { Toaster } from '@/components/ui/sonner';
import { ThemeProvider } from '@/providers/ThemeProvider';
import './analytics';
import './pixel';

const appName = 'lapsique.media';

// El PageView inicial lo dispara el HTML root (fbq) y analytics.js en la carga.
// Aqui reportamos las navegaciones client-side de Inertia, omitiendo la primera.
let skipFirstNavigate = true;
router.on('navigate', () => {
    if (skipFirstNavigate) {
        skipFirstNavigate = false;
        return;
    }

    window.trackMetaPixel?.('PageView');
    window.LapsiqueTracker?.pageview?.({ url: window.location.href });
});

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        createRoot(el).render(
            <ThemeProvider>
                <App {...props} />
                <Toaster position="top-center" richColors />
            </ThemeProvider>,
        );
    },
    progress: {
        color: 'oklch(0.78 0.14 75)',
    },
});
