<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\HttpKernel as BaseHttpKernel;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Http\Middleware\PathNormalizerMiddleware;
use Hyde\RealtimeCompiler\Routing\Router;

/**
 * The HttpKernel is the entry point for all incoming HTTP requests.
 *
 * Here we pass the request along to be processed by the Router.
 */
class HttpKernel extends BaseHttpKernel
{
    /** @var array<class-string<callable>>|array<callable(Request): Request> */
    protected array $middleware = [
        PathNormalizerMiddleware::class,
    ];

    public function handle(Request $request): Response
    {
        header('X-Server: Hyde/RealtimeCompiler');

        foreach ($this->middleware as $middleware) {
            $request = is_string($middleware)
                ? (new $middleware())($request)
                : $middleware($request);
        }

        return (new Router($request))->handle();
    }
}
