<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\Concerns\BaseMarkdownPage;
use function substr;
use function trim;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    use InteractsWithFrontMatter;

    /**
     * The front matter properties supported by this factory.
     */
    public const SCHEMA = PageSchema::PAGE_SCHEMA;

    private FrontMatter $matter;
    private Markdown|false $markdown;
    private string $identifier;
    private string $outputPath;

    protected readonly string $title;
    protected readonly ?string $canonicalUrl;
    protected readonly ?NavigationData $navigation;

    public function __construct(FrontMatter $matter, Markdown|false $markdown, string $identifier, string $outputPath)
    {
        $this->matter = $matter;
        $this->markdown = $markdown;
        $this->identifier = $identifier;
        $this->outputPath = $outputPath;

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
        // TODO: Implement makeNavigation() method.
    }

    protected function findTitleForPage(): string
    {
        return $this->matter('title')
            ?? $this->findTitleFromMarkdownHeadings()
            ?? Hyde::makeTitle($this->identifier);
    }

    protected function findTitleFromMarkdownHeadings(): ?string
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

    protected function getCanonicalUrl(): ?string
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
