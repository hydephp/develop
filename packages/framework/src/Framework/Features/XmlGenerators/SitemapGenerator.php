<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\Route;
use SimpleXMLElement;
use function config;
use function date;
use function filemtime;
use function in_array;
use function microtime;
use function round;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\SitemapServiceTest
 * @see https://www.sitemaps.org/protocol.html
 */
class SitemapGenerator extends BaseXmlGenerator
{
    protected float $timeStart;

    public function generate(): static
    {
        Route::all()->each(function (Route $route): void {
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
        $this->timeStart = microtime(true);

        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->xmlElement->addAttribute('generator', 'HydePHP ' . Hyde::version());
    }

    protected function addRoute(Route $route): void
    {
        $urlItem = $this->xmlElement->addChild('url');

        $urlItem->addChild('loc', $this->escape(Hyde::url($route->getOutputPath())));
        $urlItem->addChild('lastmod', $this->escape($this->getLastModDate($route->getSourcePath())));
        $urlItem->addChild('changefreq', 'daily');

        if (config('hyde.sitemap.dynamic_priority', true)) {
            $urlItem->addChild('priority', $this->getPriority(
                $route->getPageClass(), $route->getPage()->getIdentifier()
            ));
        }
    }

    protected function getLastModDate(string $file): string
    {
        return date('c', filemtime($file));
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

    protected function getFormattedProcessingTime(): string
    {
        return (string) round((microtime(true) - $this->timeStart) * 1000, 2);
    }
}
