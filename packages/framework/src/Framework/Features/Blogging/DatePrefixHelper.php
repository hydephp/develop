<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Hyde\Support\Models\DateString;

/**
 * @internal Helper class for handling date prefixes in blog post filenames
 */
class DatePrefixHelper
{
    protected const DATE_PATTERN = '/^(\d{4}-\d{2}-\d{2})(?:-(\d{2}-\d{2}))?-/';

    public static function hasDatePrefix(string $filepath): bool
    {
        return preg_match(static::DATE_PATTERN, basename($filepath)) === 1;
    }

    public static function extractDate(string $filepath): DateTimeInterface
    {
        if (! preg_match(static::DATE_PATTERN, basename($filepath), $matches)) {
            throw new InvalidArgumentException('The given filepath does not contain a valid ISO 8601 date prefix.');
        }

        $dateString = $matches[1];
        if (isset($matches[2])) {
            $dateString .= ' ' . str_replace('-', ':', $matches[2]);
        }

        return new DateTime($dateString);
    }

    public static function stripDatePrefix(string $filepath): string
    {
        return preg_replace(static::DATE_PATTERN, '', basename($filepath));
    }

    public static function createDateString(DateTimeInterface $date): DateString
    {
        return new DateString($date->format('Y-m-d H:i'));
    }
}
