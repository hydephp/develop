<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\View;

/**
 * Page class for Blade pages.
 *
 * Blade pages are stored in the _pages directory and using the .blade.php extension.
 * They will be compiled using the Laravel Blade engine to the _site/ directory.
 *
 * @see https://hydephp.com/docs/1.x/static-pages#creating-blade-pages
 * @see https://laravel.com/docs/1.x/blade
 */
class BladePage extends HydePage
{
    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '';
    public static string $fileExtension = '.blade.php';

    /** @param  string  $identifier The identifier, which also serves as the view key. */
    public function __construct(string $identifier = '', FrontMatter|array $matter = [])
    {
        parent::__construct($identifier, $matter);
    }

    /** @inheritDoc */
    public function getBladeView(): string
    {
        return $this->identifier;
    }

    /** @inheritDoc */
    public function compile(): string
    {
        return View::make($this->getBladeView())->render();
    }
}
