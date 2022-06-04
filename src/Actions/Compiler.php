<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Framework\StaticPageBuilder;

class Compiler extends StaticPageBuilder
{
    protected string $model;
    protected string $path;

    /**
     * @param string<\Hyde\Framework\Contracts\AbstractPage> $model
     * @param string $path
     */
    public function __construct(string $model, string $path)
    {
        $this->model = $model;
        $this->path = $path;

        // TODO parent::__construct($model, $path);
    }

    public function render(): string
    {
        return 'Not yet implemented';
    }
}