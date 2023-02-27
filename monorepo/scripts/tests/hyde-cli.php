<?php

declare(strict_types=1);

require_once __DIR__.'/MicroTest.php';

test('example', function () {
    $this->assert(true);
});

test('can install composer dependencies', function () {
    $this->assert(! file_exists(BASE_PATH.'/vendor/autoload.php'), 'Autoloader already exists');

    $output = shell_exec('cd '.BASE_PATH.' && composer install');

    $this->assert(file_exists(BASE_PATH.'/vendor/autoload.php'), 'Autoloader does not exist');

    $expected = [
        'Loading composer repositories with package information',
        'Installing dependencies from lock file',
        'Generating optimized autoload files',
    ];

    foreach ($expected as $line) {
        $this->assert(str_contains($output, $line), 'Composer output does not contain expected "'.$line.'"');
    }

    $this->assert(str_contains($output, '@php -r "@unlink(\'./app/storage/framework/cache/packages.php\');"'),
        'The package cache file was not deleted'
    );

    $this->assert(str_contains($output, '@php hyde package:discover --ansi'),
        'The package discovery command was not run'
    );
});

test('can run the HydeCLI binary', function () {
    $output = shell_exec('cd '.BASE_PATH.' && php hyde --no-ansi');
    $this->assert(str_contains($output, 'USAGE: hyde <command> [options] [arguments]'),
        'HydeCLI output does not contain "USAGE: hyde <command> [options] [arguments]"'
    );
});
