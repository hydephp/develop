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

    public function handle(): int
    {
        $this->title('Preparing a new syndicated HydePHP release!');
        $this->dryRun = $this->option('dry-run');

        $this->fetchAndCheckoutMaster();
        $this->getCurrentVersion();
        $this->askForNewVersion();
        $this->newLine();

        $this->updateVersionConstant();

        if ($this->newVersionType === 'major') {
            $this->warn('This is a major release, please make sure to update the framework version in the Hyde composer.json file!');
        }

        if ($this->newVersionType === 'patch') {
            $this->comment('Skipping release notes preparation for patch release.');
        } else {
            $this->prepareReleaseNotes();
        }

        $this->prepareFrameworkPR();

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
        $state = $this->runUnlessDryRun('git status --porcelain');
        if ($state !== null && $state !== '') {
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

        $this->info("Current version: {$this->currentVersion} <fg=gray>(Framework: v$frameworkVersion)</>");
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

        $this->newVersion = $major . '.' . $minor . '.' . $patch;

        $this->info("New version: v$this->newVersion <fg=gray>($this->newVersionType)</>");
    }

    protected function runUnlessDryRun(string $command): string|null|false
    {
        if ($this->dryRun) {
            $this->gray("DRY RUN: $command");
            return null;
        }

        $state = shell_exec($command);

        if ($state === false || $state === null) {
            $this->fail("Command failed: $command");
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
        $baseDir = __DIR__ . '/../../../';

        $notes = file_get_contents($baseDir .'RELEASE_NOTES.md');

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

        $this->line('Done. ');

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

        $this->line('Done. ');

        $this->output->write('Updating changelog with the upcoming release notes... ');

        $changelog = file_get_contents($baseDir.'/CHANGELOG.md');

        $needle = '<!-- CHANGELOG_START -->';

        $changelog = substr_replace($changelog, $needle."\n\n".$notes, strpos($changelog, $needle), strlen($needle));
        file_put_contents($baseDir.'/CHANGELOG.md', $changelog);

        $this->line('Done. ');
    }

    protected function updateVersionConstant(): void
    {
        $this->output->write('Updating version constant... ');

        $baseDir = __DIR__ . '/../../../';
        $version = ltrim($this->newVersion, 'v');

        $kernelPath = $baseDir . '/packages/framework/src/Foundation/HydeKernel.php';
        $hydeKernel = file_get_contents($kernelPath);
        $hydeKernel = preg_replace('/final public const VERSION = \'(.*)\';/', "final public const VERSION = '$version';", $hydeKernel);
        file_put_contents($kernelPath, $hydeKernel);

        $this->line('Done. ');
    }

    protected function prepareFrameworkPR(): void
    {
        // Create link to draft pull request merging develop into master
        $link = sprintf('https://github.com/hydephp/framework/compare/master...develop?expand=1&?&title=%s&body=%s&draft=true',
            urlencode($this->getTitle()),
            $this->newVersionType === 'patch' ? '' : $this->getCompanionBody()
        );

        $this->info('Opening pull request link in browser. Please review and submit the PR once all changes are propagated.');
        shell_exec((PHP_OS_FAMILY === 'Windows' ? 'explorer' : 'open'). ' '. escapeshellarg($link));
    }

    protected function getTitle(): string
    {
        return "v$this->newVersion - ".date('Y-m-d');
    }

    protected function getCompanionBody(): string
    {
        return sprintf('Please see the release notes in the development monorepo [`Release v%s`](https://github.com/hydephp/develop/releases/tag/v%s)', $this->newVersion, $this->newVersion);
    }
}
