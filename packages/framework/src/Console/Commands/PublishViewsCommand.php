<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;

/**
 * Deprecated alias for the views flow of the `publish` command.
 *
 * Kept working through v3 as a thin delegator: it prints a one-line deprecation
 * notice and forwards to `php hyde publish` with the mapped scope flag. Target
 * removal in v4.
 *
 * @see \Hyde\Console\Commands\PublishCommand
 */
class PublishViewsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:views {group? : The group to publish}';

    /** @var string */
    protected $description = 'Publish the Hyde components for customization. Note that existing files will be overwritten';

    public function handle(): int
    {
        // A bare invocation (no group) historically published every group, so it maps to
        // --all rather than the interactive wizard, keeping legacy scripts non-interactive.
        $flag = match ($this->argument('group')) {
            'layouts' => '--layouts',
            'components' => '--components',
            default => '--all',
        };

        $this->warn("publish:views is deprecated. Use php hyde publish $flag instead.");

        return $this->call('publish', [$flag => true]);
    }
}
