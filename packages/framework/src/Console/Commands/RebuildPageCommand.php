<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Console\Concerns\Command;
use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Services\BuildService;
use Hyde\Framework\Services\RebuildService;
use Hyde\Hyde;
use Illuminate\Console\OutputStyle;
use function dirname;
use function file_exists;
use function in_array;
use function str_replace;
use function unslash;

/**
 * Hyde Command to build a single static site file.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\RebuildPageCommand
 */
class RebuildPageCommand extends Command
{
    /** @var string */
    protected $signature = 'rebuild {path : The relative file path (example: _posts/hello-world.md)}';

    /** @var string */
    protected $description = 'Run the static site builder for a single file';

    public function handle(): int
    {
        if ($this->argument('path') === Hyde::getMediaDirectory()) {
            (new BuildService($this->getOutput()))->transferMediaAssets();

            $this->info('All done!');

            return Command::SUCCESS;
        }

        return $this->makeBuildTask($this->output, $this->getNormalizedPathString())->run();
    }

    protected function getNormalizedPathString(): string
    {
        return str_replace('\\', '/', unslash($this->argument('path')));
    }

    protected function makeBuildTask(OutputStyle $output, string $path): BuildTask
    {
        return new class($output, $path) extends BuildTask
        {
            public static string $message = 'Rebuilding page';

            protected string $path;

            public function __construct(OutputStyle $output, string $path)
            {
                $this->output = $output;
                $this->path = $path;
            }

            public function handle(): void
            {
                $this->validate();

                (new RebuildService($this->path))->execute();
            }

            public function printFinishMessage(): void
            {
                $this->createdSiteFile(Command::fileLink(
                    Pages::getPage($this->path)->getOutputPath()
                ))->withExecutionTime();
            }

            protected function validate(): void
            {
                $directory = Hyde::pathToRelative(dirname($this->path));

                $directories = [
                    Hyde::pathToRelative(Hyde::getBladePagePath()),
                    Hyde::pathToRelative(Hyde::getBladePagePath()),
                    Hyde::pathToRelative(Hyde::getMarkdownPagePath()),
                    Hyde::pathToRelative(Hyde::getMarkdownPostPath()),
                    Hyde::pathToRelative(Hyde::getDocumentationPagePath()),
                ];

                if (! in_array($directory, $directories)) {
                    throw new Exception("Path [$this->path] is not in a valid source directory.", 400);
                }

                if (! file_exists(Hyde::path($this->path))) {
                    throw new Exception("File [$this->path] not found.", 404);
                }
            }
        };
    }
}
