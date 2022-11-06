<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Services;

use function config;
use function date;
use Exception;
use function extension_loaded;
use function filemtime;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Helpers\XML;
use Hyde\Support\Models\Route;
use function in_array;
use function microtime;
use function round;
use SimpleXMLElement;
use function throw_unless;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\SitemapServiceTest
 * @see https://www.sitemaps.org/protocol.html
 * @phpstan-consistent-constructor
 */
class SitemapService
{
    public SimpleXMLElement $xmlElement;
    protected float $timeStart;

    public static function generateSitemap(): string
    {
        return (new static)->generate()->getXML();
    }

    public function __construct()
    {
        throw_unless(extension_loaded('simplexml'),
            new Exception('The ext-simplexml extension is not installed, but is required to generate sitemaps.')
        );

        $this->timeStart = microtime(true);

        $this->xmlElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->xmlElement->addAttribute('generator', 'HydePHP '.Hyde::version());
    }

    public function generate(): static
    {
        Route::all()->each(function (Route $route): void {
            $this->addRoute($route);
        });

        return $this;
    }

    public function getXML(): string
    {
        $this->xmlElement->addAttribute('processing_time_ms', $this->getFormattedProcessingTime());

        return (string) $this->xmlElement->asXML();
    }

    public function addRoute(Route $route): void
    {
        $urlItem = $this->xmlElement->addChild('url');

        $urlItem->addChild('loc', XML::escape(Hyde::url($route->getOutputPath())));
        $urlItem->addChild('lastmod', XML::escape($this->getLastModDate($route->getSourcePath())));
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
