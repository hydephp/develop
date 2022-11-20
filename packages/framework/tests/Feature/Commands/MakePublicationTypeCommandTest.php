<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\MakePublicationTypeCommand;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\MakePublicationTypeCommand
 */
class MakePublicationTypeCommandTest extends TestCase
{
    public function test_command_creates_publication()
    {
        $this->artisan(MakePublicationTypeCommand::class, ['title' => 'Test Publication'])
            ->expectsOutput('Creating a new Publication Type!')
            ->expectsOutput('Publication type name')
            ->expectsOutput('Choose the default field you wish to sort by:')
            ->expectsOutput('  0: dateCreated (meta field)')
            ->expectsOutput('  1: title')
            ->expectsOutput('  2: body')
            ->expectsOutput('Choose the default sort direction:')
            ->expectsOutput('  1 - Ascending (oldest items first if sorting by dateCreated)')
            ->expectsOutput('  2 - Descending (newest items first if sorting by dateCreated)')
            ->expectsOutput('Publication type created successfully!')
            ->assertExitCode(0);
    }
}
