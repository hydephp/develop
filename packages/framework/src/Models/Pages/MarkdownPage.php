<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\BaseMarkdownPage;

/**
 * Page class for Markdown pages.
 *
 * @see https://hydephp.com/docs/master/static-pages#creating-markdown-pages
 */
class MarkdownPage extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $template = 'hyde::layouts/page';
}
