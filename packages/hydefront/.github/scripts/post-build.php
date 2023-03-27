<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

main(function (): int {
    $this->info('Verifying build files...');

    if ($this->hasOption('fix')) {
        $this->info('Fixing build files...');
    }

    return 0;
});
