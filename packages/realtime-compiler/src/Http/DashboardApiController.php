<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use BadMethodCallException;
use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

use function call_user_func;
use function json_decode;

class DashboardApiController
{
    use InteractsWithLaravel;

    const ACTIONS = [];

    public function __construct()
    {
        $this->bootApplication();

        $this->preventUnauthorizedRequests();
    }

    public function handle(Request $request): Response
    {
        try {
            $action = $this->parseAction($request->data);

            return call_user_func(...$action);
        } catch (BadMethodCallException $exception) {
            return new JsonResponse(400, 'Bad Request', ['body' => $exception->getMessage()]);
        }
    }

    protected function parseAction(array $data): array
    {
        $action = $data['action'] ?? throw new BadMethodCallException('No action provided');

        if (in_array($action, self::ACTIONS)) {
            return [[self::class, $action], $this->parseParams($data['params'])];
        }

        throw new BadMethodCallException('Invalid action provided');
    }

    protected function parseParams(?string $params): array
    {
        if ($params) {
            $params = json_decode($params, true);
        } else {
            $params = [];
        }
        return (array) $params;
    }

    protected function preventUnauthorizedRequests(): void
    {
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

    public static function enabled(): bool
    {
        return $_SERVER['REMOTE_ADDR'] === '::1' && env('DASHBOARD_API', false) === true;
    }
}
