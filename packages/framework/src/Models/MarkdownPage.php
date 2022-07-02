<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Models\Parsers\MarkdownPageParser;

class MarkdownPage extends LegacyMarkdownDocument
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';

    public static string $parserClass = MarkdownPageParser::class;
}
