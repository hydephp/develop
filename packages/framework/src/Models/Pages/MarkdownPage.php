<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\BaseMarkdownPage;

class MarkdownPage extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $template = 'hyde::layouts/page';
}
