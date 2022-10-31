<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
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
    private readonly string $routeKey;
    private readonly string $outputPath;
    private readonly string $identifier;
    private readonly string $pageClass;
    private readonly Markdown|false $markdown;
    private readonly FrontMatter $matter;

    public function __construct(private readonly CoreDataObject $pageData)
    {
        $this->matter = $this->pageData->matter;
        $this->markdown = $this->pageData->markdown;
        $this->pageClass = $this->pageData->pageClass;
        $this->identifier = $this->pageData->identifier;
        $this->outputPath = $this->pageData->outputPath;
        $this->routeKey = $this->pageData->routeKey;

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
        return NavigationData::make((new NavigationDataFactory($this->pageData, $this->title))->toArray());
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
