<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Concerns\HasPageMetadata;
use Hyde\Framework\Services\CollectionService;
use Illuminate\Support\Collection;

/**
 * To ensure compatibility with the Hyde Framework, all Page Models should extend this class.
 *
 * Markdown-based Pages can extend the MarkdownDocument class to get relevant helpers.
 *
 * To learn about what the methods do, see the PHPDocs in the PageContract.
 *
 * @see \Hyde\Framework\Contracts\PageContract
 */
abstract class AbstractPage implements PageContract
{
    use HasPageMetadata;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $parserClass;

    /** @inheritDoc */
    final public static function getSourceDirectory(): string
    {
        return trim(static::$sourceDirectory, '\\/');
    }

    /** @inheritDoc */
    final public static function getOutputDirectory(): string
    {
        return trim(static::$outputDirectory, '\\/');
    }

    /** @inheritDoc */
    final public static function getFileExtension(): string
    {
        return '.'.ltrim(static::$fileExtension, '.');
    }

    /** @inheritDoc */
    final public static function getParserClass(): string
    {
        return static::$parserClass;
    }

    /**
     * @inheritDoc
     */
    public static function getParser(string $slug): PageParserContract
    {
        return new static::$parserClass($slug);
    }

    /** @inheritDoc */
    public static function parse(string $slug): static
    {
        return (new static::$parserClass($slug))->get();
    }

    /** @inheritDoc */
    public static function files(): array
    {
        return CollectionService::getSourceFileListForModel(static::class);
    }

    /** @inheritDoc */
    public static function all(): Collection
    {
        $collection = new Collection();

        foreach (static::files() as $basename) {
            $collection->push(static::parse($basename));
        }

        return $collection;
    }

    /** @inheritDoc */
    public static function qualifyBasename(string $basename): string
    {
        return static::getSourceDirectory().'/'.trim($basename, '\\/').static::getFileExtension();
    }

    /** @inheritDoc */
    public static function getOutputLocation(string $basename): string
    {
        // Using the trim function we ensure we don't have a leading slash when the output directory is the root directory.
        return trim(
            static::getOutputDirectory().'/'.trim($basename, '\\/'), '/'
        ).'.html';
    }

    public string $slug;

    /** @inheritDoc */
    public function getCurrentPagePath(): string
    {
        return trim(static::getOutputDirectory().'/'.$this->slug, '/');
    }

    /** @inheritDoc */
    public function getOutputPath(): string
    {
        return static::getCurrentPagePath().'.html';
    }
}
