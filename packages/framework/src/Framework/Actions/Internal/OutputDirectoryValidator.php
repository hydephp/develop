<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Internal;

use Hyde\Hyde;
use Hyde\Framework\Exceptions\InvalidConfigurationException;

use function Hyde\normalize_slashes;
use function Hyde\path_join;
use function str_starts_with;
use function array_values;
use function array_filter;
use function array_merge;
use function array_map;
use function strtolower;
use function in_array;
use function is_link;
use function implode;
use function explode;
use function sprintf;

/**
 * Assert that the site output directory is a location the framework may empty before a build.
 *
 * @internal This class is used internally by the framework and is not part of the public API, unless that is requested on GitHub with a valid use case.
 */
class OutputDirectoryValidator
{
    /** Directories that must survive a build, in addition to those Hyde is configured to read from. */
    protected const PROJECT_DIRECTORIES = ['.git', '.github', 'app', 'config', 'node_modules', 'resources', 'tests', 'vendor'];

    public static function validate(): void
    {
        $segments = static::pathSegments(Hyde::getOutputDirectory());

        if ($segments === [] || in_array('..', $segments, true)) {
            static::fail('The output directory (%s) must be a subdirectory of the project, as it is emptied before every build.', Hyde::sitePath());
        }

        $directory = implode('/', $segments);

        foreach (static::protectedDirectories() as $protected) {
            if (static::overlaps($directory, $protected)) {
                static::fail('The output directory (%s) must not overlap the project directory (%s), as it is emptied before every build.', $directory, $protected);
            }
        }

        $path = Hyde::path();

        foreach ($segments as $segment) {
            $path = path_join($path, $segment);

            if (is_link($path)) {
                static::fail('The output directory path (%s) must not be a symbolic link, as the build empties the directory it leads to.', $path);
            }
        }
    }

    /**
     * Split a project-relative path into its meaningful segments, leaving any upwards traversal in place.
     *
     * @return array<int, string>
     */
    protected static function pathSegments(string $path): array
    {
        $segments = explode('/', normalize_slashes($path));

        return array_values(array_filter($segments, fn (string $segment): bool => $segment !== '' && $segment !== '.'));
    }

    /** @return array<int, string> */
    protected static function protectedDirectories(): array
    {
        $directories = array_merge(static::PROJECT_DIRECTORIES, static::sourceDirectories(), [
            Hyde::getSourceRoot(),
            Hyde::getMediaDirectory(),
            '_static',
        ]);

        return array_values(array_filter(array_map(fn (string $directory): string => implode('/', static::pathSegments($directory)), $directories)));
    }

    /** @return array<int, string> */
    protected static function sourceDirectories(): array
    {
        return array_map(fn (string $page): string => $page::sourceDirectory(), Hyde::getRegisteredPageClasses());
    }

    /** Compared case-insensitively, as case-insensitive filesystems would otherwise resolve a differently cased name to the same directory. */
    protected static function overlaps(string $directory, string $protected): bool
    {
        $directory = strtolower($directory);
        $protected = strtolower($protected);

        return $directory === $protected
            || str_starts_with($directory, $protected.'/')
            || str_starts_with($protected, $directory.'/');
    }

    protected static function fail(string $message, string ...$values): never
    {
        throw new InvalidConfigurationException(sprintf($message, ...$values), 'hyde', 'output_directory');
    }
}
