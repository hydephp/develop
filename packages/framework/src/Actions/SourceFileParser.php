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

    /** @var class-string<PageContract> */
    protected string $model;
    protected string $slug;

    /**
     * @param class-string<PageContract> $model
     * @param string $slug
     * @throws \Hyde\Framework\Exceptions\FileNotFoundException
     */
    public function __construct(string $model, string $slug)
    {
        $this->slug = $slug;
        $this->model = $model;
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
