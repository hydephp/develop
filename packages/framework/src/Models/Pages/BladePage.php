<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\AbstractPage;
use Hyde\Framework\Models\FrontMatter;

/**
 * A basic wrapper for the custom Blade View compiler.
 */
class BladePage extends AbstractPage
{
    /**
     * The name of the Blade View to compile. Commonly stored in _pages/{$identifier}.blade.php.
     *
     * @var string
     */
    public string $view;

    /**
     * @param  string  $view
     * @param  \Hyde\Framework\Models\FrontMatter|array  $matter
     */
    public function __construct(string $view, FrontMatter|array $matter = [])
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
