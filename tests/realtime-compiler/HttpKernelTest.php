<?php

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Http\HttpKernel;

define('BASE_PATH', realpath(__DIR__ . '/../../'));
ob_start();

test('handle routes index page', function () {
    $kernel = new HttpKernel();

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class);

    expect($response->statusCode)->toBe(200);
    expect($response->statusMessage)->toBe('OK');
    expect($response->body)->toContain('<title>Welcome to HydePHP!</title>');
});
