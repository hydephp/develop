<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http\Middleware;

use Desilva\Microserve\Request;

class PathNormalizerMiddleware
{
    /**
     * @deprecated These do not work for dynamic routes.
     */
    protected array $pathRewrites = [
        '/docs/search.html' => '/docs/search',
    ];

    public function __invoke(Request $request): Request
    {
        $request->path = rtrim($request->path, '/');

        if (array_key_exists($request->path, $this->pathRewrites)) {
            $request->path = $this->pathRewrites[$request->path];
        }

        return $request;
    }
}
