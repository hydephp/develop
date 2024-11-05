<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging;

use DateTime;
use DateTimeInterface;
use Hyde\Support\Models\DateString;

/**
 * @internal Helper class for handling date prefixes in blog post filenames
 */
class DatePrefixHelper
{
    protected const DATE_PATTERN = '/^(\d{4}-\d{2}-\d{2})(?:-(\d{2}-\d{2}))?-/';

    public static function hasDatePrefix(string $identifier): bool
    {
        return preg_match(static::DATE_PATTERN, $identifier) === 1;
    }

    public static function extractDate(string $identifier): ?DateTimeInterface
    {
        if (! preg_match(static::DATE_PATTERN, $identifier, $matches)) {
            return null;
        }

        $dateString = $matches[1];
        if (isset($matches[2])) {
            $dateString .= ' ' . str_replace('-', ':', $matches[2]);
        }

        return new DateTime($dateString);
    }

    public static function stripDatePrefix(string $identifier): string
    {
        return preg_replace(static::DATE_PATTERN, '', $identifier);
    }

    public static function createDateString(DateTimeInterface $date): DateString
    {
        return new DateString($date->format('Y-m-d H:i'));
    }
}
