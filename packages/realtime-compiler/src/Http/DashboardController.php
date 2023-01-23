<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

class DashboardController
{
    use InteractsWithLaravel;

    public string $title;

    public function __construct()
    {
        $this->bootApplication();

        $this->title = config('site.name') . ' - Dashboard';
    }

    public function show(): string
    {
        return (new AnonymousViewCompiler(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this,
            ['controller' => $this],
        )))->__invoke();
    }
}
