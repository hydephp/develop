<?php
declare(strict_types=1);

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\DiscoveryService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * Allows features to be enabled and disabled in a simple object-oriented manner.
 *
 * @see \Hyde\Framework\Testing\Feature\ConfigurableFeaturesTest
 *
 * Based entirely on Laravel Jetstream (License MIT)
 * @see https://jetstream.laravel.com/
 */
class Features implements Arrayable, \JsonSerializable
{
    use JsonSerializesArrayable;

    /**
     * Determine if the given specified is enabled.
     *
     * @param  string  $feature
     * @return bool
     */
    public static function enabled(string $feature): bool
    {
        return in_array($feature, config('hyde.features', [
            // Page Modules
            static::htmlPages(),
            static::markdownPosts(),
            static::bladePages(),
            static::markdownPages(),
            static::documentationPages(),
            // static::dataCollections(),

            // Frontend Features
            static::darkmode(),
            static::documentationSearch(),

            // Integrations
            static::torchlight(),
        ]));
    }

    // ================================================
    // Determine if a given feature is enabled.
    // ================================================

    public static function hasHtmlPages(): bool
    {
        return static::enabled(static::htmlPages());
    }

    public static function hasBladePages(): bool
    {
        return static::enabled(static::bladePages());
    }

    public static function hasMarkdownPages(): bool
    {
        return static::enabled(static::markdownPages());
    }

    public static function hasMarkdownPosts(): bool
    {
        return static::enabled(static::markdownPosts());
    }

    public static function hasDocumentationPages(): bool
    {
        return static::enabled(static::documentationPages());
    }

    public static function hasDataCollections(): bool
    {
        return static::enabled(static::dataCollections());
    }

    public static function hasDocumentationSearch(): bool
    {
        return static::enabled(static::documentationSearch())
            && static::hasDocumentationPages()
            && count(DiscoveryService::getDocumentationPageFiles()) > 0;
    }

    public static function hasDarkmode(): bool
    {
        return static::enabled(static::darkmode());
    }

    /**
     * Torchlight is by default enabled automatically when an API token
     * is set in the .env file but is disabled when running tests.
     */
    public static function hasTorchlight(): bool
    {
        return static::enabled(static::torchlight())
            && (config('torchlight.token') !== null)
            && (app('env') !== 'testing');
    }

    // ================================================
    // Enable a given feature to be used in the config.
    // ================================================

    public static function htmlPages(): string
    {
        return 'html-pages';
    }

    public static function bladePages(): string
    {
        return 'blade-pages';
    }

    public static function markdownPages(): string
    {
        return 'markdown-pages';
    }

    public static function markdownPosts(): string
    {
        return 'markdown-posts';
    }

    public static function documentationPages(): string
    {
        return 'documentation-pages';
    }

    public static function documentationSearch(): string
    {
        return 'documentation-search';
    }

    public static function dataCollections(): string
    {
        return 'data-collections';
    }

    public static function darkmode(): string
    {
        return 'darkmode';
    }

    public static function torchlight(): string
    {
        return 'torchlight';
    }

    // ================================================
    // Dynamic features.
    // ================================================

    /** Can a sitemap be generated? */
    public static function sitemap(): bool
    {
        return Hyde::hasSiteUrl()
            && config('site.generate_sitemap', true)
            && extension_loaded('simplexml');
    }

    /** Can an RSS feed be generated? */
    public static function rss(): bool
    {
        return Hyde::hasSiteUrl()
            && static::hasMarkdownPosts()
            && config('hyde.generate_rss_feed', true)
            && extension_loaded('simplexml')
            && count(DiscoveryService::getMarkdownPostFiles()) > 0;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        $array = [];
        foreach (get_class_methods(static::class) as $method) {
            if (str_starts_with($method, 'has')) {
                $array[Str::kebab(substr($method, 3))] = static::{$method}();
            }
        }

        return $array;
    }
}
