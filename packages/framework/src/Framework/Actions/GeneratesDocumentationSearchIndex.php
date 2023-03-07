<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use function basename;
use function in_array;
use function trim;

/**
 * @internal Generate a JSON file that can be used as a search index for documentation pages.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSearchServiceTest
 */
class GeneratesDocumentationSearchIndex
{
    use InteractsWithDirectories;

    protected Collection $searchIndex;
    protected string $filePath;

    /**
     * Generate the search index and save it to disk.
     *
     * @return string The path to the generated file.
     */
    public static function generate(): string
    {
        $service = new static();
        $service->run();
        $service->save();

        return $service->filePath;
    }

    protected function __construct()
    {
        $this->searchIndex = new Collection();
        $this->filePath = $this->getFilePath();
    }

    protected function run(): void
    {
        DocumentationPage::all()->each(function (DocumentationPage $page): void {
            if (! in_array($page->identifier, Config::getArray('docs.exclude_from_search', []))) {
                $this->searchIndex->push($this->generatePageEntry($page));
            }
        });
    }

    /**
     * @return array{slug: string, title: string, content: string, destination: string}
     */
    protected function generatePageEntry(DocumentationPage $page): array
    {
        return [
            'slug' => basename($page->identifier),
            'title' => $page->title,
            'content' => trim($this->getSearchContentForDocument($page)),
            'destination' => $this->formatDestination(basename($page->identifier)),
        ];
    }

    protected function save(): void
    {
        $this->needsParentDirectory($this->filePath);

        Filesystem::putContents($this->filePath, $this->searchIndex->toJson());
    }

    protected function getSearchContentForDocument(DocumentationPage $page): string
    {
        return (new ConvertsMarkdownToPlainText($page->markdown->body()))->execute();
    }

    protected function formatDestination(string $slug): string
    {
        if (Config::getBool('hyde.pretty_urls', false) === true) {
            return $slug === 'index' ? '' : $slug;
        }

        return "$slug.html";
    }

    protected function getFilePath(): string
    {
        return Hyde::sitePath(DocumentationPage::outputDirectory().'/search.json');
    }
}
