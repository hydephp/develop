<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Modules\Markdown\MarkdownConverter;
use Hyde\Framework\Services\MarkdownService;

/**
 * Markdown facade to access Markdown services.
 *
 * @deprecated v0.58.0-beta will be merged into Models\Markdown.php
 */
class Markdown
{
    /**
     * @deprecated v0.58.0-beta use Markdown::render() instead.
     */
    public static function parse(string $markdown, ?string $sourceModel = null): string
    {
        return static::render($markdown, $sourceModel);
    }
}
