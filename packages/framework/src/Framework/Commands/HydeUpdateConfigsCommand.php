<?php

declare(strict_types=1);

namespace Hyde\Framework\Commands;

use Hyde\Hyde;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

/**
 * Publish the Hyde Config Files.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeUpdateConfigsCommandTest
 */
class HydeUpdateConfigsCommand extends Command
{
    /** @var string */
    protected $signature = 'update:configs';

    /** @var string */
    protected $description = 'Publish the default configuration files';

    public function handle(): int
    {
        File::copyDirectory(Hyde::vendorPath('config'), Hyde::path('config'));

        $this->line('<info>Published config files to</info> <comment>'.Hyde::path('config').'</comment>');

        return 0;
    }
}
