<?php

declare(strict_types=1);

define('BASE_PATH', realpath(__DIR__ . '/../../packages/framework'));

require_once __DIR__ . '/HydeStan.php';
require_once __DIR__ . '/vendor/php-console/console.php';

$analyser = new HydeStan();
$analyser->run();

foreach ($analyser->getErrors() as $error) {
    echo $error . PHP_EOL;
}

if (count($analyser->getErrors()) > 0) {
    exit(1);
}

// If warnings we could try forwarding those to GitHub Actions

exit(0);
