<?php

namespace Hyde\Framework\Contracts;

interface MarkdownDocumentContract
{
    /**
     * Construct the class.
     *
     * @param  array  $matter  The parsed front matter.
     * @param  string  $body  The parsed markdown body.
     * @param  string  $title  The page title used in the HTML.
     * @param  string  $slug  The page slug for internal reference.
     */
    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '');
}
