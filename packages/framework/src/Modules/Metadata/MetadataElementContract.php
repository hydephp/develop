<?php

declare(strict_types=1);

namespace Hyde\Framework\Modules\Metadata;

/**
 * The methods the object representations for Metadata element classes must implement.
 */
interface MetadataElementContract extends \Stringable
{
    /**
     * @return string The HTML representation of the element.
     *
     * @example <meta name="description" content="This is a description.">
     */
    public function __toString(): string;

    /**
     * @return string The unique key for the element. If multiple elements have the same key, only the first one will be used.
     *
     * @example description
     */
    public function uniqueKey(): string;
}
