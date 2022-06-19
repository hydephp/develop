<?php

namespace Hyde\Framework\Contracts;

interface MarkdownDocumentContract
{
    public function __construct(array $matter = [], string $body = '', string $title = '', string $slug = '');
}