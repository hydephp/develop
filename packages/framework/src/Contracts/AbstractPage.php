<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Concerns\HasPageMetadata;
use Hyde\Framework\Modules\Router\Concerns\RoutableContract;
use Hyde\Framework\Services\CollectionService;
use Illuminate\Support\Collection;

/**
 * To ensure compatability with the Hyde Framework,
 * all Page Models must extend this class.
 *
 * Markdown-based Pages should extend MarkdownDocument.
 */
abstract class AbstractPage implements PageContract, RoutableContract
{
    use HasPageMetadata;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $parserClass;

    public string $slug;

    public function getCurrentPagePath(): string
    {
        return $this->slug;
    }

    public static function all(): Collection
    {
        $collection = new Collection();

        foreach (CollectionService::getSourceFileListForModel(static::class) as $filepath) {
            $collection->push((new static::$parserClass(basename($filepath, static::$fileExtension)))->get());
        }

        return $collection;
    }

    public static function files(): array
    {
        return CollectionService::getSourceFileListForModel(static::class);
    }

    public static function parse(string $slug): AbstractPage
    {
        return (new static::$parserClass($slug))->get();
    }

    public static function getRouteSourcePath(): string
    {
        return static::$sourceDirectory;
    }

    public static function getRouteOutputPath(): string
    {
        return static::$outputDirectory;
    }
}
