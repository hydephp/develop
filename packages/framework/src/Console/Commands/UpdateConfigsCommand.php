<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;

/**
 * Publish the Hyde Config Files.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\UpdateConfigsCommandTest
 */
class UpdateConfigsCommand extends Command
{
    /** @var string */
    protected $signature = 'update:configs {tag?}';

    /** @var string */
    protected $description = 'Publish the default configuration files';

    /** @var bool */
    protected $hidden = true;

    public function handle(): int
    {
        $tag = $this->argument('tag') ?? $this->choice('Which configuration files do you want to publish?', [
            'configs',
            'hyde-configs',
            'support-configs',
        ], 'configs');

        Artisan::call('vendor:publish', [
            '--tag' => $tag,
            '--force' => true,
        ], $this->output);

        $this->infoComment(sprintf('Published config files to [%s]', Hyde::path('config')));

        return Command::SUCCESS;
    }
}
