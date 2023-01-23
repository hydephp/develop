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

        if ($_SERVER['REMOTE_ADDR'] !== '::1') {
            header('HTTP/1.1 403 Forbidden');
            echo '<h1>HTTP/1.1 403 - Access Denied</h1>';
            echo '<p>You must be on localhost to access this page. Refusing to serve request.</p>';
            exit;
        }

        if (env('DASHBOARD_API', false) !== true) {
            header('HTTP/1.1 403 Forbidden');
            echo '<h1>HTTP/1.1 403 - Access Denied</h1>';
            echo '<p>You must set the <code>DASHBOARD_API</code> environment variable to <code>true</code> to enable this page. Refusing to serve request.</p>';
            exit;
        }
    }

    public function handle(Request $request): Response
    {
        return new JsonResponse(200, 'OK');
    }
}
