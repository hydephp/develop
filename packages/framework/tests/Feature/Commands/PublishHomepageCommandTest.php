<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Step 7 (§8): publish:homepage is now a thin, deprecated delegator to `php hyde publish --page`.
 * It prints a one-line deprecation notice, forwards the optional template name to --page=NAME
 * (a bare invocation maps to --page, i.e. the picker), and forwards the --force flag.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishCommandPagesTest for the real pages flow.
 */
#[CoversClass(\Hyde\Console\Commands\PublishHomepageCommand::class)]
class PublishHomepageCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDefaultPages();
    }

    protected function tearDown(): void
    {
        if (Filesystem::exists('_pages/index.blade.php')) {
            Filesystem::unlink('_pages/index.blade.php');
        }

        $this->restoreDefaultPages();

        parent::tearDown();
    }

    public function testNamedTemplatePrintsNoticeAndDelegatesToPageFlag()
    {
        $this->artisan('publish:homepage welcome --no-interaction')
            ->expectsOutputToContain('publish:homepage is deprecated. Use php hyde publish --page=welcome instead.')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testUnknownTemplateDelegatesAndFailsHelpfully()
    {
        $this->artisan('publish:homepage nope --no-interaction')
            ->expectsOutputToContain('publish:homepage is deprecated. Use php hyde publish --page=nope instead.')
            ->expectsOutputToContain('The page [nope] does not exist.')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testBareInvocationPrintsNoticeAndDelegatesToThePagePicker()
    {
        $this->artisan('publish:homepage')
            ->expectsOutputToContain('publish:homepage is deprecated. Use php hyde publish --page instead.')
            ->expectsQuestion('Select pages to publish', ['welcome'])
            ->expectsConfirmation('Proceed?', 'yes')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->expectsConfirmation('Rebuild the site now?', 'no')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testForceFlagIsForwardedToOverwriteModifiedFiles()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish:homepage welcome --force --no-interaction')
            ->expectsOutputToContain('Published [welcome] to [_pages/index.blade.php]')
            ->assertExitCode(0);

        $this->assertNotSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
    }

    public function testWithoutForceModifiedFilesAreProtected()
    {
        File::put(Hyde::path('_pages/index.blade.php'), 'MODIFIED BY USER');

        $this->artisan('publish:homepage welcome --no-interaction')
            ->expectsOutput('Cannot overwrite modified files without --force:')
            ->assertExitCode(1);

        $this->assertSame('MODIFIED BY USER', File::get(Hyde::path('_pages/index.blade.php')));
    }
}
