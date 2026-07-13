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
use Hyde\Framework\Exceptions\InvalidConfigurationException;

use function array_fill_keys;
use function array_filter;
use function array_values;
use function filled;
use function get_debug_type;
use function implode;
use function is_a;
use function is_string;
use function preg_replace;
use function sprintf;
use function trim;

/**
 * Generates the contents for the llms.txt file.
 *
 * The file lists the site's content for AI services and agents, using the site name as the
 * heading, the configured `hyde.llms.description` as the summary blockquote, and a section
 * of links for each page type registered in the `hyde.llms.sections` config, in that order.
 *
 * Pages can opt out of the listing with `llms: false` front matter, and the `abstract` front
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
     * @var array<class-string<\Hyde\Pages\Concerns\HydePage>, string>
     */
    protected const DEFAULT_SECTIONS = [
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
     * Group the site's routes into their configured sections, discarding empty ones.
     *
     * @return array<string, array<\Hyde\Support\Models\Route>>
     */
    protected function getSections(): array
    {
        $map = $this->getSectionMap();

        $sections = array_fill_keys(array_values($map), []);

        Routes::all()->each(function (Route $route) use ($map, &$sections): void {
            $page = $route->getPage();

            if (! $page->showInLlmsTxt() || $this->isErrorPage($page)) {
                return;
            }

            foreach ($map as $pageClass => $heading) {
                if ($page instanceof $pageClass) {
                    $sections[$heading][] = $route;

                    return;
                }
            }
        });

        return array_filter($sections);
    }

    /**
     * @return array<class-string<\Hyde\Pages\Concerns\HydePage>, string>
     *
     * @throws \Hyde\Framework\Exceptions\InvalidConfigurationException If an entry is not a page class mapped to a heading.
     */
    protected function getSectionMap(): array
    {
        /** @var array<class-string<\Hyde\Pages\Concerns\HydePage>, string> $sections */
        $sections = Config::getArray('hyde.llms.sections', static::DEFAULT_SECTIONS);

        foreach ($sections as $pageClass => $heading) {
            if (! is_string($pageClass) || ! is_a($pageClass, HydePage::class, true)) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid `hyde.llms.sections` entry at index [%s]: each key must be a page class extending %s.',
                    $pageClass, HydePage::class
                ), 'hyde', 'sections');
            }

            if (! is_string($heading) || ! filled($heading)) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid `hyde.llms.sections` entry at index [%s]: each section heading must be a non-empty string, %s given.',
                    $pageClass, get_debug_type($heading)
                ), 'hyde', 'sections');
            }
        }

        return $sections;
    }

    /**
     * Error pages are not content, so they are never listed in the file.
     */
    protected function isErrorPage(HydePage $page): bool
    {
        return $page->getIdentifier() === '404';
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
