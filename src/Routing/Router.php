<?php

namespace Hyde\RealtimeCompiler\Routing;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;

class Router
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(): Response
    {
        return Response::make(501, 'Not Implemented');
    }

    /**
     * If the request is not for a web page, we assume it's
     * a static asset, which we instead want to proxy.
     */
    protected function shouldProxy(Request $request): bool
    {
        if (str_starts_with($request->path, '/media/')) {
            return true;
        }

        $extension = pathinfo($request->path)['extension'] ?? null;

        if ($extension === null || $extension === 'html') {
            return false;
        }

        return true;
    }
}