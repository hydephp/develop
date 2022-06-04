<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Framework\Services\DiscoveryService;
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

        parent::__construct($this->parseSourceFile());
    }

    protected function parseSourceFile()
    {
        return DiscoveryService::getParserInstanceForModel(
            $this->model, basename($this->path)
        )->get();
    }

    public function render(): string
    {
        // @todo investigate overhead of this (compiling to to disk vs in memory)
        return file_get_contents($this->__invoke());
    }
}