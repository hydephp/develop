<?php

declare(strict_types=1);

require_once __DIR__.'/MicroTest.php';

test('example', function () {
    $this->assert(true);
});

test('can install node dependencies', function () {
    $this->assert(! file_exists(BASE_PATH.'/node_modules'), 'Node modules already exist');

    // Remove package-lock.json so it can be regenerated.
    unlink(BASE_PATH.'/package-lock.json');

    shell_exec('cd '.BASE_PATH.' && npm install');

    $this->assert(file_exists(BASE_PATH.'/node_modules'), 'Node modules do not exist');

    // Test can re-install dependencies.
    shell_exec('cd '.BASE_PATH.' && npm install');

    $this->assert(file_exists(BASE_PATH.'/node_modules'), 'Node modules do not exist');
});

test('can build assets using vite', function () {
    // Add code to the JavaScript file so it compiles.
    file_put_contents(BASE_PATH.'/resources/assets/app.js', 'console.log("Hello, world!");');

    $output = shell_exec('cd '.BASE_PATH.' && npm run build');

    $this->assert(file_exists(BASE_PATH.'/_media/app.css'), 'CSS file does not exist');
    $this->assert(file_exists(BASE_PATH.'/_media/app.js'), 'JS file does not exist');
});
