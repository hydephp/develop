<?php

namespace Hyde\Framework\Models\Routing;

use Hyde\Framework\Contracts\PageContract;

abstract class Route implements RouteContract
{
    /**
     * The source model for the route.
     *
     * @var \Hyde\Framework\Contracts\PageContract $sourceModel
     */
    protected PageContract $sourceModel;

    /** @inheritDoc */
    public function __construct(PageContract $sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    /** @inheritDoc */
    public function getSourceModel(): PageContract
    {
        // TODO: Implement getSourceModel() method.
    }

    /** @inheritDoc */
    public function getSourceFilePath(): string
    {
        // TODO: Implement getSourceFilePath() method.
    }

    /** @inheritDoc */
    public function getOutputFilePath(): string
    {
        // TODO: Implement getOutputFilePath() method.
    }
}
