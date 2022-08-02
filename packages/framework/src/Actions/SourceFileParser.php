<?php

namespace Hyde\Framework\Actions;

/**
 * Parses a source file and returns a new page model instance for it.
 */
class SourceFileParser
{
    public function __construct(protected string $model, protected string $slug)
    {
        //
    }
}
