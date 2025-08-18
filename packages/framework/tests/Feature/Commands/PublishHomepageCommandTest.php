<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\PublishHomepageCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Concerns\AsksToRebuildSite::class)]
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

    public function testThereAreNoDefaultPages()
    {
        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandReturnsExpectedOutput()
    {
        $this->artisan('publish:homepage welcome')
            ->expectsConfirmation('Would you like to rebuild the site?')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandReturnsExpectedOutputWithRebuild()
    {
        $this->artisan('publish:homepage welcome')
            ->expectsConfirmation('Would you like to rebuild the site?', 'yes')
            ->expectsOutput('Okay, building site!')
            ->expectsOutput('Site is built!')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
        $this->resetSite();
    }

    public function testCommandPromptsForOutput()
    {
        $this->artisan('publish:homepage')
            ->expectsQuestion(
                'Which homepage do you want to publish?',
                'welcome: The default welcome page.'
            )
            ->expectsOutput('Published page [welcome]')
            ->expectsConfirmation('Would you like to rebuild the site?')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandShowsFeedbackOutputWhenSupplyingAHomepageName()
    {
        $this->artisan('publish:homepage welcome')
            ->expectsOutput('Published page [welcome]')
            ->expectsConfirmation('Would you like to rebuild the site?', false)
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandHandlesErrorCode404()
    {
        $this->artisan('publish:homepage invalid-page')
            ->assertExitCode(404);

        $this->assertFileDoesNotExist(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandDoesNotOverwriteModifiedFilesWithoutForceFlag()
    {
        file_put_contents(Hyde::path('_pages/index.blade.php'), 'foo');

        $this->artisan('publish:homepage welcome')
            ->assertExitCode(409);

        $this->assertSame('foo', file_get_contents(Hyde::path('_pages/index.blade.php')));

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandOverwritesModifiedFilesIfForceFlagIsSet()
    {
        file_put_contents(Hyde::path('_pages/index.blade.php'), 'foo');

        $this->artisan('publish:homepage welcome --force --no-interaction')
            ->assertExitCode(0);

        $this->assertNotSame('foo', file_get_contents(Hyde::path('_pages/index.blade.php')));

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }

    public function testCommandDoesNotReturn409IfTheCurrentFileIsADefaultFile()
    {
        copy(Hyde::vendorPath('resources/views/layouts/app.blade.php'), Hyde::path('_pages/index.blade.php'));

        $this->artisan('publish:homepage welcome --no-interaction')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_pages/index.blade.php'));
    }
}
