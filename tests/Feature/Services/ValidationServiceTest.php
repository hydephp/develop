<?php

namespace Hyde\Testing\Feature\Services;

use Hyde\Framework\Models\ValidationResult;
use Hyde\Framework\Services\ValidationService;
use Hyde\Testing\TestCase;

/**
 * Class ValidationServiceTest.
 *
 * @covers \Hyde\Framework\Services\ValidationService
 * @covers \Hyde\Framework\Models\ValidationResult
 * @see \Hyde\Testing\Feature\Commands\HydeValidateCommandTest
 */
class ValidationServiceTest extends TestCase
{
    protected ValidationService $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = new ValidationService();
    }

    public function test_checks_returns_an_array_of_validation_check_methods()
    {
        $checks = ValidationService::checks();
        $this->assertIsArray($checks);

        // Assert each key starts with 'check_' and is a valid class method name
        foreach ($checks as $check) {
            $this->assertStringStartsWith('check_', $check);
            $this->assertTrue(method_exists(ValidationService::class, $check));
        }
    }

    // Rather meta, but lets us know that the method assertions are correct, and gives us test coverage
    protected function test(string $method, int $expectedStatusCode)
    {
        $result = $this->service->run($method);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals($expectedStatusCode, $result->statusCode());
    }
}
