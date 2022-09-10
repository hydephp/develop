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
 * To ensure compatibility with the Hyde Framework, all page models should extend this class.
 * Markdown-based pages can extend the AbstractMarkdownPage class to get relevant helpers.
 *
 * Unlike other frameworks, in general you don't instantiate pages yourself in Hyde,
 * instead, the page models acts as blueprints defining information for Hyde to
 * know how to parse a file, and what data around it should be generated.
 *
 * To create a parsed file instance, you'd typically just create a source file,
 * and you can then access the parsed file from the HydeKernel's page index.
 * The source files are usually parsed by the SourceFileParser action.
 *
 * @see \Hyde\Framework\Concerns\AbstractMarkdownPage
 * @see \Hyde\Framework\Testing\Feature\HydePageTest
 */
abstract class HydePage implements CompilableContract, PageSchema
{
    use ConstructsPageSchemas;
    use Internal\HandlesPageFilesystem;

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
        $this->routeKey = static::routeKey($identifier);

        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->constructPageSchemas();
        $this->metadata = new Metadata($this);
    }

    /**
     * Parse a source file into a page model instance.
     *
     * @param  string  $identifier The identifier of the page to parse.
     * @return static New page model instance for the parsed source file.
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelParseHelperTest
     */
    public static function parse(string $identifier): HydePage
    {
        return (new SourceFileParser(static::class, $identifier))->get();
    }

    /**
     * Get an array of all the source file identifiers for the model.
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
     * @return \Hyde\Framework\Foundation\PageCollection<\Hyde\Framework\Concerns\HydePage
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelGetHelperTest
     */
    public static function all(): PageCollection
    {
        return Hyde::pages()->getPages(static::class);
    }

    /**
     * Format an identifier to a route key.
     *
     * @param  string  $identifier
     * @return string
     */
    public static function routeKey(string $identifier): string
    {
        return unslash(static::outputDirectory().'/'.$identifier);
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
     * Get the route key for the page.
     *
     * The route key is the URI path relative to the site root.
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
     * Get the route model for the page.
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
