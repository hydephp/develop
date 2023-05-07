<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Foundation\Facades;
use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Facades\Pages;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Factories\Concerns\HasFactory;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Markdown\Contracts\FrontMatter\PageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
use Illuminate\Support\Str;

use function unslash;
use function filled;
use function ltrim;
use function rtrim;

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
 * In Blade views, you can always access the current page instance being rendered using the $page variable.
 *
 * @see \Hyde\Pages\Concerns\BaseMarkdownPage
 */
abstract class HydePage implements PageSchema, SerializableContract
{
    use InteractsWithFrontMatter;
    use Serializable;
    use HasFactory;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $template;

    public readonly string $identifier;
    public readonly string $routeKey;
    public readonly string $title;

    public FrontMatter $matter;
    public PageMetadataBag $metadata;
    public NavigationData $navigation;

    /**
     * Create a new page instance. Static alias for the constructor.
     */
    public static function make(string $identifier = '', FrontMatter|array $matter = []): static
    {
        return new static($identifier, $matter);
    }

    /**
     * Construct a new page instance.
     */
    public function __construct(string $identifier = '', FrontMatter|array $matter = [])
    {
        $this->identifier = $identifier;
        $this->routeKey = RouteKey::fromPage(static::class, $identifier)->get();
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);

        $this->constructFactoryData();
        $this->constructMetadata();
    }

    // Section: State

    /**
     * Returns whether the page type is discoverable through auto-discovery.
     */
    public static function isDiscoverable(): bool
    {
        return isset(static::$sourceDirectory, static::$outputDirectory, static::$fileExtension) && filled(static::$sourceDirectory);
    }

    // Section: Query

    /**
     * Get a page instance from the Kernel's page index by its identifier.
     *
     * @throws \Hyde\Framework\Exceptions\FileNotFoundException If the page does not exist.
     */
    public static function get(string $identifier): static
    {
        return Pages::getPage(static::sourcePath($identifier));
    }

    /**
     * Parse a source file into a new page model instance.
     *
     * @param  string  $identifier  The identifier of the page to parse.
     * @return static New page model instance for the parsed source file.
     *
     * @throws \Hyde\Framework\Exceptions\FileNotFoundException If the file does not exist.
     */
    public static function parse(string $identifier): static
    {
        return (new SourceFileParser(static::class, $identifier))->get();
    }

    /**
     * Get an array of all the source file identifiers for the model.
     *
     * Note that the values do not include the source directory or file extension.
     *
     * @return array<string>
     */
    public static function files(): array
    {
        return Files::getFiles(static::class)->map(function (SourceFile $file): string {
            return static::pathToIdentifier($file->getPath());
        })->values()->toArray();
    }

    /**
     * Get a collection of all pages, parsed into page models.
     *
     * @return \Hyde\Foundation\Kernel\PageCollection<static>
     */
    public static function all(): PageCollection
    {
        return Facades\Pages::getPages(static::class);
    }

    // Section: Filesystem

    /**
     * Get the directory where source files are stored for the page type.
     */
    public static function sourceDirectory(): string
    {
        return static::$sourceDirectory ?? Hyde::getSourceRoot();
    }

    /**
     * Get the output subdirectory to store compiled HTML files for the page type.
     */
    public static function outputDirectory(): string
    {
        return static::$outputDirectory ?? '';
    }

    /**
     * Get the file extension of the source files for the page type.
     */
    public static function fileExtension(): string
    {
        return static::$fileExtension ?? '';
    }

    /**
     * Set the output directory for the page type.
     */
    public static function setSourceDirectory(string $sourceDirectory): void
    {
        static::$sourceDirectory = unslash($sourceDirectory);
    }

    /**
     * Set the source directory for the page type.
     */
    public static function setOutputDirectory(string $outputDirectory): void
    {
        static::$outputDirectory = unslash($outputDirectory);
    }

    /**
     * Set the file extension for the page type.
     */
    public static function setFileExtension(string $fileExtension): void
    {
        static::$fileExtension = rtrim('.'.ltrim($fileExtension, '.'), '.');
    }

    /**
     * Qualify a page identifier into file path to the source file, relative to the project root.
     */
    public static function sourcePath(string $identifier): string
    {
        return unslash(static::sourceDirectory().'/'.unslash($identifier).static::fileExtension());
    }

    /**
     * Qualify a page identifier into a target output file path, relative to the _site output directory.
     */
    public static function outputPath(string $identifier): string
    {
        return RouteKey::fromPage(static::class, $identifier).'.html';
    }

    /**
     * Get an absolute file path to the page's source directory, or a file within it.
     */
    public static function path(string $path = ''): string
    {
        return Hyde::path(unslash(static::sourceDirectory().'/'.unslash($path)));
    }

    /**
     * Format a filename to an identifier for a given model. Unlike the basename function, any nested paths
     * within the source directory are retained in order to satisfy the page identifier definition.
     *
     * @param  string  $path  Example: index.blade.php
     * @return string Example: index
     */
    public static function pathToIdentifier(string $path): string
    {
        return unslash(Str::between(Hyde::pathToRelative($path),
            static::sourceDirectory().'/',
            static::fileExtension())
        );
    }

    /**
     * Get the route key base for the page model.
     *
     * This is the same value as the output directory.
     */
    public static function baseRouteKey(): string
    {
        return static::outputDirectory();
    }

    /**
     * Compile the page into static HTML.
     *
     * @return string The compiled HTML for the page.
     */
    abstract public function compile(): string;

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'class' => static::class,
            'identifier' => $this->identifier,
            'routeKey' => $this->routeKey,
            'matter' => $this->matter,
            'metadata' => $this->metadata,
            'navigation' => $this->navigation,
            'title' => $this->title,
        ];
    }

    /**
     * Get the path to the instance source file, relative to the project root.
     */
    public function getSourcePath(): string
    {
        return unslash(static::sourcePath($this->identifier));
    }

    /**
     * Get the path where the compiled page will be saved.
     *
     * @return string Path relative to the site output directory.
     */
    public function getOutputPath(): string
    {
        return unslash(static::outputPath($this->identifier));
    }

    // Section: Routing

    /**
     * Get the route key for the page.
     *
     * The route key is the page URL path, relative to the site root, but without any file extensions.
     * For example, if the page will be saved to `_site/docs/index.html`, the key is `docs/index`.
     *
     * Route keys are used to identify page routes, similar to how named routes work in Laravel,
     * only that here the name is not just arbitrary, but also defines the output location,
     * as the route key is used to determine the output path which is `$routeKey.html`.
     */
    public function getRouteKey(): string
    {
        return $this->routeKey;
    }

    /**
     * Get the route object for the page.
     */
    public function getRoute(): Route
    {
        return Routes::get($this->getRouteKey()) ?? new Route($this);
    }

    /**
     * Format the page instance to a URL path, with support for pretty URLs if enabled.
     *
     * Note that the link is always relative to site root, and does not contain `../` segments.
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
     * It may also be known as a 'slug', or previously 'basename', but it retains
     * the nested path structure if the page is stored in a subdirectory.
     *
     * For example, the identifier of a source file stored as '_pages/about/contact.md'
     * would be 'about/contact', and 'pages/about.md' would simply be 'about'.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the Blade template/view key for the page.
     */
    public function getBladeView(): string
    {
        return static::$template;
    }

    // Section: Accessors

    /**
     * Get the page title to display in HTML tags like `<title>` and `<meta>` tags.
     */
    public function title(): string
    {
        return Config::getString('hyde.name', 'HydePHP').' - '.$this->title;
    }

    /**
     * Get the generated metadata for the page.
     */
    public function metadata(): PageMetadataBag
    {
        return $this->metadata;
    }

    /**
     * Can the page be shown in the navigation menu?
     */
    public function showInNavigation(): bool
    {
        return ! $this->navigation->hidden;
    }

    /**
     * Get the priority of the page in the navigation menu.
     */
    public function navigationMenuPriority(): int
    {
        return $this->navigation->priority;
    }

    /**
     * Get the label of the page in the navigation menu.
     */
    public function navigationMenuLabel(): string
    {
        return $this->navigation->label;
    }

    /**
     * Get the group of the page in the navigation menu, if any.
     */
    public function navigationMenuGroup(): ?string
    {
        return $this->navigation->group;
    }

    public function getCanonicalUrl(): ?string
    {
        if (! empty($this->matter('canonicalUrl'))) {
            return $this->matter('canonicalUrl');
        }

        if (Hyde::hasSiteUrl() && ! empty($this->identifier)) {
            return Hyde::url($this->getOutputPath());
        }

        return null;
    }

    protected function constructMetadata(): void
    {
        $this->metadata = new PageMetadataBag($this);
    }
}
