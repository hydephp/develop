<?php

try {
    define('BASE_PATH', realpath(getcwd()));
    define('HYDE_START', microtime(true));

    require_once BASE_PATH.'/vendor/autoload.php';

    try {
        $app = \Desilva\Microserve\Microserve::boot(\Hyde\RealtimeCompiler\Http\HttpKernel::class);
        $response = $app->handle(); // Process the request and create the response
        $response->send(); // Send the response to the client

        // Write to console to emulate the built-in PHP server output
        file_put_contents('php://stderr', sprintf(
            "[%s] %s [%d]: %s %s\n",
            date('D M j H:i:s Y'),
            str_replace('::1', '[::1]', $_SERVER['REMOTE_ADDR'] ). ':' . $_SERVER['REMOTE_PORT'],
            $response->statusCode,
            \Desilva\Microserve\Request::capture()->method,
            \Desilva\Microserve\Request::capture()->path,
        ));
    } catch (Throwable $exception) {
        \Hyde\RealtimeCompiler\Http\ExceptionHandler::handle($exception)->send();
        exit($exception->getCode());
    }
} catch (\Throwable $th) {
    // Auxiliary exception handler
    echo '<h1>Something went really wrong!</h1>';
    echo '<p>An error occurred that the core exception handler failed to process. Here\'s all we know:</p>';
    echo '<h2>Initial exception:</h2><pre>'.print_r($exception, true).'</pre>';
    echo '<h2>Auxiliary exception:</h2><pre>'.print_r($th, true).'</pre>';
}
