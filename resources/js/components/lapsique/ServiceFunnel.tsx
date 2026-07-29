import type { ReactNode } from 'react';
import { ArrowRight, MessageCircle, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn, formatMxn } from '@/lib/utils';

export {
    PortfolioMediaRail,
    ServicePortfolioShowcase,
    ServiceProofBand,
} from '@/components/lapsique/service-portfolio';

export const serviceFunnelPrimaryActionClass =
    'h-auto min-h-13 w-full min-w-0 rounded-none border border-primary bg-primary px-5 py-3 text-sm font-bold leading-tight text-primary-foreground shadow-none transition-[background-color,color,transform] duration-150 hover:bg-primary/88 hover:text-primary-foreground active:scale-[0.96] motion-reduce:transition-none sm:px-6';

export const serviceFunnelWhatsAppActionClass =
    'h-auto min-h-13 w-full min-w-0 rounded-none border border-[#25D366] bg-[#25D366] px-5 py-3 text-sm font-bold leading-tight text-[#04150a] shadow-none transition-[background-color,color,transform] duration-150 hover:bg-[#1ebe5d] hover:text-[#04150a] active:scale-[0.96] focus-visible:ring-[#25D366]/45 motion-reduce:transition-none sm:px-6';

type FunnelTone = 'paper' | 'soft' | 'dark';

interface ServiceFunnelHeroProps {
    eyebrow: string;
    title: string;
    description: string;
    locations?: string;
    price: number;
    priceLabel: string;
    priceNote?: string;
    primaryAction: ReactNode;
    secondaryAction?: ReactNode;
    media: ReactNode;
    mediaLabel?: string;
    mediaCaption?: string;
}

export function ServiceFunnelHero({
    eyebrow,
    title,
    description,
    locations,
    price,
    priceLabel,
    priceNote,
    primaryAction,
    secondaryAction,
    media,
    mediaLabel,
    mediaCaption,
}: ServiceFunnelHeroProps) {
    const hasLongTitle = title.trim().length > 42;

    return (
        <section
            className="relative left-1/2 w-screen -translate-x-1/2 overflow-hidden bg-[#050607] text-white"
            data-service-funnel-hero
        >
            <div className="mx-auto grid max-w-[1440px] lg:min-h-[720px] lg:grid-cols-[minmax(0,1fr)_minmax(480px,1fr)] xl:grid-cols-[minmax(0,0.96fr)_minmax(560px,1.04fr)]">
                <div
                    className="flex min-w-0 flex-col justify-center px-4 py-14 sm:px-6 sm:py-16 lg:px-10 lg:py-20 xl:px-14 2xl:px-16"
                    data-service-funnel-content
                >
                    <p className="font-mono text-[0.7rem] font-medium uppercase tracking-[0.2em] text-primary">
                        {eyebrow}
                    </p>
                    <h1
                        className={cn(
                            'mt-5 max-w-[16ch] hyphens-none text-balance font-display font-bold leading-[0.94] tracking-[-0.025em] [overflow-wrap:normal] [word-break:normal]',
                            hasLongTitle
                                ? 'text-[clamp(2.55rem,4.35vw,4.75rem)]'
                                : 'text-[clamp(2.75rem,4.8vw,5.25rem)]',
                        )}
                        data-service-funnel-title
                    >
                        {title}
                    </h1>
                    <p className="mt-6 max-w-2xl text-pretty text-base leading-[1.6] text-white/74 sm:text-lg">
                        {description}
                    </p>
                    {locations ? (
                        <p className="mt-5 font-mono text-[0.68rem] uppercase tracking-[0.17em] text-primary/90">
                            {locations}
                        </p>
                    ) : null}

                    <div className="mt-8 flex max-w-2xl flex-wrap items-end justify-between gap-5 border-y border-white/15 py-4">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-white/48">
                                {priceLabel}
                            </p>
                            <p className="mt-1 font-mono-tabular text-3xl font-bold text-white sm:text-4xl">
                                {formatMxn(price)}
                            </p>
                        </div>
                        {priceNote ? (
                            <p className="max-w-xs text-pretty text-xs leading-relaxed text-white/52">
                                {priceNote}
                            </p>
                        ) : null}
                    </div>

                    <div
                        className="mt-6 grid w-full max-w-2xl grid-cols-1 gap-3 xl:grid-cols-2 [&>*]:min-w-0 [&>*]:max-w-full [&>*]:whitespace-normal"
                        data-service-funnel-actions
                    >
                        {primaryAction}
                        {secondaryAction}
                    </div>
                </div>

                <div
                    className="relative min-h-[420px] min-w-0 border-t border-white/12 bg-black lg:min-h-full lg:border-l lg:border-t-0"
                    data-service-funnel-media
                >
                    <div className="absolute inset-0 [&>*]:h-full [&>*]:w-full [&_img]:h-full [&_img]:w-full [&_img]:object-cover [&_video]:h-full [&_video]:w-full [&_video]:object-cover">
                        {media}
                    </div>
                    <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,transparent_55%,rgb(0_0_0/0.62)_100%)]" />
                    {mediaLabel || mediaCaption ? (
                        <div className="absolute inset-x-0 bottom-0 flex flex-wrap items-end justify-between gap-3 border-t border-white/14 px-4 py-4 sm:px-6">
                            {mediaLabel ? (
                                <p className="font-mono text-[0.66rem] uppercase tracking-[0.16em] text-primary">
                                    {mediaLabel}
                                </p>
                            ) : <span />}
                            {mediaCaption ? (
                                <p className="max-w-sm text-pretty text-xs leading-relaxed text-white/62">
                                    {mediaCaption}
                                </p>
                            ) : null}
                        </div>
                    ) : null}
                </div>
            </div>
        </section>
    );
}

interface ServiceFunnelSectionProps {
    children: ReactNode;
    tone?: FunnelTone;
    className?: string;
    innerClassName?: string;
    id?: string;
}

export function ServiceFunnelSection({
    children,
    tone = 'paper',
    className,
    innerClassName,
    id,
}: ServiceFunnelSectionProps) {
    return (
        <section
            id={id}
            className={cn(
                'relative left-1/2 w-screen -translate-x-1/2',
                tone === 'paper' && 'bg-background text-foreground',
                tone === 'soft' && 'bg-secondary/55 text-foreground',
                tone === 'dark' && 'bg-[#07090b] text-white',
                className,
            )}
        >
            <div className={cn('mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:py-24', innerClassName)}>
                {children}
            </div>
        </section>
    );
}

interface ServiceFunnelHeadingProps {
    eyebrow?: string;
    title: string;
    description?: string;
    inverse?: boolean;
    className?: string;
}

export function ServiceFunnelHeading({
    eyebrow,
    title,
    description,
    inverse = false,
    className,
}: ServiceFunnelHeadingProps) {
    return (
        <div className={cn('max-w-3xl', className)}>
            {eyebrow ? (
                <p className="font-mono text-[0.7rem] font-medium uppercase tracking-[0.2em] text-primary">
                    {eyebrow}
                </p>
            ) : null}
            <h2 className={cn(
                'text-balance font-display text-4xl font-bold leading-[0.96] tracking-[-0.02em] sm:text-5xl lg:text-6xl',
                eyebrow && 'mt-4',
                inverse ? 'text-white' : 'text-foreground',
            )}>
                {title}
            </h2>
            {description ? (
                <p className={cn(
                    'mt-5 max-w-2xl text-pretty text-base leading-[1.6]',
                    inverse ? 'text-white/64' : 'text-muted-foreground',
                )}>
                    {description}
                </p>
            ) : null}
        </div>
    );
}

export type ServiceFunnelItem = {
    title: string;
    description: string;
};

interface ServiceFunnelDeliverablesProps {
    items: ServiceFunnelItem[];
    inverse?: boolean;
}

export function ServiceFunnelDeliverables({
    items,
    inverse = false,
}: ServiceFunnelDeliverablesProps) {
    return (
        <div className={cn('grid gap-x-8 gap-y-8', items.length > 3 ? 'md:grid-cols-2 xl:grid-cols-4' : 'md:grid-cols-3')}>
            {items.map((item, index) => (
                <article
                    key={`${item.title}-${index}`}
                    className={cn('border-t pt-5', inverse ? 'border-white/18' : 'border-border')}
                >
                    <p className="font-mono text-xs tabular-nums text-primary">
                        {String(index + 1).padStart(2, '0')}
                    </p>
                    <h3 className={cn(
                        'mt-5 text-balance font-display text-2xl font-bold leading-[1.05]',
                        inverse ? 'text-white' : 'text-foreground',
                    )}>
                        {item.title}
                    </h3>
                    <p className={cn(
                        'mt-3 text-pretty text-sm leading-[1.6]',
                        inverse ? 'text-white/60' : 'text-muted-foreground',
                    )}>
                        {item.description}
                    </p>
                </article>
            ))}
        </div>
    );
}

interface ServiceFunnelProcessProps {
    items: ServiceFunnelItem[];
    inverse?: boolean;
}

export function ServiceFunnelProcess({ items, inverse = false }: ServiceFunnelProcessProps) {
    return (
        <ol className={cn('grid gap-x-8 gap-y-8 border-t pt-6 md:grid-cols-3', inverse ? 'border-white/18' : 'border-border')}>
            {items.map((item, index) => (
                <li key={`${item.title}-${index}`} className="grid grid-cols-[2rem_1fr] gap-3">
                    <span className="font-mono text-xs tabular-nums text-primary" aria-hidden>
                        {String(index + 1).padStart(2, '0')}
                    </span>
                    <div>
                        <h3 className={cn(
                            'text-balance font-display text-2xl font-bold leading-[1.05]',
                            inverse ? 'text-white' : 'text-foreground',
                        )}>
                            {item.title}
                        </h3>
                        <p className={cn(
                            'mt-3 text-pretty text-sm leading-[1.6]',
                            inverse ? 'text-white/60' : 'text-muted-foreground',
                        )}>
                            {item.description}
                        </p>
                    </div>
                </li>
            ))}
        </ol>
    );
}

export type ServiceFunnelFaqItem = {
    question: string;
    answer: string;
};

interface ServiceFunnelFaqProps {
    items: ServiceFunnelFaqItem[];
    inverse?: boolean;
}

export function ServiceFunnelFaq({ items, inverse = false }: ServiceFunnelFaqProps) {
    return (
        <div className={cn('border-y', inverse ? 'divide-y divide-white/14 border-white/14' : 'divide-y divide-border border-border')}>
            {items.map((item) => (
                <details key={item.question} className="group">
                    <summary className={cn(
                        'flex min-h-16 cursor-pointer list-none items-center justify-between gap-6 py-4 font-display text-xl font-bold marker:hidden',
                        inverse ? 'text-white' : 'text-foreground',
                    )}>
                        <span className="text-balance">{item.question}</span>
                        <Plus
                            className="size-5 shrink-0 text-primary transition-transform duration-150 group-open:rotate-45 motion-reduce:transition-none"
                            aria-hidden
                        />
                    </summary>
                    <p className={cn(
                        'max-w-3xl pb-6 text-pretty text-sm leading-[1.65]',
                        inverse ? 'text-white/62' : 'text-muted-foreground',
                    )}>
                        {item.answer}
                    </p>
                </details>
            ))}
        </div>
    );
}

interface ServiceFunnelFinalCtaProps {
    eyebrow?: string;
    title: string;
    description: string;
    primaryAction: ReactNode;
    secondaryAction?: ReactNode;
}

export function ServiceFunnelFinalCta({
    eyebrow,
    title,
    description,
    primaryAction,
    secondaryAction,
}: ServiceFunnelFinalCtaProps) {
    return (
        <ServiceFunnelSection tone="dark" innerClassName="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <ServiceFunnelHeading
                eyebrow={eyebrow}
                title={title}
                description={description}
                inverse
            />
            <div className="flex flex-col gap-3 sm:flex-row lg:min-w-[460px]">
                {primaryAction}
                {secondaryAction}
            </div>
        </ServiceFunnelSection>
    );
}

interface ServiceWhatsAppButtonProps {
    href: string;
    label: string;
    onClick?: () => void;
    className?: string;
}

export function ServiceWhatsAppButton({
    href,
    label,
    onClick,
    className,
}: ServiceWhatsAppButtonProps) {
    return (
        <Button
            variant="default"
            size="xl"
            className={cn(serviceFunnelWhatsAppActionClass, className)}
            asChild
        >
            <a href={href} target="_blank" rel="noopener noreferrer" onClick={onClick}>
                <MessageCircle className="size-5" aria-hidden />
                {label}
            </a>
        </Button>
    );
}

interface ServiceTextLinkProps {
    href: string;
    label: string;
}

export function ServiceTextLink({ href, label }: ServiceTextLinkProps) {
    return (
        <a
            href={href}
            className="inline-flex min-h-11 items-center gap-2 font-semibold text-foreground underline decoration-primary/45 underline-offset-4 transition-[color,text-decoration-color] duration-150 hover:text-primary hover:decoration-primary motion-reduce:transition-none"
        >
            {label}
            <ArrowRight className="size-4" aria-hidden />
        </a>
    );
}
