<?php

namespace Hyde\RealtimeCompiler\Http;

use Desilva\Microserve\Response;

class ExceptionHandler
{
    public static function handle(\Throwable $exception): Response
    {
        $whoops = new \Whoops\Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        $html = $whoops->handleException($exception);

        return Response::make(500, 'Internal Server Error', [
            'Content-Type'   => 'text/html',
            'Content-Length' => strlen($html),
            'body'           => $html,
        ]);
    }
}
