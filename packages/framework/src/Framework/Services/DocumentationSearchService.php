<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Actions\ConvertsMarkdownToPlainText;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;

/**
 * @internal Generate a JSON file that can be used as a search index for documentation pages.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSearchServiceTest
 */
final class DocumentationSearchService
{
    use InteractsWithDirectories;

    public Collection $searchIndex;
    public static string $filePath = '_site/docs/search.json';

    public static function generate(): self
    {
        return (new self())->execute();
    }

    public static function generateSearchPage(): string
    {
        $outputDirectory = Hyde::sitePath(DocumentationPage::outputDirectory());
        self::needsDirectory($outputDirectory);

        file_put_contents(
            "$outputDirectory/search.html",
            view('hyde::pages.documentation-search')->render()
        );

        return $outputDirectory;
    }

    public function __construct()
    {
        $this->searchIndex = new Collection();
        self::$filePath = Hyde::pathToRelative(Hyde::sitePath(
            DocumentationPage::outputDirectory().'/search.json'
        ));
    }

    public function execute(): self
    {
        return $this->run()->save();
    }

    public function run(): self
    {
        /** @var \Hyde\Pages\DocumentationPage $page */
        foreach (DocumentationPage::all() as $page) {
            if (! in_array($page->identifier, config('docs.exclude_from_search', []))) {
                $this->searchIndex->push(
                    $this->generatePageEntry($page)
                );
            }
        }

        return $this;
    }

    /**
     * @return array{slug: string, title: string, content: string, destination: string}
     */
    public function generatePageEntry(DocumentationPage $page): array
    {
        return [
            'slug' => basename($page->identifier),
            'title' => $page->title,
            'content' => trim($this->getSearchContentForDocument($page)),
            'destination' => $this->getDestinationForSlug(basename($page->identifier)),
        ];
    }

    protected function save(): self
    {
        $this->needsDirectory(Hyde::path(dirname(self::$filePath)));

        file_put_contents(Hyde::path(self::$filePath), $this->searchIndex->toJson());

        return $this;
    }

    protected function getSearchContentForDocument(DocumentationPage $page): string
    {
        return (new ConvertsMarkdownToPlainText($page->markdown->body()))->execute();
    }

    public function getDestinationForSlug(string $slug): string
    {
        if (config('site.pretty_urls', false) === true) {
            return $slug !== 'index' ? $slug : '';
        }

        return $slug.'.html';
    }
}
