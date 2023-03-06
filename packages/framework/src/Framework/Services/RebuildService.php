<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Pages\Concerns\HydePage;

/**
 * Runs the static page builder for the given path.
 *
 * @deprecated Can be replaced with an action.
 */
class RebuildService
{
    protected HydePage $page;

    /**
     * Construct the service class instance.
     *
     * @param  string  $filepath  Relative source file to compile. Example: _posts/foo.md
     */
    public function __construct(string $filepath)
    {
        $this->page = Pages::getPage($filepath);
    }

    /**
     * Execute the service action.
     */
    public function execute(): void
    {
        (new StaticPageBuilder($this->page))->__invoke();
    }
}
