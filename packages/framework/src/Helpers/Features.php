<?php

namespace Hyde\Framework\Helpers;

/**
 * Allows features to be enabled and disabled in a simple object-oriented manner.
 *
 * Based entirely on Laravel Jetstream (License MIT)
 *
 * @see https://jetstream.laravel.com/
 */
class Features
{
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
            static::blogPosts(),
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

    /**
     * ================================================
     * Determine if a given feature is enabled.
     * ================================================.
     */
    public static function hasBlogPosts(): bool
    {
        return static::enabled(static::blogPosts());
    }
    
    public static function hasBladePages(): bool
    {
        return static::enabled(static::bladePages());
    }

    public static function hasMarkdownPages(): bool
    {
        return static::enabled(static::markdownPages());
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
            && static::hasDocumentationPages();
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

    /**
     * ================================================
     * Enable a given feature to be used in the config.
     * ================================================.
     */
    public static function blogPosts(): string
    {
        return 'blog-posts';
    }

    public static function bladePages(): string
    {
        return 'blade-pages';
    }

    public static function markdownPages(): string
    {
        return 'markdown-pages';
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
}
