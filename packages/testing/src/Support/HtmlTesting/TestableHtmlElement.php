<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

/**
 * A wrapper for an HTML element node, parsed into an assertable and queryable object.
 */
class TestableHtmlElement
{
    protected readonly string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }
}
