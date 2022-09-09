<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Contracts\CompilableContract;
use Hyde\Framework\Contracts\FrontMatter\PageSchema;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Metadata\Metadata;
use Hyde\Framework\Models\Route;
use Hyde\Framework\Services\DiscoveryService;

/**
 * To ensure compatibility with the Hyde Framework, all Page Models should extend this class.
 * Markdown-based Pages can extend the AbstractMarkdownPage class to get relevant helpers.
 * To learn about what the methods do, see the PHPDocs in the AbstractPage.
 *
 * Unlike other frameworks, in general you don't instantiate pages yourself in Hyde,
 * instead, the page models acts as blueprints defining information for Hyde to
 * know how to parse a file, and what data around it should be generated.
 *
 * To create a parsed file instance, you'd typically just create a source file,
 * and you can then access the parsed file from the HydeKernel's page index.
 *
 * @see \Hyde\Framework\Concerns\AbstractPage
 * @see \Hyde\Framework\Concerns\AbstractMarkdownPage
 * @see \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractPage implements CompilableContract, PageSchema
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
        $this->routeKey = trim(static::getOutputDirectory().'/'.$this->identifier, '/');

        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->constructPageSchemas();
        $this->metadata = new Metadata($this);
    }

    /**
     * Get the directory in where source files are stored.
     *
     * @return string Path relative to the root of the project
     */
    final public static function getSourceDirectory(): string
    {
        return unslash(static::$sourceDirectory);
    }

    /**
     * Get the output subdirectory to store compiled HTML.
     *
     * @return string Relative to the site output directory.
     */
    final public static function getOutputDirectory(): string
    {
        return unslash(static::$outputDirectory);
    }

    /**
     * Get the file extension of the source files.
     *
     * @return string (e.g. ".md")
     */
    final public static function getFileExtension(): string
    {
        return '.'.ltrim(static::$fileExtension, '.');
    }

    /**
     * Parse a source file slug into a page model.
     *
     * @param  string  $slug
     * @return static New page model instance for the parsed source file.
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelParseHelperTest
     */
    public static function parse(string $slug): AbstractPage
    {
        return (new SourceFileParser(static::class, $slug))->get();
    }

    /**
     * Get an array of all the source file slugs for the model.
     * Essentially an alias of DiscoveryService::getAbstractPageList().
     *
     * @return array<string>|false
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelGetAllFilesHelperTest
     */
    public static function files(): array|false
    {
        return DiscoveryService::getSourceFileListForModel(static::class);
    }

    /**
     * Get a collection of all pages, parsed into page models.
     *
     * @return \Hyde\Framework\Foundation\PageCollection<\Hyde\Framework\Concerns\AbstractPage
     *
     * @since v0.59.0-beta the returned collection is a PageCollection, and now includes the source file path as the array key
     * @see \Hyde\Framework\Testing\Unit\PageModelGetHelperTest
     */
    public static function all(): PageCollection
    {
        return Hyde::pages()->getPages(static::class);
    }

    /**
     * Qualify a page basename into a referenceable file path.
     *
     * @param  string  $basename  for the page model source file.
     * @return string path to the file relative to project root
     */
    public static function qualifyBasename(string $basename): string
    {
        return static::getSourceDirectory().'/'.unslash($basename).static::getFileExtension();
    }

    /**
     * Get the proper site output path for a page model.
     *
     * @param  string  $basename  for the page model source file.
     * @return string of the output file relative to the site output directory.
     *
     * @example DocumentationPage::getOutputPath('index') => 'docs/index.html'
     */
    public static function getOutputLocation(string $basename): string
    {
        // Using the trim function we ensure we don't have a leading slash when the output directory is the root directory.
        return trim(
            static::getOutputDirectory().'/'.unslash($basename),
            '/'
        ).'.html';
    }

    /**
     * Get a value from the computed page data, or fallback to the page's front matter, then to the default value.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        if ($key !== null && property_exists($this, $key) && isset($this->$key)) {
            return $this->$key;
        }

        return $this->matter($key, $default);
    }

    /**
     * Get the front matter object, or a value from within.
     *
     * @return \Hyde\Framework\Models\FrontMatter|mixed
     */
    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $this->matter->get($key, $default);
    }

    /**
     * See if a value exists in the computed page data or the front matter.
     *
     * @param  string  $key
     * @param  bool  $strict  When set to true, an additional check if the property is not blank is performed.
     * @return bool
     */
    public function has(string $key, bool $strict = false): bool
    {
        if ($strict) {
            return property_exists($this, $key) || $this->matter->has($key);
        }

        return ! blank($this->get($key));
    }

    /**
     * Get the page model's identifier property.
     *
     * @return string The page's identifier/slug.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the path to the source file, relative to the project root.
     *
     * @return string Path relative to the project root.
     */
    public function getSourcePath(): string
    {
        return static::qualifyBasename($this->identifier);
    }

    /**
     * Get the path where the compiled page will be saved.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputPath(): string
    {
        return $this->getRouteKey().'.html';
    }

    /**
     * Get the route key for the page.
     *
     * The route key is the URI path relative to the site root.
     *
     * For example, if the compiled page will be saved to _site/docs/index.html,
     * then this method will return 'docs/index'. Route keys are used to
     * identify pages, similar to how named routes work in Laravel.
     *
     * @return string URI path relative to the site root.
     */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /**
     * Get the route for the page.
     *
     * @return \Hyde\Framework\Contracts\RouteContract
     */
    public function getRoute(): RouteContract
    {
        return new Route($this);
    }

    /**
     * Get the page title to display in the <head> section's <title> tag.
     *
     * @return string Example: "Site Name - Page Title"
     */
    public function htmlTitle(): string
    {
        return config('site.name', 'HydePHP').' - '.$this->title;
    }

    /** @inheritDoc */
    public function getBladeView(): string
    {
        return static::$template;
    }

    /**
     * Compile the page into static HTML.
     *
     * @return string The compiled HTML for the page.
     */
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
