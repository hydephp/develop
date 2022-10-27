<?php

declare(strict_types=1);

/** @noinspection PhpComposerExtensionStubsInspection */

namespace Hyde\Framework\Services;

use Exception;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Routing\Route;
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
        if (! extension_loaded('simplexml') || config('testing.mock_disabled_extensions', false) === true) {
            throw new Exception('The ext-simplexml extension is not installed, but is required to generate RSS feeds.');
        }

        $this->time_start = microtime(true);

        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->xmlElement->addAttribute('generator', 'HydePHP '.Hyde::version());
    }

    public function generate(): static
    {
        \Hyde\Facades\Route::all()->each(function ($route) {
            $this->addRoute($route);
        });

        return $this;
    }

    public function getXML(): string
    {
        $this->xmlElement->addAttribute('processing_time_ms', (string) round((microtime(true) - $this->time_start) * 1000, 2));

        return (string) $this->xmlElement->asXML();
    }

    public function addRoute(Route $route): void
    {
        $urlItem = $this->xmlElement->addChild('url');
        $urlItem->addChild('loc', htmlentities($route->getQualifiedUrl()));
        $urlItem->addChild('lastmod', htmlentities($this->getLastModDate($route->getSourcePath())));
        $urlItem->addChild('changefreq', 'daily');
        if (config('hyde.sitemap.dynamic_priority', true)) {
            $urlItem->addChild('priority', $this->getPriority($route->getPageClass(), $route->getPage()->getIdentifier()));
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
}
