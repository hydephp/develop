<?php

ob_start();

test('server binary exists', function () {
    expect(__DIR__.'/../bin/server.php')->toBeFile();
});

test('server binary is executable', function () {
    ob_start();

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $server = require __DIR__.'/../bin/server.php';

    $output = ob_get_clean();

    expect($output)->toContain('Welcome to HydePHP!');
    expect($server)->toBeInt()->and($server)->toBe(1);
});
