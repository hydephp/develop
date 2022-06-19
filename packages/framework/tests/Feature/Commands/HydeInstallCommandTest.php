<?php

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeInstallCommand
 */
class HydeInstallCommandTest extends TestCase
{
    public function test_command_output()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', true)
            ->expectsOutput('Installing HydePHP...')
            ->doesntExpectOutput('Aborting installation.')
            ->expectsQuestion('What is the name of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('What is the URL of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsOutput('Hyde has a few different homepage options.')
            ->expectsQuestion('Would you like to select an index.blade.php file?', false)
            ->expectsQuestion('Would you like to rebuild the site?', false)
            ->assertExitCode(0);
    }

    public function test_command_exits_with_sigint_130_if_user_declines_confirmation()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', false)
            ->expectsOutput('Aborting installation.')
            ->doesntExpectOutput('Installing HydePHP...')
            ->assertExitCode(130);
    }

    public function test_prompt_for_site_name_saves_selected_site_name()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', true)
            ->expectsQuestion('What is the name of your site? <fg=gray>(leave blank to skip)</>', 'My Site')
            ->expectsQuestion('What is the URL of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('Would you like to select an index.blade.php file?', false)
            ->expectsQuestion('Would you like to rebuild the site?', false)
            ->assertExitCode(0);

        $this->assertStringContainsString('My Site',
            file_get_contents(Hyde::path('config/hyde.php')));
    }

    public function test_prompt_for_site_name_does_nothing_if_user_skips()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', true)
            ->expectsQuestion('What is the name of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('What is the URL of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('Would you like to select an index.blade.php file?', false)
            ->expectsQuestion('Would you like to rebuild the site?', false)
            ->assertExitCode(0);

        $this->assertStringNotContainsString('My Site',
            file_get_contents(Hyde::path('config/hyde.php')));
    }

    public function test_prompt_for_site_url_saves_selected_site_url()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', true)
            ->expectsQuestion('What is the name of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('What is the URL of your site? <fg=gray>(leave blank to skip)</>', 'https://foo.example.com')
            ->expectsQuestion('Would you like to select an index.blade.php file?', false)
            ->expectsQuestion('Would you like to rebuild the site?', false)
            ->assertExitCode(0);

        $this->assertStringContainsString('https://foo.example.com',
            file_get_contents(Hyde::path('config/hyde.php')));
    }

    public function test_prompt_for_site_url_does_nothing_if_user_skips()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', true)
            ->expectsQuestion('What is the name of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('What is the URL of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('Would you like to select an index.blade.php file?', false)
            ->expectsQuestion('Would you like to rebuild the site?', false)
            ->assertExitCode(0);

        $this->assertStringContainsString("env('SITE_URL', null)",
            file_get_contents(Hyde::path('config/hyde.php')));
    }

    public function test_command_calls_publish_homepage_command()
    {
        $this->artisan('install')
            ->expectsOutputToContain('Welcome to HydePHP!')
            ->expectsQuestion('Do you want to continue?', true)
            ->expectsOutput('Installing HydePHP...')
            ->doesntExpectOutput('Aborting installation.')
            ->expectsQuestion('What is the name of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsQuestion('What is the URL of your site? <fg=gray>(leave blank to skip)</>', null)
            ->expectsOutput('Hyde has a few different homepage options.')
            ->expectsQuestion('Would you like to select an index.blade.php file?', true)
            ->expectsQuestion('Which homepage do you want to publish?', 'default')
            ->expectsQuestion('Would you like to rebuild the site?', false)
            ->assertExitCode(0);
    }

    public function test_mark_installed_option_marks_site_as_installed()
    {
        $this->artisan('install --mark-installed')
            ->expectsOutput('Marking Hyde as installed and hiding the command!')
            ->assertExitCode(0);
    }
}
