<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

main(function (): int {
    if ($this->hasOption('verify')) {
        $this->info('Verifying build files...');
    } else if ($this->hasOption('fix')) {
        $this->info('Fixing build files...');
    } else {
        $this->error('Invalid option: Must be either --verify or --fix');
        return 400;
    }

    return 0;
});
