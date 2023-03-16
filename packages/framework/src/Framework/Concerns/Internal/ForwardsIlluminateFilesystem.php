<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns\Internal;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Finder\SplFileInfo;

use function array_map;
use function collect;
use function in_array;
use function is_array;
use function is_int;

/**
 * Forwards calls to the Laravel File facade to the HydePHP Filesystem Facade,
 * while turning all paths arguments into absolute project paths.
 *
 * @interal This trait is not covered by the backward compatibility promise.
 *
 * @see \Hyde\Facades\Filesystem
 *
 * @method static bool exists(string $path)
 * @method static bool missing(string $path)
 * @method static string get(string $path, bool $lock = false)
 * @method static string sharedGet(string $path)
 * @method static mixed getRequire(string $path, array $data = [])
 * @method static mixed requireOnce(string $path, array $data = [])
 * @method static LazyCollection lines(string $path)
 * @method static string hash(string $path, string $algorithm = 'md5')
 * @method static int|bool put(string $path, string $contents, bool $lock = false)
 * @method static void replace(string $path, string $content)
 * @method static void replaceInFile(array|string $search, array|string $replace, string $path)
 * @method static int prepend(string $path, string $data)
 * @method static int append(string $path, string $data)
 * @method static mixed chmod(string $path, int|null $mode = null)
 * @method static bool delete(string|array $paths)
 * @method static bool move(string $path, string $target)
 * @method static bool copy(string $path, string $target)
 * @method static void link(string $target, string $link)
 * @method static void relativeLink(string $target, string $link)
 * @method static string name(string $path)
 * @method static string basename(string $path)
 * @method static string dirname(string $path)
 * @method static string extension(string $path)
 * @method static string|null guessExtension(string $path)
 * @method static string type(string $path)
 * @method static string|false mimeType(string $path)
 * @method static int size(string $path)
 * @method static int lastModified(string $path)
 * @method static bool isDirectory(string $directory)
 * @method static bool isEmptyDirectory(string $directory, bool $ignoreDotFiles = false)
 * @method static bool isReadable(string $path)
 * @method static bool isWritable(string $path)
 * @method static bool hasSameHash(string $firstFile, string $secondFile)
 * @method static bool isFile(string $file)
 * @method static array glob(string $pattern, int $flags = 0)
 * @method static SplFileInfo[] files(string $directory, bool $hidden = false)
 * @method static SplFileInfo[] allFiles(string $directory, bool $hidden = false)
 * @method static array directories(string $directory)
 * @method static void ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true)
 * @method static bool makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
 * @method static bool moveDirectory(string $from, string $to, bool $overwrite = false)
 * @method static bool copyDirectory(string $directory, string $destination, int|null $options = null)
 * @method static bool deleteDirectory(string $directory, bool $preserve = false)
 * @method static bool deleteDirectories(string $directory)
 * @method static bool cleanDirectory(string $directory)
 */
trait ForwardsIlluminateFilesystem
{
    public static function __callStatic(string $name, array $arguments): string|array|int|bool|null|LazyCollection
    {
        return self::filesystem()->{$name}(...self::qualifyArguments(self::getParameterNames($name), $arguments));
    }

    protected static function getParameterNames(string $name): array
    {
        return array_map(fn (ReflectionParameter $parameter): string => $parameter->getName(),
            (new ReflectionMethod(Filesystem::class, $name))->getParameters()
        );
    }

    protected static function qualifyArguments(array $parameterNames, array $arguments): Collection
    {
        return collect($arguments)->mapWithKeys(function (string|array|int|bool $argumentValue, int|string $key) use ($parameterNames): string|array|int|bool {
            $argumentsToQualify = [
                'path', 'paths', 'file', 'target', 'directory', 'destination', 'firstFile', 'secondFile',
                'pattern', 'link', 'from', 'to',
            ];

            if (is_int($key)) {
                // If the argument is not named, we'll retrieve it from the reflection data.
                $key = $parameterNames[$key];
            }

            if (in_array($key, $argumentsToQualify)) {
                $argumentValue = self::qualifyPathArgument($argumentValue);
            }

            return [$key => $argumentValue];
        });
    }

    /**
     * @param  string|string[]  $path
     * @return string|string[]
     */
    protected static function qualifyPathArgument(array|string $path): string|array
    {
        return is_array($path)
            ? array_map(fn (string $path): string => (string) self::qualifyPathArgument($path), $path)
            : (string) self::absolutePath($path);
    }
}
