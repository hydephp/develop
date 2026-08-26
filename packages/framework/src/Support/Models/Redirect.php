<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Facades\Localization;
use Hyde\Pages\InMemoryPage;
use Hyde\Markdown\Models\FrontMatter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

use function substr_count;
use function str_ends_with;
use function str_repeat;
use function in_array;
use function substr;

/**
 * A basic redirect page, normally created from the redirects defined in the Hyde configuration file.
 * Once viewed in a web browser a meta refresh will redirect the user to the new location.
 */
class Redirect extends InMemoryPage
{
    public readonly string $path;
    public readonly string $destination;

    /**
     * @param  string  $path  The URI path to redirect from.
     * @param  \Hyde\Markdown\Models\FrontMatter|array<string, mixed>  $matter  The front matter for the redirect page.
     */
    public function __construct(string $path, string $destination, FrontMatter|array $matter = [])
    {
        $this->path = $this->normalizePath($path);
        $this->destination = $destination;

        parent::__construct($this->path, $matter);
    }

    public function compile(): string
    {
        return View::make('hyde::pages.redirect', [
            'destination' => $this->resolveDestination(),
        ])->render();
    }

    /**
     * Resolve the destination for the language this redirect is compiled for.
     *
     * Destinations are relative to the redirect itself, so one naming another page resolves
     * within the language directory the redirect sits in, and thus follows the redirect
     * into its language on its own, which is what an internal destination wants.
     *
     * A destination naming a language explicitly wants that language rather than the one
     * it is reached through, so it is resolved from the site webroot instead, or it
     * would be looked for within the current language, as `sv/en/about`.
     *
     * External destinations name no language, and are left alone either way.
     */
    protected function resolveDestination(): string
    {
        if ($this->language === null || ! $this->destinationNamesLanguage()) {
            return $this->destination;
        }

        return str_repeat('../', substr_count($this->getRouteKey(), '/')).$this->destination;
    }

    protected function destinationNamesLanguage(): bool
    {
        return in_array(Str::before($this->destination, '/'), Localization::languages(), true);
    }

    public function showInNavigation(): bool
    {
        return false;
    }

    protected function normalizePath(string $path): string
    {
        if (str_ends_with($path, '.html')) {
            return substr($path, 0, -5);
        }

        return $path;
    }
}
