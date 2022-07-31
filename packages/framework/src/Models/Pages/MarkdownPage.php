<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Models\Parsers\MarkdownPageParser;

class MarkdownPage extends AbstractMarkdownPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $template = 'hyde::layouts/page';
    public static string $parserClass = MarkdownPageParser::class;
}
