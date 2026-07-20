<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\TextGenerators;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;

use function addcslashes;
use function array_fill_keys;
use function array_filter;
use function array_values;
use function filled;
use function implode;
use function is_string;
use function preg_replace;
use function sprintf;
use function trim;

/**
 * Generates the contents for the llms.txt file.
 *
 * The file lists the site's content for AI services and agents, using the site name as the
 * heading, the configured `hyde.llms.description` as the summary blockquote, and the site's
 * pages as Markdown links, grouped into a section for each page type.
 *
 * A page is listed when it is included in the sitemap, as both files are machine-readable
 * indexes of the site's published pages, meaning `sitemap: false` front matter leaves a page
 * out of both. The `abstract` front matter of a page, falling back to its `description`, is
 * used as its link description.
 *
 * Sections are written in the order they are declared in, and the pages within each section
 * are written in route order, which is the same order the sitemap lists and the build compiles
 * them in, so numerically prefixed source files keep their intended reading order.
 *
 * Note that llms.txt is an emerging standard which is still subject to change, so the format
 * of the generated file may change in future minor and patch releases to follow the spec.
 *
 * @see https://llmstxt.org/
 */
class LlmsTxtGenerator
{
    public function generate(): string
    {
        return implode("\n", $this->getLines())."\n";
    }

    /**
     * The page types listed in the file, and the section heading each is listed under.
     *
     * Pages of a type not listed here, like the virtual pages Hyde generates, are not
     * added to the file. Override this to group your own page types into sections.
     *
     * @return array<class-string<\Hyde\Pages\Concerns\HydePage>, string>
     */
    protected function sections(): array
    {
        return [
            HtmlPage::class => 'Pages',
            BladePage::class => 'Pages',
            MarkdownPage::class => 'Pages',
            DocumentationPage::class => 'Documentation',
            MarkdownPost::class => 'Blog Posts',
        ];
    }

    /** @return array<string> */
    protected function getLines(): array
    {
        $lines = ['# '.Config::getString('hyde.name', 'HydePHP')];

        $description = Config::getNullableString('hyde.llms.description');

        if (filled($description)) {
            $lines[] = '';
            $lines[] = '> '.$this->normalizeText($description);
        }

        foreach ($this->getSections() as $heading => $routes) {
            $lines[] = '';
            $lines[] = "## $heading";
            $lines[] = '';

            foreach ($routes as $route) {
                $lines[] = $this->makeLink($route);
            }
        }

        return $lines;
    }

    protected function getSections(): array
    {
        $sections = $this->sections();

        $grouped = array_fill_keys(array_values($sections), []);

        Routes::all()->each(function (Route $route) use ($sections, &$grouped): void {
            $page = $route->getPage();

            if (! $this->shouldListPage($page)) {
                return;
            }

            foreach ($sections as $pageClass => $heading) {
                if ($page instanceof $pageClass) {
                    $grouped[$heading][] = $route;

                    return;
                }
            }
        });

        return array_filter($grouped);
    }

    protected function shouldListPage(HydePage $page): bool
    {
        return $page->showInSitemap() && $page->getIdentifier() !== '404';
    }

    protected function makeLink(Route $route): string
    {
        $page = $route->getPage();

        $link = sprintf('- [%s](%s)', $this->escapeLinkLabel($page->title), Hyde::url($route->getOutputPath()));

        $description = $this->getPageDescription($page);

        return $description === null ? $link : "$link: $description";
    }

    protected function getPageDescription(HydePage $page): ?string
    {
        $description = $page->matter('abstract') ?? $page->matter('description');

        if (! is_string($description) || ! filled($description)) {
            return null;
        }

        return $this->normalizeText($description);
    }

    protected function escapeLinkLabel(string $label): string
    {
        return addcslashes($this->normalizeText($label), '[]\\');
    }

    protected function normalizeText(string $text): string
    {
        return preg_replace('/\s+/', ' ', trim($text));
    }
}
