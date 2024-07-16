<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\JsonResponse;

class VirtualRouteController
{
    public static function ping(): JsonResponse
    {
        return new JsonResponse(200, 'OK', [
            'server' => 'Hyde/RealtimeCompiler',
        ]);
    }
}
