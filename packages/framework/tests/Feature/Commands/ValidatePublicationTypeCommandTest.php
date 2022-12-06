<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\ValidatePublicationTypeCommand;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\ValidatePublicationTypeCommand
 */
class ValidatePublicationTypeCommandTest extends TestCase
{
    public function testCommandWithNoPublicationTypes()
    {
        $this->artisan(ValidatePublicationTypeCommand::class)
            ->expectsOutput('Error: No publication types to validate!')
            ->assertExitCode(1);
    }
}
