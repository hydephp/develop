<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Closure;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

/**
 * Extendable class for in-memory (or virtual) Hyde pages that are not based on any source files.
 *
 * When used in a package, it's on the package developer to ensure that the virtual page is registered with Hyde,
 * usually within the boot method of the package's service provider, or a page collection callback in an extension.
 * This is because these pages cannot be discovered by the auto discovery process as there's no source files to parse.
 *
 * Pages may use literal string contents, a lazy closure, or a Blade view. Contents and views are mutually exclusive,
 * and null means the source was omitted. Content closures receive the current page as their first argument, which
 * they may declare or omit.
 *
 * This class is especially useful for one-off custom pages. But if your usage grows, or if you want to utilize Hyde
 * autodiscovery, add custom methods, or control compilation completely, extend this class. Subclasses can add ordinary
 * methods or override compile() as needed.
 */
class InMemoryPage extends HydePage
{
    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;

    /**
     * The literal page contents, or a closure that lazily generates them.
     *
     * The closure is always called with the current page instance, but may declare no parameters to ignore it.
     *
     * @var string|(Closure(): string)|(Closure(static): string)
     */
    protected string|Closure $contents;
    protected string $view;

    /**
     * Static alias for the constructor.
     *
     * @param  string  $identifier
     * @param  FrontMatter|array  $matter
     * @param  string|(Closure():string)|(Closure(static):string)|null  $contents
     * @param  string|null  $view
     */
    public static function make(string $identifier = '', FrontMatter|array $matter = [], string|Closure|null $contents = null, ?string $view = null): static
    {
        return new static($identifier, $matter, $contents, $view);
    }

    /**
     * Create a new in-memory/virtual page instance.
     *
     * The in-memory page class offers three content strategies. You can pass a literal string,
     * or closure to the `$contents` parameter, or pass a view name or Blade file to the `$view` parameter.
     * Closures return strings and are invoked during compile time. We inject the page instance as their first argument,
     * though closures that don't need the page context can simply declare no parameters.
     *
     * Contents and views are alternative content sources and cannot be used together. Omit both to create an empty page.
     * Pass null to omit a content source. An empty string is a valid literal for contents, but is not a valid view.
     *
     * @param  string  $identifier  The identifier of the page. This is used to generate the route key which is used to create the output filename.
     *                              If the identifier for an in-memory page is "foo/bar" the page will be saved to "_site/foo/bar.html".
     *                              You can then also use the route helper to get a link to it by using the route key "foo/bar".
     *                              Take note that the identifier must be unique to prevent overwriting other pages.
     * @param  \Hyde\Markdown\Models\FrontMatter|array  $matter  The front matter of the page. When using the Blade view rendering option,
     *                                                           all this data will be passed to the view rendering engine.
     * @param  string|(Closure():string)|(Closure(static):string)|null  $contents  Literal page contents or a closure that lazily generates them. The closure may declare the page parameter or omit it.
     * @param  string|null  $view  The non-empty view key or Blade file for the view to use to render the page contents, or null when no view is used.
     *
     * @throws \InvalidArgumentException If both contents and a view are supplied, or if the view is an empty string.
     */
    public function __construct(string $identifier = '', FrontMatter|array $matter = [], string|Closure|null $contents = null, ?string $view = null)
    {
        parent::__construct($identifier, $matter);

        if ($view === '') {
            throw new InvalidArgumentException('InMemoryPage view cannot be an empty string. Pass null to omit the view.');
        }

        if ($contents !== null && $view !== null) {
            throw new InvalidArgumentException('InMemoryPage cannot define both contents and a view.');
        }

        $this->contents = $contents ?? '';
        $this->view = $view ?? '';
    }

    /** Get the literal contents or invoke the configured content closure with the current page as its first argument. */
    public function getContents(): string
    {
        return $this->contents instanceof Closure
            ? ($this->contents)($this)
            : $this->contents;
    }

    /** Get the view key or Blade file for the view to use to render the page contents, or an empty string when the page uses no view. */
    public function getBladeView(): string
    {
        return $this->view;
    }

    /**
     * Get the contents that will be saved to disk for this page.
     *
     * The configured content source is selected during construction. Extend this class and override the method for
     * complete control.
     */
    public function compile(): string
    {
        if ($this->getBladeView() !== '') {
            if (str_ends_with($this->getBladeView(), '.blade.php')) {
                // If the view key is for a Blade file path, we'll use the anonymous view compiler to compile it.
                // This allows you to use any arbitrary file, without needing to register its namespace or directory.
                return AnonymousViewCompiler::handle($this->getBladeView(), $this->matter->toArray());
            }

            return View::make($this->getBladeView(), $this->matter->toArray())->render();
        }

        return $this->getContents();
    }
}
