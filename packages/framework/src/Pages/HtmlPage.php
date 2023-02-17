<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Pages\Concerns\DiscoverablePage;
use Hyde\Support\Contracts\DiscoverableContract;

/**
 * Page class for HTML pages.
 *
 * Html pages are stored in the _pages directory and using the .html extension.
 * These pages will be copied exactly as they are to the _site/ directory.
 *
 * @see https://hydephp.com/docs/master/static-pages#bonus-creating-html-pages
 */
class HtmlPage extends DiscoverablePage implements DiscoverableContract
{
    protected static string $sourceDirectory = '_pages';
    protected static string $outputDirectory = '';
    protected static string $fileExtension = '.html';

    public function contents(): string
    {
        return file_get_contents($this->getSourcePath());
    }

    public function compile(): string
    {
        return $this->contents();
    }

    public function getBladeView(): string
    {
        return $this->getSourcePath();
    }
}
