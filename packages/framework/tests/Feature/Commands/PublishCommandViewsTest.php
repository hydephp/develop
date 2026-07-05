<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\PublishCommand;
use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Console\Helpers\InteractiveMultiselect;
use Hyde\Console\Helpers\PublisherConsole;
use Hyde\Console\Helpers\ViewsPublisher;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\File;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(PublishCommand::class)]
#[CoversClass(ViewsPublisher::class)]
#[CoversClass(\Hyde\Console\Helpers\InteractiveMultiselect::class)]
class PublishCommandViewsTest extends TestCase
{
    public function testAllPublishesEveryView()
    {
        $count = $this->viewCount('layouts') + $this->viewCount('components');

        $this->artisan('publish --all --no-interaction')
            ->expectsOutputToContain("Published all $count views to [resources/views/vendor/hyde]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));
    }

    public function testLayoutsPublishesOnlyLayoutsNonInteractively()
    {
        $count = $this->viewCount('layouts');

        $this->artisan('publish --layouts --no-interaction')
            ->expectsOutputToContain("Published all $count views to [resources/views/vendor/hyde/layouts]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde/components'));
    }

    public function testComponentsPublishesOnlyComponentsNonInteractively()
    {
        $count = $this->viewCount('components');

        $this->artisan('publish --components --no-interaction')
            ->expectsOutputToContain("Published all $count views to [resources/views/vendor/hyde/components]")
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/components/article-excerpt.blade.php'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde/layouts'));
    }

    public function testPickerCanPublishASingleView()
    {
        $this->artisan('publish --layouts')
            ->expectsQuestion('Select Hyde views to publish', [$this->source('layouts', 'app.blade.php')])
            ->expectsOutputToContain('Published 1 view to [resources/views/vendor/hyde/layouts/app.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertFileDoesNotExist(Hyde::path('resources/views/vendor/hyde/layouts/page.blade.php'));
    }

    public function testEmptyViewSelectionExitsWithoutPublishing()
    {
        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput([], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        $publisher = new class(new PublisherConsole($command, $input)) extends ViewsPublisher
        {
            protected function selectFiles(array $offered, array $labels): array
            {
                return [];
            }
        };

        $this->assertSame(0, $publisher->publish());

        $contents = $output->fetch();
        $this->assertStringContainsString('No views selected; nothing to publish.', $contents);
        $this->assertStringNotContainsString('Published', $contents);

        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde'));
    }

    public function testPickerCanPublishManyViewsFromOneGroup()
    {
        $this->artisan('publish --layouts')
            ->expectsQuestion('Select Hyde views to publish', [
                $this->source('layouts', 'app.blade.php'),
                $this->source('layouts', 'page.blade.php'),
                $this->source('layouts', 'post.blade.php'),
            ])
            ->expectsOutputToContain('Published 3 views to [resources/views/vendor/hyde/layouts]')
            ->assertExitCode(0);
    }

    public function testPickerBaseDirectorySpansGroupsWhenBothAreSelected()
    {
        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'views')
            ->expectsQuestion('Select Hyde views to publish', [
                $this->source('layouts', 'app.blade.php'),
                $this->source('components', 'article-excerpt.blade.php'),
            ])
            ->expectsOutputToContain('Published 2 views to [resources/views/vendor/hyde]')
            ->assertExitCode(0);
    }

    public function testBaseDirectoryIgnoresSharedSegmentsAfterPathsDiverge()
    {
        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput([], $command->getDefinition());
        $publisher = new class(new PublisherConsole($command, $input)) extends ViewsPublisher
        {
            public function exposeBaseDirectory(array $files): string
            {
                return $this->baseDirectory($files);
            }
        };

        $this->assertSame('resources/views/vendor/hyde', $publisher->exposeBaseDirectory([
            'resources/views/vendor/hyde/layouts/page.blade.php',
            'resources/views/vendor/hyde/components/page.blade.php',
        ]));
    }

    public function testLayoutsPickerIsPrefilteredWithGroupPrefixedLabels()
    {
        $output = $this->runViewsPicker(['--layouts' => true], [Key::SPACE, Key::ENTER]);

        Prompt::assertOutputContains('Select Hyde views to publish');
        Prompt::assertOutputContains('All views');
        Prompt::assertOutputContains('layouts/app.blade.php');
        Prompt::assertOutputDoesntContain('components/');

        // Checking the "All views" sentinel selects every offered (layouts) view.
        $this->assertStringContainsString('Published all', $output->fetch());
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
        $this->assertDirectoryDoesNotExist(Hyde::path('resources/views/vendor/hyde/components'));
    }

    public function testComponentsPickerIsPrefiltered()
    {
        $this->runViewsPicker(['--components' => true], [Key::SPACE, Key::ENTER]);

        Prompt::assertOutputContains('components/article-excerpt.blade.php');
        Prompt::assertOutputDoesntContain('layouts/');
    }

    public function testAllSentinelPreservesNumericOptionKeys()
    {
        if (windows_os()) {
            $this->markTestSkipped('Interactive prompts are not applicable on Windows systems.');
        }

        Prompt::fake([Key::DOWN, Key::SPACE, Key::ENTER]);

        $this->assertSame([404], InteractiveMultiselect::select('Select test option', [404 => 'Not found'], 'All options'));
    }

    public function testIdenticalViewsAreSkippedAsAlreadyCurrent()
    {
        $this->seedAllViews();

        $this->artisan('publish --all --no-interaction')
            ->expectsOutputToContain('All selected views are already up to date.')
            ->assertExitCode(0);
    }

    public function testModifiedViewsCannotBeOverwrittenNonInteractivelyWithoutForce()
    {
        $this->seedAllViews();
        $target = $this->modifyPublishedView();

        $this->artisan('publish --all --no-interaction')
            ->expectsOutput('Cannot overwrite modified files without --force:')
            ->expectsOutputToContain('resources/views/vendor/hyde/layouts/app.blade.php')
            ->expectsOutput('Run again with --force to overwrite.')
            ->assertExitCode(1);

        // Hard stop: the modified file is left untouched and nothing else is written either.
        $this->assertSame('MODIFIED BY USER', File::get($target));
    }

    public function testForceOverwritesModifiedViews()
    {
        $this->seedAllViews();
        $target = $this->modifyPublishedView();

        $this->artisan('publish --all --force --no-interaction')
            ->assertExitCode(0);

        $this->assertNotSame('MODIFIED BY USER', File::get($target));
        $this->assertSame(File::get(Hyde::path($this->source('layouts', 'app.blade.php'))), File::get($target));
    }

    public function testCopyFailureFailsWithoutReportingSuccess()
    {
        app()->instance(\Illuminate\Filesystem\Filesystem::class, new class extends \Illuminate\Filesystem\Filesystem
        {
            public function copy($path, $target): bool
            {
                return false;
            }
        });

        $this->artisan('publish --layouts --no-interaction')
            ->expectsOutputToContain('Error: Failed to copy')
            ->doesntExpectOutputToContain('Published')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
    }

    public function testInvalidViewDoesNotStopValidViewsFromPublishing()
    {
        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput([], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        $validSource = $this->source('layouts', 'app.blade.php');
        $missingSource = $this->source('layouts', 'missing-view.blade.php');

        $publisher = new class(new PublisherConsole($command, $input), $validSource, $missingSource) extends ViewsPublisher
        {
            public function __construct(PublisherConsole $console, protected string $validSource, protected string $missingSource)
            {
                parent::__construct($console);
            }

            protected function collectOfferedFiles(): array
            {
                return [
                    [
                        $this->missingSource => 'resources/views/vendor/hyde/layouts/missing-view.blade.php',
                        $this->validSource => 'resources/views/vendor/hyde/layouts/app.blade.php',
                    ],
                    [
                        $this->missingSource => 'layouts/missing-view.blade.php',
                        $this->validSource => 'layouts/app.blade.php',
                    ],
                ];
            }

            protected function selectFiles(array $offered, array $labels): array
            {
                return array_keys($offered);
            }
        };

        $this->assertSame(1, $publisher->publish());

        $contents = $output->fetch();
        $this->assertStringContainsString('Skipped [resources/views/vendor/hyde/layouts/missing-view.blade.php]: source file ['.$missingSource.'] does not exist.', $contents);
        $this->assertStringContainsString('Published 1 view to [resources/views/vendor/hyde/layouts/app.blade.php]', $contents);
        $this->assertFileDoesNotExist(Hyde::path('resources/views/vendor/hyde/layouts/missing-view.blade.php'));
        $this->assertFileExists(Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php'));
    }

    public function testInteractiveConflictPromptCanOverwrite()
    {
        $this->seedAllViews();
        $target = $this->modifyPublishedView();

        $this->artisan('publish --all')
            ->expectsQuestion('1 selected files already exist and appear modified.', 'overwrite')
            ->expectsOutputToContain('Published 1 view to [resources/views/vendor/hyde/layouts/app.blade.php]')
            ->assertExitCode(0);

        $this->assertNotSame('MODIFIED BY USER', File::get($target));
    }

    public function testInteractiveConflictPromptCanSkip()
    {
        $this->seedAllViews();
        $target = $this->modifyPublishedView();

        $this->artisan('publish --all')
            ->expectsQuestion('1 selected files already exist and appear modified.', 'skip')
            ->expectsOutputToContain('left unchanged because they were modified')
            ->assertExitCode(0);

        $this->assertSame('MODIFIED BY USER', File::get($target));
    }

    public function testInteractiveConflictPromptCanCancel()
    {
        $this->seedAllViews();
        $target = $this->modifyPublishedView();

        $this->artisan('publish --all')
            ->expectsQuestion('1 selected files already exist and appear modified.', 'cancel')
            ->expectsOutputToContain('Cancelled. No views were published.')
            ->assertExitCode(0);

        $this->assertSame('MODIFIED BY USER', File::get($target));
    }

    public function testMixedRunReportsPublishedAlongsideAlreadyCurrentViews()
    {
        // Seed only the layouts so they are already current, then publish everything: components copy, layouts skip.
        $this->artisan('publish --layouts --no-interaction')->assertExitCode(0);

        $components = $this->viewCount('components');
        $layouts = $this->viewCount('layouts');

        $this->artisan('publish --all --no-interaction')
            ->expectsOutputToContain("Published $components views to [resources/views/vendor/hyde/components]")
            ->expectsOutputToContain("$layouts views already up to date and skipped.")
            ->doesntExpectOutputToContain('Published all')
            ->assertExitCode(0);
    }

    protected function viewCount(string $group): int
    {
        return Filesystem::findFiles("packages/framework/resources/views/$group", '.blade.php', true)->count();
    }

    protected function source(string $group, string $file): string
    {
        return (is_dir(Hyde::path('packages')) ? 'packages' : 'vendor/hyde')."/framework/resources/views/$group/$file";
    }

    /** Publish every view so subsequent runs see identical (already current) destinations. */
    protected function seedAllViews(): void
    {
        $this->artisan('publish --all --no-interaction')->assertExitCode(0);
    }

    /** Modify one already-published view so it is seen as user-modified, and return its target path. */
    protected function modifyPublishedView(): string
    {
        $target = Hyde::path('resources/views/vendor/hyde/layouts/app.blade.php');
        File::put($target, 'MODIFIED BY USER');

        return $target;
    }

    /** Drive the interactive picker with faked keystrokes and return the buffered output. */
    protected function runViewsPicker(array $parameters, array $keys): BufferedOutput
    {
        if (windows_os()) {
            $this->markTestSkipped('Interactive prompts are not applicable on Windows systems.');
        }

        Prompt::fake($keys);

        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput($parameters, $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        return $output;
    }

    protected function tearDown(): void
    {
        app()->forgetInstance(\Illuminate\Filesystem\Filesystem::class);

        ConsoleHelper::clearMocks();
        ViewsPromptsReset::resetFallbacks();

        if (File::isDirectory(Hyde::path('resources/views/vendor'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor'));
        }

        parent::tearDown();
    }
}

abstract class ViewsPromptsReset extends Prompt
{
    // Workaround for https://github.com/laravel/prompts/issues/158
    public static function resetFallbacks(): void
    {
        static::$shouldFallback = false;
    }
}
