import { Head, usePage } from '@inertiajs/react';
import type { PageProps, SeoMeta } from '@/types';

interface SeoHeadProps {
    /** Override shared SEO (e.g. when page title differs from route default). */
    seo?: Partial<SeoMeta>;
}

export function SeoHead({ seo: override }: SeoHeadProps) {
    const { seo: shared, locale, site } = usePage<PageProps>().props;
    const ogLocale = locale === 'en' ? 'en_US' : 'es_MX';
    if (!shared && !override) {
        return null;
    }

    const seo: SeoMeta = { ...shared!, ...override };
    const ogImage = seo.ogImage ?? '';
    const siteName = site.name;

    return (
        <Head title={seo.title}>
            <meta name="description" content={seo.description} />
            <meta name="robots" content={seo.noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'} />
            <meta name="author" content="Lapsique Media" />
            <meta name="theme-color" content="#11100e" />
            {seo.keywords ? <meta name="keywords" content={seo.keywords} /> : null}
            {ogImage ? <meta name="thumbnail" content={ogImage} /> : null}
            <meta property="og:type" content={seo.ogType} />
            <meta property="og:url" content={seo.canonicalUrl} />
            <meta property="og:title" content={seo.metaTitle} />
            <meta property="og:description" content={seo.description} />
            {ogImage ? <meta property="og:image" content={ogImage} /> : null}
            {ogImage ? <meta property="og:image:secure_url" content={ogImage} /> : null}
            {ogImage ? <meta property="og:image:width" content="1200" /> : null}
            {ogImage ? <meta property="og:image:height" content="630" /> : null}
            {ogImage ? <meta property="og:image:type" content="image/jpeg" /> : null}
            {seo.ogImageAlt ? <meta property="og:image:alt" content={seo.ogImageAlt} /> : null}
            <meta property="og:site_name" content={siteName} />
            <meta property="og:locale" content={ogLocale} />
            <meta property="og:locale:alternate" content={locale === 'en' ? 'es_MX' : 'en_US'} />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:url" content={seo.canonicalUrl} />
            <meta name="twitter:title" content={seo.metaTitle} />
            <meta name="twitter:description" content={seo.description} />
            {ogImage ? <meta name="twitter:image" content={ogImage} /> : null}
            {seo.ogImageAlt ? <meta name="twitter:image:alt" content={seo.ogImageAlt} /> : null}
            <link rel="canonical" href={seo.canonicalUrl} />
            {ogImage ? <link rel="image_src" href={ogImage} /> : null}
            {seo.jsonLd ? (
                <script type="application/ld+json">
                    {JSON.stringify(seo.jsonLd)}
                </script>
            ) : null}
        </Head>
    );
}
