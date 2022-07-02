<?php

namespace Hyde\Framework\Models;

use Hyde\Framework\Contracts\MarkdownDocumentContract;

class MarkdownDocument implements MarkdownDocumentContract
{
    public array $matter;
    public string $body;

    public function __construct(array $matter = [], string $body = '')
    {
        $this->matter = $matter;
        $this->body = $body;
    }
}
