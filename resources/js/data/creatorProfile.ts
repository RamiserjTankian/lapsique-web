export interface CreatorBrand {
    name: string;
}

/** Perfil del creador en la landing — edita marcas e Instagram aquí. */
export const CREATOR_PROFILE = {
    name: 'Ramiro',
    role: 'Video maker · Photographer',
    instagramUsername: 'ramiro.tech',
    instagramHandle: '@ramiro.tech',
    instagramUrl: 'https://www.instagram.com/ramiro.tech/',
    instagramBio: 'Video maker y photographer · producción para marcas, artistas y negocios.',
    /** Permalinks opcionales de posts para embeds adicionales (instagram.com/p/...). */
    featuredPostUrls: [] as string[],
    brands: [
        { name: 'Lapsique Media' },
        { name: 'Blue Point RS' },
        { name: 'Venues Riviera Maya' },
        { name: 'Marcas de lifestyle' },
        { name: 'Artistas y DJs' },
        { name: 'Negocios locales' },
    ] satisfies CreatorBrand[],
};
