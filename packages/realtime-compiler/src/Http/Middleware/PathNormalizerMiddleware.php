<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http\Middleware;

use Desilva\Microserve\Request;

class PathNormalizerMiddleware
{
    protected array $pathRewrites = [
        '/docs' => '/docs/index',
        '/docs/search.html' => '/docs/search',
    ];

    public function __invoke(Request $request): Request
    {
        if (array_key_exists($request->path, $this->pathRewrites)) {
            $request->path = $this->pathRewrites[$request->path];
        }

        $request->path = rtrim($request->path, '/');

        return $request;
    }
}
