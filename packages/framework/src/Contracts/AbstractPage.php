<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Concerns\HasPageMetadata;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Navigation\NavigationMenuItemContract;
use Hyde\Framework\Modules\Routing\Route;
use Hyde\Framework\Modules\Routing\RouteContract;
use Hyde\Framework\Services\CollectionService;
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
abstract class AbstractPage implements PageContract, NavigationMenuItemContract
{
    use HasPageMetadata;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $parserClass;

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

    /** @inheritDoc */
    final public static function getParserClass(): string
    {
        return static::$parserClass;
    }

    /** @inheritDoc */
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
    public function getRoute(): RouteContract
    {
        return new Route($this);
    }

    /** @inheritDoc */
    public function showInNavigation(): bool
    {
        if ($this instanceof MarkdownPost) {
            return false;
        }

        if ($this instanceof DocumentationPage) {
            return $this->slug === 'index';
        }

        if ($this instanceof MarkdownDocument) {
            if ($this->matter('navigation.hidden', false)) {
                return false;
            }
        }

        if (in_array($this->slug, config('hyde.navigation.exclude', [])))
        {
            return false;
        }

        return true;
    }

    /** @inheritDoc */
    public function navigationMenuPriority(): int
    {
        if ($this instanceof MarkdownDocument) {
            if ($this->matter('navigation.priority') !== null) {
                return $this->matter('navigation.priority');
            }
        }

        if (array_key_exists($this->slug, config('hyde.navigation.order', []))) {
            return (int) config('hyde.navigation.order.'.$this->slug);
        }

        if ($this->slug === 'index') {
            return 0;
        }

        if ($this->slug === 'posts') {
            return 10;
        }

        if ($this instanceof DocumentationPage) {
            return 100;
        }

        return 1000;
    }

    /** @inheritDoc */
    public function navigationMenuTitle(): string
    {
        if ($this instanceof MarkdownDocument) {
            if ($this->matter('navigation.title') !== null) {
                return $this->matter('navigation.title');
            }
        }

        if (isset($this->title) ) {
            return $this->title;
        }

        if ($this->slug === 'index') {
            return 'Home';
        }

        return Hyde::makeTitle($this->slug);
    }
}
