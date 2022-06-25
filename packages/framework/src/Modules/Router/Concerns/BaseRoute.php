<?php

namespace Hyde\Framework\Modules\Router\Concerns;

abstract class BaseRoute implements RouteContract
{
    protected string $sourceModel;
    protected string $sourceFile;
    protected string $name;

    public function __construct(string $sourceModel, string $sourceFile)
    {
        $this->sourceModel = $sourceModel;
        $this->sourceFile = $sourceFile;
    }
}