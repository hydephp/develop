<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Contracts\CompilableContract;
use Hyde\Framework\Contracts\FrontMatter\PageSchema;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Metadata\Metadata;
use Hyde\Framework\Models\Route;
use Hyde\Framework\Services\DiscoveryService;

/**
 * To ensure compatibility with the Hyde Framework, all Page Models should extend this class.
 * Markdown-based Pages can extend the AbstractMarkdownPage class to get relevant helpers.
 * To learn about what the methods do, see the PHPDocs in the PageContract.
 *
 * Unlike other frameworks, in general you don't instantiate pages yourself in Hyde,
 * instead, the page models acts as blueprints defining information for Hyde to
 * know how to parse a file, and what data around it should be generated.
 *
 * To create a parsed file instance, you'd typically just create a source file,
 * and you can then access the parsed file from the HydeKernel's page index.
 *
 * @see \Hyde\Framework\Contracts\PageContract
 * @see \Hyde\Framework\Concerns\AbstractMarkdownPage
 * @see \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractPage implements PageContract, CompilableContract, PageSchema
{
    use ConstructsPageSchemas;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $template;

    public string $identifier;
    public string $routeKey;

    public FrontMatter $matter;
    public Metadata $metadata;

    public string $title;
    public ?array $navigation = null;
    public ?string $canonicalUrl = null;

    public function __construct(string $identifier = '', FrontMatter|array $matter = [])
    {
        $this->identifier = $identifier;
        $this->routeKey = $this->getCurrentPagePath();

        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->constructPageSchemas();
        $this->metadata = new Metadata($this);
    }

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
    public static function parse(string $slug): PageContract
    {
        return (new SourceFileParser(static::class, $slug))->get();
    }

    /** @inheritDoc */
    public static function files(): array|false
    {
        return DiscoveryService::getSourceFileListForModel(static::class);
    }

    /** @inheritDoc */
    public static function all(): PageCollection
    {
        return Hyde::pages()->getPages(static::class);
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

    /** @inheritDoc */
    public function get(string $key = null, mixed $default = null): mixed
    {
        if ($key !== null && property_exists($this, $key) && isset($this->$key)) {
            return $this->$key;
        }

        return $this->matter($key, $default);
    }

    /** @inheritDoc */
    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $this->matter->get($key, $default);
    }

    /** @inheritDoc */
    public function has(string $key, bool $strict = false): bool
    {
        if ($strict) {
            return property_exists($this, $key) || $this->matter->has($key);
        }

        return ! blank($this->get($key));
    }

    /** @inheritDoc */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /** @inheritDoc */
    public function getSourcePath(): string
    {
        return static::qualifyBasename($this->identifier);
    }

    /** @inheritDoc */
    public function getOutputPath(): string
    {
        return $this->getCurrentPagePath().'.html';
    }

    /** @deprecated will be merged with route key  */
    public function getCurrentPagePath(): string
    {
        return trim(static::getOutputDirectory().'/'.$this->identifier, '/');
    }

    /** @inheritDoc */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /** @inheritDoc */
    public function getRoute(): Route
    {
        return new Route($this);
    }

    /** @inheritDoc */
    public function htmlTitle(): string
    {
        return config('site.name', 'HydePHP').' - '.$this->title;
    }

    /** @inheritDoc */
    public function getBladeView(): string
    {
        return static::$template;
    }

    /** @inheritDoc */
    abstract public function compile(): string;

    public function renderPageMetadata(): string
    {
        return $this->metadata->render();
    }

    public function showInNavigation(): bool
    {
        return ! $this->navigation['hidden'];
    }

    public function navigationMenuPriority(): int
    {
        return $this->navigation['priority'];
    }

    public function navigationMenuTitle(): string
    {
        return $this->navigation['title'];
    }
}
