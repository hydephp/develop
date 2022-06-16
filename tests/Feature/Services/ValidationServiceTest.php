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

    // Test check_validators_can_run method
    public function test_check_validators_can_run()
    {
        $this->test('check_validators_can_run', 0);
    }

    // Test check_site_has_a_404_page passes if 404.md or 404.blade.php exists
    public function test_check_site_has_a_404_page_can_pass()
    {
        $this->test('check_site_has_a_404_page', 0);
    }

    // Test check_site_has_a_404_page fails if 404.md or 404.blade.php does not exist
    public function test_check_site_has_a_404_page_can_fail()
    {
        rename(Hyde::path('_pages/404.blade.php'), Hyde::path('_pages/404.blade.php.bak'));
        $this->test('check_site_has_a_404_page', 2);
        rename(Hyde::path('_pages/404.blade.php.bak'), Hyde::path('_pages/404.blade.php'));
    }

    // Test check_site_has_an_index_page passes if index.md or index.blade.php exists
    public function test_check_site_has_an_index_page_can_pass()
    {
        $this->test('check_site_has_an_index_page', 0);
    }

    // Test check_site_has_an_index_page fails if index.md or index.blade.php does not exist
    public function test_check_site_has_an_index_page_can_fail()
    {
        rename(Hyde::path('_pages/index.blade.php'), Hyde::path('_pages/index.blade.php.bak'));
        $this->test('check_site_has_an_index_page', 2);
        rename(Hyde::path('_pages/index.blade.php.bak'), Hyde::path('_pages/index.blade.php'));
    }

    // Test check_site_has_an_app_css_stylesheet passes if app.css exists
    public function test_check_site_has_an_app_css_stylesheet_can_pass()
    {
        $this->test('check_site_has_an_app_css_stylesheet', 0);
    }

    // Test check_site_has_an_app_css_stylesheet fails if app.css does not exist
    public function test_check_site_has_an_app_css_stylesheet_can_fail()
    {
        rename(Hyde::path('_media/app.css'), Hyde::path('_media/app.css.bak'));
        $this->test('check_site_has_an_app_css_stylesheet', 2);
        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

    // Test check_site_has_a_base_url_set passes if site_url is set in config
    public function test_check_site_has_a_base_url_set_can_pass()
    {
        config(['hyde.site_url' => 'https://example.com']);
        $this->test('check_site_has_a_base_url_set', 0);
    }

    // Test check_site_has_a_base_url_set fails if site_url is not set in config
    public function test_check_site_has_a_base_url_set_can_fail()
    {
        config(['hyde.site_url' => null]);
        $this->test('check_site_has_a_base_url_set', 2);
    }

    // Test check_a_torchlight_api_token_is_set is skipped if Torchlight is not enabled
    public function test_check_a_torchlight_api_token_is_set_can_skip()
    {
        config(['hyde.features' => []]);
        $this->test('check_a_torchlight_api_token_is_set', 1);
    }

    // Test check_a_torchlight_api_token_is_set passes if a token is set
    public function test_check_a_torchlight_api_token_is_set_can_pass()
    {
        config(['torchlight.token' => '12345']);
        $this->test('check_a_torchlight_api_token_is_set', 0);
    }

    // Test check_a_torchlight_api_token_is_set fails if a token is not set
    public function test_check_a_torchlight_api_token_is_set_can_fail()
    {
        config(['torchlight.token' => null]);
        $this->test('check_a_torchlight_api_token_is_set', 2);
    }

    // Test check_for_conflicts_between_blade_and_markdown_pages passes if no conflicts are found
    public function test_check_for_conflicts_between_blade_and_markdown_pages_can_pass()
    {
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 0);
    }

    // Test check_for_conflicts_between_blade_and_markdown_pages fails if conflicts are found
    public function test_check_for_conflicts_between_blade_and_markdown_pages_can_fail()
    {
        touch(Hyde::path('_pages/index.md'));
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 2);
        unlink(Hyde::path('_pages/index.md'));
    }

    // Rather meta, but lets us know that the method assertions are correct, and gives us test coverage
    protected function test(string $method, int $expectedStatusCode)
    {
        $result = $this->service->run($method);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals($expectedStatusCode, $result->statusCode());
    }
}
