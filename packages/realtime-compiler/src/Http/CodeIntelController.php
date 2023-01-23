<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

class CodeIntelController
{
    use InteractsWithLaravel;

    public function __construct()
    {
        $this->bootApplication();
    }

    public function show(): string
    {
        return (new AnonymousViewCompiler(__DIR__.'/../resources/dashboard.blade.php'))->__invoke();
    }
}
