<?php

namespace Hyde\Framework\Concerns\Markdown;

/**
 * Global Markdown Feature Handler.
 *
 * @see HasConfigurableMarkdownFeatures for per-object configuration
 * @deprecated v0.54.0
 */
trait HasMarkdownFeatures
{
    public static function hasTableOfContents(): bool
    {
        return config('docs.table_of_contents.enabled', true);
    }
}
