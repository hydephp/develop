<?php

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Modules\Metadata\Models\GlobalMetadataBag;
use Hyde\Framework\Modules\Metadata\Models\LinkElement;
use Hyde\Framework\Modules\Metadata\Models\MetadataElement;
use Hyde\Framework\Modules\Metadata\Models\OpenGraphElement;

/**
 * Helpers to fluently declare HTML meta elements using their object representations.
 *
 * @see \Hyde\Framework\Testing\Feature\MetadataHelperTest
 */
class Meta
{
    public static function name(string $name, string $content): MetadataElement
    {
        return new MetadataElement($name, $content);
    }

    public static function property(string $property, string $content): OpenGraphElement
    {
        return new OpenGraphElement($property, $content);
    }

    public static function link(string $rel, string $href, array $attr = []): LinkElement
    {
        return new LinkElement($rel, $href, $attr);
    }

    /**
     * Get the global metadata bag.
     */
    public static function get(): GlobalMetadataBag
    {
        return GlobalMetadataBag::make();
    }

    /**
     * Render the global metadata bag.
     *
     * @return string
     */
    public static function render(): string
    {
        return static::get()->render();
    }
}
