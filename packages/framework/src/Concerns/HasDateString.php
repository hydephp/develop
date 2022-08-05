<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Models\DateString;

/**
 * Create a DateString from a Page model's front matter.
 *
 * @see \Hyde\Framework\Models\DateString
 * @deprecated Will be merged into PageModelConstructor
 */
trait HasDateString
{
    public function constructDateString(): void
    {
        if ($this->matter('date') !== null) {
            $this->date = new DateString($this->matter('date'));
        }
    }
}
