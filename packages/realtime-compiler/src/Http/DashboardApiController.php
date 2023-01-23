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
        if ($_SERVER['REMOTE_ADDR'] !== '::1') {
            header('HTTP/1.1 403 Forbidden');
            echo '<h1>HTTP/1.1 403 - Access Denied</h1>';
            echo '<p>You must be on localhost to access this page. Refusing to serve request.</p>';
            exit;
        }

        $this->bootApplication();
    }

    public function handle(Request $request): Response
    {
        return new JsonResponse(200, 'OK');
    }
}
