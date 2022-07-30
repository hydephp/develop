<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\RssFeedService;
use Hyde\Framework\Services\SitemapService;

/**
 * @todo Move logic into service class to make it easier to test.
 *
 * @see \Hyde\Framework\Testing\Feature\Concerns\HasPageMetadataTest
 */
trait HasPageMetadata
{
    abstract public function htmlTitle(?string $title = null): string;

    public function getCanonicalUrl(): string
    {
        return Hyde::url(Hyde::pageLink($this->getCurrentPagePath().'.html'));
    }

    public function getDynamicMetadata(): array
    {
        $array = [];

        if ($this->canUseCanonicalUrl()) {
            $array[] = '<link rel="canonical" href="'.$this->getCanonicalUrl().'" />';
        }

        if ($this->canUseSitemapLink()) {
            $array[] = '<link rel="sitemap" type="application/xml" title="Sitemap" href="'.Hyde::url('sitemap.xml').'" />';
        }

        if ($this->canUseRssFeedlink()) {
            $array[] = $this->makeRssFeedLink();
        }

        if (isset($this->title)) {
            if ($this->hasTwitterTitleInConfig()) {
                $array[] = '<meta name="twitter:title" content="'.$this->htmlTitle().'" />';
            }
            if ($this->hasOpenGraphTitleInConfig()) {
                $array[] = '<meta property="og:title" content="'.$this->htmlTitle().'" />';
            }
        }

        if ($this instanceof MarkdownPost) {
            $array[] = "\n<!-- Blog Post Meta Tags -->";
            foreach ($this->getMetadata() as $name => $content) {
                $array[] = Meta::name($name, $content);
            }
            foreach ($this->getMetaProperties() as $property => $content) {
                $array[] = Meta::property($property, $content);
            }
        }

        return $array;
    }

    public function renderPageMetadata(): string
    {
        return Meta::render(
            withMergedData: $this->getDynamicMetadata()
        );
    }

    public function canUseCanonicalUrl(): bool
    {
        return Hyde::hasSiteUrl() && isset($this->slug);
    }

    public function canUseSitemapLink(): bool
    {
        return SitemapService::canGenerateSitemap();
    }

    public function canUseRssFeedLink(): bool
    {
        if (RssFeedService::canGenerateFeed() && isset($this->slug)) {
            if ($this instanceof MarkdownPost) {
                return true;
            }

            if (str_starts_with($this->getCurrentPagePath(), 'post')) {
                return true;
            }

            if ($this->getCurrentPagePath() === 'index') {
                return true;
            }
        }

        return false;
    }

    public function hasTwitterTitleInConfig(): bool
    {
        return str_contains(json_encode(config('hyde.meta', [])), 'twitter:title');
    }

    public function hasOpenGraphTitleInConfig(): bool
    {
        return str_contains(json_encode(config('hyde.meta', [])), 'og:title');
    }

    protected function makeRssFeedLink(): string
    {
        return sprintf('<link rel="alternate" type="application/rss+xml" title="%s" href="%s" />',
            RssFeedService::getDescription(),
            Hyde::url(RssFeedService::getDefaultOutputFilename())
        );
    }
}
