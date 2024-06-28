<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use Hyde\Hyde;
use SimpleXMLElement;
use Hyde\Facades\Config;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Facades\Filesystem;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Illuminate\Support\Carbon;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Facades\Routes;

use function in_array;
use function date;

/**
 * @see https://www.sitemaps.org/protocol.html
 */
class SitemapGenerator extends BaseXmlGenerator
{
    public function generate(): static
    {
        Routes::all()->each(function (Route $route): void {
            $this->addRoute($route);
        });

        return $this;
    }

    protected function constructBaseElement(): void
    {
        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->xmlElement->addAttribute('generator', 'HydePHP '.Hyde::version());
    }

    protected function addRoute(Route $route): void
    {
        $urlItem = $this->xmlElement->addChild('url');

        $this->addChild($urlItem, 'loc', $this->resolveRouteLink($route));
        $this->addChild($urlItem, 'lastmod', $this->getLastModDate($route->getSourcePath()));
        $this->addChild($urlItem, 'changefreq', $this->getChangeFrequency($route->getPageClass(), $route->getPage()->getIdentifier()));

        if (Config::getBool('hyde.sitemap.dynamic_priority', true)) {
            $this->addChild($urlItem, 'priority', $this->getPriority(
                $route->getPageClass(), $route->getPage()->getIdentifier()
            ));
        }
    }

    protected function getLastModDate(string $file): string
    {
        return date('c', @Filesystem::lastModified($file) ?: Carbon::now()->timestamp);
    }

    /** Intelligently find a good priority for the given page based on assumptions about the site structure. */
    protected function getPriority(string $pageClass, string $identifier): string
    {
        // The default priority, unless we find a better match.
        $priority = 0.5;

        if (in_array($pageClass, [BladePage::class, MarkdownPage::class, DocumentationPage::class])) {
            // These pages are usually high up in the site hierarchy, so they get a higher priority.
            $priority = 0.9;

            if ($identifier === 'index') {
                // The homepage is the most important page, so it gets the highest priority.
                $priority = 1;
            }
        }

        if (in_array($pageClass, [MarkdownPost::class, InMemoryPage::class, HtmlPage::class])) {
            // Posts are usually less important than normal pages as there may be many of them.
            // We group in InMemoryPages and HtmlPages here since we don't have much context for them.
            $priority = 0.75;
        }

        if ($identifier === '404') {
            // 404 pages are rarely important to index, so they get a lower priority.
            $priority = 0.25;
        }

        return (string) $priority;
    }

    /** Intelligently find a good change frequency for the given page based on assumptions about the site structure. */
    protected function getChangeFrequency(string $pageClass, string $identifier): string
    {
        $frequency = 'weekly';

        if (in_array($pageClass, [BladePage::class, MarkdownPage::class, DocumentationPage::class])) {
            $frequency = 'daily';
        }

        if ($identifier === '404') {
            $frequency = 'monthly';
        }

        return $frequency;
    }

    protected function resolveRouteLink(Route $route): string
    {
        return Hyde::url($route->getOutputPath());
    }
}
