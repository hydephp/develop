<?php

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Response;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Exceptions are caught by the server.php to be handled here,
 * where we render a pretty error page with a 500 HTTP code.
 */
class ExceptionHandler
{
    public static function handle(Throwable $exception): Response
    {
        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new PrettyPageHandler());
        $html = $whoops->handleException($exception);

        return Response::make(500, 'Internal Server Error', [
            'Content-Type' => 'text/html',
            'Content-Length' => strlen($html),
            'body' => $html,
        ]);
    }
}
