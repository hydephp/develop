<?php

define('BASE_PATH', realpath(getcwd()));
define('HYDE_START', microtime(true));

// If request path is dashboard.php then load that file instead
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/dashboard.php') {
	require_once __DIR__ . '/dashboard.php';
	exit;
}

require_once sprintf('%s/vendor/autoload.php', BASE_PATH);

try {
    $app = \Desilva\Microserve\Microserve::boot(\Hyde\RealtimeCompiler\Http\HttpKernel::class);
    $app->handle() // Process the request and create the response
        ->send(); // Send the response to the client
} catch (Throwable $exception) {
    \Hyde\RealtimeCompiler\Http\ExceptionHandler::handle($exception);
    exit($exception->getCode());
}
