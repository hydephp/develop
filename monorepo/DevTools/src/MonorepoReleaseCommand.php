<?php

declare(strict_types=1);

namespace Hyde\MonorepoDevTools;

use Hyde\Foundation\HydeKernel;
use Hyde\Console\Concerns\Command;

/**
 * @internal This class is internal to the hydephp/develop monorepo.
 */
class MonorepoReleaseCommand extends Command
{
    /** @var string */
    protected $signature = 'monorepo:release {--dry-run}';

    /** @var string */
    protected $description = 'Prepare a new syndicated HydePHP release';

    protected const VERSION_TYPES = ['major', 'minor', 'patch'];

    protected bool $dryRun = false;

    protected string $currentVersion;
    protected array $currentVersionParts;

    protected string $newVersionType;
    protected string $newVersion;

    protected bool $failed = false;
    protected string $branch;
    protected string $releaseBody;

    public function handle(): int
    {
        $this->title('Preparing a new syndicated HydePHP release!');
        $this->dryRun = $this->option('dry-run');

        $this->fetchAndCheckoutMaster();
        $this->getCurrentVersion();
        $this->askForNewVersion();
        $this->newLine();
        $this->createNewBranch();

        $this->updateVersionConstant();
        if ($this->isPatch()) {
            $this->commitFrameworkVersion();
        }

        if ($this->isMajor()) {
            $this->warn('This is a major release, please make sure to update the framework version in the Hyde composer.json file!');
        } elseif ($this->isMinor()) {
            $this->warn('Please make sure to update the framework version in the Hyde composer.json file!');
        }

        if ($this->isPatch()) {
            $this->comment('Skipping release notes preparation for patch release.');
        } else {
            $this->prepareReleaseNotes();
        }

        if ($this->isNotPatch()) {
            $this->makeMonorepoCommit();
        }

        $this->prepareFrameworkPR();

        if ($this->isNotPatch()) {
            $this->prepareHydePR();
            $this->prepareDocsPR();
        }

        $this->prepareMonorepoPR();

        $this->confirm('Once the pull requests are merged and propagated, press enter to proceed and draft the releases.', true);

        $this->prepareFrameworkRelease();

        if ($this->isNotPatch()) {
            $this->prepareHydeRelease();
            $this->prepareMonorepoRelease();
        }

        $this->info('All done!');

        return Command::SUCCESS;
    }

    protected function fetchAndCheckoutMaster(): void
    {
        $this->info('Fetching and checking out master branch...');

        $this->runUnlessDryRun('echo hi');
        $this->runUnlessDryRun('git checkout master');
        $this->runUnlessDryRun('git pull');

        $this->exitIfFailed();

        // $this->info('Checking that the working directory is clean...');
        if ($this->runUnlessDryRun('git status --porcelain', true)) {
            $this->fail('Working directory is not clean, aborting.');
        }

        $this->exitIfFailed();
        $this->newLine();
    }

    protected function getCurrentVersion(): void
    {
        $this->currentVersion = trim(shell_exec('git describe --abbrev=0 --tags'));
        $this->currentVersionParts = explode('.', ltrim($this->currentVersion, 'v'));
        $frameworkVersion = HydeKernel::VERSION;

        $this->info("Current version: $this->currentVersion <fg=gray>(Framework: v$frameworkVersion)</>");
    }

    protected function askForNewVersion(): void
    {
        $this->newVersionType = $this->choice('What type of release is this?', static::VERSION_TYPES, 1);

        $major = $this->currentVersionParts[0];
        $minor = $this->currentVersionParts[1];
        $patch = $this->currentVersionParts[2];

        switch ($this->newVersionType) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
                $patch++;
                break;
        }

        $this->newVersion = $major.'.'.$minor.'.'.$patch;

        $this->info("New version: v$this->newVersion <fg=gray>($this->newVersionType)</>");
    }

    protected function runUnlessDryRun(string $command, bool $allowSilent = false): string|null|false
    {
        if ($this->dryRun) {
            $this->gray("DRY RUN: $command");

            return null;
        }

        $state = shell_exec($command);

        if ($allowSilent === false) {
            if ($state === false || ($state === null)) {
                $this->fail("Command failed: $command");
            }
        }

        return $state;
    }

    protected function fail(string $message): void
    {
        $this->newLine();
        $this->error($message);
        $this->newLine();
        $this->failed = true;
    }

    protected function exitIfFailed(): void
    {
        if ($this->failed) {
            exit(1);
        }
    }

    protected function prepareReleaseNotes(): void
    {
        $this->output->write('Transforming upcoming release notes... ');

        $version = $this->newVersion;
        $baseDir = __DIR__.'/../../../';

        $notes = file_get_contents($baseDir.'RELEASE_NOTES.md');

        $notes = str_replace("\r", '', $notes);

        // remove default release notes
        $defaults = [
            '- for new features.',
            '- for changes in existing functionality.',
            '- for soon-to-be removed features.',
            '- for now removed features.',
            '- for any bug fixes.',
            '- in case of vulnerabilities.',
        ];

        foreach ($defaults as $default) {
            $notes = str_replace($default, 'DEFAULT', $notes);
        }

        $notes = str_replace('Keep an Unreleased section at the top to track upcoming changes.

This serves two purposes:

1. People can see what changes they might expect in upcoming releases
2. At release time, you can move the Unreleased section changes into a new release version section.', '', $notes);

        $notes = trim($notes);

        $notes = str_replace('## [Unreleased]', "## [$version](https://github.com/hydephp/develop/releases/tag/$version)", $notes);
        $notes = str_replace('YYYY-MM-DD', date('Y-m-d'), $notes);
        $notes = $notes."\n";

        // remove empty sections
        $notes = preg_replace('/### (Added|Changed|Deprecated|Removed|Fixed|Security)\nDEFAULT/', '', $notes);

        // remove ### About if it's empty
        $notes = str_replace("### About\n\n\n", "\n", $notes);

        // remove empty lines
        $notes = preg_replace('/\n{3,}/', "\n", $notes);

        $this->releaseBody = $notes;

        $this->line('Done.');

        $this->output->write('Resetting upcoming release notes stub... ');
        file_put_contents($baseDir.'RELEASE_NOTES.md', <<<'MARKDOWN'
        ## [Unreleased] - YYYY-MM-DD

        ### About
        
        Keep an Unreleased section at the top to track upcoming changes.
        
        This serves two purposes:
        
        1. People can see what changes they might expect in upcoming releases
        2. At release time, you can move the Unreleased section changes into a new release version section.
        
        ### Added
        - for new features.
        
        ### Changed
        - for changes in existing functionality.
        
        ### Deprecated
        - for soon-to-be removed features.
        
        ### Removed
        - for now removed features.
        
        ### Fixed
        - for any bug fixes.
        
        ### Security
        - in case of vulnerabilities.
        
        MARKDOWN);

        $this->line('Done.');

        $this->output->write('Updating changelog with the upcoming release notes... ');

        $changelog = file_get_contents($baseDir.'/CHANGELOG.md');

        $needle = '<!-- CHANGELOG_START -->';

        $changelog = substr_replace($changelog, $needle."\n\n".$notes, strpos($changelog, $needle), strlen($needle));
        file_put_contents($baseDir.'/CHANGELOG.md', $changelog);

        $this->line('Done.');
    }

    protected function updateVersionConstant(): void
    {
        $this->output->write('Updating version constant... ');

        $baseDir = __DIR__.'/../../../';
        $version = ltrim($this->newVersion, 'v');

        $kernelPath = $baseDir.'/packages/framework/src/Foundation/HydeKernel.php';
        $hydeKernel = file_get_contents($kernelPath);
        $hydeKernel = preg_replace('/final public const VERSION = \'(.*)\';/', "final public const VERSION = '$version';", $hydeKernel);
        file_put_contents($kernelPath, $hydeKernel);

        $this->line('Done.');
    }

    protected function commitFrameworkVersion(): void
    {
        $this->output->write('Committing framework version change... ');

        $this->runUnlessDryRun('git add packages/framework/src/Foundation/HydeKernel.php', true);
        $this->runUnlessDryRun('git commit -m "Framework version v'.$this->newVersion.'"');

        $this->exitIfFailed();

        $this->line('Done.');
    }

    protected function makeMonorepoCommit(): void
    {
        $this->output->write('Committing framework version change... ');

        $this->runUnlessDryRun('git add .', true);
        $this->runUnlessDryRun('git commit -m "HydePHP v'.$this->newVersion.' - '.date('Y-m-d').'"');

        $this->exitIfFailed();

        $this->line('Done.');
    }

    protected function prepareFrameworkPR(): void
    {
        $this->preparePackagePR('framework');
    }

    protected function prepareHydePR(): void
    {
        $this->preparePackagePR('hyde');
    }

    protected function prepareDocsPR(): void
    {
        $this->preparePackagePR('hydephp.com', 'upcoming', 'Merge upcoming documentation', 'This PR merges the upcoming documentation for `v'.$this->newVersion.'` into the master branch.');
    }

    protected function getTitle(): string
    {
        return "HydePHP v$this->newVersion - ".date('Y-m-d');
    }

    protected function getCompanionBody(): string
    {
        // return sprintf('Please see the release notes in the development monorepo [`Release v%s`](https://github.com/hydephp/develop/releases/tag/v%s)', $this->newVersion, $this->newVersion);
        return sprintf('Please see the release notes in the development monorepo https://github.com/hydephp/develop/releases/tag/v%s', $this->newVersion);
    }

    protected function preparePackagePR(string $package, string $branch = 'develop', ?string $title = null, ?string $body = null): void
    {
        // Create link to draft pull request merging develop into master
        $link = sprintf('https://github.com/hydephp/'.$package.'/compare/master...'.$branch.'?expand=1&draft=1&title=%s&body=%s',
            urlencode($title ?? $this->getTitle()),
            $body ?? ($this->isPatch() ? '' : $this->getCompanionBody())
        );

        $this->info("Opening $package pull request link in browser. Please review and submit the PR once all changes are propagated.");
        $this->runUnlessDryRun((PHP_OS_FAMILY === 'Windows' ? 'explorer' : 'open').' '.escapeshellarg($link), true);
    }

    protected function prepareMonorepoPR(): void
    {
        $title = $this->isPatch()
            ? "Framework version v$this->newVersion"
            : "HydePHP v$this->newVersion - ".date('Y-m-d');

        $body = $this->releaseBody;

        // Inject "version" before version in PR body
        $body = preg_replace('/## \[(.*)]/', '## Version [v$1]', $body, 1);

        $link = sprintf('https://github.com/hydephp/develop/compare/master...'.$this->branch.'?expand=1&draft=1&title=%s&body=%s',
            urlencode($title),
            $this->isPatch() ? 'Framework patch release' : urlencode($body)
        );

        if ($this->dryRun) {
            $this->info('Opening release pull request link in browser. Please review and submit the PR.');
        } else {
            if (PHP_OS_FAMILY === 'Windows') {
                // Seems to be the most reliable way to get the encoding right
                shell_exec('powershell -Command "Start-Process \''.$link.'\'"');
            } else {
                shell_exec('open'.' '.escapeshellarg($link));
            }
        }
    }

    protected function prepareFrameworkRelease(): void
    {
        $this->preparePackageRelease('framework');
    }

    protected function prepareHydeRelease(): void
    {
        $this->preparePackageRelease('hyde');
    }

    protected function preparePackageRelease(string $package): void
    {
        $version = "v$this->newVersion";
        $title = "$version - ".date('Y-m-d');
        $companionBody = $this->getCompanionBody();

        $link = "https://github.com/hydephp/$package/releases/new?tag=$version&title=".urlencode($title).'&body='.urlencode($companionBody);

        $this->info("Opening $package release link in browser. Please review and submit the release.");
        $this->runUnlessDryRun(sprintf("%s '%s'", PHP_OS_FAMILY === 'Windows' ? 'powershell -Command "Start-Process ' : 'open', $link), true);
    }

    protected function prepareMonorepoRelease(): void
    {
        $version = "v$this->newVersion";
        $title = "$version - ".date('Y-m-d');
        $body = "$this->releaseBody\n## What's Changed in the Monorepo";

        $link = "https://github.com/hydephp/develop/releases/new?tag=$version&title=".urlencode($title).'&body='.urlencode($body);

        $this->info('Opening monorepo release link in browser. Please review and submit the release.');
        $this->runUnlessDryRun(sprintf("%s '%s'", PHP_OS_FAMILY === 'Windows' ? 'powershell -Command "Start-Process ' : 'open', $link), true);
    }

    protected function createNewBranch(): void
    {
        $prefix = $this->isPatch() ? 'framework' : 'release';
        $name = "$prefix-v$this->newVersion";
        $this->branch = $name;

        $this->info("Creating new branch $name... ");
        $this->runUnlessDryRun('git checkout -b '.$name, true);

        // Verify changed to new branch
        $state = $this->runUnlessDryRun('git branch --show-current');

        if ($this->dryRun !== true && trim($state ?? '') !== $name) {
            $this->fail("Failed to checkout new branch $name, aborting.");
        }

        $this->exitIfFailed();

        $this->line('Checked out new branch.');
    }

    protected function isMajor(): bool
    {
        return $this->newVersionType === 'major';
    }

    protected function isMinor(): bool
    {
        return $this->newVersionType === 'minor';
    }

    protected function isPatch(): bool
    {
        return $this->newVersionType === 'patch';
    }

    protected function isNotPatch(): bool
    {
        return $this->newVersionType !== 'patch';
    }
}
