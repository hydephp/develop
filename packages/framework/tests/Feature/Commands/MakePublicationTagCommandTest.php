<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\MakePublicationTagCommand;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\MakePublicationTagCommand
 */
class MakePublicationTagCommandTest extends TestCase
{
    public function testCanCreateNewPublicationType()
    {
        MakePublicationTagCommand::mockInput('foo
bar
baz
');

        $this->artisan('make:publicationTag')
             ->expectsQuestion('Tag name', 'foo')
             ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('tags.json'));
        $this->assertSame(
            json_encode(['foo' => ['foo', 'bar', 'baz']], 128),
            file_get_contents(Hyde::path('tags.json'))
        );

        unlink(Hyde::path('tags.json'));
    }
}
