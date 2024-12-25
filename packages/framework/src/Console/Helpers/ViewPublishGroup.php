<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

use function Hyde\unslash;

/**
 * @internal Helper object for publishable view groups.
 */
class ViewPublishGroup
{
    readonly public string $group;

    readonly public string $name;
    readonly public string $description;

    readonly public string $source;
    readonly public string $target;
    readonly public bool $isDirectory;

    /** @var array<string> The filenames relative to the source, if the source is a directory. */
    readonly public array $files;


    /** @var class-string<\Hyde\Foundation\Providers\ViewServiceProvider> */
    protected static string $provider = ViewServiceProvider::class;

    protected function __construct(string $group, string $source, string $target, bool $isDirectory, array $files, ?string $name = null, ?string $description = null)
    {
        $this->group = $group;
        $this->source = $source;
        $this->target = $target;
        $this->files = $files;
        $this->name = $name ?? Hyde::makeTitle($group);
        $this->description = $description ?? "Publish the '$group' files for customization.";
    }

    public static function fromGroup(string $group, ?string $name = null, ?string $description = null): self
    {
        [$source, $target] = static::keyedArrayToTuple(ServiceProvider::pathsToPublish(static::$provider, $group));

        $source = static::normalizePath($source);
        $target = static::normalizePath($target);

        $isDirectory = Filesystem::isDirectory($source);
        $files = $isDirectory ? self::findFiles($source) : [];

        return new static($group, $source, $target, $isDirectory, $files, $name, $description);
    }

    protected static function keyedArrayToTuple(array $array): array
    {
        return [key($array), current($array)];
    }

    /** @return array<string> */
    protected static function findFiles(string $source): array
    {
        return Filesystem::findFiles($source, recursive: true)
            ->map(fn (string $file) => static::normalizePath($file))
            ->map(fn (string $file) => unslash(Str::after($file, $source)))
            ->sort(fn (string $a, string $b): int => substr_count($a, '/') <=> substr_count($b, '/') ?: strcmp($a, $b))
            ->all();
    }

    protected static function normalizePath(string $path): string
    {
        return Hyde::pathToRelative(
            Filesystem::exists($path) ? realpath($path) : $path
        );
    }
}
