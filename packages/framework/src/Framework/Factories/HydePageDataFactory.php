<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Markdown\Models\Markdown;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Framework\Features\Navigation\NumericalPageOrderingHelper;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

use function is_a;
use function basename;
use function dirname;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function trim;

class HydePageDataFactory extends Concerns\PageDataFactory implements PageSchema
{
    /**
     * The front matter properties supported by this factory.
     */
    final public const SCHEMA = PageSchema::PAGE_SCHEMA;

    protected readonly string $title;
    protected readonly ?NavigationData $navigation;
    private readonly string $identifier;
    private readonly Markdown|false $markdown;
    private readonly FrontMatter $matter;

    public function __construct(private readonly CoreDataObject $pageData)
    {
        $this->matter = $this->pageData->matter;
        $this->markdown = $this->pageData->markdown;
        $this->identifier = $this->pageData->identifier;

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
        return $this->getMatter('title')
            ?? $this->findTitleFromMarkdownHeadings()
            ?? $this->findTitleFromParentIdentifier()
            ?? Hyde::makeTitle($this->getCleanBasename());
    }

    private function getCleanBasename(): string
    {
        $basename = basename($this->identifier);

        if (NumericalPageOrderingHelper::hasNumericalPrefix($basename)) {
            return NumericalPageOrderingHelper::splitNumericPrefix($basename)[1];
        }

        return $basename;
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
        // We strip any documentation version prefix, so that version index pages
        // do not get titled after the version directory they are stored in.
        $identifier = is_a($this->pageData->pageClass, DocumentationPage::class, true)
            ? DocumentationVersions::stripVersionPrefix($this->identifier)
            : $this->identifier;

        if (str_contains($identifier, '/') && str_ends_with($identifier, '/index')) {
            $parentBasename = basename(dirname($identifier));

            if (NumericalPageOrderingHelper::hasNumericalPrefix($parentBasename)) {
                $parentBasename = NumericalPageOrderingHelper::splitNumericPrefix($parentBasename)[1];
            }

            return Hyde::makeTitle($parentBasename);
        }

        return null;
    }

    protected function getMatter(string $key): ?string
    {
        /** @var ?string $value */
        $value = $this->matter->get($key);

        return $value;
    }
}
