<?php

declare(strict_types=1);

require_once __DIR__.'/MicroTest.php';

test('example', function () {
    $this->assert(true);
});

test('can install composer dependencies', function () {
    $this->assert(! file_exists(BASE_PATH.'/vendor/autoload.php'), 'Autoloader already exists');

    shell_exec('cd '.BASE_PATH.' && composer install');

    $this->assert(file_exists(BASE_PATH.'/vendor/autoload.php'), 'Autoloader does not exist');
});

test('can run the HydeCLI binary', function () {
    $output = shell_exec('cd '.BASE_PATH.' && php hyde --no-ansi');
    $this->assert(str_contains($output, 'USAGE:  <command> [options] [arguments]'),
        'HydeCLI output does not contain "USAGE:  <command> [options] [arguments]"'
    );
});

test('can build the static site', function () {
    $output = shell_exec('cd '.BASE_PATH.' && php hyde build --no-ansi');

    $this->assert(str_contains($output, 'Building your static site!'),
        'HydeCLI output does not contain "Building your static site!"'
    );

    $this->assert(file_exists(BASE_PATH.'/_site/index.html'), 'Index file does not exist');
    $this->assert(file_exists(BASE_PATH.'/_site/404.html'), '404 file does not exist');
    $this->assert(file_exists(BASE_PATH.'/_site/media/app.css'), 'CSS file does not exist');

    $this->assert(str_contains(file_get_contents(BASE_PATH.'/_site/index.html'), 'Welcome to HydePHP!'),
        'Index file does not contain "Welcome to HydePHP!"'
    );
});
