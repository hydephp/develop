<?php

try {
    define('BASE_PATH', realpath(getcwd()));
    define('HYDE_START', microtime(true));

    require_once BASE_PATH.'/vendor/autoload.php';

    try {
        $app = \Desilva\Microserve\Microserve::boot(\Hyde\RealtimeCompiler\Http\HttpKernel::class);
        $response = $app->handle(); // Process the request and create the response
        $response->send(); // Send the response to the client
    } catch (Throwable $exception) {
        \Hyde\RealtimeCompiler\Http\ExceptionHandler::handle($exception)->send();
    }
} catch (\Throwable $th) {
    // Auxiliary exception handler
    echo '<h1>Something went really wrong!</h1>';
    echo '<p>An error occurred that the core exception handler failed to process. Here\'s all we know:</p>';
    echo '<h2>Initial exception:</h2><pre>'.print_r($exception, true).'</pre>';
    echo '<h2>Auxiliary exception:</h2><pre>'.print_r($th, true).'</pre>';
} finally {
    // Write to console to emulate the standard built-in PHP server output
    $request = \Desilva\Microserve\Request::capture();
    file_put_contents('php://stderr', sprintf(
        "[%s] %s [%d]: %s %s\n",
        date('D M j H:i:s Y'),
        str_replace('::1', '[::1]', $_SERVER['REMOTE_ADDR'] ). ':' . $_SERVER['REMOTE_PORT'],
        $response->statusCode ?? $exception->getCode() ?? 500,
        $request->method,
        $request->path,
    ));

    if (isset($exception)) {
        exit($exception->getCode());
    }
}
