<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;
use function unlink;

/**
 * @covers \Hyde\Publications\Commands\MakePublicationTagCommand
 * @covers \Hyde\Publications\Commands\Helpers\InputStreamHandler
 */
class MakePublicationTagCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        unlink(Hyde::path('tags.yml'));

        parent::tearDown();
    }

    public function testCanCreateNewPublicationTag()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag')
            ->expectsQuestion('Tag name', 'foo')
            ->expectsOutput('Enter the tag values: (end with an empty line)')
            ->expectsOutput('Adding the following tags:')
            ->expectsOutput('  foo: foo, bar, baz')
            ->expectsOutput('Saving tag data to ['.Hyde::path('tags.yml').']')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.yml'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.yml'))
        );
    }

    public function testCanCreateNewPublicationTagWithTagNameArgument()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
            ->expectsOutput('Using tag name [foo] from command line argument')
            ->expectsOutput('Enter the tag values: (end with an empty line)')
            ->expectsOutput('Adding the following tags:')
            ->expectsOutput('  foo: foo, bar, baz')
            ->expectsOutput('Saving tag data to ['.Hyde::path('tags.yml').']')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.yml'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.yml'))
        );
    }

    public function testCommandFailsIfTagNameIsAlreadySet()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
             ->assertExitCode(0);

        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
            ->expectsOutput('Error: Tag [foo] already exists')
             ->assertExitCode(1);
    }
}
