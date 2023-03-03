<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\MarkdownFileParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Automatically generates Laravel Collections from static data files,
 * such as Markdown components and YAML files using Hyde Autodiscovery.
 *
 * This class acts both as a base collection class, a factory for
 * creating collections, and static facade shorthand helper methods.
 *
 * All collections are keyed by their filename which is relative
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
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     *
     * @param  string  $key  for a subdirectory of the resources/collections directory
     * @return DataCollection<string, \Hyde\Markdown\Models\MarkdownDocument>
     *
     * @example `Usage: DataCollection::markdown('cards')`
     * @example `Returns: ['cards/card-1.md' => MarkdownDocument, etc...]` (assuming card-1.md exists as resources/collections/cards/card-1.md)
     */
    public static function markdown(string $key): static
    {
        return new static(static::findMarkdownFiles($key)->mapWithKeys(function (string $file): array {
            return [static::makeIdentifier($file) => (new MarkdownFileParser($file))->get()];
        }));
    }

    protected static function findMarkdownFiles(string $key): Collection
    {
        return Filesystem::smartGlob(static::$sourceDirectory."/$key/*.md");
    }

    protected static function makeIdentifier(string $path): string
    {
        return unslash(Str::after($path, static::$sourceDirectory));
    }
}
