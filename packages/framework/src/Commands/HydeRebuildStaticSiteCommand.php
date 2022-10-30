<?php

declare(strict_types=1);

namespace Hyde\Framework\Commands;

use Exception;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\BuildService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\Services\RebuildService;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to build a single static site file.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeRebuildStaticSiteCommandTest
 */
class HydeRebuildStaticSiteCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'rebuild
        {path : The relative file path (example: _posts/hello-world.md)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run the static site builder for a single file';

    /**
     * The source path.
     *
     * @var string
     */
    public string $path;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $time_start = microtime(true);

        if ($this->argument('path') === '_media') {
            (new BuildService($this->getOutput()))->transferMediaAssets();

            return Command::SUCCESS;
        }

        $this->path = $this->sanitizePathString($this->argument('path'));

        try {
            $this->validate();
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }

        (new RebuildService($this->path))->execute();

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);

        $this->info(sprintf(
            'Created %s in %s seconds. (%sms)',
            DiscoveryService::createClickableFilepath(Hyde::pages()->getPage($this->path)->getOutputPath()),
            number_format(
                $execution_time,
                2
            ),
            number_format(($execution_time * 1000), 2)
        ));

        return Command::SUCCESS;
    }

    /**
     * Perform a basic sanitation to strip trailing characters.
     *
     * @param  string  $path
     * @return string
     */
    public function sanitizePathString(string $path): string
    {
        return str_replace('\\', '/', trim($path, '.\\/'));
    }

    /**
     * Validate the path to catch common errors.
     *
     * @throws Exception
     */
    public function validate(): void
    {
        if (! (
            str_starts_with($this->path, Hyde::pathToRelative(Hyde::getBladePagePath())) ||
            str_starts_with($this->path, Hyde::pathToRelative(Hyde::getMarkdownPagePath())) ||
            str_starts_with($this->path, Hyde::pathToRelative(Hyde::getMarkdownPostPath())) ||
            str_starts_with($this->path, Hyde::pathToRelative(Hyde::getDocumentationPagePath()))
        )) {
            throw new Exception("Path [$this->path] is not in a valid source directory.", 400);
        }

        if (! file_exists(Hyde::path($this->path))) {
            throw new Exception("File [$this->path] not found.", 404);
        }
    }

    /**
     * Output the contents of an exception.
     *
     * @param  Exception  $exception
     * @return int Error code
     */
    public function handleException(Exception $exception): int
    {
        $this->error('Something went wrong!');
        $this->warn($exception->getMessage());

        return (int) $exception->getCode();
    }
}
