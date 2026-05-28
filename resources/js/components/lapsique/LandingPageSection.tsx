import { forwardRef, type ComponentProps, type ReactNode } from 'react';
import {
    landingSectionInnerPaddingClass,
    landingSectionShellClass,
    landingSectionSurfaceClass,
} from '@/lib/landingSection';
import { cn } from '@/lib/utils';

type LandingPageSectionProps = ComponentProps<'section'> & {
    children: ReactNode;
    /** Skip the glass surface (e.g. full-bleed hero). */
    bare?: boolean;
    surfaceClassName?: string;
    innerClassName?: string;
    padded?: boolean;
};

export const LandingPageSection = forwardRef<HTMLElement, LandingPageSectionProps>(
    function LandingPageSection(
        {
            children,
            bare = false,
            surfaceClassName,
            innerClassName,
            padded = true,
            className,
            ...sectionProps
        },
        ref,
    ) {
        if (bare) {
            return (
                <section
                    ref={ref}
                    className={cn(landingSectionShellClass, className)}
                    {...sectionProps}
                >
                    {children}
                </section>
            );
        }

        return (
            <section
                ref={ref}
                className={cn(landingSectionShellClass, className)}
                {...sectionProps}
            >
                <div
                    className={cn(
                        landingSectionSurfaceClass,
                        padded && landingSectionInnerPaddingClass,
                        surfaceClassName,
                    )}
                >
                    <div className={innerClassName}>{children}</div>
                </div>
            </section>
        );
    },
);

LandingPageSection.displayName = 'LandingPageSection';
