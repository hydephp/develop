<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Facades\View;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Component;

use function count;
use function explode;

class BreadcrumbsComponent extends Component
{
    public readonly array $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs = $this->makeBreadcrumbs();
    }

    /** @interitDoc */
    public function render(): ViewContract
    {
        return View::make('hyde::components.breadcrumbs');
    }

    protected function makeBreadcrumbs(): array
    {
        $identifier = Hyde::currentRoute()->getPage()->getIdentifier();
        $breadcrumbs = [(Routes::get('index')?->getLink() ?? '/') => 'Home'];

        if ($identifier === 'index') {
            return $breadcrumbs;
        }

        $previous = '';
        $fields = explode('/', $identifier);
        foreach ($fields as $index => $basename) {
            if ($basename === 'index') {
                break;
            }

            // if it's not the last basename, add index.html (since it must be a directory) otherwise add .html
            $path = $previous.$basename.($index < count($fields) - 1 ? '/index.html' : '.html');

            $breadcrumbs[Hyde::relativeLink($path)] = Hyde::makeTitle($basename);

            $previous .= $basename.'/';
        }

        return $breadcrumbs;
    }
}
