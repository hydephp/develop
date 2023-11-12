<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Desilva\Microserve\JsonResponse;
use Hyde\RealtimeCompiler\ConsoleOutput;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @internal This class is not intended to be edited outside the Hyde Realtime Compiler.
 */
abstract class BaseController
{
    protected Request $request;
    protected ConsoleOutput $console;
    protected bool $withConsoleOutput = true;

    abstract public function handle(): Response;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? Request::capture();

        if ($this->withConsoleOutput && ((bool) env('HYDE_SERVER_REQUEST_OUTPUT', false)) === true) {
            $this->console = new ConsoleOutput();
        }
    }

    protected function sendJsonErrorResponse(int $statusCode, string $message): JsonResponse
    {
        return new JsonResponse($statusCode, $this->matchStatusCode($statusCode), [
            'error' => $message,
        ]);
    }

    protected function abort(int $code, string $message): never
    {
        throw new HttpException($code, $message);
    }

    protected function matchStatusCode(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            409 => 'Conflict',
            default => 'Internal Server Error',
        };
    }

    protected function shouldUnsafeRequestBeBlocked(): bool
    {
        // As the dashboard is not password-protected, and it can make changes to the file system,
        // we block any requests that are not coming from the host machine. While we are clear
        // in the documentation that the realtime compiler should only be used for local
        // development, we still want to be extra careful in case someone forgets.

        $requestIp = $_SERVER['REMOTE_ADDR'];
        $allowedIps = ['::1', '127.0.0.1', 'localhost'];

        return ! in_array($requestIp, $allowedIps, true);
    }

    protected function writeToConsole(string $message, string $context = 'dashboard'): void
    {
        if (isset($this->console)) {
            $this->console->printMessage($message, $context);
        }
    }
}
