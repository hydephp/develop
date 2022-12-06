<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use function copy;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\ValidatePublicationsCommand
 */
class ValidatePublicationsCommandTest extends TestCase
{
    public function testWithNoPublicationTypes()
    {
        $this->artisan('validate:publications')
            ->expectsOutput('Error: No publication types to validate!')
            ->assertExitCode(1);
    }

    public function testWithInvalidPublicationType()
    {
        $this->artisan('validate:publications', ['publicationType' => 'invalid'])
            ->expectsOutput('Error: Publication type [invalid] does not exist')
            ->assertExitCode(1);
    }

    public function testWithPublicationType()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();
        copy(Hyde::path('tests/fixtures/test-publication.md'), Hyde::path('test-publication/test.md'));

        $this->artisan('validate:publications')
            ->expectsOutputToContain('Validating publications!')
            ->expectsOutput('Validating publication type [test-publication]')
            ->expectsOutputToContain('Validating publication [My Title]')
            ->doesntExpectOutputToContain('Validating field')
            ->expectsOutput('Validated 1 Publication Types, 1 Publications, 1 Fields')
            ->expectsOutput('Found 0 Warnings')
            ->expectsOutput('Found 0 Errors')
            ->assertExitCode(0);
    }

    public function testWithPublicationTypeAndVerboseOutput()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();
        copy(Hyde::path('tests/fixtures/test-publication.md'), Hyde::path('test-publication/test.md'));

        $this->artisan('validate:publications', ['--verbose' => true])
             ->expectsOutputToContain('Validating publications!')
             ->expectsOutput('Validating publication type [test-publication]')
             ->expectsOutputToContain('Validating publication [My Title]')
             ->expectsOutputToContain('Validating field')
             ->expectsOutput('Validated 1 Publication Types, 1 Publications, 1 Fields')
             ->expectsOutput('Found 0 Warnings')
             ->expectsOutput('Found 0 Errors')
             ->assertExitCode(0);
    }

    public function testWithInvalidPublication()
    {
        $this->directory('test-publication');
        $this->setupTestPublication();
        file_put_contents(Hyde::path('test-publication/test.md'), '---
Foo: bar
---

Hello World
');

        $this->artisan('validate:publications')
             ->expectsOutputToContain('Validating publications!')
             ->expectsOutput('Validating publication type [test-publication]')
             ->expectsOutputToContain('Validating publication [Test]')
             ->doesntExpectOutputToContain('Validating field')
             ->expectsOutput('Validated 1 Publication Types, 1 Publications, 1 Fields')
             ->expectsOutput('Found 1 Warnings')
             ->expectsOutput('Found 1 Errors')
             ->assertExitCode(1);
    }

    public function testWithMultiplePublicationTypes()
    {
        $this->directory('test-publication');
        $this->directory('test-publication-two');
        $this->setupTestPublication();
        $this->setupTestPublication('test-publication-two');

        $this->artisan('validate:publications')
            ->expectsOutput('Validating publication type [test-publication-two]')
            ->expectsOutput('Validating publication type [test-publication]')
            ->assertExitCode(0);
    }

    public function testOnlySpecifiedTypeIsValidatedWhenUsingArgument()
    {
        $this->directory('test-publication');
        $this->directory('test-publication-two');
        $this->setupTestPublication();
        $this->setupTestPublication('test-publication-two');

        $this->artisan('validate:publications test-publication-two')
            ->expectsOutput('Validating publication type [test-publication-two]')
            ->doesntExpectOutput('Validating publication type [test-publication]')
            ->assertExitCode(0);
    }
}
