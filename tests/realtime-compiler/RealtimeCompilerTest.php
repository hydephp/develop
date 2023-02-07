<?php

use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\RouteNotFoundException;
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

test('handle throws route not found exception for missing route', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/missing';

    $kernel = new HttpKernel();
    $kernel->handle(new Request());

})->throws(RouteNotFoundException::class, "Route not found: 'missing'");

test('handle sends 404 error response for missing asset', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/missing.css';

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(404)
        ->and($response->statusMessage)->toBe('Not Found');
});

test('docs uri path is rerouted to docs/index', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/docs';

    Filesystem::put('_docs/index.md', '# Hello World!');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');

    expect($response->body)->toContain('HydePHP Docs');

    Filesystem::unlink('_docs/index.md');
    Filesystem::unlink('_site/docs/index.html');
});

test('ping route returns ping response', function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/ping';

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');
});
