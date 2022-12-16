<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

class MakePostCommandTest extends TestCase
{
    public function test_command_has_expected_output_and_creates_valid_file()
    {
        // Assert that no old file exists which would cause issues
        $this->assertFileDoesNotExist(Hyde::path('_posts/test-post.md'));

        $this->artisan('make:post')
            ->expectsQuestion('What is the title of the post?', 'Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'PHPUnit')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsOutput('Creating a post with the following details:')

            ->expectsOutput('Title: Test Post')
            ->expectsOutput('Description: A short description')
            ->expectsOutput('Category: general')
            ->expectsOutput('Author: PHPUnit')
            ->expectsOutputToContain('Date: '.date('Y-m-d')) // Don't check min/sec to avoid flaky tests
            ->expectsOutput('Identifier: test-post')

            ->expectsConfirmation('Do you wish to continue?', 'yes')

            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_posts/test-post.md'));
        $this->assertStringContainsString(
            "title: 'Test Post'",
            file_get_contents(Hyde::path('_posts/test-post.md'))
        );

        $this->unlinkFile();
    }

    public function test_that_files_are_not_overwritten_when_force_flag_is_not_set()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), 'This should not be overwritten');
        $this->artisan('make:post')
            ->expectsQuestion('What is the title of the post?', 'Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'PHPUnit')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsOutput('Creating a post with the following details:')

            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->expectsOutput('If you want to overwrite the file supply the --force flag.')

            ->assertExitCode(409);

        $this->assertStringContainsString(
            'This should not be overwritten',
            file_get_contents(Hyde::path('_posts/test-post.md'))
        );

        $this->unlinkFile();
    }

    public function test_that_files_are_overwritten_when_force_flag_is_set()
    {
        file_put_contents(Hyde::path('_posts/test-post.md'), 'This should be overwritten');
        $this->artisan('make:post --force')
            ->expectsQuestion('What is the title of the post?', 'Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'PHPUnit')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsOutput('Creating a post with the following details:')
            ->expectsConfirmation('Do you wish to continue?', 'yes')

            ->assertExitCode(0);

        $this->assertStringNotContainsString(
            'This should be overwritten',
            file_get_contents(Hyde::path('_posts/test-post.md'))
        );
        $this->assertStringContainsString(
            "title: 'Test Post'",
            file_get_contents(Hyde::path('_posts/test-post.md'))
        );

        $this->unlinkFile();
    }

    public function test_that_title_can_be_specified_in_command_signature()
    {
        $this->artisan('make:post "Test Post"')
            ->expectsOutputToContain('Selected title: Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'PHPUnit')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsConfirmation('Do you wish to continue?', 'yes')

            ->assertExitCode(0);

        $this->unlinkFile();
    }

    public function test_that_command_can_be_canceled()
    {
        $this->artisan('make:post "Test Post"')
        ->expectsOutputToContain('Selected title: Test Post')
        ->expectsQuestion('Write a short post excerpt/description', 'A short description')
        ->expectsQuestion('What is your (the author\'s) name?', 'PHPUnit')
        ->expectsQuestion('What is the primary category of the post?', 'general')
        ->expectsConfirmation('Do you wish to continue?')
        ->expectsOutput('Aborting.')
        ->assertExitCode(130);

        $this->assertFileDoesNotExist(Hyde::path('_posts/test-post.md'));
    }

    protected function unlinkFile(): void
    {
        Hyde::unlink('_posts/test-post.md');
    }
}
