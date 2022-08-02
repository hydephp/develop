<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Concerns\CanBeInNavigation;
use Hyde\Framework\Concerns\HasPageMetadata;
use Hyde\Framework\Models\Route;
use Hyde\Framework\Services\DiscoveryService;
use Illuminate\Support\Collection;

/**
 * To ensure compatibility with the Hyde Framework, all Page Models should extend this class.
 *
 * Markdown-based Pages can extend the AbstractMarkdownPage class to get relevant helpers.
 *
 * To learn about what the methods do, see the PHPDocs in the PageContract.
 *
 * @see \Hyde\Framework\Contracts\PageContract
 * @see \Hyde\Framework\Contracts\AbstractMarkdownPage
 * @test \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractPage implements PageContract, CompilableContract
{
    use HasPageMetadata;
    use CanBeInNavigation;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;

    /**
     * @deprecated
     */
    public static string $parserClass;
    public static string $template;

    /** @inheritDoc */
    final public static function getSourceDirectory(): string
    {
        return unslash(static::$sourceDirectory);
    }

    /** @inheritDoc */
    final public static function getOutputDirectory(): string
    {
        return unslash(static::$outputDirectory);
    }

    /** @inheritDoc */
    final public static function getFileExtension(): string
    {
        return '.'.ltrim(static::$fileExtension, '.');
    }

    /** @deprecated */
    final public static function getParserClass(): string
    {
        return static::$parserClass;
    }

    /** @deprecated */
    public static function getParser(string $slug): PageParserContract
    {
        return new static::$parserClass($slug);
    }

    /** @inheritDoc */
    public static function parse(string $slug): static
    {
        return (new SourceFileParser(static::class, $slug))->get();
    }

    /** @inheritDoc */
    public static function files(): array|false
    {
        return DiscoveryService::getSourceFileListForModel(static::class);
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
        return static::getSourceDirectory().'/'.unslash($basename).static::getFileExtension();
    }

    /** @inheritDoc */
    public static function getOutputLocation(string $basename): string
    {
        // Using the trim function we ensure we don't have a leading slash when the output directory is the root directory.
        return trim(
            static::getOutputDirectory().'/'.unslash($basename),
            '/'
        ).'.html';
    }

    public string $slug;

    /** @inheritDoc */
    public function getSourcePath(): string
    {
        return static::qualifyBasename($this->slug);
    }

    /** @inheritDoc */
    public function getOutputPath(): string
    {
        return static::getCurrentPagePath().'.html';
    }

    /** @inheritDoc */
    public function getCurrentPagePath(): string
    {
        return trim(static::getOutputDirectory().'/'.$this->slug, '/');
    }

    /** @inheritDoc */
    public function getRoute(): Route
    {
        return new Route($this);
    }

    /** @inheritDoc */
    public function htmlTitle(?string $title = null): string
    {
        $pageTitle = $title ?? $this->title ?? null;

        return $pageTitle
            ? config('site.name', 'HydePHP').' - '.$pageTitle
            : config('site.name', 'HydePHP');
    }

    /** @inheritDoc */
    public function getBladeView(): string
    {
        return static::$template;
    }

    /** @inheritDoc */
    abstract public function compile(): string;
}
