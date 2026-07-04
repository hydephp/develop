<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\PublishCommand;
use Hyde\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Covers the PublishCommand spine: the flag surface, all guardrails (§9), and the
 * interactive wizard routing (§3). The views and pages handlers are stubs in this step,
 * so these tests assert routing and guardrails, not real publishing.
 */
#[CoversClass(PublishCommand::class)]
class PublishCommandTest extends TestCase
{
    protected string $viewsStub = 'Publishing views is not yet implemented.';
    protected string $pagesStub = 'Publishing pages is not yet implemented.';

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

    public function testToOptionIsAllowedAlongsideThePageFlag()
    {
        $this->artisan('publish --page --to=_pages/index.blade.php')
            ->expectsOutputToContain($this->pagesStub)
            ->assertExitCode(0);
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

    // Flag routing to the (stubbed) handlers.

    public function testLayoutsFlagRoutesToViews()
    {
        $this->artisan('publish --layouts')
            ->expectsOutputToContain($this->viewsStub)
            ->assertExitCode(0);
    }

    public function testComponentsFlagRoutesToViews()
    {
        $this->artisan('publish --components')
            ->expectsOutputToContain($this->viewsStub)
            ->assertExitCode(0);
    }

    public function testAllFlagRoutesToViews()
    {
        $this->artisan('publish --all')
            ->expectsOutputToContain($this->viewsStub)
            ->assertExitCode(0);
    }

    public function testBarePageFlagRoutesToPages()
    {
        $this->artisan('publish --page')
            ->expectsOutputToContain($this->pagesStub)
            ->assertExitCode(0);
    }

    public function testPageFlagWithNameRoutesToPages()
    {
        $this->artisan('publish --page=welcome')
            ->expectsOutputToContain($this->pagesStub)
            ->assertExitCode(0);
    }

    // Interactive wizard routing (§3).

    public function testWizardRoutesToViews()
    {
        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'views')
            ->expectsOutputToContain($this->viewsStub)
            ->assertExitCode(0);
    }

    public function testWizardRoutesToPages()
    {
        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'page')
            ->expectsOutputToContain($this->pagesStub)
            ->assertExitCode(0);
    }

    public function testWizardCancelExitsCleanlyWithoutPublishing()
    {
        $this->artisan('publish')
            ->expectsQuestion('What do you want to publish?', 'cancel')
            ->doesntExpectOutputToContain($this->viewsStub)
            ->doesntExpectOutputToContain($this->pagesStub)
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
}
