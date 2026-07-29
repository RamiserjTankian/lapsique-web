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
    const ogImageType = imageMimeType(ogImage);
    const siteName = site.name;
    const headKey = (key: string) => ({ 'head-key': key });

    return (
        <Head title={seo.metaTitle || seo.title}>
            <meta {...headKey('meta-title')} name="title" content={seo.metaTitle} />
            <meta {...headKey('description')} name="description" content={seo.description} />
            <meta {...headKey('robots')} name="robots" content={seo.noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'} />
            <meta {...headKey('author')} name="author" content={siteName} />
            <meta {...headKey('application-name')} name="application-name" content={siteName} />
            {seo.keywords ? <meta {...headKey('keywords')} name="keywords" content={seo.keywords} /> : null}
            {ogImage ? <meta {...headKey('thumbnail')} name="thumbnail" content={ogImage} /> : null}
            <meta {...headKey('og-type')} property="og:type" content={seo.ogType} />
            <meta {...headKey('og-url')} property="og:url" content={seo.canonicalUrl} />
            <meta {...headKey('og-title')} property="og:title" content={seo.metaTitle} />
            <meta {...headKey('og-description')} property="og:description" content={seo.description} />
            {ogImage ? <meta {...headKey('og-image')} property="og:image" content={ogImage} /> : null}
            {ogImage ? <meta {...headKey('og-image-url')} property="og:image:url" content={ogImage} /> : null}
            {ogImage ? <meta {...headKey('og-image-secure-url')} property="og:image:secure_url" content={ogImage} /> : null}
            {ogImageType ? <meta {...headKey('og-image-type')} property="og:image:type" content={ogImageType} /> : null}
            {seo.ogImageAlt ? <meta {...headKey('og-image-alt')} property="og:image:alt" content={seo.ogImageAlt} /> : null}
            <meta {...headKey('og-site-name')} property="og:site_name" content={siteName} />
            <meta {...headKey('og-locale')} property="og:locale" content={ogLocale} />
            <meta {...headKey('og-locale-alternate')} property="og:locale:alternate" content={locale === 'en' ? 'es_MX' : 'en_US'} />
            <meta {...headKey('twitter-card')} name="twitter:card" content="summary_large_image" />
            <meta {...headKey('twitter-url')} name="twitter:url" content={seo.canonicalUrl} />
            <meta {...headKey('twitter-title')} name="twitter:title" content={seo.metaTitle} />
            <meta {...headKey('twitter-description')} name="twitter:description" content={seo.description} />
            {ogImage ? <meta {...headKey('twitter-image')} name="twitter:image" content={ogImage} /> : null}
            {seo.ogImageAlt ? <meta {...headKey('twitter-image-alt')} name="twitter:image:alt" content={seo.ogImageAlt} /> : null}
            <link {...headKey('canonical')} rel="canonical" href={seo.canonicalUrl} />
            {ogImage ? <link {...headKey('image-src')} rel="image_src" href={ogImage} /> : null}
            {seo.jsonLd ? (
                <script {...headKey('json-ld')} type="application/ld+json">
                    {JSON.stringify(seo.jsonLd)}
                </script>
            ) : null}
        </Head>
    );
}

function imageMimeType(url: string): string | null {
    const extension = url.split(/[?#]/, 1)[0]?.split('.').pop()?.toLowerCase();

    if (extension === 'webp') return 'image/webp';
    if (extension === 'png') return 'image/png';
    if (extension === 'jpg' || extension === 'jpeg') return 'image/jpeg';

    return null;
}
