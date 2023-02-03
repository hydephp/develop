<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;

/**
 * Publish the Hyde Config Files.
 *
 * @deprecated May be replaced by vendor:publish in the future.
 * @see \Hyde\Framework\Testing\Feature\Commands\UpdateConfigsCommandTest
 */
class UpdateConfigsCommand extends Command
{
    /** @var string */
    protected $signature = 'update:configs';

    /** @var string */
    protected $description = 'Publish the default configuration files';

    public function handle(): int
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'configs',
            '--force' => true,
        ], $this->output);

        $this->line(sprintf('<info>Published config files to</info> <comment>%s</comment>', Hyde::path('config')));

        return Command::SUCCESS;
    }
}
