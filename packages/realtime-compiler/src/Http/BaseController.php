<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use BadMethodCallException;
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
    protected bool $withConsoleOutput = false;
    protected bool $withSession = false;

    protected static bool $sessionStarted = false;

    abstract public function handle(): Response;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? Request::capture();

        if ($this->withConsoleOutput && ((bool) env('HYDE_SERVER_REQUEST_OUTPUT', false)) === true) {
            $this->console = new ConsoleOutput();
        }

        if ($this->withSession && ! self::$sessionStarted) {
            session_start();
            self::$sessionStarted = true;
        }
    }

    public function __destruct()
    {
        if ($this->withSession && self::$sessionStarted) {
            session_write_close();
            self::$sessionStarted = false;
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

    protected function authorizePostRequest(): void
    {
        if (! $this->isRequestMadeFromLocalhost()) {
            throw new HttpException(403, "Refusing to serve request from address {$_SERVER['REMOTE_ADDR']} (must be on localhost)");
        }

        if ($this->withSession) {
            if (! $this->validateCSRFToken($this->request->get('_token'))) {
                throw new HttpException(403, 'Invalid CSRF token');
            } else {
                $this->expireCSRFToken();
            }
        }
    }

    protected function isRequestMadeFromLocalhost(): bool
    {
        // As the dashboard is not password-protected, and it can make changes to the file system,
        // we block any requests that are not coming from the host machine. While we are clear
        // in the documentation that the realtime compiler should only be used for local
        // development, we still want to be extra careful in case someone forgets.

        $requestIp = $_SERVER['REMOTE_ADDR'];
        $allowedIps = ['::1', '127.0.0.1', 'localhost'];

        return in_array($requestIp, $allowedIps, true);
    }

    protected function generateCSRFToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new BadMethodCallException('Session not started');
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    protected function validateCSRFToken(?string $suppliedToken): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new BadMethodCallException('Session not started');
        }

        if ($suppliedToken === null) {
            return false;
        }

        return ! empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $suppliedToken);
    }

    protected function expireCSRFToken(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new BadMethodCallException('Session not started');
        }

        unset($_SESSION['csrf_token']);
    }

    protected function writeToConsole(string $message, string $context = 'dashboard'): void
    {
        if (isset($this->console)) {
            $this->console->printMessage($message, $context);
        }
    }
}
