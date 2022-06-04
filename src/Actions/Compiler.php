<?php

namespace Hyde\RealtimeCompiler\Actions;

class Compiler
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
    }

    public function render(): string
    {
        return 'Not yet implemented';
    }
}