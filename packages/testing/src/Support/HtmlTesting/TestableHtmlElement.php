<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use Illuminate\Support\Collection;

/**
 * A wrapper for an HTML element node, parsed into an assertable and queryable object.
 */
class TestableHtmlElement
{
    protected readonly string $html;

    /** @var \Illuminate\Support\Collection<\Hyde\Testing\Support\HtmlTesting\TestableHtmlElement> */
    protected Collection $nodes;

    public function __construct(string $html)
    {
        $this->html = $html;
    }
}
