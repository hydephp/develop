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

    public function __construct(protected string $model, protected string $slug)
    {
        $this->validateExistence($model, $slug);

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
