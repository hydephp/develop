<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

/**
 * A wrapper for an HTML document, parsed into an assertable and queryable object, with an abstract syntax tree.
 */
class TestableHtmlDocument
{
    protected readonly string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }
}
