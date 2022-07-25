<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Actions\GeneratesTableOfContents;

/**
 * Generate Table of Contents as HTML from a Markdown document body.
 *
 * @see \Hyde\Framework\Testing\Unit\HasTableOfContentsTest
 */
trait HasTableOfContents
{
    public function getTableOfContents(): string
    {
        return (new GeneratesTableOfContents($this->body))->execute();
    }
}
