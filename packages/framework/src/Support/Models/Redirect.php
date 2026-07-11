<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Pages\InMemoryPage;
use Hyde\Markdown\Models\FrontMatter;
use Illuminate\Support\Facades\View;

use function str_ends_with;
use function substr;

/**
 * A basic redirect page, normally created from the redirects defined in the Hyde configuration file.
 * Once viewed in a web browser a meta refresh will redirect the user to the new location.
 */
class Redirect extends InMemoryPage
{
    public readonly string $path;
    public readonly string $destination;
    public readonly bool $showText;

    /**
     * @param  string  $path  The URI path to redirect from.
     * @param  \Hyde\Markdown\Models\FrontMatter|array<string, mixed>  $matter  The front matter for the redirect page.
     */
    public function __construct(string $path, string $destination, bool $showText = true, FrontMatter|array $matter = [])
    {
        $this->path = $this->normalizePath($path);
        $this->destination = $destination;
        $this->showText = $showText;

        parent::__construct($this->path, $matter);
    }

    public function compile(): string
    {
        return View::make('hyde::pages.redirect', [
            'destination' => $this->destination,
            'showText' => $this->showText,
        ])->render();
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
