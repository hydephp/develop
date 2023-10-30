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
}
