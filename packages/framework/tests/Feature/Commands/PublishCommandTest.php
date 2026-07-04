<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\PublishCommand;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Covers the PublishCommand spine: the flag surface, all guardrails (§9), and the
 * interactive wizard routing (§3). The views and pages handlers are stubs in this step,
 * so these tests assert routing and guardrails, not real publishing.
 */
#[CoversClass(PublishCommand::class)]
class PublishCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        // The views-routing tests below publish real files; remove them so the tree stays clean.
        if (File::isDirectory(Hyde::path('resources/views/vendor'))) {
            File::deleteDirectory(Hyde::path('resources/views/vendor'));
        }

        parent::tearDown();
    }

    // Guardrails: raw tag/provider/config publishing is redirected to vendor:publish (§9).

    public function testTagFlagIsRedirectedToVendorPublish()
    {
        $this->artisan('publish --tag=foo')
            ->expectsOutputToContain('Use php hyde vendor:publish --tag=foo for tag/provider publishing.')
            ->assertExitCode(1);
    }

    public function testBareTagFlagIsRedirectedToVendorPublish()
    {
        $this->artisan('publish --tag')
            ->expectsOutputToContain('Use php hyde vendor:publish --tag for tag/provider publishing.')
            ->assertExitCode(1);
    }

    public function testProviderFlagIsRedirectedToVendorPublish()
    {
        $this->artisan('publish --provider=FooServiceProvider')
            ->expectsOutputToContain('Use php hyde vendor:publish --provider=FooServiceProvider for tag/provider publishing.')
            ->assertExitCode(1);
    }

    public function testConfigFlagIsRedirectedToVendorPublish()
    {
        $this->artisan('publish --config')
            ->expectsOutputToContain('Config is not published through this command. Use php hyde vendor:publish --tag=hyde-config instead.')
            ->assertExitCode(1);
    }

    // Guardrails: the command's own flag combinations (§9).

    public function testLayoutsAndComponentsAreMutuallyExclusive()
    {
        $this->artisan('publish --layouts --components')
            ->expectsOutputToContain('The --layouts and --components options are mutually exclusive. Use --all to publish both.')
            ->assertExitCode(1);
    }

    public function testToOptionRequiresThePageFlag()
    {
        $this->artisan('publish --to=_pages/index.blade.php')
            ->expectsOutputToContain('--to is only valid when publishing a page.')
            ->assertExitCode(1);
    }

    public function testToOptionRequiresANamedPageNotABarePageFlag()
    {
        // --to names one destination, so it is only valid with a single named page (§5.4); a bare --page
        // (multi-select) with --to is rejected rather than letting one path stand in for several pages.
        $this->artisan('publish --page --to=_pages/index.blade.php')
            ->expectsOutputToContain('--to is only valid when publishing a single page. Use --page=NAME with --to.')
            ->assertExitCode(1);
    }

    public function testNonInteractiveWithNoActionableFlagsFailsWithUsageHint()
    {
        $this->artisan('publish --no-interaction')
            ->expectsOutput('Nothing to publish. Try:')
            ->expectsOutput('  php hyde publish --all')
            ->expectsOutput('  php hyde publish --layouts')
            ->expectsOutput('  php hyde publish --page=welcome')
            ->assertExitCode(1);
    }

    // Flag routing to the views handler. The full views behavior is covered in PublishCommandViewsTest;
    // here we assert only that each flag actually reaches the real views publisher (routing coverage).

    public function testLayoutsFlagRoutesToViews()
    {
        $this->artisan('publish --layouts --no-interaction')
            ->expectsOutputToContain('views to [resources/views/vendor/hyde/layouts]')
            ->assertExitCode(0);
    }

    public function testComponentsFlagRoutesToViews()
    {
        $this->artisan('publish --components --no-interaction')
            ->expectsOutputToContain('views to [resources/views/vendor/hyde/components]')
            ->assertExitCode(0);
    }

    public function testAllFlagRoutesToViews()
    {
        $this->artisan('publish --all --no-interaction')
            ->expectsOutputToContain('Published all')
            ->assertExitCode(0);
    }

    public function testBarePageFlagRoutesToPages()
    {
        // A bare --page needs the interactive picker; non-interactively it reaches the pages flow and fails there.
        $this->artisan('publish --page --no-interaction')
            ->expectsOutputToContain('No page specified for publishing. Provide one, for example --page=welcome.')
            ->assertExitCode(1);
    }

    public function testPageFlagWithNameRoutesToPages()
    {
        // An unknown name proves the flag routes into the pages flow and its registry lookup, without writing anything.
        $this->artisan('publish --page=nonexistent --no-interaction')
            ->expectsOutputToContain('The page [nonexistent] does not exist.')
            ->assertExitCode(1);
    }

    // Interactive wizard routing (§3).

    public function testWizardRoutesToViews()
    {
        $appLayout = (is_dir(Hyde::path('packages')) ? 'packages' : 'vendor/hyde').'/framework/resources/views/layouts/app.blade.php';

        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'views')
            ->expectsQuestion('Select Hyde views to publish', [$appLayout])
            ->expectsOutputToContain('Published 1 view')
            ->assertExitCode(0);
    }

    public function testWizardRoutesToPages()
    {
        // Route through the wizard into the real pages flow. Welcome resolves to the default _pages/index.blade.php,
        // which ships identical to the source, so this is a non-destructive "already up to date" skip.
        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'page')
            ->expectsQuestion('Select pages to publish', ['welcome'])
            ->expectsConfirmation('Proceed?', 'yes')
            ->expectsOutputToContain('All selected pages are already up to date.')
            ->assertExitCode(0);
    }

    public function testWizardCancelExitsCleanlyWithoutPublishing()
    {
        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'cancel')
            ->doesntExpectOutputToContain('Published')
            ->assertExitCode(0);
    }

    // Approach 1 must not swallow genuine mistakes: unknown options and stray arguments
    // still hit Symfony's native errors rather than our redirect or a stub handler.

    public function testUnknownOptionIsNotSwallowed()
    {
        // A typo for --layouts must surface Symfony's native error, not be eaten by our
        // raw-flag interception (which only short-circuits --tag/--provider/--config).
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The "--layout" option does not exist.');

        $this->artisan('publish --layout')->run();
    }

    public function testArbitrarySourcePathArgumentIsRejected()
    {
        // The command declares no arguments, so a stray source path is rejected outright
        // rather than being interpreted as a publishable target.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No arguments expected for "publish" command, got "resources/views/foo.blade.php".');

        $this->artisan('publish resources/views/foo.blade.php')->run();
    }

    // The legacy publish commands are removed in v3, not aliased. Invoking one must raise Symfony's
    // native command-not-found error, proving the command is gone and that no shim intercepts it.

    public function testRemovedLegacyPublishViewsCommandRaisesCommandNotFound()
    {
        $this->expectException(CommandNotFoundException::class);
        $this->expectExceptionMessage('The command "publish:views" does not exist.');

        $this->artisan('publish:views')->run();
    }
}
