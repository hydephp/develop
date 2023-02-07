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
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/foo';

    Filesystem::put('_pages/foo.md', '# Hello World!');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');

    expect($response->body)->toContain('<h1>Hello World!</h1>');

    Filesystem::unlink('_pages/foo.md');
    Filesystem::unlink('_site/foo.html');
});

test('handle routes pages with .html extension', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/foo.html';

    Filesystem::put('_pages/foo.md', '# Hello World!');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');

    expect($response->body)->toContain('<h1>Hello World!</h1>');

    Filesystem::unlink('_pages/foo.md');
    Filesystem::unlink('_site/foo.html');
});

test('handle routes static assets', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/media/app.css';

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');

    expect($response->body)->toContain('/*! HydeFront v2.0.0');
});
