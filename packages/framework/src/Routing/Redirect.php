<?php

declare(strict_types=1);

namespace Hyde\Routing;

use Hyde\Hyde;

/**
 * A basic redirect page. Is not discoverable by Hyde, instead you manually need to create the pages.
 * Typically, you'll do this in a build task. Pass data a new object, then call the store method.
 * The store method will then render the redirect page to the project's site output directory.
 * Once viewed in a web browser a meta refresh will redirect the user to the new location.
 *
 * @example `Redirect::make('foo', 'bar')->store();`
 */
class Redirect
{
    public string $path;
    public string $destination;

    public function __construct(string $path, string $destination)
    {
        $this->path = $path;
        $this->destination = $destination;
    }

    public static function make(string $path, string $destination): static
    {
        return new static($path, $destination);
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
}
