<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Foundation\Concerns\Feature;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;

use function is_array;
use function array_keys;
use function array_filter;
use function extension_loaded;
use function in_array;
use function count;
use function app;

/**
 * Allows features to be enabled and disabled in a simple object-oriented manner.
 */
class Features implements SerializableContract
{
    use Serializable;

    /**
     * The features that are enabled.
     *
     * @var array<string, bool>
     */
    protected array $features = [];

    public function __construct()
    {
        $this->features = $this->boot();
    }

    /**
     * Determine if the given specified is enabled.
     */
    public static function has(Feature $feature): bool
    {
        return in_array($feature, static::enabled());
    }

    /**
     * Get all enabled features.
     *
     * @return array<string>
     */
    public static function enabled(): array
    {
        return array_keys(array_filter(Hyde::features()->getFeatures()));
    }

    /**
     * Get all features and their status.
     *
     * @deprecated This method might not actually provide value, as it's not interesting information to have.
     *
     * @return array<string, bool>
     */
    public static function getFeatures(): array
    {
        return Hyde::features()->toArray();
    }

    // =================================================
    // Configure features to be used in the config file.
    // =================================================

    public static function htmlPages(): Feature
    {
        return Feature::HtmlPages;
    }

    public static function bladePages(): Feature
    {
        return Feature::BladePages;
    }

    public static function markdownPages(): Feature
    {
        return Feature::MarkdownPages;
    }

    public static function markdownPosts(): Feature
    {
        return Feature::MarkdownPosts;
    }

    public static function documentationPages(): Feature
    {
        return Feature::DocumentationPages;
    }

    public static function documentationSearch(): Feature
    {
        return Feature::DocumentationSearch;
    }

    public static function darkmode(): Feature
    {
        return Feature::Darkmode;
    }

    public static function torchlight(): Feature
    {
        return Feature::Torchlight;
    }

    // ================================================
    // Determine if a given feature is enabled.
    // ================================================

    public static function hasHtmlPages(): bool
    {
        return static::has(static::htmlPages());
    }

    public static function hasBladePages(): bool
    {
        return static::has(static::bladePages());
    }

    public static function hasMarkdownPages(): bool
    {
        return static::has(static::markdownPages());
    }

    public static function hasMarkdownPosts(): bool
    {
        return static::has(static::markdownPosts());
    }

    public static function hasDocumentationPages(): bool
    {
        return static::has(static::documentationPages());
    }

    public static function hasDarkmode(): bool
    {
        return static::has(static::darkmode());
    }

    // ====================================================
    // Dynamic features that in addition to being enabled
    // in the config file, require preconditions to be met.
    // ====================================================

    /**
     * Can a sitemap be generated?
     */
    public static function hasSitemap(): bool
    {
        return Hyde::hasSiteUrl()
            && Config::getBool('hyde.generate_sitemap', true)
            && extension_loaded('simplexml');
    }

    /**
     * Can an RSS feed be generated?
     */
    public static function hasRss(): bool
    {
        return Hyde::hasSiteUrl()
            && static::hasMarkdownPosts()
            && Config::getBool('hyde.rss.enabled', true)
            && extension_loaded('simplexml')
            && count(MarkdownPost::files()) > 0;
    }

    /**
     * Torchlight is by default enabled automatically when an API token
     * is set in the .env file but is disabled when running tests.
     */
    public static function hasTorchlight(): bool
    {
        return static::has(static::torchlight())
            && (Config::getNullableString('torchlight.token') !== null)
            && (app('env') !== 'testing');
    }

    public static function hasDocumentationSearch(): bool
    {
        return static::has(static::documentationSearch())
            && static::hasDocumentationPages()
            && count(DocumentationPage::files()) > 0;
    }

    /**
     * Get an array representation of the features and their status.
     *
     * @return array<string, bool>
     *
     * @example ['html-pages' => true, 'markdown-pages' => false, ...]
     */
    public function toArray(): array
    {
        return $this->features;
    }

    /** @return array<Feature> */
    protected static function getDefaultOptions(): array
    {
        return [
            // Page Modules
            static::htmlPages(),
            static::markdownPosts(),
            static::bladePages(),
            static::markdownPages(),
            static::documentationPages(),

            // Frontend Features
            static::darkmode(),
            static::documentationSearch(),

            // Integrations
            static::torchlight(),
        ];
    }

    protected function boot(): array
    {
        $options = static::getDefaultOptions();

        $enabled = [];

        // Set all default features to false
        foreach ($options as $feature) {
            $enabled[$feature] = false;
        }

        // Set all features to true if they are enabled in the config file
        foreach ($this->getConfiguredFeatures() as $feature) {
            if (in_array($feature, $options)) {
                $enabled[$feature] = true;
            }
        }

        return $enabled;
    }

    /** @return array<Feature> */
    protected function getConfiguredFeatures(): array
    {
        return Config::getArray('hyde.features', static::getDefaultOptions());
    }

    /**
     * @internal This method is not covered by the backward compatibility promise.
     *
     * @param  string|array<string, bool>  $feature
     */
    public static function mock(string|array $feature, bool $enabled = true): void
    {
        $features = is_array($feature) ? $feature : [$feature => $enabled];

        foreach ($features as $feature => $enabled) {
            Hyde::features()->features[$feature] = $enabled;
        }
    }
}
