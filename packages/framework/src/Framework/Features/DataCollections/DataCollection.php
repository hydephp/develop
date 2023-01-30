<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\DataCollections;

use Hyde\Framework\Actions\MarkdownFileParser;
use Hyde\Hyde;
use Illuminate\Support\Collection;

/**
 * Automatically generates Laravel Collections from static data files,
 * such as Markdown components and YAML files using Hyde Autodiscovery.
 *
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class DataCollection extends Collection
{
    public string $key;

    public static string $sourceDirectory = 'resources/collections';

    public function __construct(string $key)
    {
        $this->key = $key;

        parent::__construct();
    }

    public function getCollection(): static
    {
        return $this;
    }

    public function getMarkdownFiles(): array
    {
        return glob(Hyde::path(
            static::$sourceDirectory.'/'.$this->key.'/*.md'
        ));
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
                (new MarkdownFileParser(Hyde::pathToRelative($file)))->get()
            );
        }

        return $collection->getCollection();
    }
}
