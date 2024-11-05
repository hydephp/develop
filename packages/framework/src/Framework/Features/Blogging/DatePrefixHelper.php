<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * @internal Helper class for handling date prefixes in blog post filenames
 */
class DatePrefixHelper
{
    /**
     * We accept ISO 8601 dates in the format 'YYYY-MM-DD'.
     *
     * @var string The regular expression pattern for matching a date prefix in a filename
     */
    protected const DATE_PATTERN = '/^(\d{4}-\d{2}-\d{2})-/';

    public static function hasDatePrefix(string $filepath): bool
    {
        return preg_match(static::DATE_PATTERN, basename($filepath)) === 1;
    }

    public static function extractDate(string $filepath): DateTimeInterface
    {
        if (! preg_match(static::DATE_PATTERN, basename($filepath), $matches)) {
            throw new InvalidArgumentException('The given filepath does not contain a valid ISO 8601 date prefix.');
        }

        return new DateTime($matches[1].' 00:00');
    }

    public static function stripDatePrefix(string $filepath): string
    {
        return preg_replace(static::DATE_PATTERN, '', basename($filepath));
    }
}
