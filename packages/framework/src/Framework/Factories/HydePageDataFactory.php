<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    /**
     * The front matter properties supported by this factory.
     */
    public const SCHEMA = PageSchema::PAGE_SCHEMA;

    protected readonly string $title;
    protected readonly ?string $canonicalUrl;
    protected readonly ?NavigationData $navigation;

    public function __construct()
    {
        $this->title = $this->makeTitle();
        $this->canonicalUrl = $this->makeCanonicalUrl();
        $this->navigation = $this->makeNavigation();
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
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
