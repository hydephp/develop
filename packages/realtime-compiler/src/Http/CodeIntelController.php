<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Illuminate\Support\Facades\Blade;

class CodeIntelController
{
    use InteractsWithLaravel;

    public function __construct()
    {
        $this->bootApplication();
    }

    public function show(): string
    {
        return Blade::render(file_get_contents(__DIR__.'/../../resources/dashboard.blade.php'));
    }
}
