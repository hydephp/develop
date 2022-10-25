<?php

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Modules\Metadata\Metadata\LinkItem;
use Hyde\Framework\Modules\Metadata\Metadata\MetadataItem;
use Hyde\Framework\Modules\Metadata\Metadata\OpenGraphItem;

/**
 * Helpers to fluently declare HTML meta tags.
 *
 * @see \Hyde\Framework\Testing\Feature\MetadataHelperTest
 */
class Meta
{
    public static function name(string $name, string $content): MetadataItem
    {
        return new MetadataItem($name, $content);
    }

    public static function property(string $property, string $content): OpenGraphItem
    {
        return new OpenGraphItem($property, $content);
    }

    public static function link(string $rel, string $href, array $attr = []): LinkItem
    {
        return new LinkItem($rel, $href, $attr);
    }

    public static function get(array $withMergedData = []): array
    {
        return static::filterUnique(
            array_merge(
                static::getGlobalMeta(),
                $withMergedData
            )
        );
    }

    public static function render(array $withMergedData = []): string
    {
        return implode(
            "\n",
            static::get($withMergedData)
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

        return $array;
    }

    protected static function getConfiguredMeta(): array
    {
        return config('hyde.meta', []);
    }
}
