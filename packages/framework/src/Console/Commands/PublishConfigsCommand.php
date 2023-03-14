<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Illuminate\Support\Facades\Artisan;

use function array_search;
use function sprintf;

/**
 * Publish the Hyde Config Files.
 *
 */
class PublishConfigsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:configs';

    /** @var string */
    protected $description = 'Publish the default configuration files';

    public function handle(): int
    {
        $options = [
            'All configs',
            '<comment>hyde-configs</comment>: Main configuration files',
            '<comment>support-configs</comment>: Laravel and package configuration files',
        ];
        $selection = $this->choice('Which configuration files do you want to publish?', $options, 'All configs');

        $tag = $this->parseTagFromSelection($selection, $options);

        Artisan::call('vendor:publish', [
            '--tag' => $tag,
            '--force' => true,
        ], $this->output);

        $this->infoComment(sprintf('Published config files to [%s]', Hyde::path('config')));

        return Command::SUCCESS;
    }

    protected function parseTagFromSelection(string $selection, array $options): string
    {
        $tags = ['configs', 'hyde-configs', 'support-configs'];

        return $tags[array_search($selection, $options)];
    }
}
