<?php

declare(strict_types=1);

namespace Hyde\Support;

use stdClass;
use Hyde\Facades\Filesystem;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Framework\Actions\MarkdownFileParser;
use Symfony\Component\Yaml\Exception\ParseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function trim;
use function blank;
use function rtrim;
use function implode;
use function json_decode;
use function sprintf;
use function unslash;
use function json_last_error_msg;

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
     * @return static<string, \Hyde\Markdown\Models\MarkdownDocument>
     */
    public static function markdown(string $name): static
    {
        return static::discover($name, 'md', static::parseMarkdownFile(...));
    }

    /**
     * Get a collection of YAML documents in the resources/collections/<$key> directory.
     *
     * Each YAML file will be parsed into a FrontMatter object.
     *
     * @return static<string, \Hyde\Markdown\Models\FrontMatter>
     */
    public static function yaml(string $name): static
    {
        return static::discover($name, ['yaml', 'yml'], static::parseYamlFile(...));
    }

    /**
     * Get a collection of JSON documents in the resources/collections/<$key> directory.
     *
     * Each JSON file will be parsed into a stdClass object, or an associative array, depending on the second parameter.
     *
     * @return static<string, \stdClass|array>
     */
    public static function json(string $name, bool $asArray = false): static
    {
        return static::discover($name, 'json', static::parseJsonFile(...), [$asArray]);
    }

    /**
     * @param  array<string>|string  $extensions
     * @param  callable(string): mixed  $parseUsing
     * @return static<string, mixed>
     */
    protected static function discover(string $name, array|string $extensions, callable $parseUsing, array $extraArgs = []): static
    {
        return new static(static::findFiles($name, $extensions)->mapWithKeys(function (string $file) use ($parseUsing, $extraArgs): array {
            return [static::makeIdentifier($file) => $parseUsing($file, ...$extraArgs)];
        }));
    }

    /**
     * @param  array<string>|string  $extensions
     * @return Collection<string>
     */
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

    protected static function parseMarkdownFile(string $file): MarkdownDocument
    {
        return MarkdownFileParser::parse($file);
    }

    /** @throws InvalidArgumentException If the YAML is invalid and cannot be parsed. */
    protected static function parseYamlFile(string $file): FrontMatter
    {
        $content = Filesystem::getContents($file);
        $content = Str::between($content, '---', '---');

        try {
            if (blank(trim($content))) {
                // We throw an exception here in order to match the behavior of the JSON validation.
                throw new ParseException('File is empty');
            }

            return new FrontMatter(Yaml::parse($content));
        } catch (ParseException $exception) {
            throw new InvalidArgumentException(sprintf("Invalid YAML in file: '%s' (%s)", $file, rtrim($exception->getMessage(), '.')), previous: $exception);
        }
    }

    /** @throws InvalidArgumentException If the JSON is invalid and cannot be parsed. */
    protected static function parseJsonFile(string $file, bool $asArray): stdClass|array
    {
        $contents = Filesystem::getContents($file);

        if (! json_validate($contents)) {
            throw new InvalidArgumentException(sprintf("Invalid JSON in file: '%s' (%s)", $file, json_last_error_msg()));
        }

        return json_decode($contents, $asArray);
    }
}
