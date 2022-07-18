<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Route;
use SimpleXMLElement;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\SitemapServiceTest
 * @see https://www.sitemaps.org/protocol.html
 * @phpstan-consistent-constructor
 */
class SitemapService
{
    public SimpleXMLElement $xmlElement;
    protected float $time_start;

    public function __construct()
    {
        $this->time_start = microtime(true);

        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->xmlElement->addAttribute('generator', 'HydePHP '.Hyde::version());
    }

    public function generate(): self
    {
        Route::all()->each(function ($route) {
            $this->addRoute($route);
        });

        return $this;
    }

    public function getXML(): string
    {
        $this->xmlElement->addAttribute('processing_time_ms', (string) round((microtime(true) - $this->time_start) * 1000, 2));

        return $this->xmlElement->asXML();
    }

    public function addRoute(RouteContract $route): void
    {
        $urlItem = $this->xmlElement->addChild('url');
        $urlItem->addChild('loc', htmlentities(Hyde::uriPath($route->getLink())));
        $urlItem->addChild('lastmod', htmlentities($this->getLastModDate($route->getSourceFilePath())));
        $urlItem->addChild('changefreq', 'daily');
        if (config('hyde.sitemap.dynamic_priority', true)) {
            $urlItem->addChild('priority', $this->getPriority($route->getPageType(), $route->getSourceModel()->slug));
        }
    }

    protected function getLastModDate(string $file): string
    {
        return date('c', filemtime(
            $file
        ));
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

    public static function generateSitemap(): string
    {
        return (new static)->generate()->getXML();
    }

    public static function canGenerateSitemap(): bool
    {
        return (Hyde::uriPath() !== false)
            && config('site.generate_sitemap', true)
            && extension_loaded('simplexml');
    }
}
