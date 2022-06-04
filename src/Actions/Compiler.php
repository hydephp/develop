<?php

namespace Hyde\RealtimeCompiler\Actions;

class Compiler
{
    protected string $model;
    protected string $path;

    /**
     * @param string $model<Hyde\Framework\Contracts\AbstractPage>
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