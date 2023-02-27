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
