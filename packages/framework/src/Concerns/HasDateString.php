<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Models\DateString;

/**
 * Create a DateString from a Page model's front matter.
 *
 * @see \Hyde\Framework\Models\DateString
 */
trait HasDateString
{
    public ?DateString $date = null;

    public function constructDateString(): void
    {
        if (isset($this->matter['date'])) {
            $this->date = new DateString($this->matter['date']);
        }
    }
}
