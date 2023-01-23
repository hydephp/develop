<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

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
        return 'Hello World';
    }
}
