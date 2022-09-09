<?php

namespace Hyde\Admin;

use Desilva\Microserve\Request;
use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;

final class AdminPage extends BladePage implements PageContract
{
    /** Not yet implemented, but could be used by the framework to ignore this when compiling */
    protected static bool $compilable = false;

    public string $route;

    /**
     * @inheritDoc
     */
    public function __construct(string $view)
    {
        parent::__construct($view);

        $this->route = $this->request()?->get('route', 'dashboard') ?? 'dashboard';
    }

    public function getRouteKey(): string
    {
        return 'admin';
    }

    public function navigationMenuTitle(): string
    {
        return 'Admin Panel';
    }

    public function request(): ?Request
    {
        return isset($_SERVER['REQUEST_METHOD'])
            ? Request::capture()
            : null;
    }

    public function view(): string
    {
        $view = 'hyde-admin::pages.'.$this->route;

        return view()->exists($view) ? $view : 'hyde-admin::pages.404';
    }
}
