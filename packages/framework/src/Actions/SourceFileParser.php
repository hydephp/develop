<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Contracts\PageContract;

/**
 * Parses a source file and returns a new page model instance for it.
 *
 * @see \Hyde\Framework\Testing\Feature\SourceFileParserTest
 */
class SourceFileParser
{
    use ValidatesExistence;

    protected PageContract $page;

    public function __construct(string $pageClass, string $slug)
    {
        $this->validateExistence($pageClass, $slug);

        //
    }

    public function get(): PageContract
    {
        return $this->page;
    }
}
