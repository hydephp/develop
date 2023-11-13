<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @internal This class is not intended to be edited outside the Hyde Realtime Compiler.
 */
class LiveEditController extends BaseController
{
    protected bool $withConsoleOutput = true;
    protected bool $withSession = true;

    public function handle(): JsonResponse
    {
        try {
            $this->authorizePostRequest();

            return $this->handleRequest();
        } catch (HttpException $exception) {
            if ($this->expectsJson()) {
                return $this->sendJsonErrorResponse($exception->getStatusCode(), $exception->getMessage());
            }

            throw $exception;
        }
    }

    protected function handleRequest(): JsonResponse
    {
        //
    }

    public static function enabled(): bool
    {
        return config('hyde.server.live_edit', true);
    }
}
