<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Concerns\HasPageMetadata;
use Hyde\Framework\Services\CollectionService;
use Illuminate\Support\Collection;

/**
 * To ensure compatability with the Hyde Framework,
 * all Page Models must extend this class.
 *
 * Markdown-based Pages should extend MarkdownDocument.
 */
abstract class AbstractPage implements PageContract
{
    use HasPageMetadata;

    /**
     * The directory in where source files are stored.
     * Relative to the root of the project.
     */
    public static string $sourceDirectory;

    /**
     * The output subdirectory to store compiled HTML.
     * Relative to the site output directory.
     */
    public static string $outputDirectory;

    /**
     * The file extension of the source file (e.g. ".md").
     */
    public static string $fileExtension;

    /**
     * The class that parses source files into page models.
     * @var string<\Hyde\Framework\Contracts\PageParserContract>
     */
    public static string $parserClass;

    final public static function getSourceDirectory(): string
    {
        return static::$sourceDirectory;
    }

    final public static function getOutputDirectory(): string
    {
        return static::$outputDirectory;
    }

    final public static function getFileExtension(): string
    {
        return static::$fileExtension;
    }

    final public static function getParserClass(): string
    {
        return static::$parserClass;
    }


    /** @inheritDoc */
    public static function all(): Collection
    {
        $collection = new Collection();

        foreach (CollectionService::getSourceFileListForModel(static::class) as $filepath) {
            $collection->push((new static::$parserClass(basename($filepath, static::$fileExtension)))->get());
        }

        return $collection;
    }

    /** @inheritDoc */
    public static function files(): array
    {
        return CollectionService::getSourceFileListForModel(static::class);
    }

    /** @inheritDoc */
    public static function parse(string $slug): AbstractPage
    {
        return (new static::$parserClass($slug))->get();
    }


    public string $slug;

    public function getCurrentPagePath(): string
    {
        return $this->slug;
    }
}
