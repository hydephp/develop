<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Contracts\PageParserContract;

/**
 * A basic wrapper for the custom Blade View compiler.
 */
class BladePage extends AbstractPage implements PageParserContract
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
    public static string $outputDirectory = '';

    public static string $fileExtension = '.blade.php';
    public static string $parserClass = self::class;

    /**
     * Since this model also acts as a Page parser,
     * we implement the get method for compatability.
     */
    public function get(): BladePage
    {
        return $this;
    }

    /**
     * Since this model also acts as a Page parser,
     * we implement the execute method for compatability.
     */
    public function execute(): void
    {
        // There's nothing to do here.
    }

    /** @inheritDoc */
    public function getBladeView(): string
    {
        return $this->view;
    }
}
