<?php

declare(strict_types=1);

namespace Hyde\Pages;

use BadMethodCallException;
use Closure;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\Contracts\DynamicPage;
use Illuminate\Support\Facades\View;

/**
 * A virtual page is a page that does not have a source file.
 *
 * @experimental This feature is experimental and may change substantially before the 1.0.0 release.
 *
 * This can be useful for creating pagination pages and the like.
 * When used in a package, it's on the package developer to ensure
 * that the virtual page is registered with Hyde, usually within the
 * boot method of the package's service provider so it can be compiled.
 *
 * This class is especially useful for one-off pages, but if your usage grows,
 * you may benefit from creating a custom page class instead to get full control.
 */
class VirtualPage extends HydePage implements DynamicPage
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
     * Create a new virtual page instance.
     *
     * The virtual page class offers two content options. You can either pass a string to the $contents parameter,
     * Hyde will then save that literally as the page's contents. Alternatively, you can pass a view name to the $view parameter,
     * and Hyde will use that view to render the page contents with the supplied front matter during the static site build process.
     *
     * Note that $contents take precedence over $view, so if you pass both, only $contents will be used.
     * You can also register a macro with the name 'compile' to overload the default compile method.
     *
     * @param  string  $identifier  The identifier of the page. This is used to generate the route key which is used to create the output filename.
     *                              If the identifier for a virtual page is "foo/bar" the page will be saved to "_site/foo/bar.html".
     * @param  \Hyde\Markdown\Models\FrontMatter|array  $matter  The front matter of the page. When using the Blade view rendering option,
     *                                                           this will be passed to the view.
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
     */
    public function compile(): string
    {
        if (isset($this->macros['compile'])) {
            return $this->__call('compile', []);
        }

        if (! $this->contents && $this->view) {
            if (str_ends_with($this->view, '.blade.php')) {
                return AnonymousViewCompiler::call($this->view, $this->matter->toArray());
            }

            return View::make($this->getBladeView(), $this->matter->toArray())->render();
        }

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
