<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;

use function is_string;

/**
 * Deprecated alias for the page flow of the `publish` command.
 *
 * Kept working through v3 as a thin delegator: it prints a one-line deprecation
 * notice and forwards to `php hyde publish --page`, mapping the optional template
 * name and the --force flag. Target removal in v4.
 *
 * @see \Hyde\Console\Commands\PublishCommand
 */
class PublishHomepageCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:homepage {homepage? : The name of the page to publish}
                                {--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Publish one of the default homepages to index.blade.php';

    public function handle(): int
    {
        $homepage = $this->argument('homepage');
        $name = is_string($homepage) ? $homepage : null;

        $hint = $name !== null ? "--page=$name" : '--page';

        $this->warn("publish:homepage is deprecated. Use php hyde publish $hint instead.");

        $parameters = ['--page' => $name];

        if ($this->option('force')) {
            $parameters['--force'] = true;
        }

        return $this->call('publish', $parameters);
    }
}
