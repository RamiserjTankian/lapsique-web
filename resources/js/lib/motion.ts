export const fadeUp = {
    hidden: { opacity: 0, y: 24 },
    visible: (i = 0) => ({
        opacity: 1,
        y: 0,
        transition: {
            delay: i * 0.08,
            duration: 0.5,
            ease: [0.22, 1, 0.36, 1] as const,
        },
    }),
};

export const fadeIn = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: { duration: 0.4, ease: [0.22, 1, 0.36, 1] as const },
    },
};

export const scaleIn = {
    hidden: { opacity: 0, scale: 0.96 },
    visible: {
        opacity: 1,
        scale: 1,
        transition: { duration: 0.35, ease: [0.22, 1, 0.36, 1] as const },
    },
};

export const staggerContainer = {
    hidden: {},
    visible: {
        transition: { staggerChildren: 0.06, delayChildren: 0.04 },
    },
};
