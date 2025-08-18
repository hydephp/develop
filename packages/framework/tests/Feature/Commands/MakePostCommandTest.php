<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\MakePostCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\CreatesNewMarkdownPostFile::class)]
class MakePostCommandTest extends TestCase
{
    public function testCommandHasExpectedOutputAndCreatesValidFile()
    {
        // Assert that no old file exists which would cause issues
        $this->assertFileDoesNotExist(Hyde::path('_posts/test-post.md'));
        $this->cleanUpWhenDone('_posts/test-post.md');

        Carbon::setTestNow(Carbon::create(2024, hour: 12));

        $this->artisan('make:post')
            ->expectsQuestion('What is the title of the post?', 'Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'Mr Hyde')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsOutput('Creating a post with the following details:')
            ->expectsOutput('Title: Test Post')
            ->expectsOutput('Description: A short description')
            ->expectsOutput('Category: general')
            ->expectsOutput('Author: Mr Hyde')
            ->expectsOutput('Date: 2024-01-01 12:00')
            ->expectsOutput('Identifier: test-post')
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_posts/test-post.md'));

        $this->assertFileEqualsString(<<<'MARKDOWN'
            ---
            title: 'Test Post'
            description: 'A short description'
            category: general
            author: 'Mr Hyde'
            date: '2024-01-01 12:00'
            ---

            ## Write something awesome.

            MARKDOWN,
            Hyde::path('_posts/test-post.md')
        );
    }

    public function testThatFilesAreNotOverwrittenWhenForceFlagIsNotSet()
    {
        $this->file('_posts/test-post.md', 'This should not be overwritten');

        $this->artisan('make:post')
            ->expectsQuestion('What is the title of the post?', 'Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'Mr Hyde')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsOutput('Creating a post with the following details:')
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->expectsOutput('If you want to overwrite the file supply the --force flag.')
            ->assertExitCode(409);

        $this->assertStringContainsString(
            'This should not be overwritten',
            file_get_contents(Hyde::path('_posts/test-post.md'))
        );
    }

    public function testThatFilesAreOverwrittenWhenForceFlagIsSet()
    {
        $this->file('_posts/test-post.md', 'This should be overwritten');

        $this->artisan('make:post --force')
            ->expectsQuestion('What is the title of the post?', 'Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'Mr Hyde')
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
    }

    public function testThatTitleCanBeSpecifiedInCommandSignature()
    {
        $this->cleanUpWhenDone('_posts/test-post.md');

        $this->artisan('make:post "Test Post"')
            ->expectsOutputToContain('Selected title: Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'Mr Hyde')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->assertExitCode(0);
    }

    public function testThatCommandCanBeCanceled()
    {
        $this->artisan('make:post "Test Post"')
            ->expectsOutputToContain('Selected title: Test Post')
            ->expectsQuestion('Write a short post excerpt/description', 'A short description')
            ->expectsQuestion('What is your (the author\'s) name?', 'Mr Hyde')
            ->expectsQuestion('What is the primary category of the post?', 'general')
            ->expectsConfirmation('Do you wish to continue?')
            ->expectsOutput('Aborting.')
            ->assertExitCode(130);

        $this->assertFileDoesNotExist(Hyde::path('_posts/test-post.md'));
    }
}
