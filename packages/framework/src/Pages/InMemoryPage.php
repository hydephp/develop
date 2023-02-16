<?php

declare(strict_types=1);

namespace Hyde\Pages;

use BadMethodCallException;
use Closure;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\View;

/**
 * Extendable class for in-memory (or virtual) Hyde pages that are not based on any source files.
 *
 * @experimental This feature is experimental and may change substantially before the 1.0.0 release.
 *
 * When used in a package, it's on the package developer to ensure that the virtual page is registered with Hyde,
 * usually within the boot method of the package's service provider, or a page collection callback in an extension.
 * This is because these pages cannot be discovered by the auto discovery process as there's no source files to parse.
 *
 * This class is especially useful for one-off custom pages. But if your usage grows, or if you want to utilize
 * Hyde autodiscovery, you may benefit from creating a custom page class instead, as that will give you full control.
 */
class InMemoryPage extends HydePage
{
    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $fileExtension = '';

    protected string $contents;
    protected string $view;

    /** @var array<string, callable> */
    protected array $macros = [];

    /**
     * Static alias for the constructor.
     */
    public static function make(string $identifier = '', FrontMatter|array $matter = [], string $contents = '', string $view = ''): static
    {
        return new static($identifier, $matter, $contents, $view);
    }

    /**
     * Create a new in-memory/virtual page instance.
     *
     * The in-memory page class offers two content options. You can either pass a string to the $contents parameter,
     * Hyde will then save that literally as the page's contents. Alternatively, you can pass a view name to the $view parameter,
     * and Hyde will use that view to render the page contents with the supplied front matter during the static site build process.
     *
     * Note that $contents take precedence over $view, so if you pass both, only $contents will be used.
     * You can also register a macro with the name 'compile' to overload the default compile method.
     *
     * @param  string  $identifier  The identifier of the page. This is used to generate the route key which is used to create the output filename.
     *                              If the identifier for an in-memory page is "foo/bar" the page will be saved to "_site/foo/bar.html".
     *                              You can then also use the route helper to get a link to it by using the route key "foo/bar".
     * @param  \Hyde\Markdown\Models\FrontMatter|array  $matter  The front matter of the page. When using the Blade view rendering option,
     *                                                           all this data will be passed to the view rendering engine.
     * @param  string  $contents  The contents of the page. This will be saved as-is to the output file.
     * @param  string  $view  The view key or Blade file for the view to use to render the page contents.
     */
    public function __construct(string $identifier, FrontMatter|array $matter = [], string $contents = '', string $view = '')
    {
        parent::__construct($identifier, $matter);

        $this->contents = $contents;
        $this->view = $view;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getBladeView(): string
    {
        return $this->view;
    }

    /**
     * Get the contents that will be saved to disk for this page.
     *
     * In order to make your virtual page easy to use we provide a few options for how the page can be compiled.
     * If you want even more control, you can register a macro with the name 'compile' to overload the method,
     * or simply extend the class and override the method yourself, either in a standard or anonymous class.
     */
    public function compile(): string
    {
        if (isset($this->macros['compile'])) {
            return $this->__call('compile', []);
        }

        if (! $this->getContents() && $this->getBladeView()) {
            if (str_ends_with($this->getBladeView(), '.blade.php')) {
                // If the view key is for a Blade file path, we'll use the anonymous view compiler to compile it.
                // This allows you to use any arbitrary file, without needing to register its namespace or directory.
                return AnonymousViewCompiler::call($this->getBladeView(), $this->matter->toArray());
            }

            return View::make($this->getBladeView(), $this->matter->toArray())->render();
        }

        // If there's no macro or view configured, we'll just return the contents as-is.
        return $this->getContents();
    }

    /**
     * Register a macro for the instance.
     */
    public function macro(string $name, callable $macro): void
    {
        $this->macros[$name] = $macro;
    }

    /**
     * Dynamically handle calls to the class.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (! isset($this->macros[$method])) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $macro = $this->macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }
}
