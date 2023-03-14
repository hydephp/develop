<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use function explode;
use function view;

class BreadcrumbsComponent extends Component
{
    public readonly array $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs = $this->makeBreadcrumbs();
    }

    /** @interitDoc */
    public function render(): Factory|View
    {
        return view('hyde::components.breadcrumbs');
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

            // makeTitle() will place spaces between words, so we need to remove them if the title is all uppercase
            $title = Hyde::makeTitle($basename);
            if (strtoupper($title) === $title) {
                $title = str_replace(' ', '', $title);
            }
            $breadcrumbs[Hyde::relativeLink($path)] = $title;

            $previous .= $basename.'/';
        }

        return $breadcrumbs;
    }
}
