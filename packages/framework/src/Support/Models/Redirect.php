<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Hyde;
use Illuminate\Contracts\Support\Renderable;

/**
 * A basic redirect page. Is not discoverable by Hyde, instead you manually need to create the pages.
 * Typically, you'll do this in a build task. Pass data a new object, then call the store method.
 * The store method will then render the redirect page to the project's site output directory.
 * Once viewed in a web browser a meta refresh will redirect the user to the new location.
 *
 * Since redirects are not discoverable, they also never show up in navigation, sitemaps, etc.
 *
 * @example `Redirect::make('foo', 'bar')->store();`
 */
class Redirect implements Renderable
{
    protected string $path;
    protected string $destination;

    /**
     * Create a new redirect page file in the project's site output directory.
     *
     * @param string $path The URI path to redirect from.
     * @param string $destination The destination to redirect to.
     */
    public function __construct(string $path, string $destination)
    {
        $this->path = $this->normalizePath($path);
        $this->destination = $destination;
    }

    public static function make(string $path, string $destination): static
    {
        return (new static($path, $destination))->store();
    }

    public function render(): string
    {
        return view('hyde::pages.redirect', [
            'destination' => $this->destination,
        ])->render();
    }

    public function store(): static
    {
        file_put_contents(Hyde::sitePath("$this->path.html"), $this->render());

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
