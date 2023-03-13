<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Facades\View;

use function substr;

/**
 * A basic redirect page. Is not discoverable by Hyde, instead you manually need to create the pages.
 * Typically, you'll do this in a build task. Pass data a new object, then call the store method.
 * The store method will then render the redirect page to the project's site output directory.
 * Once viewed in a web browser a meta refresh will redirect the user to the new location.
 *
 * Since redirects are not discoverable, they also never show up in navigation, sitemaps, etc.
 * If you want, you can however add the pages to the HydeKernel route index by adding it
 * in the boot method of your AppServiceProvider, or any other suitable location.
 * That way, your redirect will be saved by the standard build command.
 *
 * @example `Redirect::make('foo', 'bar')->store();`
 */
class Redirect extends InMemoryPage
{
    public readonly string $path;
    public readonly string $destination;
    public readonly bool $showText;

    /**
     * Create a new redirect instance that can be saved using the store method.
     *
     * @param  string  $path  The URI path to redirect from.
     * @param  string  $destination  The destination to redirect to.
     */
    public function __construct(string $path, string $destination, bool $showText = true)
    {
        $this->path = $this->normalizePath($path);
        $this->destination = $destination;
        $this->showText = $showText;

        parent::__construct($this->path);
    }

    /**
     * Create a new redirect page file in the project's site output directory.
     *
     * @param  string  $path  The URI path to redirect from.
     * @param  string  $destination  The destination to redirect to.
     */
    public static function create(string $path, string $destination, bool $showText = true): static
    {
        return (new static($path, $destination, $showText))->store();
    }

    public function compile(): string
    {
        return View::make('hyde::pages.redirect', [
            'destination' => $this->destination,
            'showText' => $this->showText,
        ])->render();
    }

    public function store(): static
    {
        Filesystem::putContents(Hyde::sitePath("$this->path.html"), $this->compile());

        return $this;
    }

    protected function normalizePath(string $path): string
    {
        if (str_ends_with($path, '.html')) {
            return substr($path, 0, -5);
        }

        return $path;
    }
}
