<?php

namespace Hyde\Framework\Modules\DataCollections;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Framework\Services\MarkdownFileService;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Provides a facade for generating on-the-fly Laravel Collections
 * from static data files such as Markdown components and YAML files.
 */
class DataCollection
{
    public static string $dataPath = '_data';

    /**
     * Get a collection of Markdown documents in the _data/<$key> directory.
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     */
    public static function markdown(string $key): BaseCollection
    {
        $time_start = microtime(true);
        $collection = new BaseCollection();
        $collection->key = $key;
        $collection->name = Hyde::titleFromSlug($key);
        foreach (glob(Hyde::path(static::$dataPath . '/' . $key . '/*' . MarkdownDocument::$fileExtension)) as $file) {
            $collection->push(
                (new MarkdownFileService($file))->get()
            );
        }
        $collection->parseTimeInMs = static::getExecutionTime($time_start);
        return $collection;
    }

    protected static function getExecutionTime(float $time_start): float
    {
        return round((microtime(true) - $time_start) * 1000, 2);
    }
}
