<?php

declare(strict_types=1);

namespace Hyde\MonorepoDevTools;

use LaravelZero\Framework\Commands\Command;

/**
 * @internal This class is internal to the hydephp/develop monorepo.
 */
class MonorepoReleaseCommand extends Command
{
    /** @var string */
    protected $signature = 'monorepo:release';

    /** @var string */
    protected $description = 'Prepare a new syndicated HydePHP release';

    public function handle(): void
    {
        //
    }
}
