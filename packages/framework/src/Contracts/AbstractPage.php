<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Actions\SourceFileParser;
use Hyde\Framework\Helpers\Features;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Pages\DocumentationPage;
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
 * @test \Hyde\Framework\Testing\Feature\AbstractPageTest
 */
abstract class AbstractPage implements PageContract, CompilableContract
{
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
    }

    /** @interitDoc */
    public function __get(string $name)
    {
        return $this->matter->get($name);
    }

    /** @inheritDoc */
    public function __set(string $name, $value): void
    {
        $this->matter->set($name, $value);
    }

    /** @inheritDoc */
    public function matter(string $key = null, mixed $default = null): mixed
    {
        return $this->matter->get($key, $default);
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

    public function getCanonicalUrl(): string
    {
        return $this->getRoute()->getQualifiedUrl();
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string>
     */
    public function getDynamicMetadata(): array
    {
        $array = [];

        if ($this->canUseCanonicalUrl()) {
            $array[] = '<link rel="canonical" href="'.$this->getCanonicalUrl().'" />';
        }

        if (Features::sitemap()) {
            $array[] = '<link rel="sitemap" type="application/xml" title="Sitemap" href="'.Hyde::url('sitemap.xml').'" />';
        }

        if (Features::rss()) {
            $array[] = $this->makeRssFeedLink();
        }

        if (isset($this->title)) {
            if ($this->hasTwitterTitleInConfig()) {
                $array[] = '<meta name="twitter:title" content="'.$this->htmlTitle().'" />';
            }
            if ($this->hasOpenGraphTitleInConfig()) {
                $array[] = '<meta property="og:title" content="'.$this->htmlTitle().'" />';
            }
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

    public function canUseCanonicalUrl(): bool
    {
        return Hyde::hasSiteUrl() && isset($this->identifier);
    }

    public function hasTwitterTitleInConfig(): bool
    {
        return str_contains(json_encode(config('hyde.meta', [])), 'twitter:title');
    }

    public function hasOpenGraphTitleInConfig(): bool
    {
        return str_contains(json_encode(config('hyde.meta', [])), 'og:title');
    }

    protected function makeRssFeedLink(): string
    {
        return sprintf(
            '<link rel="alternate" type="application/rss+xml" title="%s" href="%s" />',
            RssFeedService::getDescription(),
            Hyde::url(RssFeedService::getDefaultOutputFilename())
        );
    }

    /**
     * Should the item should be displayed in the navigation menu?
     *
     * @return bool
     */
    public function showInNavigation(): bool
    {
        if ($this instanceof MarkdownPost) {
            return false;
        }

        if ($this instanceof DocumentationPage) {
            return $this->identifier === 'index' && ! in_array('docs', config('hyde.navigation.exclude', []));
        }

        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.hidden', false)) {
                return false;
            }
        }

        if (in_array($this->identifier, config('hyde.navigation.exclude', ['404']))) {
            return false;
        }

        return true;
    }

    /**
     * The relative priority, determining the position of the item in the menu.
     *
     * @return int
     */
    public function navigationMenuPriority(): int
    {
        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.priority') !== null) {
                return $this->matter('navigation.priority');
            }
        }

        if ($this instanceof DocumentationPage) {
            return (int) config('hyde.navigation.order.docs', 100);
        }

        if ($this->identifier === 'index') {
            return (int) config('hyde.navigation.order.index', 0);
        }

        if ($this->identifier === 'posts') {
            return (int) config('hyde.navigation.order.posts', 10);
        }

        if (array_key_exists($this->identifier, config('hyde.navigation.order', []))) {
            return (int) config('hyde.navigation.order.'.$this->identifier);
        }

        return 999;
    }

    /**
     * The page title to display in the navigation menu.
     *
     * @return string
     */
    public function navigationMenuTitle(): string
    {
        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.title') !== null) {
                return $this->matter('navigation.title');
            }

            if ($this->matter('title') !== null) {
                return $this->matter('title');
            }
        }

        if ($this->identifier === 'index') {
            if ($this instanceof DocumentationPage) {
                return config('hyde.navigation.labels.docs', 'Docs');
            }

            return config('hyde.navigation.labels.home', 'Home');
        }

        return $this->title;
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
