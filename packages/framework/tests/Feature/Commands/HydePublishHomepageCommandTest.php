<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\HydePublishHomepageCommand
 */
class HydePublishHomepageCommandTest extends TestCase
{
    protected string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = Hyde::path('_pages/index.blade.php');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->restoreDefaultPages();
    }

    public function test_command_returns_expected_output()
    {
        unlinkIfExists($this->file);
        $this->artisan('publish:homepage welcome')
            ->expectsConfirmation('Would you like to rebuild the site?')
            ->assertExitCode(0);
    }

    public function test_command_returns_expected_output_with_rebuild()
    {
        backupDirectory(Hyde::path('_site'));

        unlinkIfExists($this->file);
        $this->artisan('publish:homepage welcome')
            ->expectsConfirmation('Would you like to rebuild the site?', 'yes')
            ->expectsOutput('Okay, building site!')
            ->expectsOutput('Site is built!')
            ->assertExitCode(0);

        restoreDirectory(Hyde::path('_site'));
    }

    public function test_command_prompts_for_output()
    {
        unlinkIfExists($this->file);
        $this->artisan('publish:homepage')
            ->expectsQuestion(
                'Which homepage do you want to publish?',
                'welcome: The default welcome page.'
            )
            ->expectsOutput('Published page [welcome]')
            ->expectsConfirmation('Would you like to rebuild the site?')
            ->assertExitCode(0);
    }

    public function test_command_shows_feedback_output_when_supplying_a_homepage_name()
    {
        unlinkIfExists($this->file);
        $this->artisan('publish:homepage welcome')
            ->expectsOutput('Published page [welcome]')
            ->expectsConfirmation('Would you like to rebuild the site?', false)
            ->assertExitCode(0);
    }

    public function test_command_handles_error_code_404()
    {
        $this->artisan('publish:homepage invalid-page')
            ->assertExitCode(404);
    }

    public function test_command_does_not_overwrite_modified_files_without_force_flag()
    {
        file_put_contents($this->file, 'foo');

        $this->artisan('publish:homepage welcome')
            ->assertExitCode(409);

        $this->assertEquals('foo', file_get_contents($this->file));
    }

    public function test_command_overwrites_modified_files_if_force_flag_is_set()
    {
        file_put_contents($this->file, 'foo');

        $this->artisan('publish:homepage welcome --force --no-interaction')
            ->assertExitCode(0);

        $this->assertNotEquals('foo', file_get_contents($this->file));
    }

    public function test_command_does_not_return_409_if_the_current_file_is_a_default_file()
    {
        copy(Hyde::vendorPath('resources/views/layouts/app.blade.php'), $this->file);

        $this->artisan('publish:homepage welcome --no-interaction')
            ->assertExitCode(0);
    }
}
