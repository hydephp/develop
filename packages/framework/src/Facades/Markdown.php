<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Modules\Markdown\MarkdownConverter;
use Hyde\Framework\Services\MarkdownService;

/**
 * Markdown facade to access Markdown services.
 */
class Markdown
{
    /**
     * Parse a Markdown string into HTML.
     *
     * If a source model is provided, the Markdown will be converted using the dynamic MarkdownService,
     * otherwise, the pre-configured singleton from the service container will be used instead.
     *
     * @return string $html
     */
    public static function parse(string $markdown, ?string $sourceModel = null): string
    {
        return $sourceModel !== null
            ? (new MarkdownService($markdown, $sourceModel))->parse()
            : app(MarkdownConverter::class)->convert($markdown);
    }
}
