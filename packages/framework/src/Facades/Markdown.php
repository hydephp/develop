<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Modules\Markdown\MarkdownConverter;
use Hyde\Framework\Services\MarkdownService;

/**
 * Markdown facade to access Markdown services.
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

    /**
     * Render a Markdown string into HTML.
     *
     * If a source model is provided, the Markdown will be converted using the dynamic MarkdownService,
     * otherwise, the pre-configured singleton from the service container will be used instead.
     *
     * @return string $html
     */
    public static function render(string $markdown, ?string $sourceModel = null): string
    {
        return $sourceModel !== null
            ? (new MarkdownService($markdown, $sourceModel))->parse()
            : app(MarkdownConverter::class)->convert($markdown);
    }
}
