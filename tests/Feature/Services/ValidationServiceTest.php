<?php

namespace Hyde\Testing\Feature\Services;

use Hyde\Framework\Hyde;
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

    // Rather meta, but lets us know that the method assertions are correct, and gives us test coverage
    protected function test(string $method, int $expectedStatusCode)
    {
        $result = $this->service->run($method);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals($expectedStatusCode, $result->statusCode());
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

    public function test_check_validators_can_run()
    {
        $this->test('check_validators_can_run', 0);
    }

    public function test_check_site_has_a_404_page_can_pass()
    {
        $this->test('check_site_has_a_404_page', 0);
    }

    public function test_check_site_has_a_404_page_can_fail()
    {
        rename(Hyde::path('_pages/404.blade.php'), Hyde::path('_pages/404.blade.php.bak'));
        $this->test('check_site_has_a_404_page', 2);
        rename(Hyde::path('_pages/404.blade.php.bak'), Hyde::path('_pages/404.blade.php'));
    }

    public function test_check_site_has_an_index_page_can_pass()
    {
        $this->test('check_site_has_an_index_page', 0);
    }

    public function test_check_site_has_an_index_page_can_fail()
    {
        rename(Hyde::path('_pages/index.blade.php'), Hyde::path('_pages/index.blade.php.bak'));
        $this->test('check_site_has_an_index_page', 2);
        rename(Hyde::path('_pages/index.blade.php.bak'), Hyde::path('_pages/index.blade.php'));
    }

    public function test_check_site_has_an_app_css_stylesheet_can_pass()
    {
        $this->test('check_site_has_an_app_css_stylesheet', 0);
    }

    public function test_check_site_has_an_app_css_stylesheet_can_fail()
    {
        rename(Hyde::path('_media/app.css'), Hyde::path('_media/app.css.bak'));
        $this->test('check_site_has_an_app_css_stylesheet', 2);
        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

    public function test_check_site_has_a_base_url_set_can_pass()
    {
        config(['hyde.site_url' => 'https://example.com']);
        $this->test('check_site_has_a_base_url_set', 0);
    }

    public function test_check_site_has_a_base_url_set_can_fail()
    {
        config(['hyde.site_url' => null]);
        $this->test('check_site_has_a_base_url_set', 2);
    }

    public function test_check_a_torchlight_api_token_is_set_can_skip()
    {
        config(['hyde.features' => []]);
        $this->test('check_a_torchlight_api_token_is_set', 1);
    }

    public function test_check_a_torchlight_api_token_is_set_can_pass()
    {
        config(['torchlight.token' => '12345']);
        $this->test('check_a_torchlight_api_token_is_set', 0);
    }

    public function test_check_a_torchlight_api_token_is_set_can_fail()
    {
        config(['torchlight.token' => null]);
        $this->test('check_a_torchlight_api_token_is_set', 2);
    }

    public function test_check_for_conflicts_between_blade_and_markdown_pages_can_pass()
    {
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 0);
    }

    public function test_check_for_conflicts_between_blade_and_markdown_pages_can_fail()
    {
        touch(Hyde::path('_pages/index.md'));
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 2);
        unlink(Hyde::path('_pages/index.md'));
    }

    // Some unit tests

    // Test ValidationResult::message() returns $result->message
    public function test_validation_result_message_returns_message()
    {
        $result = new ValidationResult();
        $result->message = 'foo';
        $this->assertEquals('foo', $result->message());
    }

    // Test ValidationResult::passed() returns true when passed is true, otherwise false
    public function test_validation_result_passed_returns_true_when_passed_is_true()
    {
        $result = new ValidationResult();
        $result->passed = true;
        $this->assertTrue($result->passed());
        $result->passed = false;
        $this->assertFalse($result->passed());
    }

    // Test ValidationResult::failed() returns true when passed is false, otherwise false
    public function test_validation_result_failed_returns_true_when_passed_is_false()
    {
        $result = new ValidationResult();
        $result->passed = true;
        $this->assertFalse($result->failed());
        $result->passed = false;
        $this->assertTrue($result->failed());
    }

    // Test ValidationResult::skipped() returns true when skipped is true, or false if false or not set
    public function test_validation_result_skipped_returns_true_when_skipped_is_true()
    {
        $result = new ValidationResult();
        $this->assertFalse($result->skipped());
        $result->skipped = true;
        $this->assertTrue($result->skipped());
        $result->skipped = false;
        $this->assertFalse($result->skipped());
    }

    // Test ValidationResult::tip() returns message when set, otherwise false
    public function test_validation_result_tip_returns_message_when_set()
    {
        $result = new ValidationResult();
        $this->assertFalse($result->tip());
        $result->tip = 'foo';
        $this->assertEquals('foo', $result->tip());
    }
}
