<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Closure;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\View;
use ReflectionFunction;

/**
 * Extendable class for in-memory (or virtual) Hyde pages that are not based on any source files.
 *
 * When used in a package, it's on the package developer to ensure that the virtual page is registered with Hyde,
 * usually within the boot method of the package's service provider, or a page collection callback in an extension.
 * This is because these pages cannot be discovered by the auto discovery process as there's no source files to parse.
 *
 * Pages may use literal string contents, a lazy closure, or a Blade view. Configured contents take precedence over
 * a view. Non-static closures are bound to the page instance and may use $this, while static closures run unbound.
 *
 * This class is especially useful for one-off custom pages. But if your usage grows, or if you want to utilize Hyde
 * autodiscovery or control compilation completely, create a custom page class and override compile() instead.
 */
class InMemoryPage extends HydePage
{
    public static string $sourceDirectory;
    public static string $outputDirectory;
    public static string $fileExtension;

    protected string|Closure $contents;
    protected string $view;

    /**
     * Static alias for the constructor.
     */
    public static function make(string $identifier = '', FrontMatter|array $matter = [], string|Closure $contents = '', string $view = ''): static
    {
        return new static($identifier, $matter, $contents, $view);
    }

    /**
     * Create a new in-memory/virtual page instance.
     *
     * The in-memory page class offers three content strategies. You can pass a literal string or a lazy closure to
     * the $contents parameter, or pass a view name or Blade file to the $view parameter. Closures are resolved each
     * time the contents are requested. Non-static closures are bound to this page and may use $this. Static closures
     * are supported but run unbound and cannot use $this.
     *
     * Configured contents take precedence over a view, including closures that return an empty string.
     *
     * @param  string  $identifier  The identifier of the page. This is used to generate the route key which is used to create the output filename.
     *                              If the identifier for an in-memory page is "foo/bar" the page will be saved to "_site/foo/bar.html".
     *                              You can then also use the route helper to get a link to it by using the route key "foo/bar".
     *                              Take note that the identifier must be unique to prevent overwriting other pages.
     * @param  \Hyde\Markdown\Models\FrontMatter|array  $matter  The front matter of the page. When using the Blade view rendering option,
     *                                                           all this data will be passed to the view rendering engine.
     * @param  string|\Closure  $contents  Literal page contents or a closure that lazily generates them.
     * @param  string  $view  The view key or Blade file for the view to use to render the page contents.
     */
    public function __construct(string $identifier = '', FrontMatter|array $matter = [], string|Closure $contents = '', string $view = '')
    {
        parent::__construct($identifier, $matter);

        $this->contents = $contents;
        $this->view = $view;
    }

    /** Get the literal contents or lazily resolve the configured content closure. */
    public function getContents(): string
    {
        if (! $this->contents instanceof Closure) {
            return $this->contents;
        }

        $contents = $this->contents;

        if (! (new ReflectionFunction($contents))->isStatic()) {
            $contents = $contents->bindTo($this, static::class);
        }

        return $contents();
    }

    /** Get the view key or Blade file for the view to use to render the page contents when this strategy is used. */
    public function getBladeView(): string
    {
        return $this->view;
    }

    /**
     * Get the contents that will be saved to disk for this page.
     *
     * Configured literal or closure contents take precedence over Blade views. A view is rendered only when the
     * configured contents are the empty string. Extend this class and override this method for complete control.
     */
    public function compile(): string
    {
        if ($this->contents instanceof Closure || $this->contents !== '') {
            return $this->getContents();
        }

        if ($this->getBladeView() !== '') {
            if (str_ends_with($this->getBladeView(), '.blade.php')) {
                // If the view key is for a Blade file path, we'll use the anonymous view compiler to compile it.
                // This allows you to use any arbitrary file, without needing to register its namespace or directory.
                return AnonymousViewCompiler::handle($this->getBladeView(), $this->matter->toArray());
            }

            return View::make($this->getBladeView(), $this->matter->toArray())->render();
        }

        return '';
    }
}
