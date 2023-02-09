<?php

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\HttpKernel as BaseHttpKernel;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Routing\Router;

/**
 * The HttpKernel is the entry point for all incoming HTTP requests.
 *
 * Here we pass the request along to be processed by the Router.
 */
class HttpKernel extends BaseHttpKernel
{
    protected array $middleware = [
        //
    ];

    public function handle(Request $request): Response
    {
        header('X-Server: Hyde/RealtimeCompiler');

        return (new Router($request))->handle();
    }
}
