<?php

use Desilva\Microserve\JsonResponse;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\RealtimeCompiler\Http\ExceptionHandler;
use Hyde\RealtimeCompiler\Http\HtmlResponse;
use Hyde\RealtimeCompiler\Http\HttpKernel;

define('BASE_PATH', realpath(__DIR__.'/../../../'));

if (BASE_PATH === false || ! file_exists(BASE_PATH.'/hyde')) {
    throw new InvalidArgumentException('This test suite must be run from the root of the hydephp/develop monorepo.');
}

ob_start();

test('handle routes index page', function () {
    putenv('SERVER_DASHBOARD=false');
    mockRoute('');

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
    mockRoute('foo');

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
    mockRoute('foo.html');

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
    mockRoute('media/app.css');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');

    expect($response->body)->toBeFile(\Hyde\Hyde::path('_media/app.css'));
});

test('handle throws route not found exception for missing route', function () {
    mockRoute('missing');

    $kernel = new HttpKernel();
    $kernel->handle(new Request());
})->throws(RouteNotFoundException::class, 'Route [missing] not found');

test('handle sends 404 error response for missing asset', function () {
    mockRoute('missing.css');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(404)
        ->and($response->statusMessage)->toBe('Not Found');
});

//test('html responses contain the correct headers', function () {
//    mockRoute('foo');
//
//    Filesystem::put('_pages/foo.md', '# Hello World!');
//
//    $kernel = new HttpKernel();
//    $response = $kernel->handle(new Request());
//
//    expect($response)->toBeInstanceOf(HtmlResponse::class)
//        ->and($response->statusCode)->toBe(200)
//        ->and($response->statusMessage)->toBe('OK')
//        ->and($response->headers)->toContain('Content-Type', 'text/html')
//        ->and($response->headers)->toContain('Content-Length', strlen($response->body));
//
//    expect($response->body)->toContain('<h1>Hello World!</h1>');
//
//    Filesystem::unlink('_pages/foo.md');
//    Filesystem::unlink('_site/foo.html');
//})->skip('Underlying framework does not buffer headers (yet)');

test('trailing slashes are normalized from route', function () {
    mockRoute('foo/');

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

test('docs uri path is rerouted to docs/index', function () {
    mockRoute('docs');

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

test('docs/search renders search page', function () {
    mockRoute('docs/search');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(HtmlResponse::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');

    expect($response->body)->toContain('Search the documentation site');

    Filesystem::unlink('_site/docs/search.html');
});

test('ping route returns ping response', function () {
    mockRoute('ping');

    $kernel = new HttpKernel();
    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->statusCode)->toBe(200)
        ->and($response->statusMessage)->toBe('OK');
});

test('exception handling', function () {
    $exception = new Exception('foo');
    $response = ExceptionHandler::handle($exception);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->statusCode)->toBe(500)
        ->and($response->statusMessage)->toBe('Internal Server Error');
});

function mockRoute(string $route, $method = 'GET'): void
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = "/$route";
}
