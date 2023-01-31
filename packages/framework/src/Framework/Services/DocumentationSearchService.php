<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function strip_tags;

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
        self::needsDirectory(($outputDirectory));

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
        $this->needsDirectory(Hyde::path(str_replace('/search.json', '', self::$filePath)));

        file_put_contents(Hyde::path(self::$filePath), $this->searchIndex->toJson());

        return $this;
    }

    /**
     * There are a few ways we could go about this. The goal is to allow the user
     * to run a free-text search to find relevant documentation pages.
     *
     * The easiest way to do this is by adding the Markdown body to the search index.
     * But this is of course not ideal as it may take an incredible amount of space
     * for large documentation sites. The Hyde docs weight around 80kb of JSON.
     *
     * Another option is to assemble all the headings in a document and use that
     * for the search basis. A truncated version of the body could also be included.
     *
     * A third option which might be the most space efficient (besides from just
     * adding titles, which doesn't offer much help to the user since it is just
     * a filterable sidebar at that point), would be to search for keywords
     * in the document. This would however add complexity as well as extra
     * computing time.
     *
     * The current function is benchmarked on the official Hyde docs and takes
     * around 230ms to generate the search index for all page files.
     */
    protected function getSearchContentForDocument(DocumentationPage $page): string
    {
        // This is compiles the Markdown body into HTML, and then strips out all
        // HTML tags to get a plain text version of the body. This takes a long
        // site, but is the simplest implementation I've found so far.
        return $this->convertMarkdownToPlainText($page->markdown->body());
    }

    public function getDestinationForSlug(string $slug): string
    {
        if (config('site.pretty_urls', false) === true) {
            return $slug !== 'index' ? $slug : '';
        }

        return $slug.'.html';
    }

    /**
     * Regex based on https://github.com/stiang/remove-markdown, licensed under MIT.
     */
    protected function convertMarkdownToPlainText(string $markdown): string
    {
        // Remove any HTML tags
        $markdown = strip_tags($markdown);

        $patterns = [
            // Headers
            '/\n={2,}/' => "\n",
            // Fenced codeblocks
            '/~{3}.*\n/' => '',
            // Strikethrough
            '/~~/' => '',
            // Fenced codeblocks
            '/`{3}.*\n/' => '',
            // Fenced end tags
            '/`{3}/' => '',
            // Remove HTML tags
            '/<[^>]*>/' => '',
            // Remove setext-style headers
            '/^[=\-]{2,}\s*$/' => '',
            // Remove footnotes?
            '/\[\^.+?\](\: .*?$)?/' => '',
            '/\s{0,2}\[.*?\]: .*?$/' => '',
            // Remove images
            '/\!\[(.*?)\][\[\(].*?[\]\)]/' => '$1',
            // Remove inline links
            '/\[(.*?)\][\[\(].*?[\]\)]/' => '$1',
            // Remove blockquotes
            '/^\s{0,3}>\s?/' => '',
            // Remove reference-style links?
            '/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/' => '',
            // Remove atx-style headers
            '/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m' => '$1$2$3',
            // Remove emphasis
            '/([\*_]{1,3})(\S.*?\S{0,1})\1/' => '$2',
            // Remove code blocks
            '/(`{3,})(.*?)\1/m' => '$2',
            // Remove inline code
            '/`(.+?)`/' => '$1',
            // Replace two or more newlines with exactly two
            '/\n{2,}/' => "\n\n",
            // Remove horizontal rules
            '/^(-\s*?|\*\s*?|_\s*?){3,}\s*/m' => '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown) ?? $markdown;
        }

        $lines = explode("\n", $markdown);
        foreach ($lines as $line => $contents) {
            $newContents = ltrim($contents, '# ');
            // Remove tables (dividers)
            if (str_starts_with($newContents, '|--') && str_ends_with($newContents, '--|')) {
                $newContents = str_replace(['|', '-'], ['', ''], $newContents);
            }
            // Remove tables (cells)
            if (str_starts_with($newContents, '| ') && str_ends_with($newContents, '|')) {
                $newContents = str_replace(['| ', ' | ', ' |'], ['', '', ''], $newContents);
            }
            $lines[$line] = $newContents;
        }
        $markdown = implode("\n", $lines);

        return $markdown;
    }
}
