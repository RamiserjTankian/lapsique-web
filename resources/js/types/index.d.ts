export interface BookingSlot {
    id: number;
    date: string;
    time_label: string;
    time_value: string;
}

export interface VideoDjSummary {
    id: number;
    name: string;
    slug: string;
    avatar_url: string | null;
    bio: string | null;
}

export interface VideoItem {
    id: number;
    title: string;
    slug: string;
    thumbnail_url: string | null;
    youtube_url: string | null;
    youtube_id?: string | null;
    tags: string[];
    is_featured: boolean;
    description?: string | null;
    location?: string | null;
    published_at?: string | null;
    djs?: VideoDjSummary[];
}

export interface Paginated<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
}

export interface HeroProofVideoData {
    title: string | null;
    media_type: 'youtube' | 'video' | 'image';
    embed_url: string | null;
    playback_url: string | null;
    poster_url: string | null;
}

export interface HeroBackgroundImageData {
    url: string;
    alt: string | null;
}

export interface PortfolioItemData {
    id: number;
    title: string | null;
    slug: string | null;
    type: string;
    source: string;
    caption: string | null;
    tags: string[];
    asset_url: string | null;
    poster_url: string | null;
    playback_url: string | null;
    embed_url: string | null;
    youtube_id: string | null;
    youtube_url: string | null;
    media_type: 'image' | 'video' | 'youtube';
    is_featured: boolean;
    orientation?: string | null;
}

export interface DjGalleryImage {
    id: number;
    url: string;
    thumb_url: string;
}

export interface DjTechnicalRiderItem {
    label: string;
    value?: string | null;
}

export interface DjItem {
    id: number;
    name: string;
    slug: string;
    avatar_url: string | null;
    cover_url?: string | null;
    bio?: string | null;
    instagram_handle?: string | null;
    youtube_url?: string | null;
    soundcloud_url?: string | null;
    website_url?: string | null;
    technical_rider?: DjTechnicalRiderItem[];
    gallery?: DjGalleryImage[];
    is_featured: boolean;
    is_highlighted: boolean;
    tags?: string[];
}

export interface EventItem {
    id: number;
    title: string;
    slug: string;
    starts_at: string | null;
    cover_url: string | null;
    location_name: string | null;
}

export interface ContentBookingDeliverableLink {
    id: number;
    label: string;
    url: string;
    created_at?: string | null;
}

export interface ContentBookingData {
    public_id: string;
    customer_id?: number | null;
    status: string;
    status_label?: string;
    client_name: string;
    client_email: string;
    client_phone: string;
    amount: number;
    formatted_amount?: string;
    currency: string;
    payment_provider: string | null;
    service_type: 'content_session' | 'dj_set';
    service_name: string;
    service_short_name: string;
    service_description: string;
    paid_at?: string | null;
    slot_summary?: string;
    deliverables_ready_at?: string | null;
    deliverables_drive_url?: string | null;
    deliverable_links?: ContentBookingDeliverableLink[];
    was_rescheduled?: boolean;
    is_test_booking?: boolean;
    slot: {
        date: string;
        time_label: string;
    } | null;
}

export interface LandingVideoEntry {
    src: string;
    poster: string | null;
    title: string | null;
}

export interface LandingVideosProps {
    hero: LandingVideoEntry | null;
    offer: LandingVideoEntry | null;
    proof: LandingVideoEntry | null;
    pauta: LandingVideoEntry | null;
    creative: LandingVideoEntry[];
    equipment: LandingVideoEntry[];
    aftermovies: LandingVideoEntry[];
    floats: LandingVideoEntry[];
    package: LandingVideoEntry | null;
    gear: LandingVideoEntry | null;
}

export interface ReelLibraryEntry {
    id: string;
    src: string;
    poster?: string | null;
    title?: string;
}

export interface ReelLibraryStats {
    totalSourceVideos: number;
    uniqueVideos: number;
}

export interface SeoMeta {
    title: string;
    metaTitle: string;
    description: string;
    canonicalUrl: string;
    ogType: string;
    ogImage: string | null;
    ogImageAlt: string;
    keywords: string;
    noindex: boolean;
    jsonLd?: Record<string, unknown> | null;
}

export interface SiteProps {
    name: string;
    bookingPrice: number;
    bookingTitle: string | null;
    bookingSubtitle: string | null;
    bookingTeamName: string | null;
    bookingTeamBio: string | null;
    whatsapp: string;
    instagramUrl: string;
    youtubeHandle: string;
    studioLocation: string | null;
}

export interface SharedPageProps {
    site: SiteProps;
    booking: {
        skipPayment: boolean;
    };
    payments: {
        stripeConfigured: boolean;
        mercadopagoConfigured: boolean;
    };
    flash: {
        success?: string;
        error?: string;
        status?: string;
    };
    customer: { id: number; name: string; email: string } | null;
    locale: string;
    availableLocales: string[];
    translations: Record<string, Record<string, unknown>>;
    seo: SeoMeta;
    ziggy: import('ziggy-js').Config & { location: string };
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T &
    SharedPageProps;
