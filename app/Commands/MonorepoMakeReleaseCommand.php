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

        $this->cachePath = 'build/cache/release';
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function handle(): int
    {
        $this->title('Creating a new release!');

        if ($this->dryRun) {
            $this->info('This is a dry run. No changes will be pushed to GitHub.');
        }

        $this->task('Fetching origin remote', function() {
            if (! $this->dryRun) {
                $this->git('fetch origin');
            }
        });

        // Fetch remote and abort if we are not synced with the upstream repository.
        // Also abort if we are not on the master branch. (Not doing this now as I am not on the master branch.)

        // First, get the current tag and increment it depending on the desired semver tag.
        $this->task('Getting current tag', function() {
            $this->currentTag = trim(shell_exec('git describe --tags --abbrev=0'));
        });
        $this->line('Current tag: <info>' . $this->currentTag . '</info>');

        // Prompt for what type of release we are creating.
        $this->task('Getting release type', function() {
            $this->releaseType = $this->choice('What type of release are you creating?', [
                'patch', 'minor', 'major'
            ], 'patch');
        });
        $this->line('Release type: <info>' . $this->releaseType . '</info>');

        // Increment the current tag.
        $this->task('Incrementing current tag', function() {
            $this->tag = $this->incrementTag($this->currentTag, $this->releaseType);
        });
        $this->line('New tag: <info>' . $this->tag . '</info>');

        // $this->line('Creating update <info>'. $this->currentTag . '</info> > <info>'. $this->tag . '</info>');

        // Then, move the Unreleased section in the changelog to the desired release,
        // remove any empty sections?

        $this->task('Updating changelog', function() {
            $this->notes = $this->extractChangelog($this->tag);
        });
        $this->line('Changelog entry cached to file://'. str_replace('\\', '/', realpath($this->cachePath.'/changelog-entry.md')));

        // Then create a new Unreleased template

        // Next, create syndicated release drafts
        // (they are drafts as some GitHub actions may need to run before the release is ready
        // plus, it's best if a human actually reviews everything first )

        return 0;
    }

    protected function incrementTag(string $tag, string $releaseType): string
    {
        $prefix = substr($tag, 0, 1);
        $suffix = substr($tag, strpos($tag, '-')); 
        $tag = substr($tag, 1, strpos($tag, '-') - 1);
        $tag = explode('.', $tag);

        foreach ($tag as $key => $value) {
            $tag[$key] = (int) $value;
        }

        switch ($releaseType) {
            case 'patch':
                $tag[2]++;
                break;
            case 'minor':
                $tag[1]++;
                $tag[2] = 0;
                break;
            case 'major':
                $tag[0]++;
                $tag[1] = 0;
                $tag[2] = 0;
                break;
        }

        $tag = implode('.', $tag);
        return $prefix . $tag . $suffix;
    }

    protected function extractChangelog(string $tag): string
    {
        $changelog = file_get_contents('CHANGELOG.md');
        $changelog = str_replace("\r", '', $changelog);
        $changelog = explode("\n", $changelog);
        $changelog = array_slice($changelog, 
            array_search('<!-- UNRELEASED_START -->', $changelog) + 2,
            array_search('<!-- UNRELEASED_END -->', $changelog) - 8
        );
        $changelog = implode("\n", $changelog);
        $changelog = str_replace('## [Unreleased]', '## ' . $tag, $changelog);
        file_put_contents($this->cachePath.'/changelog-entry.md', $changelog);

        return $changelog;
    }
}
