<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    /**
     * The front matter properties supported by this factory.
     */
    public const SCHEMA = PageSchema::PAGE_SCHEMA;

    private FrontMatter $matter;
    private Markdown|false $markdown;

    protected readonly string $title;
    protected readonly ?string $canonicalUrl;
    protected readonly ?NavigationData $navigation;

    public function __construct(FrontMatter $matter, Markdown|false $markdown)
    {
        $this->matter = $matter;
        $this->markdown = $markdown;

        $this->title = $this->makeTitle();
        $this->canonicalUrl = $this->makeCanonicalUrl();
        $this->navigation = $this->makeNavigation();
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'canonicalUrl' => $this->canonicalUrl,
            'navigation' => $this->navigation,
        ];
    }

    protected function makeTitle(): string
    {
        // TODO: Implement makeTitle() method.
    }

    protected function makeCanonicalUrl(): ?string
    {
        // TODO: Implement makeCanonicalUrl() method.
    }

    protected function makeNavigation(): ?NavigationData
    {
        // TODO: Implement makeNavigation() method.
    }
}
