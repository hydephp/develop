<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\StaticPageBuilder;

/**
 * Hooks into the Hyde application to parse and compile
 * a page source file to static HTML for the request.
 */
class Compiler extends StaticPageBuilder
{
    protected string $model;
    protected string $path;

    /**
     * Initialize the StaticPageBuilder parent class.
     */
    public function __construct(string $pageClass, string $relativePath)
    {
        $this->model = $pageClass;
        $this->path = $relativePath;

        parent::__construct($this->parseSourceFile());
    }

    /**
     * Parse the source file to a Page model the compiler can process.
     */
    protected function parseSourceFile()
    {
        return DiscoveryService::getParserInstanceForModel(
            $this->model,
            basename($this->path)
        )->get();
    }

    /**
     * Invoke the underlying compiler and return the compiled HTML stream.
     */
    public function render(): string
    {
        // @todo investigate overhead of this (compiling to to disk vs in memory)
        return file_get_contents($this->__invoke());
    }
}
