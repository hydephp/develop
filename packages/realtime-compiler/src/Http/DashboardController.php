<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Hyde;
use Hyde\Pages\VirtualPage;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

class DashboardController
{
    use InteractsWithLaravel;

    public string $title = 'Dashboard';
    public VirtualPage $page;

    public function __construct()
    {
        $this->bootApplication();

        $this->page = new VirtualPage($this->title);
    }

    public function show(): string
    {
        Hyde::shareViewData($this->page);
        return (new AnonymousViewCompiler(__DIR__.'/../../resources/dashboard.blade.php', (array) $this))->__invoke();
    }
}
