<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Pages\Concerns\BaseMarkdownPage;

/**
 * Page class for Markdown pages.
 *
 * Markdown pages are stored in the _pages directory and using the .md extension.
 * The Markdown will be compiled to HTML using a minimalistic layout to the _site/ directory.
 *
 * @see https://hydephp.com/docs/master/static-pages#creating-markdown-pages
 */
class MarkdownPage extends BaseMarkdownPage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $template = 'hyde::layouts/page';
}
