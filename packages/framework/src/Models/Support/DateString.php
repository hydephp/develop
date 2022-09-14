<?php

namespace Hyde\Framework\Models\Support;

use DateTime;

/**
 * Parse a date string and create normalized formats.
 *
 * @see \Hyde\Framework\Testing\Unit\DateStringTest
 */
class DateString implements \Stringable
{
    /** The original date string. */
    public string $string;

    /** The parsed date object. */
    public DateTime $dateTimeObject;

    /** The machine-readable datetime string. */
    public string $datetime;

    /** The human-readable sentence string. */
    public string $sentence;

    /** Shorter version of the sentence string. */
    public string $short;

    public function __construct(string $string)
    {
        $this->string = $string;
        $this->dateTimeObject = new DateTime($this->string);

        $this->datetime = $this->dateTimeObject->format('c');
        $this->sentence = $this->dateTimeObject->format('l M jS, Y, \a\t g:ia');
        $this->short = $this->dateTimeObject->format('M jS, Y');
    }

    public function __toString(): string
    {
        return $this->short;
    }
}
