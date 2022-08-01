<?php

namespace Hyde\Framework\Helpers;

/**
 * General interface for Markdown services.
 *
 * @see \Hyde\Framework\Services\MarkdownConverterService
 */
class Markdown
{
    public static function hasTableOfContents(): bool
    {
        return config('docs.table_of_contents.enabled', true);
    }
}
