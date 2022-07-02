<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\Parsers\MarkdownPageParser;

class MarkdownPage extends AbstractPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';

    public static string $parserClass = MarkdownPageParser::class;
}
