<?php

namespace Hyde\Framework\Modules\Router\Concerns;

abstract class BaseRoute implements RouteContract
{
    /**
     * @var string<\Hyde\Framework\Contracts\AbstractPage> $sourceModel
     */
    protected string $sourceModel;

    /**
     * @var string relative path to the source file
     */
    protected string $sourceFile;

    /**
     * @var string the calculated route key/name
     */
    protected string $name;

    /**
     * @var string the calculated HTML file path
     */
    protected string $path;

    public function __construct(string $sourceModel, string $sourceFile)
    {
        $this->sourceModel = $sourceModel;
        $this->sourceFile = $sourceFile;
    }
}