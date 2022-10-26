<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Hyde;

/**
 * Build static pages, but intelligently.
 *
 * Runs the static page builder for a given path.
 */
class RebuildService
{
    /**
     * The source file to build.
     * Should be relative to the Hyde installation.
     *
     * @var string
     */
    public string $filepath;

    /**
     * The page builder instance.
     * Used to get debug output from the builder.
     *
     * @var StaticPageBuilder
     */
    public StaticPageBuilder $builder;

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
            Hyde::pages()->getPage($this->filepath),
            true
        ));
    }
}
