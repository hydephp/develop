<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use function unlink;

/**
 * @covers \Hyde\Console\Commands\MakePublicationTagCommand
 * @covers \Hyde\Console\Commands\Helpers\InputStreamHandler {@todo Extract this to a separate test class}
 */
class MakePublicationTagCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        unlink(Hyde::path('tags.json'));

        parent::tearDown();
    }

    public function testCanCreateNewPublicationTag()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag')
            ->expectsQuestion('Tag name', 'foo')
            ->expectsOutput('Enter the tag values (end with an empty line):')
            ->expectsOutput('Adding the following tags:')
            ->expectsOutput('  foo: foo, bar, baz')
            ->expectsOutput('Saving tag data to ['.Hyde::path('tags.json').']')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.json'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.json'))
        );
    }

    public function testCanCreateNewPublicationTagWithTagNameArgument()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
            ->expectsOutput('Using tag name [foo] from command line argument')
            ->expectsOutput('Enter the tag values (end with an empty line):')
            ->expectsOutput('Adding the following tags:')
            ->expectsOutput('  foo: foo, bar, baz')
            ->expectsOutput('Saving tag data to ['.Hyde::path('tags.json').']')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.json'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.json'))
        );
    }

    public function testCommandFailsIfTagNameIsAlreadySet()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
             ->assertExitCode(0);

        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
            ->expectsOutput('Tag [foo] already exists')
             ->assertExitCode(1);
    }

    public function testCanTerminateWithCarriageReturns()
    {
        InputStreamHandler::mockInput("foo\r\nbar\r\nbaz\r\n");

        $this->artisan('make:publicationTag foo')->assertExitCode(0);
    }

    public function testCanTerminateWithUnixEndings()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')->assertExitCode(0);
    }
}
