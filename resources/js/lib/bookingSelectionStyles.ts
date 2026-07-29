/** Golden selected styling for booking modal; green badge marks the active choice. */

export const bookingOptionSelectedClasses =
    'border-primary bg-primary text-primary-foreground shadow-[0_0_40px_oklch(0.78_0.14_75/0.32)] ring-2 ring-primary/50 ring-offset-2 ring-offset-background';

export const bookingOptionSelectedDayClasses = 'text-primary-foreground/80';
export const bookingOptionSelectedMonthClasses = 'text-primary-foreground/85';

export const bookingOptionSelectedBadgeClasses =
    'inline-flex shrink-0 items-center gap-1 rounded-full border border-emerald-600/35 bg-emerald-600 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.08em] text-white shadow-[0_4px_14px_rgb(16_185_129/0.28)] sm:px-2 sm:text-[10px]';

export const bookingOptionSuggestedClasses =
    'border-primary/55 border-dashed bg-primary/12 text-foreground hover:border-primary hover:bg-primary/18';

export const bookingOptionSuggestedLabelClasses = 'text-primary';
export const bookingOptionSuggestedBadgeClasses =
    'border-primary/35 bg-primary/15 text-primary';

export const bookingSlotSelectedClasses =
    'border-primary bg-primary text-primary-foreground shadow-[0_0_24px_oklch(0.78_0.14_75/0.28)] ring-2 ring-primary ring-offset-2 ring-offset-background hover:border-primary hover:bg-primary hover:text-primary-foreground';

export const bookingStepActiveSectionClasses = 'border-primary/50 ring-2 ring-primary';
export const bookingStepCompleteSectionClasses = 'border-primary/40';

export const bookingWizardCompleteClasses = 'border-primary/45 bg-primary/15';
export const bookingWizardActiveClasses = 'border-primary ring-2 ring-primary bg-primary/10';
export const bookingWizardCompleteTextClasses = 'text-primary';

export const bookingPaymentSelectedClasses = 'border-primary/40 bg-primary/5 ring-1 ring-primary/20';

export const bookingCheckboxSelectedClasses =
    'data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground';

/** Primary confirm / pay CTA inside the booking checkout form. */
export const bookingConfirmButtonClasses =
    'h-12 w-full rounded-xl border-0 bg-primary px-6 text-base font-bold tracking-[0.02em] text-primary-foreground shadow-[0_0_32px_oklch(0.78_0.14_75/0.35)] transition-[background-color,color,box-shadow,transform,opacity] duration-150 hover:bg-primary/90 hover:shadow-[0_0_48px_oklch(0.78_0.14_75/0.5)] active:scale-[0.96] motion-reduce:transition-none motion-reduce:active:scale-100 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2 focus-visible:ring-offset-background';

export const bookingCheckoutPanelClasses =
    'rounded-2xl border border-border/70 bg-muted/30';

export const bookingCheckoutLinkClasses =
    'font-semibold text-primary underline-offset-4 hover:text-primary/80 hover:underline';

export const bookingCheckoutPriceClasses = 'font-mono-tabular text-2xl font-bold text-primary md:text-3xl';
