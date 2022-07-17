<?php

namespace Hyde\Admin;

use Desilva\Microserve\Request;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;

final class AdminPage extends BladePage implements PageContract
{
    /** Not yet implemented, but could be used by the framework to ignore this when compiling */
    protected static bool $compilable = false;

    public function getCurrentPagePath(): string
    {
        return 'admin';
    }

    public function navigationMenuTitle(): string
    {
        return 'Admin Panel';
    }

    public function request(): Request
    {
        return Request::capture();
    }

    public function adminRoute(): string
    {
        return $this->request()->get('route', 'dashboard');
    }

    public function view(): string
    {
        $view = 'hyde-admin::pages.' . $this->adminRoute();
        return view()->exists($view) ? $view : 'hyde-admin::pages.404';
    }
}
