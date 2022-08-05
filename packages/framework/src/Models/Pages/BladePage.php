<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Contracts\AbstractPage;

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
     * The page identifier for compatibility.
     *
     * @var string
     */
    public string $identifier;

    /** @deprecated - for backwards compatibility */
    public string $title = '';

    /**
     * @param  string  $view
     */
    public function __construct(string $view)
    {
        parent::__construct($view);
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
