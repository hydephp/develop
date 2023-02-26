<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class BreadcrumbsComponent extends Component
{
    public array $breadcrumbs;

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
        $breadcrumbs = ['/' => 'Home'];
        if ($identifier == 'index') {
            return $breadcrumbs;
        }

        $path = '';
        $fields = Str::of($identifier)->explode('/');
        foreach ($fields as $k => $field) {
            if ($field == 'index') {
                return $breadcrumbs;
            }

            // if it's not the last field, add a trailing slash (since it must be a directory)
            $path .= $field.($k < count($fields) - 1 ? '/' : '');
            $title = Str::of($field)->replace('-', ' ')->title();
            $breadcrumbs[$path] = $title->toString();
        }

        return $breadcrumbs;
    }
}
