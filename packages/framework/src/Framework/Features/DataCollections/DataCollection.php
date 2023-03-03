<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\DataCollections;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Facades\Filesystem;
use Illuminate\Support\Collection;

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

    public string $key;

    protected function __construct(array $items = [], string $key = null)
    {
        $this->key = $key;

        parent::__construct($items);
    }

    public static function create(string $key): static
    {
        return new static(key: $key);
    }

    public function getCollection(): static
    {
        return $this;
    }

    public function getMarkdownFiles(): array
    {
        return Filesystem::smartGlob(
            static::$sourceDirectory.'/'.$this->key.'/*.md'
        )->toArray();
    }

    /**
     * Get a collection of Markdown documents in the resources/collections/<$key> directory.
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     *
     * @param  string  $key  for a subdirectory of the resources/collections directory
     * @return DataCollection<\Hyde\Markdown\Models\MarkdownDocument>
     */
    public static function markdown(string $key): static
    {
        $collection = new DataCollection($key);
        foreach ($collection->getMarkdownFiles() as $file) {
            $collection->push(
                (new MarkdownFileParser($file))->get()
            );
        }

        return $collection->getCollection();
    }
}
