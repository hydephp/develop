<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeValidateCommand
 * @covers \Hyde\Framework\Services\ValidationService
 * @covers \Hyde\Framework\Support\ValidationResult
 *
 * @see \Hyde\Framework\Testing\Feature\Services\ValidationServiceTest
 */
class HydeValidateCommandTest extends TestCase
{
    public function test_validate_command_can_run()
    {
        $this->artisan('validate')
            ->expectsOutput('Running validation tests!')
            ->expectsOutputToContain('PASS')
            ->expectsOutputToContain('FAIL')
            ->expectsOutputToContain('All done!')
            ->assertExitCode(0);
    }

    public function test_validate_command_can_run_with_skips()
    {
        // Trigger skipping of Torchlight and documentation index check
        config(['hyde.features' => []]);

        $this->artisan('validate')
            ->expectsOutput('Running validation tests!')
            ->expectsOutputToContain('SKIP')
            ->assertExitCode(0);
    }
}
