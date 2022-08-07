<?php

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\RssFeedService;

/**
 * Helpers to fluently declare HTML meta tags.
 *
 * @see \Hyde\Framework\Testing\Feature\MetadataHelperTest
 */
class Meta
{
    public static function name(string $name, string $content): string
    {
        return '<meta name="'.e($name).'" content="'.e($content).'">';
    }

    public static function property(string $property, string $content): string
    {
        $property = static::formatOpenGraphProperty($property);

        return '<meta property="'.e($property).'" content="'.e($content).'">';
    }

    public static function link(string $rel, string $href, array $attr = []): string
    {
        if (! $attr) {
            return '<link rel="'.e($rel).'" href="'.e($href).'">';
        }

        $attributes = collect($attr)->map(function ($value, $key) {
            return e($key).'="'.e($value).'"';
        })->implode(' ');

        return '<link rel="'.e($rel).'" href="'.e($href).'" '.$attributes.'>';
    }

    public static function render(array $withMergedData = []): string
    {
        return implode(
            "\n",
            static::filterUnique(
                array_merge(
                    static::getGlobalMeta(),
                    $withMergedData
                )
            )
        );
    }

    protected static function filterUnique(array $meta): array
    {
        $array = [];
        $existing = [];

        foreach (array_reverse($meta) as $metaItem) {
            $substring = substr($metaItem, 6, strpos($metaItem, ' content="') - 6);

            if (! in_array($substring, $existing)) {
                $array[] = $metaItem;
                $existing[] = $substring;
            }
        }

        return array_reverse($array);
    }

    public static function getGlobalMeta(): array
    {
        return array_merge(
            static::getDynamicMeta(),
            static::getConfiguredMeta()
        );
    }

    protected static function getDynamicMeta(): array
    {
        $array = [];

        if (Features::sitemap()) {
            $array[] = Meta::link('sitemap', Hyde::url('sitemap.xml'), [
                'type' => 'application/xml', 'title' => 'Sitemap',
            ]);
        }

        if (Features::rss()) {
            $array[] = Meta::link('alternate', Hyde::url(RssFeedService::getDefaultOutputFilename()), [
                'type' => 'application/rss+xml', 'title' => RssFeedService::getDescription(),
            ]);
        }

        return $array;
    }

    protected static function getConfiguredMeta(): array
    {
        return config('hyde.meta', []);
    }

    protected static function formatOpenGraphProperty(string $property): string
    {
        return str_starts_with($property, 'og:') ? $property : 'og:'.$property;
    }
}
