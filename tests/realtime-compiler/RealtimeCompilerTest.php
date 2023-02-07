<?php

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Filesystem;
use Hyde\RealtimeCompiler\Http\HttpKernel;

define('BASE_PATH', realpath(__DIR__ . '/../../'));
ob_start();

test('handle routes index page', function () {
    putenv('SERVER_DASHBOARD=false');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class);

    expect($response->statusCode)->toBe(200);
    expect($response->statusMessage)->toBe('OK');
    expect($response->body)->toContain('<title>Welcome to HydePHP!</title>');

    expect(hyde()->path('_site/index.html'))->toBeFile()
        ->and(Filesystem::get('_site/index.html'))->toBe($response->body);

    Filesystem::unlink('_site/index.html');
});

test('handle routes custom pages', function () {
    //
});
