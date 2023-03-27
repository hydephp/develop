<?php

declare(strict_types=1);

require_once __DIR__ . '/minima.php';

exit(main(function (): int {
    if (! is_dir(getcwd() . '/packages')) {
        $this->error('This script must be run from the root of the monorepo');
        $this->warning('Current working directory: ' . getcwd());
        return 1;
    }

    return 0;
}));
