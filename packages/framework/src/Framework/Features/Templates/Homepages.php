<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Illuminate\Support\Collection;

/**
 * @deprecated May be replaced by vendor:publish in the future.
 *
 * @internal This class is currently experimental and should not be relied upon outside the framework as it may change at any time.
 * @experimental
 */
final class Homepages
{
    public static function options(): Collection
    {
        return new Collection();
    }

    public static function exists(string $page): bool
    {
        return self::options()->has($page);
    }

    public static function get(string $page): ?PublishableContract
    {
        return self::options()->get($page);
    }
}
