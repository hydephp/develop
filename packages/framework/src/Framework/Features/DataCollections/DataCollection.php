<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\DataCollections;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Facades\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Automatically generates Laravel Collections from static data files,
 * such as Markdown components and YAML files using Hyde Autodiscovery.
 *
 * This class acts both as a base collection class, a factory for
 * creating collections, and static facade shorthand helper methods.
 */
class DataCollection extends Collection
{
    public static string $sourceDirectory = 'resources/collections';

    /**
     * Get a collection of Markdown documents in the resources/collections/<$key> directory.
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     *
     * @param  string  $key  for a subdirectory of the resources/collections directory
     * @return DataCollection<string, \Hyde\Markdown\Models\MarkdownDocument> Collection is keyed by filename relative to the source directory.
     *
     * @example `Usage: DataCollection::markdown('cards')`
     * @example `Returns: ['cards/card-1.md' => MarkdownDocument, etc...]` (assuming card-1.md exists as resources/collections/cards/card-1.md)
     */
    public static function markdown(string $key): static
    {
        return new DataCollection(static::findMarkdownFiles($key)->mapWithKeys(function (string $file): array {
            return [unslash(Str::after($file, static::$sourceDirectory)) => (new MarkdownFileParser($file))->get()];
        }));
    }

    protected static function findMarkdownFiles(string $key): Collection
    {
        return Filesystem::smartGlob(static::$sourceDirectory."/$key/*.md");
    }
}
