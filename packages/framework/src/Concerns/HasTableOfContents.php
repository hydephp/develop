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
    public string $tableOfContents;

    public function constructTableOfContents(): void
    {
        if (config('docs.table_of_contents.enabled', true)) {
            $this->tableOfContents = (new GeneratesTableOfContents($this->body))->execute();
        }
    }
}
