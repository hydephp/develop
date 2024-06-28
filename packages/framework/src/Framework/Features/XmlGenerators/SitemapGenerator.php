<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use Hyde\Hyde;
use SimpleXMLElement;
use Hyde\Facades\Config;
use Hyde\Pages\BladePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Facades\Filesystem;
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

    protected function getPriority(string $pageClass, string $identifier): string
    {
        // The default priority, unless we find a better match.
        $priority = 0.5;

        if (in_array($pageClass, [BladePage::class, MarkdownPage::class])) {
            $priority = 0.9;

            if ($identifier === 'index') {
                $priority = 1;
            }
        }

        if ($pageClass === DocumentationPage::class) {
            $priority = 0.9;
        }

        if ($pageClass === MarkdownPost::class) {
            $priority = 0.75;
        }

        if ($identifier === '404') {
            $priority = 0.5;
        }

        return (string) $priority;
    }

    protected function getChangeFrequency(string $pageClass, string $identifier): string
    {
        return 'daily';
    }

    protected function resolveRouteLink(Route $route): string
    {
        return Hyde::url($route->getOutputPath());
    }
}
