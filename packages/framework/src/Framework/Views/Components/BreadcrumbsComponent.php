<?php

declare(strict_types=1);

namespace Hyde\Framework\Views\Components;

use Hyde\Hyde;
use Hyde\Facades\Route;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
        $breadcrumbs = [(Route::get('index')?->getLink() ?? '/') => 'Home'];
        if ($identifier === 'index') {
            return $breadcrumbs;
        }

        $previous = '';
        $fields = explode('/', $identifier);
        foreach ($fields as $index => $field) {
            if ($field === 'index') {
                break;
            }

            // if it's not the last field, add index.html (since it must be a directory) otherwise add .html
            if ($index < count($fields) - 1) {
                $path = $previous."$field/index.html";
            } else {
                $path = $previous."$field.html";
            }
            $breadcrumbs[Hyde::relativeLink($path)] = Hyde::makeTitle($field);

            $previous .= $field.'/';
        }

        return $breadcrumbs;
    }
}
