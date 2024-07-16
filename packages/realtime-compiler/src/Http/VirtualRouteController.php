<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Desilva\Microserve\JsonResponse;

class VirtualRouteController
{
    public static function ping(): JsonResponse
    {
        return new JsonResponse(200, 'OK', [
            'server' => 'Hyde/RealtimeCompiler',
        ]);
    }

    public static function dashboard(Request $request): Response
    {
        return (new DashboardController($request))->handle();
    }
}
