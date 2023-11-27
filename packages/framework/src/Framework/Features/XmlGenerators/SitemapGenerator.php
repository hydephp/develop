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
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Concerns\TracksExecutionTime;

use function filemtime;
use function in_array;
use function date;
use function time;

/**
 * @see https://www.sitemaps.org/protocol.html
 */
class SitemapGenerator extends BaseXmlGenerator
{
    use TracksExecutionTime;

    public function generate(): static
    {
        Routes::all()->each(function (Route $route): void {
            $this->addRoute($route);
        });

        return $this;
    }

    public function getXml(): string
    {
        $this->xmlElement->addAttribute('processing_time_ms', $this->getFormattedProcessingTime());

        return parent::getXml();
    }

    protected function constructBaseElement(): void
    {
        $this->startClock();

        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->xmlElement->addAttribute('generator', 'HydePHP '.Hyde::version());
    }

    protected function addRoute(Route $route): void
    {
        $urlItem = $this->xmlElement->addChild('url');

        $this->addChild($urlItem, 'loc', $this->resolveRouteLink($route));
        $this->addChild($urlItem, 'lastmod', $this->getLastModDate($route->getSourcePath()));
        $this->addChild($urlItem, 'changefreq', 'daily');

        if (Config::getBool('hyde.sitemap.dynamic_priority', true)) {
            $this->addChild($urlItem, 'priority', $this->getPriority(
                $route->getPageClass(), $route->getPage()->getIdentifier()
            ));
        }
    }

    protected function getLastModDate(string $file): string
    {
        return date('c', @filemtime($file) ?: time());
    }

    protected function getPriority(string $pageClass, string $slug): string
    {
        $priority = 0.5;

        if (in_array($pageClass, [BladePage::class, MarkdownPage::class])) {
            $priority = 0.9;
            if ($slug === 'index') {
                $priority = 1;
            }
            if ($slug === '404') {
                $priority = 0.5;
            }
        }

        if ($pageClass === DocumentationPage::class) {
            $priority = 0.9;
        }

        if ($pageClass === MarkdownPost::class) {
            $priority = 0.75;
        }

        return (string) $priority;
    }

    /** @return numeric-string */
    protected function getFormattedProcessingTime(): string
    {
        return (string) $this->getExecutionTimeInMs();
    }

    protected function resolveRouteLink(Route $route): string
    {
        return Hyde::url($route->getOutputPath());
    }
}
