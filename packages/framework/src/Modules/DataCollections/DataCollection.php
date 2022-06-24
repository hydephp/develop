<?php

namespace Hyde\Framework\Modules\DataCollections;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\MarkdownFileService;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Provides a facade for generating on-the-fly Laravel Collections
 * from static data files such as Markdown components and YAML files.
 */
class DataCollection
{
    /**
     * Get a collection of Markdown documents in the _data/<$key> directory.
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     */
    public static function markdown(string $key): BaseCollection
    {
        $files = glob(Hyde::path('_data/' . $key . '/*.md'));
        $collection = new BaseCollection();
        foreach ($files as $file) {
            $collection->push(
                (new MarkdownFileService($file))->get()
            );
        }
        return $collection;
    }
}
