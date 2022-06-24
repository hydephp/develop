<?php

namespace App\Monorepo\Commands;

use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

/**
 * @internal - Bodged together for a quick development aid. Don't use in production.
 *
 * This command is included in the Hyde Monorepo,
 * but is removed when packaging the Hyde application.
 *
 * IDEAS for improvement:
 *
 * @todo Add option to create the git commit and tag (or just commit the updated changelog)
 * @todo Create pull request drafts to merge develop into master for the packages
 */
class MonorepoMakeReleaseCommand extends Command
{
    protected $signature = 'monorepo:release {tag? : Leave blank to prompt for one.} {--dry-run : Don\'t push changes to remote. Will still edit filesystem.} {--allow-duplicates : Allow duplicate release names in the changelog.}';
    protected $description = '🪓 Create a new syndicated release for the Hyde Monorepo';

    protected bool $dryRun = true;

    protected const USER = 'hydephp';

    protected static array $repositories = [
        'develop',
        'framework',
        'hyde',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->cachePath = 'build/cache/release';
        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function handle(): int
    {
        $this->title('Creating a new release!');

        if ($this->dryRun) {
            $this->info('This is a dry run. No changes will be pushed to GitHub.');
            $this->dryRun = true;
        } else {
            $this->info('Changes will be pushed to GitHub at the end of the workflow.');
            $this->dryRun = false;
        }

        if ($this->option('allow-duplicates')) {
            $this->warn('You passed the allow duplicates flag. If you are doing this because a false positive, please create an issue on GitHub!');
        }

        $this->task('Fetching origin remote', function () {
            if (! $this->dryRun) {
                shell_exec('git fetch origin');
            }
        });

        // @TODO Fetch remote and abort if we are not synced with the upstream repository.
        // Also abort if we are not on the master branch. (Not doing this now as I am not on the master branch.)

        // First, get the current tag and increment it depending on the desired semver tag.
        if (! $this->argument('tag')) {
            $this->task('Getting current tag', function () {
                $this->currentTag = trim(shell_exec('git describe --tags --abbrev=0'));
            });
            $this->line('Current tag: <info>'.$this->currentTag.'</info>');

            // Prompt for what type of release we are creating.
            $this->task('Getting release type', function () {
                $this->releaseType = $this->choice('What type of release are you creating?', [
                    'patch', 'minor', 'major',
                ], 'patch');
            });
            $this->line('Release type: <info>'.$this->releaseType.'</info>');

            // Increment the current tag.
            $this->task('Incrementing current tag', function () {
                $this->tag = $this->incrementTag($this->currentTag, $this->releaseType);
            });
            $this->line('New tag: <info>'.$this->tag.'</info>');
        // $this->line('Creating update <info>'. $this->currentTag . '</info> > <info>'. $this->tag . '</info>');
        } else {
            $this->tag = $this->argument('tag');
            $this->line('Creating update <info>'.$this->tag.'</info>');
        }

        // Then, move the Unreleased section in the changelog to the desired release,
        // remove any empty sections?

        $this->task('Updating changelog', function () {
            $this->updateChangelog($this->tag);
        });
        $this->line('Changelog entry cached to file://'.str_replace('\\', '/', realpath($this->cachePath.'/changelog-entry.md')));

        // Create the release notes cache file.
        $this->task('Creating GitHub release notes', function () {
            $this->createReleaseNotes();
        });

        // Next, create syndicated release drafts
        // (they are drafts as some GitHub actions may need to run before the release is ready
        // plus, it's best if a human actually reviews everything first )

        $this->task('Preparing GitHub release object', function () {
            $this->prepareGitHubRelease();
        });

        // Finally, create the releases on GitHub

        $release = json_decode(file_get_contents($this->cachePath.'/release.json'));

        $this->info('Creating release with tag: '.$release->tag_name);
        $this->info('Release data:');
        $this->line('Title: '.$release->name);
        $this->line('Tag: '.$release->tag_name);
        $this->line('Dry run: '.($this->option('dry-run') ? 'true' : 'false'));
        $this->newLine();

        $owner = self::USER;

        foreach (self::$repositories as $repository) {
            $this->info("Creating release for {$owner}/{$repository}");
            $this->createRelease($owner, $repository, $release);
        }

        $this->info('Done!');
        $this->warn('Remember to merge develop branches into master! (And composer/package versions if minor!)');

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

        return $prefix.$tag.$suffix;
    }

    protected function updateChangelog(string $tag)
    {
        $changelog = file_get_contents('CHANGELOG.md');

        // Check if the tag is already in the changelog.
        if (! $this->option('allow-duplicates') && strpos($changelog, '## '.$tag.' - ') !== false) {
            throw new \Exception('The tag is already in used in the changelog at line '.substr_count($changelog, "\n", 0, strpos($changelog, $tag)).'! (Suppy --allow-duplicates to ignore)');
        }

        $changelog = str_replace("\r", '', $changelog);
        $changelog = explode("\n", $changelog);

        $changelog = array_slice($changelog,
            array_search('<!-- UNRELEASED_START -->', $changelog) + 2,
            array_search('<!-- UNRELEASED_END -->', $changelog) - (array_search('<!-- UNRELEASED_START -->', $changelog) + 2)
        );

        $changelog = implode("\n", $changelog);
        $changelog = str_replace('## [Unreleased]', '## '.$tag, $changelog);
        $changelog = str_replace('YYYY-MM-DD', date('Y-m-d'), $changelog);
        file_put_contents($this->cachePath.'/changelog-entry.md', $changelog);

        // Remove everything between the markers
        $changelog = file_get_contents('CHANGELOG.md');

        $updated = substr($changelog, 0, strpos($changelog, '<!-- UNRELEASED_START -->') + 25).
            "\n\n".file_get_contents(__DIR__.'/../Monorepo/stubs/changelog-unreleased.md')."\n".
            substr($changelog, strpos($changelog, '<!-- UNRELEASED_END -->'));

        // Insert the new changelog entry after the <!-- CHANGELOG_START --> marker
        $updated = str_replace('<!-- CHANGELOG_START -->', "<!-- CHANGELOG_START -->\n\n\n".rtrim(file_get_contents($this->cachePath.'/changelog-entry.md')), $updated);

        file_put_contents('CHANGELOG.md', $updated);
    }

    protected function createReleaseNotes()
    {
        $notes = file_get_contents($this->cachePath.'/changelog-entry.md');

        // Remove title and change about heading level
        $notes = substr($notes, strpos($notes, "\n") + 3)."\n<!-- Autogenerated GitHub release notes below -->\n";

        file_put_contents($this->cachePath.'/release-notes.md', $notes);
    }

    protected function prepareGitHubRelease()
    {
        $notes = file_get_contents($this->cachePath.'/release-notes.md');
        $changelog = file_get_contents($this->cachePath.'/changelog-entry.md');
        $title = trim(substr($changelog, 2, strpos($changelog, "\n")));

        $release = [
            'tag_name' => $this->tag,
            'name' => $title,
            'body' => $notes,
            'draft' => true,
        ];
        file_put_contents($this->cachePath.'/release.json', json_encode($release, 128));
    }

    protected function createRelease(string $owner, string $repository, object $release)
    {
        if ($this->dryRun) {
            Http::fake();
        }

        $response = Http::withHeaders([
            'Authorization' => 'token '.env('GITHUB_TOKEN'),
            'Accept' => 'application/vnd.github.v3+json',
        ])->post("https://api.github.com/repos/{$owner}/{$repository}/releases", [
            'tag_name' => $release->tag_name,
            'name' => $release->name,
            'body' => $release->body,
            'draft' => true,
            'prerelease' => false,
            'generate_release_notes' => true,
        ]);

        if ($response->successful()) {
            $this->info("Release created for {$owner}/{$repository}");
            if (! $this->dryRun) {
                $this->line('Release URL: '.$response->json()['html_url']);
            }
        } else {
            $this->error("Failed to create release for {$owner}/{$repository}");
            $this->warn($response->body());
        }
    }
}
