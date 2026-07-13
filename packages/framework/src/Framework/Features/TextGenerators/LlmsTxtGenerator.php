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

use function array_fill_keys;
use function array_filter;
use function array_values;
use function filled;
use function filter_var;
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
 * Pages are listed when they are included in the sitemap, so a page excluded from the sitemap
 * is left out of this file as well. Use `llms: false` front matter to leave out a single page
 * regardless of its sitemap state, and `llms: true` to add one back. The `abstract` front
 * matter of a page, falling back to its `description`, is used as its link description.
 *
 * Note that llms.txt is an emerging standard which is still subject to change, so the format
 * of the generated file may change in future minor and patch releases to follow the spec.
 *
 * @see https://llmstxt.org/
 * @see \Hyde\Framework\Features\TextGenerators\LlmsTxtPage
 */
class LlmsTxtGenerator
{
    /**
     * The page types listed in the file, and the section heading each is listed under.
     *
     * Sections are written in the order declared here, and pages of a type not listed
     * here, like the virtual pages Hyde generates, are not added to the file.
     *
     * @var array<class-string<\Hyde\Pages\Concerns\HydePage>, string>
     */
    protected const SECTIONS = [
        HtmlPage::class => 'Pages',
        BladePage::class => 'Pages',
        MarkdownPage::class => 'Pages',
        DocumentationPage::class => 'Documentation',
        MarkdownPost::class => 'Blog Posts',
    ];

    public function generate(): string
    {
        return implode("\n", $this->getLines())."\n";
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

    /**
     * Group the site's listed routes into their sections, discarding the empty ones.
     *
     * @return array<string, array<\Hyde\Support\Models\Route>>
     */
    protected function getSections(): array
    {
        $sections = array_fill_keys(array_values(static::SECTIONS), []);

        Routes::all()->each(function (Route $route) use (&$sections): void {
            $page = $route->getPage();

            if (! $this->shouldListPage($page)) {
                return;
            }

            foreach (static::SECTIONS as $pageClass => $heading) {
                if ($page instanceof $pageClass) {
                    $sections[$heading][] = $route;

                    return;
                }
            }
        });

        return array_filter($sections);
    }

    /**
     * Pages follow their sitemap inclusion unless they set the `llms` front matter key.
     * Error pages are never listed, as they are not content.
     */
    protected function shouldListPage(HydePage $page): bool
    {
        if ($page->getIdentifier() === '404') {
            return false;
        }

        return filter_var($page->matter('llms', $page->showInSitemap()), FILTER_VALIDATE_BOOLEAN);
    }

    protected function makeLink(Route $route): string
    {
        $page = $route->getPage();

        $link = sprintf('- [%s](%s)', $page->title, Hyde::url($route->getOutputPath()));

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

    /**
     * Collapse whitespace so that multi-line front matter cannot break the line-based file format.
     */
    protected function normalizeText(string $text): string
    {
        return preg_replace('/\s+/', ' ', trim($text));
    }
}
