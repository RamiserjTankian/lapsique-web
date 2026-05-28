import { Moon, Sun } from 'lucide-react';
import { useTheme } from 'next-themes';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/useTranslations';

function updateThemeColorMeta(theme: string) {
    const meta = document.getElementById('theme-color-meta');
    if (meta) {
        meta.setAttribute('content', theme === 'dark' ? '#06060a' : '#f7f5f0');
    }
}

export function ThemeToggle() {
    const { t } = useTranslations();
    const { theme, setTheme, resolvedTheme } = useTheme();
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    if (!mounted) {
        return (
            <Button variant="ghost" size="icon" className="rounded-xl" aria-label={t('common.theme.change')} disabled>
                <Sun className="h-5 w-5" />
            </Button>
        );
    }

    const isDark = (resolvedTheme ?? theme) === 'dark';

    const toggle = () => {
        const next = isDark ? 'light' : 'dark';
        setTheme(next);
        updateThemeColorMeta(next);
    };

    return (
        <Button
            variant="ghost"
            size="icon"
            className="rounded-xl"
            onClick={toggle}
            aria-label={isDark ? t('common.theme.light_mode') : t('common.theme.dark_mode')}
        >
            {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
        </Button>
    );
}
