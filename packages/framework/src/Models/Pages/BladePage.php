<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Contracts\AbstractPage;

/**
 * A basic wrapper for the custom Blade View compiler.
 */
class BladePage extends AbstractPage
{
    /**
     * The name of the Blade View to compile.
     *
     * @var string
     *
     * Must be a top level file relative to
     * resources\views\pages\ and ending
     * in .blade.php to be compiled.
     */
    public string $view;

    /**
     * The page slug for compatibility.
     *
     * @var string
     */
    public string $identifier;

    /**
     * @param  string  $view
     */
    public function __construct(string $view)
    {
        $this->view = $view;
        $this->identifier = $view;
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
