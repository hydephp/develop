<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Foundation\PageCollection;
use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Concerns\Internal\ConstructsPageSchemas;
use Hyde\Framework\Factories\Concerns\HasFactory;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Support\Contracts\CompilableContract;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;

/**
 * The base class for all Hyde pages.
 *
 * To ensure compatibility with the Hyde Framework, all page models should extend this class.
 * Markdown-based pages can extend the BaseMarkdownPage class to get relevant helpers.
 *
 * Unlike other frameworks, in general you don't instantiate pages yourself in Hyde,
 * instead, the page models acts as blueprints defining information for Hyde to
 * know how to parse a file, and what data around it should be generated.
 *
 * To create a parsed file instance, you'd typically just create a source file,
 * and you can then access the parsed file from the HydeKernel's page index.
 * The source files are usually parsed by the SourceFileParser action.
 *
 * @see \Hyde\Framework\Concerns\BaseMarkdownPage
 * @see \Hyde\Framework\Testing\Feature\HydePageTest
 */
abstract class HydePage implements CompilableContract, PageSchema
{
    use ConstructsPageSchemas;
    use HasFrontMatter;
    use HasFactory;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $template;

    public string $identifier;
    public string $routeKey;

    public FrontMatter $matter;
    public PageMetadataBag $metadata;

    public string $title;
    public ?string $canonicalUrl = null;
    public ?NavigationData $navigation = null;

    public static function make(string $identifier = '', FrontMatter|array $matter = []): static
    {
        return new static($identifier, $matter);
    }

    public function __construct(string $identifier = '', FrontMatter|array $matter = [])
    {
        $this->identifier = $identifier;
        $this->routeKey = RouteKey::fromPage(static::class, $identifier)->get();

        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->constructPageSchemas();
        $this->metadata = new PageMetadataBag($this);
    }

    // Section: Query

    /**
     * Parse a source file into a page model instance.
     *
     * @param  string  $identifier  The identifier of the page to parse.
     * @return static New page model instance for the parsed source file.
     */
    public static function parse(string $identifier): HydePage
    {
        return (new SourceFileParser(static::class, $identifier))->get();
    }

    /**
     * Get an array of all the source file identifiers for the model.
     *
     * Essentially an alias of DiscoveryService::getAbstractPageList().
     *
     * @return array<string>|false
     */
    public static function files(): array|false
    {
        return DiscoveryService::getSourceFileListForModel(static::class);
    }

    /**
     * Get a collection of all pages, parsed into page models.
     *
     * @return \Hyde\Foundation\PageCollection<\Hyde\Framework\Concerns\HydePage
     */
    public static function all(): PageCollection
    {
        return Hyde::pages()->getPages(static::class);
    }

    // Section: Filesystem

    /**
     * Get the directory in where source files are stored.
     */
    final public static function sourceDirectory(): string
    {
        return unslash(static::$sourceDirectory);
    }

    /**
     * Get the output subdirectory to store compiled HTML.
     */
    final public static function outputDirectory(): string
    {
        return unslash(static::$outputDirectory);
    }

    /**
     * Get the file extension of the source files.
     */
    final public static function fileExtension(): string
    {
        return '.'.ltrim(static::$fileExtension, '.');
    }

    /**
     * Qualify a page identifier into a local file path for the page source file relative to the project root.
     */
    public static function sourcePath(string $identifier): string
    {
        return static::sourceDirectory().'/'.unslash($identifier).static::fileExtension();
    }

    /**
     * Qualify a page identifier into a target output file path relative to the _site output directory.
     */
    public static function outputPath(string $identifier): string
    {
        return RouteKey::fromPage(static::class, $identifier).'.html';
    }

    /**
     * Get the path to the instance source file, relative to the project root.
     */
    public function getSourcePath(): string
    {
        return static::sourcePath($this->identifier);
    }

    /**
     * Get the path where the compiled page instance will be saved.
     */
    public function getOutputPath(): string
    {
        return static::outputPath($this->identifier);
    }

    // Section: Routing

    /**
     * Get the route key for the page.
     *
     * The route key is the URL path relative to the site root.
     *
     * For example, if the compiled page will be saved to _site/docs/index.html,
     * then this method will return 'docs/index'. Route keys are used to
     * identify pages, similar to how named routes work in Laravel.
     *
     * @return string The page's route key.
     */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /**
     * Get the route for the page.
     *
     * @return \Hyde\Support\Models\Route The page's route.
     */
    public function getRoute(): Route
    {
        return new Route($this);
    }

    /**
     * Format the page instance to a URL path (relative to site root) with support for pretty URLs if enabled.
     */
    public function getLink(): string
    {
        return Hyde::formatLink($this->getOutputPath());
    }

    // Section: Getters

    /**
     * Get the page model's identifier property.
     *
     * The identifier is the part between the source directory and the file extension.
     * It may also be known as a 'slug', or previously 'basename'.
     *
     * For example, the identifier of a source file stored as '_pages/about/contact.md'
     * would be 'about/contact', and 'pages/about.md' would simply be 'about'.
     *
     * @return string The page's identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the Blade template key for the page.
     */
    public function getBladeView(): string
    {
        return static::$template;
    }

    // Section: Accessors

    /**
     * Get the page title to display in HTML tags like <title> and <meta> tags.
     */
    public function htmlTitle(): string
    {
        return config('site.name', 'HydePHP').' - '.$this->title;
    }

    public function metadata(): PageMetadataBag
    {
        return $this->metadata;
    }

    public function showInNavigation(): bool
    {
        return ! $this->navigation['hidden'];
    }

    public function navigationMenuPriority(): int
    {
        return $this->navigation['priority'];
    }

    public function navigationMenuLabel(): string
    {
        return $this->navigation['label'];
    }

    public function navigationMenuGroup(): ?string
    {
        return $this->navigation['group'];
    }
}
