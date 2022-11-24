<?php

declare(strict_types=1);

require_once __DIR__ . '/HydeStan.php';

$analyser = new HydeStan();
$analyser->run();

foreach ($analyser->getErrors() as $error) {
    echo $error . PHP_EOL;
}

if ($analyser->hasErrors()) {
    exit(1);
}

exit(0);
