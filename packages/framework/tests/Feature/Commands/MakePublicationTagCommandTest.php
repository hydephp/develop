<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\MakePublicationTagCommand;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

use function unlink;

/**
 * @covers \Hyde\Console\Commands\MakePublicationTagCommand
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
        MakePublicationTagCommand::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag')
            ->expectsQuestion('Tag name', 'foo')
            ->expectsOutput('Enter the tag values (end with an empty line):')
            ->expectsOutput('Adding the following tags:')
            ->expectsOutput('  foo: foo, bar, baz')
            ->expectsOutput('Saving tag data to [tags.json]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.json'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.json'))
        );
    }

    public function testCanCreateNewPublicationTagWithTagNameArgument()
    {
        MakePublicationTagCommand::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag foo')
            ->expectsOutput('Using tag name [foo] from command line argument')
            ->expectsOutput('Enter the tag values (end with an empty line):')
            ->expectsOutput('Adding the following tags:')
            ->expectsOutput('  foo: foo, bar, baz')
            ->expectsOutput('Saving tag data to [tags.json]')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.json'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.json'))
        );
    }

    public function testCanTerminateWithCarriageReturns()
    {
        MakePublicationTagCommand::mockInput("foo\r\nbar\r\nbaz\r\n");

        $this->artisan('make:publicationTag')
            ->expectsQuestion('Tag name', 'foo')
            ->assertExitCode(0);
    }

    public function testCanTerminateWithUnixEndings()
    {
        MakePublicationTagCommand::mockInput("foo\nbar\nbaz\n");

        $this->artisan('make:publicationTag')
             ->expectsQuestion('Tag name', 'foo')
             ->assertExitCode(0);
    }
}
