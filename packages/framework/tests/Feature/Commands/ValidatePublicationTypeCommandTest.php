<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function copy;
use Hyde\Console\Commands\ValidatePublicationTypeCommand;
use Hyde\Hyde;
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

    public function testCommandWithInvalidPublicationType()
    {
        $this->artisan(ValidatePublicationTypeCommand::class, ['publicationType' => 'invalid'])
            ->expectsOutput('Error: Publication type [invalid] does not exist')
            ->assertExitCode(1);
    }

    public function testCommandWithPublicationType()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();
        copy(Hyde::path('tests/fixtures/test-publication.md'), Hyde::path('test-publication/test.md'));

        $this->artisan(ValidatePublicationTypeCommand::class)
            ->expectsOutputToContain('Validating publication types!')
            ->expectsOutput('Validating publication type [test-publication]')
            ->expectsOutputToContain('Validating publication [My Title]')
            ->doesntExpectOutputToContain('Validating field')
            ->doesntExpectOutputToContain('is missing from publication')
            ->expectsOutput('Validated 1 Publication Types, 1 Publications, 1 Fields')
            ->expectsOutput('Found 0 Warnings')
            ->expectsOutput('Found 0 Errors')
            ->assertExitCode(0);
    }
}
