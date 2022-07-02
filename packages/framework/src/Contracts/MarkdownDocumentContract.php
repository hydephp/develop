<?php

namespace Hyde\Framework\Contracts;

interface MarkdownDocumentContract
{
    /**
     * Construct the class.
     *
     * @param  array  $matter  The parsed front matter.
     * @param  string  $body  The parsed markdown body.
     */
    public function __construct(array $matter = [], string $body = '');
}
