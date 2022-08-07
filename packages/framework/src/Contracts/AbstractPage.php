<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Concerns\FrontMatter\Schemas\PageSchema;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Route;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\Services\RssFeedService;
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
 * @see \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractPage implements PageContract, CompilableContract
{
    use PageSchema;

    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;
    public static string $template;

    public string $identifier;
    public FrontMatter $matter;

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

    public function __construct(string $identifier = '', FrontMatter|array $matter = [])
    {
        $this->identifier = $identifier;
        $this->matter = $matter instanceof FrontMatter ? $matter : new FrontMatter($matter);
        $this->constructPageSchema();
    }

    /** @inheritDoc */
    public function get(string $key = null, mixed $default = null): mixed
    {
        if (property_exists($this, $key) && isset($this->$key)) {
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
        return static::getCurrentPagePath().'.html';
    }

    /** @inheritDoc */
    public function getCurrentPagePath(): string
    {
        return trim(static::getOutputDirectory().'/'.$this->identifier, '/');
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

    /**
     * @return string[]
     *
     * @psalm-return list<string>
     */
    public function getDynamicMetadata(): array
    {
        $array = [];

        if ($this->canonicalUrl) {
            $array[] = Meta::link('canonical', $this->canonicalUrl);
        }

        if (Features::sitemap()) {
            $array[] = Meta::link('sitemap', Hyde::url('sitemap.xml'), [
                'type' => 'application/xml', 'title' => 'Sitemap',
            ]);
        }

        if (Features::rss()) {
            $array[] = Meta::link('alternate', Hyde::url(RssFeedService::getDefaultOutputFilename()), [
                'type' => 'application/rss+xml', 'title' => RssFeedService::getDescription(),
            ]);
        }

        if (! empty($this->title)) {
            $array[] = Meta::name('twitter:title', $this->htmlTitle());
            $array[] = Meta::property('title', $this->htmlTitle());
        }

        if ($this instanceof MarkdownPost) {
            $array[] = "\n<!-- Blog Post Meta Tags -->";
            foreach ($this->getMetadata() as $name => $content) {
                $array[] = Meta::name($name, $content);
            }
            foreach ($this->getMetaProperties() as $property => $content) {
                $array[] = Meta::property($property, $content);
            }
        }

        return $array;
    }

    public function renderPageMetadata(): string
    {
        return Meta::render(
            withMergedData: $this->getDynamicMetadata()
        );
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

    /**
     * Not yet implemented.
     *
     * If an item returns a route collection,
     * it will automatically be made into a dropdown.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Models\Route>
     */
    // public function navigationMenuChildren(): Collection;
}
