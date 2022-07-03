<?php

namespace Hyde\Framework\Models\Routing;

use Hyde\Framework\Contracts\PageContract;

abstract class Route implements RouteContract
{
    protected PageContract $sourceModel;

    public function __construct(PageContract $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    public function getSourceModel(): PageContract
    {
        // TODO: Implement getSourceModel() method.
    }

    public function getSourceFilePath(): string
    {
        // TODO: Implement getSourceFilePath() method.
    }

    public function getOutputFilePath(): string
    {
        // TODO: Implement getOutputFilePath() method.
    }
}
