<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

/**
 * This command is included in the Hyde Monorepo,
 * but is removed when packaging the Hyde application.
 */
class MonorepoMakeReleaseCommand extends Command
{
    protected $signature = 'monorepo:release {--dry-run}';
    protected $description = '🪓 Create a new syndicated release for the Hyde Monorepo';

    protected bool $dryRun = false;

    public function __construct()
    {
        parent::__construct();

        // $this->dryRun = $this->option('dry-run');
        // Force dry run for debugging purposes.
        $this->dryRun = true;
    }

    public function handle(): int
    {
        $this->title('Creating a new release!');

        if ($this->dryRun) {
            $this->info('This is a dry run. No changes will be pushed to GitHub.');
        }

        // Fetch remote and abort if we are not synced with the upstream repository.
        // Also abort if we are not on the master branch.

        // First, get the current tag and increment it depending on the desired semver tag.

        // Then, move the Unreleased section in the changelog to the desired release,
        // remove any empty sections?

        // Then create a new Unreleased template

        // Next, create syndicated release drafts
        // (they are drafts as some GitHub actions may need to run before the release is ready
        // plus, it's best if a human actually reviews everything first )

        return 0;
    }
}
