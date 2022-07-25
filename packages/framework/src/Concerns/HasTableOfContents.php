<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Actions\GeneratesSidebarTableOfContents;

/**
 * Generate Table of Contents as HTML from a Markdown document body.
 * 
 * Intended to be used for documentation pages.
 *
 * @see \Hyde\Framework\Testing\Unit\HasTableOfContentsTest
 */
trait HasTableOfContents
{
    public function getTableOfContents(): string
    {
        return (new GeneratesSidebarTableOfContents($this->body))->execute();
    }
}
