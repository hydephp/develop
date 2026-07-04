<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;

/**
 * Deprecated alias for publishing the Hyde config files.
 *
 * Kept working through v3 as a thin delegator: it prints a one-line deprecation
 * notice and forwards to `php hyde vendor:publish --tag=hyde-config`. Target
 * removal in v4.
 *
 * @see \Hyde\Console\Commands\VendorPublishCommand
 */
class PublishConfigsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:configs';

    /** @var string */
    protected $description = 'Publish the default configuration files';

    public function handle(): int
    {
        $this->warn('publish:configs is deprecated. Use php hyde vendor:publish --tag=hyde-config instead.');

        return $this->call('vendor:publish', ['--tag' => 'hyde-config']);
    }
}
