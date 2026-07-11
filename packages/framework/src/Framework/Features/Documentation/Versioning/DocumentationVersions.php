<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Documentation\Versioning;

use Hyde\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Exceptions\InvalidConfigurationException;

use function count;
use function sprintf;
use function in_array;
use function Hyde\unslash;
use function preg_match;
use function str_contains;
use function str_starts_with;

/**
 * Registry for the documentation versions defined in the `docs.versions` configuration.
 *
 * When the configuration array is empty (the default), documentation versioning is disabled,
 * and the documentation module behaves exactly like a single-version site.
 */
final class DocumentationVersions
{
    /**
     * The pattern version names must match. Notably, this excludes slashes,
     * as version names are used as single directory and URL path segments.
     */
    final public const VERSION_NAME_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/';

    /**
     * Is documentation versioning enabled? It is enabled by registering versions in the `docs.versions` configuration.
     */
    public static function enabled(): bool
    {
        return count(Config::getArray('docs.versions', [])) > 0;
    }

    /**
     * Get all registered documentation versions, keyed by version name.
     *
     * @return \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion>
     */
    public static function all(): Collection
    {
        if (! self::enabled()) {
            return new Collection();
        }

        /** @var array<int, string> $versions */
        $versions = Config::getArray('docs.versions', []);

        return (new Collection($versions))->mapWithKeys(function (string $name): array {
            return [self::validateVersionName($name) => new DocumentationVersion($name)];
        });
    }

    /**
     * Get a registered version by name, or null if it is not registered.
     */
    public static function get(string $name): ?DocumentationVersion
    {
        return self::all()->get($name);
    }

    /**
     * Get the default documentation version, or null when documentation versioning is disabled.
     *
     * This is the version set in the `docs.default_version` configuration,
     * falling back to the last entry in the `docs.versions` list.
     */
    public static function default(): ?DocumentationVersion
    {
        if (! self::enabled()) {
            return null;
        }

        /** @var array<int, string> $versions */
        $versions = Config::getArray('docs.versions', []);

        $default = self::defaultVersionName($versions);

        return $default === null ? null : self::get($default);
    }

    /**
     * Get the version a documentation page identifier belongs to, or null if it does not belong to a registered version.
     *
     * The version is determined by the first path segment of the identifier, for example `1.x/installation`.
     */
    public static function fromIdentifier(string $identifier): ?DocumentationVersion
    {
        if (! self::enabled() || ! str_contains($identifier, '/')) {
            return null;
        }

        return self::get(Str::before($identifier, '/'));
    }

    /**
     * Get the version a route key belongs to, or null if it does not belong to a registered version.
     */
    public static function fromRouteKey(string $routeKey): ?DocumentationVersion
    {
        if (! self::enabled()) {
            return null;
        }

        return self::all()->first(function (DocumentationVersion $version) use ($routeKey): bool {
            return str_starts_with($routeKey, $version->routeKeyPrefix().'/');
        });
    }

    /**
     * Get the route for the page equivalent to the given page, but in another version.
     *
     * Returns null when the given page does not belong to a version,
     * or when the equivalent page does not exist in the target version.
     */
    public static function getEquivalentRoute(HydePage $page, DocumentationVersion $targetVersion): ?Route
    {
        $currentVersion = self::fromRouteKey($page->getRouteKey());

        if ($currentVersion === null) {
            return null;
        }

        $pathWithinVersion = Str::after($page->getRouteKey(), $currentVersion->routeKeyPrefix().'/');

        return Routes::find($targetVersion->routeKeyPrefix().'/'.$pathWithinVersion);
    }

    /**
     * Strip the version prefix from a documentation page identifier, if it has one.
     *
     * For example, `1.x/getting-started/installation` becomes `getting-started/installation`.
     */
    public static function stripVersionPrefix(string $identifier): string
    {
        $version = self::fromIdentifier($identifier);

        return $version === null ? $identifier : Str::after($identifier, $version->name.'/');
    }

    /**
     * Strip the version segment from a documentation route key, if it has one.
     *
     * For example, `docs/1.x/installation` becomes `docs/installation`.
     */
    public static function stripVersionPrefixFromRouteKey(string $routeKey): string
    {
        $version = self::fromRouteKey($routeKey);

        return $version === null
            ? $routeKey
            : unslash(DocumentationPage::outputDirectory().'/'.Str::after($routeKey, $version->routeKeyPrefix().'/'));
    }

    /** @param array<int, string> $versions */
    protected static function defaultVersionName(array $versions): ?string
    {
        if ($versions === []) {
            return null;
        }

        $default = Config::getNullableString('docs.default_version') ?? Arr::last($versions);

        if (! in_array($default, $versions, true)) {
            throw new InvalidConfigurationException(sprintf("The default documentation version '%s' is not present in the `docs.versions` configuration.", $default), 'docs', 'default_version');
        }

        return $default;
    }

    protected static function validateVersionName(string $name): string
    {
        if (! preg_match(self::VERSION_NAME_PATTERN, $name)) {
            throw new InvalidConfigurationException(sprintf("Invalid documentation version name '%s'. Version names may only contain alphanumeric characters, dots, dashes, and underscores.", $name), 'docs', 'versions');
        }

        return $name;
    }
}
