<?php

namespace Hyde\Framework\Models\Routing;

use Hyde\Framework\Contracts\PageContract;

interface RouteContract
{
    /**
     * Construct a new Route instance for the given page model.
     *
     * @param \Hyde\Framework\Contracts\PageContract $sourceModel
     */
    public function __construct(PageContract $sourceModel);

    /**
     * Get the source model for the route.
     *
     * @return \Hyde\Framework\Contracts\PageContract
     */
    public function getSourceModel(): PageContract;

    /**
     * Get the path to the source file.
     *
     * @return string Path relative to the root of the project.
     */
    public function getSourceFilePath(): string;

    /**
     * Get the path to the output file.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputFilePath(): string;
}
