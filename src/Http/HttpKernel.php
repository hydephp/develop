<?php

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\HttpKernel as BaseHttpKernel;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Routing\Router;

class HttpKernel extends BaseHttpKernel
{
    public function handle(Request $request): Response
    {
        return (new Router($request))->handle();
    }
}