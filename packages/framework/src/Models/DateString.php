<?php

namespace Hyde\Framework\Models;

use DateTime;
use Hyde\Framework\Exceptions\CouldNotParseDateStringException;

/**
 * Parse a date string and create normalized formats.
 *
 * @see \Hyde\Framework\Testing\Unit\DateStringTest
 */
class DateString
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

    /**
     * @param  string  $string
     *
     * @throws \Hyde\Framework\Exceptions\CouldNotParseDateStringException
     */
    public function __construct(string $string)
    {
        $this->string = $string;

        try {
            $this->dateTimeObject = new DateTime($this->string);
        } catch (\Exception $e) {
            throw new CouldNotParseDateStringException($e->getMessage());
        }

        $this->datetime = $this->dateTimeObject->format('c');
        $this->sentence = $this->dateTimeObject->format('l M jS, Y, \a\t g:ia');
        $this->short = $this->dateTimeObject->format('M jS, Y');
    }
}
