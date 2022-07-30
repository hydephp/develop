<?php

namespace Hyde\Framework\Helpers;

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
        return config('hyde.meta', []);
    }

    protected static function formatOpenGraphProperty(string $property): string
    {
        return str_starts_with($property, 'og:') ? $property : 'og:'.$property;
    }
}
