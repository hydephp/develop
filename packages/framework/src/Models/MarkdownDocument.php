<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Hyde;
use Hyde\Framework\Contracts\MarkdownDocumentContract;
use Hyde\Framework\Services\MarkdownFileService;
use Illuminate\Support\Arr;

/**
 * @see \Hyde\Framework\Testing\MarkdownDocumentTest
 */
class MarkdownDocument implements MarkdownDocumentContract
{
    public array $matter;
    public string $body;

    public function __construct(array $matter = [], string $body = '')
    {
        $this->matter = $matter;
        $this->body = $body;
    }

    public function __get(string $key)
    {
        return $this->matter($key);
    }

    public function matter(string $key = null, $default = null)
    {
        if ($key) {
            return Arr::get($this->matter, $key, $default);
        }

        return $this->matter;
    }

    public function body(): string
    {
        return $this->body;
    }

    public static function parseFile(string $localFilepath): static
    {
        return (new MarkdownFileService(Hyde::path($localFilepath)))->get();
    }
}
