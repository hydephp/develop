<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Commands\MakePublicationTagCommand
 * @covers \Hyde\Publications\Commands\Helpers\InputStreamHandler
 */
class MakePublicationTagCommandTest extends TestCase
{
    public function testCanCreateNewPublicationTag()
    {
        $this->cleanUpWhenDone('tags.yml');

        InputStreamHandler::mockInput("foo\nbar\nbaz\n<<<");

        $this->artisan('make:publicationTag')
            ->expectsOutputToContain('Enter the tag values:')
            ->expectsOutput('Adding the following tags: foo, bar, baz')
            ->expectsOutput('Saving tag data to [file://'.str_replace('\\', '/', Hyde::path('tags.yml')).']')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.yml'));
        $this->assertSame(
            <<<'YAML'
            - foo
            - bar
            - baz

            YAML,
            file_get_contents(Hyde::path('tags.yml'))
        );
    }
}
