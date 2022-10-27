<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Markdown\Models\FrontMatter;

/**
 * Page class for Blade pages.
 *
 * Blade pages are stored in the _pages directory and using the .blade.php extension.
 * They will be compiled using the Laravel Blade engine the _site/ directory.
 *
 * @see https://hydephp.com/docs/master/static-pages#creating-blade-pages
 * @see https://laravel.com/docs/master/blade
 */
class BladePage extends HydePage
{
    /**
     * The name of the Blade View to compile. Commonly stored in _pages/{$identifier}.blade.php.
     *
     * @var string
     */
    public string $view;

    /**
     * @param  string  $view
     * @param  \Hyde\Markdown\Models\FrontMatter|array  $matter
     */
    public function __construct(string $view = '', FrontMatter|array $matter = [])
    {
        parent::__construct($view, $matter);
        $this->view = $view;
    }

    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $fileExtension = '.blade.php';

    /** @inheritDoc */
    public function getBladeView(): string
    {
        return $this->view;
    }

    /** @inheritDoc */
    public function compile(): string
    {
        return view($this->getBladeView())->render();
    }
}
