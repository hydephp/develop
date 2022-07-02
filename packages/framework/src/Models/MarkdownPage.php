<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\Parsers\MarkdownPageParser;

/**
 * The base class for all Markdown-based Page Models.
 *
 * The other Markdown based pages extend this class,
 * to take advantage of Markdown specific helpers.
 */
class MarkdownPage extends AbstractPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';

    public static string $parserClass = MarkdownPageParser::class;
}
