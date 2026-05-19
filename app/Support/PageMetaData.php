<?php

namespace App\Support;

class PageMetaData
{
    public function __construct(
        public string $title,
        public string $metaTitle,
        public string $description,
        public string $canonicalUrl,
        public string $ogType = 'website',
        public ?string $ogImage = null,
        public string $ogImageAlt = '',
        public string $keywords = '',
        public bool $noindex = false,
        public ?array $jsonLd = null,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'metaTitle' => $this->metaTitle,
            'description' => $this->description,
            'canonicalUrl' => $this->canonicalUrl,
            'ogType' => $this->ogType,
            'ogImage' => $this->ogImage,
            'ogImageAlt' => $this->ogImageAlt,
            'keywords' => $this->keywords,
            'noindex' => $this->noindex,
            'jsonLd' => $this->jsonLd,
        ];
    }
}
