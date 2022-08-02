<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Contracts\PageContract;

/**
 * Parses a source file and returns a new page model instance for it.
 */
class SourceFileParser
{
    public function __construct(protected string $model, protected string $slug)
    {
        $this->parse();
    }

    public function parse(): void
    {
        //
    }

    public function get(): PageContract
    {
        return new $this->model($this->slug);
    }
}
