<?php

namespace Hyde\Framework\Modules\Markdown;

use Hyde\Framework\Services\MarkdownConverterService;

/**
 * Markdown facade to access Markdown services.
 */
class Markdown
{
    //
    /**
     * Parse the Markdown into HTML.
     *
     * @return string $html
     */
    public static function parse(string $markdown, ?string $sourceModel = null): string
    {
        return (new MarkdownConverterService($markdown, $sourceModel))->parse();
    }
}
