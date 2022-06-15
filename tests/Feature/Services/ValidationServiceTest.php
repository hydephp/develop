<?php

namespace Hyde\Testing\Feature\Services;

use Hyde\Framework\Actions\ValidationCheck;
use Hyde\Framework\Services\ValidationService;
use Hyde\Testing\TestCase;

/**
 * Class ValidationServiceTest.
 *
 * @covers \Hyde\Framework\Services\ValidationService
 * @covers \Hyde\Framework\Actions\ValidationCheck
 */
class ValidationServiceTest extends TestCase
{
    public function test_checks_returns_an_array_of_validation_check_objects()
    {
        $checks = ValidationService::checks();

        $this->assertIsArray($checks);
        $this->assertContainsOnlyInstancesOf(ValidationCheck::class, $checks);
    }
}
