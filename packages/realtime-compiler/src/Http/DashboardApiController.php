<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

class DashboardApiController
{
    use InteractsWithLaravel;

    public function __construct()
    {
        $this->bootApplication();
    }

    public function handle(Request $request): Response
    {
        return new JsonResponse(200, 'OK');
    }
}
