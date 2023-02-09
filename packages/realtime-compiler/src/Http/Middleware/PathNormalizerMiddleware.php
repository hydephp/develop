<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http\Middleware;

use Desilva\Microserve\Request;

class PathNormalizerMiddleware
{
    public function __invoke(Request $request): Request
    {
        return $request;
    }
}
