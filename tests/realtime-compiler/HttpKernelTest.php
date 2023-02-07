<?php

use Desilva\Microserve\Request;
use Desilva\Microserve\Response;
use Hyde\RealtimeCompiler\Http\HttpKernel;

test('handle', function () {
    $kernel = new HttpKernel();

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $response = $kernel->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class);
});
