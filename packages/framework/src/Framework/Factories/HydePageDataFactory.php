<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Hyde;
use Hyde\Markdown\Models\Markdown;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Navigation\NavigationData;

use function basename;
use function dirname;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function trim;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    use InteractsWithFrontMatter;

    /**
     * The front matter properties supported by this factory.
     */
    final public const SCHEMA = PageSchema::PAGE_SCHEMA;

    protected readonly string $title;
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
        $this->navigation = $this->makeNavigation();
    }

    /**
     * @return array{title: string, navigation: \Hyde\Framework\Features\Navigation\NavigationData|null}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'navigation' => $this->navigation,
        ];
    }

    protected function makeTitle(): string
    {
        return trim($this->findTitleForPage());
    }

    protected function makeNavigation(): NavigationData
    {
        return NavigationData::make((new NavigationDataFactory($this->pageData, $this->title))->toArray());
    }

    private function findTitleForPage(): string
    {
        return $this->matter('title')
            ?? $this->findTitleFromMarkdownHeadings()
            ?? $this->findTitleFromParentIdentifier()
            ?? Hyde::makeTitle(basename($this->identifier));
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

    private function findTitleFromParentIdentifier(): ?string
    {
        if (str_contains($this->identifier, '/') && str_ends_with($this->identifier, '/index')) {
            return Hyde::makeTitle(basename(dirname($this->identifier)));
        }

        return null;
    }
}
