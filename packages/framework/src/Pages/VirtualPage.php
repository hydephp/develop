<?php

declare(strict_types=1);

namespace Hyde\Pages;

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
 */
class VirtualPage extends HydePage implements DynamicPage
{
    public static string $sourceDirectory = '';
    public static string $outputDirectory = '';
    public static string $fileExtension = '';

    protected string $contents;
    protected string $view;

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
     *
     * @param  string  $identifier  The identifier of the page. This is used to generate the route key which is used to create the output filename.
     *                              If the identifier for a virtual page is "foo/bar" the page will be saved to "_site/foo/bar.html".
     * @param  \Hyde\Markdown\Models\FrontMatter|array  $matter  The front matter of the page. When using the Blade view rendering option,
     *                                                           this will be passed to the view.
     * @param  string  $contents  The contents of the page. This will be saved as-is to the output file.
     * @param  string  $view  The view key for the view to use to render the page contents.
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

    public function compile(): string
    {
        if (! $this->contents && $this->view) {
            return View::make($this->getBladeView(), $this->matter->toArray())->render();
        }

        return $this->getContents();
    }
}
