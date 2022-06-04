<?php

define('BASE_PATH', realpath(getcwd()));
define('HYDE_START', microtime(true));

require_once sprintf("%s/vendor/autoload.php", BASE_PATH);

try {
    $app = \Desilva\Microserve\Microserve::boot(\Hyde\RealtimeCompiler\Http\HttpKernel::class);
    $app->handle() // Process the request and create the response
        ->send(); // Send the response to the client
} catch (Throwable $exception) {
    \Hyde\RealtimeCompiler\Http\ExceptionHandler::handle($exception);
    exit($exception->getCode());
}