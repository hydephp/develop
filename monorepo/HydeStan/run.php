<?php

declare(strict_types=1);

define('BASE_PATH', realpath(__DIR__.'/../../packages/framework'));

require_once __DIR__.'/HydeStan.php';
require_once __DIR__.'/vendor/php-console/console.php';

$debug = in_array('--debug', $argv, true);

$analyser = new HydeStan($debug);
$analyser->run();

if ($analyser->hasErrors()) {
    exit(1);
}

exit(0);
