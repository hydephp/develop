<?php

namespace Hyde\Framework\Models;

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
    public string $slug;

    /**
     * @param  string  $view
     */
    public function __construct(string $view)
    {
        $this->view = $view;
        $this->slug = $view;
    }

    public static string $sourceDirectory = '_pages';
    public static string $outputDirectory = '/';
    public static string $fileExtension = '.blade.php';
    public static string $parserClass = self::class;

    /**
     * Since this model also acts as a Blade View compiler,
     * we implement the get method for compatability.
     */
    public function get(): BladePage
    {
        return $this;
    }

    public function getCurrentPagePath(): string
    {
        return $this->view;
    }
}
