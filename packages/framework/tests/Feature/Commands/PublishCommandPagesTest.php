<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\PublishCommand;
use Hyde\Console\Helpers\ConsoleHelper;
use Hyde\Console\Helpers\PagesPublisher;
use Hyde\Console\Helpers\PublishablePage;
use Hyde\Console\Helpers\PublishablePages;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function glob;

#[CoversClass(PublishCommand::class)]
#[CoversClass(PagesPublisher::class)]
class PublishCommandPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDefaultPages();
    }

    protected function tearDown(): void
    {
        app()->forgetInstance(\Illuminate\Filesystem\Filesystem::class);

        ConsoleHelper::clearMocks();
        PagesPromptsReset::resetFallbacks();
        PublishablePages::clear();

        foreach (glob(Hyde::path('_pages/*.blade.php')) as $file) {
            File::delete($file);
        }

        $this->restoreDefaultPages();

        parent::tearDown();
    }

    public function testNamedPagePublishesToItsDefaultTargetNonInteractively()
    {
        $this->artisan('publish --page=welcome --no-interaction')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
        $this->assertSame(
            File::get(Hyde::vendorPath('resources/views/homepages/welcome.blade.php')),
            File::get(Hyde::path('_pages/index.blade.php'))
        );
    }

    // §2/§11: --all means "all views" and does not apply to pages. Combined with --page, the page
    // flow wins (page-intent is resolved before view-intent) and --all is inert — it must not divert
    // into the views "Published all" path. PagesPublisher never reads the --all option.
    public function testAllFlagDoesNotApplyToPagesWhenCombinedWithPage()
    {
        $this->artisan('publish --page=welcome --all --no-interaction')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->doesntExpectOutputToContain('Published all')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testUnknownPageNameFailsHelpfully()
    {
        $this->artisan('publish --page=nope --no-interaction')
            ->expectsOutputToContain('The page [nope] does not exist.')
            ->expectsOutputToContain('Available pages: welcome, posts, blank, 404')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    // The '404' key is a string on the value object but coerces to an int array key: lookup must compare ->key (§5.1).

    public function testNumericPageKeyIsResolvedByItsStringKey()
    {
        $this->artisan('publish --page=404 --no-interaction')
            ->expectsOutputToContain('Published [404] to [_pages/404.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/404.blade.php'));
    }

    public function testToOverridesTheDefaultTarget()
    {
        $this->artisan('publish --page=posts --to=_pages/index.blade.php --no-interaction')
            ->expectsOutputToContain('Published [posts] to [_pages/index.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
        $this->assertFileDoesNotExist(Hyde::path('_pages/posts.blade.php'));
    }

    public function testPageWithoutDefaultTargetFailsNonInteractivelyWithoutTo()
    {
        $this->artisan('publish --page=blank --no-interaction')
            ->expectsOutputToContain('The [blank] page has no default destination. Provide one with --to.')
            ->assertExitCode(1);
    }

    public function testPageWithoutDefaultTargetPublishesWithTo()
    {
        $this->artisan('publish --page=blank --to=_pages/about.blade.php --no-interaction')
            ->expectsOutputToContain('Published [blank] to [_pages/about.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/about.blade.php'));
    }

    public function testToPathOutsidePagesDirectoryIsRejected()
    {
        $this->artisan('publish --page=welcome --to=resources/views/foo.blade.php --no-interaction')
            ->expectsOutputToContain('The --to path must be within _pages/ and end in .blade.php, for example _pages/index.blade.php.')
            ->assertExitCode(1);
    }

    public function testToPathWithWrongExtensionIsRejected()
    {
        $this->artisan('publish --page=welcome --to=_pages/index.md --no-interaction')
            ->expectsOutputToContain('The --to path must be within _pages/ and end in .blade.php, for example _pages/index.blade.php.')
            ->assertExitCode(1);
    }

    public function testToIsRejectedForAPageThatDisallowsCustomTargets()
    {
        $this->artisan('publish --page=404 --to=_pages/error.blade.php --no-interaction')
            ->expectsOutputToContain('The [404] page cannot be published to a custom path; omit --to to use its default (_pages/404.blade.php).')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/error.blade.php'));
    }

    // The two --to rejections must never disagree. Bare --page + --to is a multi-select context with a single
    // destination: the "one path can't serve several pages" guard (message A) must win over any per-page reason
    // (message B, e.g. 404's custom-path rejection) AND fire before the picker — so this interactive run, which
    // would hang on an unanswered picker prompt if the picker were reached, asks nothing and exits on message A.

    public function testBarePageWithToIsRejectedBeforeThePickerAndBeatsThePerPageReason()
    {
        $this->artisan('publish --page --to=_pages/error.blade.php')
            ->expectsOutputToContain('--to is only valid when publishing a single page. Use --page=NAME with --to.')
            ->doesntExpectOutputToContain('cannot be published to a custom path')
            ->assertExitCode(1);
    }

    public function testIdenticalPageIsSkippedAsAlreadyCurrent()
    {
        $this->artisan('publish --page=welcome --no-interaction')->assertExitCode(0);

        $this->artisan('publish --page=welcome --no-interaction')
            ->expectsOutputToContain('All selected pages are already up to date.')
            ->assertExitCode(0);
    }

    public function testModifiedPageCannotBeOverwrittenNonInteractivelyWithoutForce()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish --page=welcome --no-interaction')
            ->expectsOutput('Cannot overwrite modified files without --force:')
            ->expectsOutputToContain('_pages/index.blade.php')
            ->expectsOutput('Run again with --force to overwrite.')
            ->assertExitCode(1);

        $this->assertSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
    }

    public function testForceOverwritesModifiedPage()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish --page=welcome --force --no-interaction')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->assertExitCode(0);

        $this->assertNotSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
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

        $this->artisan('publish --page=welcome --no-interaction')
            ->expectsOutputToContain('Error: Failed to copy')
            ->doesntExpectOutputToContain('Published')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testInteractiveResolutionCanChooseAnAlternativeTarget()
    {
        $this->artisan('publish --page=posts')
            ->expectsQuestion('Where should "Posts feed" be published?', '_pages/index.blade.php')
            ->expectsOutputToContain('Published [posts] to [_pages/index.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
        $this->assertFileDoesNotExist(Hyde::path('_pages/posts.blade.php'));
    }

    public function testInteractiveResolutionCanChooseACustomPath()
    {
        $this->artisan('publish --page=blank')
            ->expectsQuestion('Where should "Blank page" be published?', '__hyde_custom_target__')
            ->expectsQuestion('Enter a path within _pages/', '_pages/custom.blade.php')
            ->expectsOutputToContain('Published [blank] to [_pages/custom.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/custom.blade.php'));
    }

    public function testCustomPathFromPromptRepromptsUntilValid()
    {
        if (windows_os()) {
            $this->markTestSkipped('Interactive prompts are not applicable on Windows systems.');
        }

        PagesPromptsReset::resetFallbacks();

        $invalidPath = 'somewhere/else.blade.php';

        Prompt::fake([
            Key::ENTER,
            $invalidPath,
            Key::ENTER,
            ...array_fill(0, strlen($invalidPath), Key::BACKSPACE),
            '_pages/custom.blade.php',
            Key::ENTER,
            Key::ENTER,
        ]);

        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput(['--page' => 'blank'], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        $this->assertSame(0, $command->handle());

        $this->assertFileExists(Hyde::path('_pages/custom.blade.php'));
        $this->assertStringContainsString('Published [blank] to [_pages/custom.blade.php]', $output->fetch());
        Prompt::assertStrippedOutputContains('The path must be within _pages/ and end in .blade.php.');
    }

    public function testInteractivePickerPublishesSelectedPagesAfterConfirmation()
    {
        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome'])
            ->expectsOutput('Ready to publish:')
            ->expectsOutputToContain('Welcome page → _pages/index.blade.php')
            ->expectsConfirmation('Proceed?', 'yes')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testInteractivePickerCanBeDeclinedAtConfirmation()
    {
        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome'])
            ->expectsConfirmation('Proceed?', 'no')
            ->expectsOutputToContain('Cancelled. No pages were published.')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testEmptyPageSelectionExitsWithoutPublishing()
    {
        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput([], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        $publisher = new class($command, $input) extends PagesPublisher
        {
            protected function selectPages(): ?array
            {
                return [];
            }
        };

        $this->assertSame(0, $publisher->publish());

        $contents = $output->fetch();
        $this->assertStringContainsString('No pages selected; nothing to publish.', $contents);
        $this->assertStringNotContainsString('Ready to publish:', $contents);
        $this->assertStringNotContainsString('Published', $contents);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
        $this->assertFileDoesNotExist(Hyde::path('_pages/404.blade.php'));
    }

    public function testTwoPagesResolvingToTheSameTargetAreRejectedBeforeWriting()
    {
        PublishablePages::register(new PublishablePage(
            key: 'clash',
            label: 'Clashing page',
            description: 'A page that targets the homepage too.',
            source: 'resources/views/homepages/blank.blade.php',
            defaultTarget: '_pages/index.blade.php',
            allowCustomTarget: false,
        ));

        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome', 'clash'])
            ->expectsOutputToContain('Welcome page and Clashing page both target _pages/index.blade.php.')
            ->expectsOutputToContain('Pick one, or set --to for each.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testCustomTargetWithDuplicateSlashesConflictsWithNormalizedDefaultTarget()
    {
        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome', 'blank'])
            ->expectsQuestion('Where should "Blank page" be published?', '__hyde_custom_target__')
            ->expectsQuestion('Enter a path within _pages/', '_pages//index.blade.php')
            ->expectsOutputToContain('Welcome page and Blank page both target _pages/index.blade.php.')
            ->expectsOutputToContain('Pick one, or set --to for each.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testRebuildIsOfferedInteractivelyAfterPublishing()
    {
        $this->artisan('publish --page=welcome')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);
    }

    public function testRebuildIsNeverOfferedNonInteractively()
    {
        $this->artisan('publish --page=welcome --no-interaction')
            ->doesntExpectOutputToContain('Rebuild the site now?')
            ->assertExitCode(0);
    }

    // The picker round-trips the numeric '404' key: PHP coerces it to an int option key, and it must cast
    // back to the string key to resolve. This covers the sneakier path the named --page=404 test cannot reach.

    public function testPickerCanSelectTheNumericKeyedPage()
    {
        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['404'])
            ->expectsConfirmation('Proceed?', 'yes')
            ->expectsOutputToContain('Published [404] to [_pages/404.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/404.blade.php'));
    }

    public function testPickerDoesNotOfferAnAllRow()
    {
        $output = $this->runPagesPicker([Key::SPACE, Key::ENTER, Key::ENTER, Key::ENTER]);

        Prompt::assertOutputContains('Select pages to publish');
        Prompt::assertOutputContains('Welcome page');
        Prompt::assertOutputDoesntContain('All pages');
        Prompt::assertOutputDoesntContain('All views');

        $this->assertStringContainsString('Published [welcome]', $output->fetch());
    }

    public function testBarePageWithoutInteractionFailsHelpfully()
    {
        $this->artisan('publish --page --no-interaction')
            ->expectsOutputToContain('No page specified for publishing. Provide one, for example --page=welcome.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    // §9: a --to path may not escape _pages/ via traversal, even though it starts with _pages/ and ends in .blade.php.

    public function testToPathWithParentTraversalIsRejected()
    {
        $this->artisan('publish --page=welcome --to=_pages/../secret.blade.php --no-interaction')
            ->expectsOutputToContain('The --to path must be within _pages/ and end in .blade.php, for example _pages/index.blade.php.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('secret.blade.php'));
    }

    // §5.6: three or more pages colliding on one target switch the message from "both target" to "all target".

    public function testThreePagesResolvingToTheSameTargetReportAllTarget()
    {
        foreach (['clash-one' => 'Clash One', 'clash-two' => 'Clash Two'] as $key => $label) {
            PublishablePages::register(new PublishablePage(
                key: $key,
                label: $label,
                description: 'A page that targets the homepage too.',
                source: 'resources/views/homepages/blank.blade.php',
                defaultTarget: '_pages/index.blade.php',
                allowCustomTarget: false,
            ));
        }

        // None of the three is prompted for (each resolves straight to _pages/index.blade.php), so the collision is
        // caught from the picker selection alone, before any confirmation or write.
        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome', 'clash-one', 'clash-two'])
            ->expectsOutputToContain('Welcome page, Clash One and Clash Two all target _pages/index.blade.php.')
            ->expectsOutputToContain('Pick one, or set --to for each.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testInteractiveConflictPromptCanOverwriteAPage()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish --page=welcome')
            ->expectsQuestion('1 selected files already exist and appear modified.', 'overwrite')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertNotSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
    }

    public function testApprovedOverwriteAbortsWhenDestinationChangesAfterPrompt()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BEFORE PROMPT');

        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput([], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        $publisher = new class($command, $input) extends PagesPublisher
        {
            protected function selectPages(): ?array
            {
                return [PublishablePages::get('welcome')];
            }

            protected function resolveBlocked(array $blocked): ?array
            {
                File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED AFTER PROMPT');

                return $blocked;
            }
        };

        try {
            $publisher->publish();
            $this->fail('The publisher should abort when an approved destination changes before copy.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Cannot publish: destination [_pages/index.blade.php] changed after overwrite checks. Run the command again.', $exception->getMessage());
        }

        $this->assertSame('MODIFIED AFTER PROMPT', File::get(Hyde::path('_pages/index.blade.php')));
    }

    public function testInteractiveConflictPromptCanSkipAModifiedPage()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish --page=welcome')
            ->expectsQuestion('1 selected files already exist and appear modified.', 'skip')
            ->expectsOutputToContain('1 page left unchanged because they were modified:')
            ->expectsOutputToContain('_pages/index.blade.php')
            ->expectsOutputToContain('Run again with --force to overwrite.')
            ->assertExitCode(0);

        $this->assertSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
    }

    public function testInteractiveConflictPromptCanCancelForPages()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish --page=welcome')
            ->expectsQuestion('1 selected files already exist and appear modified.', 'cancel')
            ->expectsOutputToContain('Cancelled. No pages were published.')
            ->assertExitCode(0);

        $this->assertSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
    }

    public function testMixedRunReportsPublishedAlongsideAlreadyCurrentPages()
    {
        $this->artisan('publish --page=welcome --no-interaction')->assertExitCode(0);
        $this->artisan('publish --page=404 --no-interaction')->assertExitCode(0);

        PublishablePages::register(new PublishablePage(
            key: 'about',
            label: 'About page',
            description: 'A simple about page.',
            source: 'resources/views/homepages/blank.blade.php',
            defaultTarget: '_pages/about.blade.php',
        ));

        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome', '404', 'about'])
            ->expectsConfirmation('Proceed?', 'yes')
            ->expectsOutputToContain('Published [about] to [_pages/about.blade.php]')
            ->expectsOutputToContain('2 pages already up to date and skipped.')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/about.blade.php'));
    }

    // §5.7: accepting the interactive rebuild offer runs the build command; declining is covered elsewhere.
    // The command is driven directly (not through the console kernel) so that mocking the Artisan facade
    // intercepts only maybeRebuild's own build call, rather than the runner's call that dispatches the command.

    public function testAcceptingTheRebuildOfferRunsTheBuild()
    {
        if (windows_os()) {
            $this->markTestSkipped('Interactive prompts are not applicable on Windows systems.');
        }

        PagesPromptsReset::resetFallbacks();

        Artisan::shouldReceive('call')->once()->with('build', [], \Mockery::any())->andReturn(0);

        Prompt::fake(['y', Key::ENTER]);

        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput(['--page' => 'welcome'], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));

        $this->assertSame(0, $command->handle());
        $this->assertStringContainsString('Published [welcome]', $output->fetch());
    }

    protected function runPagesPicker(array $keys): BufferedOutput
    {
        if (windows_os()) {
            $this->markTestSkipped('Interactive prompts are not applicable on Windows systems.');
        }

        // Earlier --no-interaction runs in this class leave Prompt::$shouldFallback stuck true, which would
        // route the picker through the (unrendered) fallback path; reset it so the prompt renders to the fake buffer.
        PagesPromptsReset::resetFallbacks();

        Prompt::fake($keys);

        $command = $this->app->make(PublishCommand::class);
        $input = new ArrayInput(['--page' => null], $command->getDefinition());
        $output = new BufferedOutput();
        $command->setLaravel($this->app);
        $command->setInput($input);
        $command->setOutput(new OutputStyle($input, $output));
        $command->handle();

        return $output;
    }
}

abstract class PagesPromptsReset extends Prompt
{
    // Workaround for https://github.com/laravel/prompts/issues/158
    public static function resetFallbacks(): void
    {
        static::$shouldFallback = false;
    }
}
