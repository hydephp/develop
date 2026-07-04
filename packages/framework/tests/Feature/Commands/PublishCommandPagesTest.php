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
use Illuminate\Support\Facades\File;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

use function glob;

/**
 * Covers the starter-page publishing flow (§5): named vs. picker selection, the §5.4 destination
 * resolution precedence (--to → non-interactive default → interactive prompt → default), --to
 * validation, destination-conflict detection (§5.6), the interactive confirm (§5.5), the shared
 * overwrite policy (§7) applied to pages, and the interactive-only optional rebuild (§5.7).
 */
#[CoversClass(PublishCommand::class)]
#[CoversClass(PagesPublisher::class)]
class PublishCommandPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Start from a known-empty _pages so each test controls exactly which destinations exist.
        $this->withoutDefaultPages();
    }

    protected function tearDown(): void
    {
        ConsoleHelper::clearMocks();
        PagesPromptsReset::resetFallbacks();
        PublishablePages::clear();

        // Remove anything a test published, then restore the two committed default pages so the tree stays clean.
        foreach (glob(Hyde::path('_pages/*.blade.php')) as $file) {
            File::delete($file);
        }

        $this->restoreDefaultPages();

        parent::tearDown();
    }

    // Named-page publishing (--page=NAME) with non-interactive destination resolution (§5.4 step 2).

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

    // Destination resolution: --to wins over the default (§5.4 step 1).

    public function testToOverridesTheDefaultTarget()
    {
        $this->artisan('publish --page=posts --to=_pages/index.blade.php --no-interaction')
            ->expectsOutputToContain('Published [posts] to [_pages/index.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
        $this->assertFileDoesNotExist(Hyde::path('_pages/posts.blade.php'));
    }

    // A page with no default target (blank) cannot be resolved non-interactively without --to (§5.4 step 2).

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

    // --to validation: must live under _pages/ and end in .blade.php (§5.4 step 1, §9).

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

    // A page that disallows custom targets (404) rejects --to and keeps its fixed default.

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

    // Overwrite policy (§7): identical -> skip, modified -> fail without --force, --force overwrites.

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

    // Interactive destination prompt (§5.4 step 3): default / alternative / custom path.

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

    public function testCustomPathFromPromptIsValidated()
    {
        $this->artisan('publish --page=blank')
            ->expectsQuestion('Where should "Blank page" be published?', '__hyde_custom_target__')
            ->expectsQuestion('Enter a path within _pages/', 'somewhere/else.blade.php')
            ->expectsOutputToContain('The --to path must be within _pages/ and end in .blade.php, for example _pages/index.blade.php.')
            ->assertExitCode(1);
    }

    // Interactive picker flow (§5.5): select -> resolve -> confirm.

    public function testInteractivePickerPublishesSelectedPagesAfterConfirmation()
    {
        // Welcome has a single sensible destination, so it is not prompted for; it resolves to its default.
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

    // Destination-conflict detection before any write (§5.6).

    public function testTwoPagesResolvingToTheSameTargetAreRejectedBeforeWriting()
    {
        // Register a second page whose default collides with welcome's default so the picker offers both.
        PublishablePages::register(new PublishablePage(
            key: 'clash',
            label: 'Clashing page',
            description: 'A page that targets the homepage too.',
            source: 'resources/views/homepages/blank.blade.php',
            defaultTarget: '_pages/index.blade.php',
            allowCustomTarget: false,
        ));

        // Neither page is prompted for (welcome and clash each resolve straight to their default), so the
        // collision is caught purely from the picker selection, before any destination prompt or write.
        $this->artisan('publish --page')
            ->expectsQuestion('Select pages to publish', ['welcome', 'clash'])
            ->expectsOutputToContain('Welcome page and Clashing page both target _pages/index.blade.php.')
            ->expectsOutputToContain('Pick one, or set --to for each.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    // Optional rebuild (§5.7): offered interactively, never non-interactively.

    public function testRebuildIsOfferedInteractivelyAfterPublishing()
    {
        // Welcome resolves to its default without a destination prompt, so the only interaction is the rebuild offer.
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

    // Option 2's whole point: the pages picker must NOT offer an "All" row (unlike the views picker).

    public function testPickerDoesNotOfferAnAllRow()
    {
        // Space+enter selects the first row (welcome); the next enter accepts "Proceed?" (default yes), the
        // last accepts "Rebuild the site now?" (default no) — so the run completes without leftover prompts.
        $output = $this->runPagesPicker([Key::SPACE, Key::ENTER, Key::ENTER, Key::ENTER]);

        Prompt::assertOutputContains('Select pages to publish');
        Prompt::assertOutputContains('Welcome page');
        Prompt::assertOutputDoesntContain('All pages');
        Prompt::assertOutputDoesntContain('All views');

        // The first offered row is a real page (welcome), not a select-all sentinel, so a single space+enter publishes it.
        $this->assertStringContainsString('Published [welcome]', $output->fetch());
    }

    /** Drive the interactive pages picker with faked keystrokes and return the buffered output. */
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
