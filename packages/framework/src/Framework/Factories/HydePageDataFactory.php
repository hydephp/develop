<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\Concerns\HydePage;
use function substr;
use function trim;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    use InteractsWithFrontMatter;

    /**
     * The front matter properties supported by this factory.
     */
    public const SCHEMA = PageSchema::PAGE_SCHEMA;

    protected readonly string $title;
    protected readonly ?string $canonicalUrl;
    protected readonly ?NavigationData $navigation;

    public function __construct(
        private readonly FrontMatter $matter,
        private readonly Markdown|false $markdown,
        private readonly string $pageClass,
        private readonly string $identifier,
        private readonly string $outputPath,
        private readonly string $routeKey,
    ) {
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
        return trim($this->findTitleForPage());
    }

    protected function makeCanonicalUrl(): ?string
    {
        return $this->getCanonicalUrl();
    }

    protected function makeNavigation(): ?NavigationData
    {
        return NavigationDataFactory::make($this->matter, $this->identifier, $this->pageClass, $this->routeKey, $this->title);
    }

    private function findTitleForPage(): string
    {
        return $this->matter('title')
            ?? $this->findTitleFromMarkdownHeadings()
            ?? Hyde::makeTitle($this->identifier);
    }

    private function findTitleFromMarkdownHeadings(): ?string
    {
        if ($this->markdown !== false) {
            foreach ($this->markdown->toArray() as $line) {
                if (str_starts_with($line, '# ')) {
                    return trim(substr($line, 2), ' ');
                }
            }
        }

        return null;
    }

    private function getCanonicalUrl(): ?string
    {
        if (! empty($this->matter('canonicalUrl'))) {
            return $this->matter('canonicalUrl');
        }

        if (Hyde::hasSiteUrl() && ! empty($this->identifier)) {
            return Hyde::url($this->outputPath);
        }

        return null;
    }
}
