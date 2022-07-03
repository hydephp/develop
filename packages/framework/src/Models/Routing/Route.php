<?php

namespace Hyde\Framework\Models\Routing;

use Hyde\Framework\Contracts\PageContract;

class Route implements RouteContract
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
        return $this->sourceModel;
    }

    /** @inheritDoc */
    public function getSourceFilePath(): string
    {
        return $this->sourceModel->getSourcePath();
    }

    /** @inheritDoc */
    public function getOutputFilePath(): string
    {
        return $this->sourceModel->getOutputPath();
    }
}
