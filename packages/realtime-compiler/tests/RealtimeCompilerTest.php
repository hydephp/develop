<?php

use Hyde\RealtimeCompiler\RealtimeCompiler;
use Desilva\Microserve\Request;
use Desilva\Microserve\Response;

uses(\Hyde\Testing\UnitTestCase::class);

test('can instantiate RealtimeCompiler', function () {
    $compiler = new RealtimeCompiler();

    expect($compiler)->toBeInstanceOf(RealtimeCompiler::class);
});

test('can register virtual route', function () {
    $compiler = new RealtimeCompiler();

    $route = function (Request $request): Response {
        return new Response('test', 200);
    };

    $compiler->registerVirtualRoute('/test', $route);

    $routes = $compiler->getVirtualRoutes();
    expect($routes)->toHaveKey('/test');
    expect($routes['/test'])->toBe($route);
});

test('can register multiple virtual routes', function () {
    $compiler = new RealtimeCompiler();

    $route1 = function (Request $request): Response {
        return new Response('test1', 200);
    };

    $route2 = function (Request $request): Response {
        return new Response('test2', 200);
    };

    $compiler->registerVirtualRoute('/test1', $route1);
    $compiler->registerVirtualRoute('/test2', $route2);

    $routes = $compiler->getVirtualRoutes();
    expect($routes)->toHaveKey('/test1');
    expect($routes)->toHaveKey('/test2');
    expect($routes['/test1'])->toBe($route1);
    expect($routes['/test2'])->toBe($route2);
});

test('getVirtualRoutes returns empty array initially', function () {
    $compiler = new RealtimeCompiler();

    expect($compiler->getVirtualRoutes())->toBe([]);
});

test('virtual routes can be called', function () {
    $compiler = new RealtimeCompiler();

    $route = function (Request $request): Response {
        return new Response(200, 'OK', ['body' => 'Hello World']);
    };

    $compiler->registerVirtualRoute('/hello', $route);

    $routes = $compiler->getVirtualRoutes();

    // Mock $_SERVER variables for Request construction
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/hello';

    $request = new Request();
    $response = $routes['/hello']($request);

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->body)->toBe('Hello World');
    expect($response->statusCode)->toBe(200);
});
