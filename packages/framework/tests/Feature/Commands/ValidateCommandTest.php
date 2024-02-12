<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\ValidateCommand
 * @covers \Hyde\Framework\Services\ValidationService
 * @covers \Hyde\Support\Models\ValidationResult
 *
 * @see \Hyde\Framework\Testing\Feature\Services\ValidationServiceTest
 */
class ValidateCommandTest extends TestCase
{
    public function testValidateCommandCanRun()
    {
        // Ensure the environment is clean to prevent false positives
        config(['torchlight.token' => null]);

        $this->artisan('validate')
            ->expectsOutput('Running validation tests!')
            ->expectsOutputToContain('PASS')
            ->expectsOutputToContain('FAIL')
            ->expectsOutputToContain('All done!')
            ->assertExitCode(0);
    }

    public function testValidateCommandCanRunWithSkips()
    {
        // Trigger skipping of Torchlight and documentation index check
        config(['hyde.features' => []]);

        $this->artisan('validate')
            ->expectsOutput('Running validation tests!')
            ->expectsOutputToContain('SKIP')
            ->assertExitCode(0);
    }
}
