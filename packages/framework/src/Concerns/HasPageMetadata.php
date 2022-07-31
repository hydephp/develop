<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\RssFeedService;

/**
 * @todo Move logic into service class to make it easier to test.
 *
 * @see \Hyde\Framework\Testing\Feature\Concerns\HasPageMetadataTest
 */
trait HasPageMetadata
{
    abstract public function htmlTitle(?string $title = null): string;

    abstract public function getRoute(): RouteContract;

    public function getCanonicalUrl(): string
    {
        return $this->getRoute()->getQualifiedUrl();
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string>
     */
    public function getDynamicMetadata(): array
    {
        $array = [];

        if ($this->canUseCanonicalUrl()) {
            $array[] = '<link rel="canonical" href="'.$this->getCanonicalUrl().'" />';
        }

        if (Features::sitemap()) {
            $array[] = '<link rel="sitemap" type="application/xml" title="Sitemap" href="'.Hyde::url('sitemap.xml').'" />';
        }

        if (Features::canGenerateFeed()) {
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
        return sprintf(
            '<link rel="alternate" type="application/rss+xml" title="%s" href="%s" />',
            RssFeedService::getDescription(),
            Hyde::url(RssFeedService::getDefaultOutputFilename())
        );
    }
}
