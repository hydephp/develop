<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;

/**
 * Runs the static page builder for the given path.
 *
 * @deprecated Can be replaced with an action.
 */
class RebuildService
{
    /**
     * The source file to build.
     * Should be relative to the Hyde installation.
     */
    protected string $filepath;

    /**
     * The page builder instance.
     */
    protected StaticPageBuilder $builder;

    /**
     * Construct the service class instance.
     *
     * @param  string  $filepath  Relative source file to compile. Example: _posts/foo.md
     */
    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * Execute the service action.
     */
    public function execute(): StaticPageBuilder
    {
        return $this->builder = (new StaticPageBuilder(
            Pages::getPage($this->filepath),
            true
        ));
    }
}
