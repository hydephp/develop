<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Facades\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Framework\Actions\MarkdownFileParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function implode;
use function json_decode;
use function sprintf;
use function unslash;

/**
 * Automatically generates Laravel Collections from static data files,
 * such as Markdown components and YAML files using Hyde Autodiscovery.
 *
 * This class acts both as a base collection class, a factory for
 * creating collections, and static facade shorthand helper methods.
 *
 * The static "facade" methods are what makes this class special,
 * they allow you to quickly access the data collections.
 *
 * To use them retrieve a collection, call a facade method with the
 * name of the data collection subdirectory.
 *
 * All collections are indexed by their filename, which is relative
 * to the configured data collection source directory.
 */
class DataCollection extends Collection
{
    /**
     * The base directory for all data collections. Can be modified using a service provider.
     */
    public static string $sourceDirectory = 'resources/collections';

    /**
     * Get a collection of Markdown documents in the resources/collections/<$key> directory.
     *
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     *
     * @return DataCollection<string, \Hyde\Markdown\Models\MarkdownDocument>
     */
    public static function markdown(string $name): static
    {
        return static::discover($name, 'md', function (string $file): array {
            return [static::makeIdentifier($file) => MarkdownFileParser::parse($file)];
        });
    }

    /**
     * Get a collection of YAML documents in the resources/collections/<$key> directory.
     *
     * Each YAML file will be parsed into a FrontMatter object.
     *
     * @return DataCollection<string, \Hyde\Markdown\Models\FrontMatter>
     */
    public static function yaml(string $name): static
    {
        return static::discover($name, ['yaml', 'yml'], function (string $file): array {
            $content = Filesystem::getContents($file);
            $content = Str::between($content, '---', '---');

            $parsed = Yaml::parse($content) ?: [];
            $matter = new FrontMatter($parsed);

            return [static::makeIdentifier($file) => $matter];
        });
    }

    /**
     * Get a collection of JSON documents in the resources/collections/<$key> directory.
     *
     * Each JSON file will be parsed into a stdClass object, or an associative array, depending on the second parameter.
     *
     * @return DataCollection<string, \stdClass|array>
     */
    public static function json(string $name, bool $asArray = false): static
    {
        return static::discover($name, 'json', function (string $file) use ($asArray): array {
            return [static::makeIdentifier($file) => json_decode(Filesystem::getContents($file), $asArray)];
        });
    }

    protected static function discover(string $name, array|string $extensions, callable $callback): static
    {
        return new static(static::findFiles($name, $extensions)->mapWithKeys($callback));
    }

    protected static function findFiles(string $name, array|string $extensions): Collection
    {
        return Filesystem::smartGlob(sprintf('%s/%s/*.{%s}',
            static::$sourceDirectory, $name, implode(',', (array) $extensions)
        ), GLOB_BRACE);
    }

    protected static function makeIdentifier(string $path): string
    {
        return unslash(Str::after($path, static::$sourceDirectory));
    }
}
