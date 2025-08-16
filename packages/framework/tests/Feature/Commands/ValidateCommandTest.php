<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\ValidationServiceTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\ValidateCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Services\ValidationService::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Models\ValidationResult::class)]
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
