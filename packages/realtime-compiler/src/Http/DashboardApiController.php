<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use BadMethodCallException;
use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

use Illuminate\Support\Str;

use function basename;
use function call_user_func;
use function in_array;

class DashboardApiController
{
    use InteractsWithLaravel;

    const ACTIONS = [
        'ping',
        'openFileInEditor'
    ];

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

    /** @internal */
    public function ping(array $params): JsonResponse
    {
        return new JsonResponse(200, 'OK', ['body' => 'pong', 'params' => $params]);
    }

    public function openFileInEditor(array $params): Response
    {
        $path = $params['path'] ?? throw new BadMethodCallException('Missing path parameter');
        $path = realpath(Hyde::path($path)) ?: throw new BadMethodCallException('Invalid path parameter');

        // Extra security precaution (using custom logic to get extension to support Blade files)
        if (! in_array(Str::after(basename($path), '.'), ['md', 'blade.php'])) {
            throw new BadMethodCallException('Invalid file type');
        }

        // Shell execs are scary, which is exactly why this API is only to be used for local development
        shell_exec($path);

        return $this->redirectToDashboard();
    }

    protected function parseAction(array $data): array
    {
        $action = $data['action'] ?? throw new BadMethodCallException('No action provided');

        if (in_array($action, self::ACTIONS)) {
            return [[self::class, $action], $this->parseParams($data)];
        }

        throw new BadMethodCallException('Invalid action provided');
    }

    protected function parseParams(array $data): array
    {
        unset ($data['action']);
        return $data;
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

    protected function redirectToDashboard(): Response
    {
        return (new Response(303, 'See Other', [
            'Location' => '/dashboard',
        ]))->withHeaders([
            'Location' => '/dashboard',
        ]);
    }

    public static function enabled(): bool
    {
        return $_SERVER['REMOTE_ADDR'] === '::1' && config('hyde.server.dashboard.enhanced_api', false) === true;
    }
}
