<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Services\ValidationService;
use Hyde\Hyde;
use Hyde\Support\Models\ValidationResult;
use Hyde\Testing\TestCase;

/**
 * Class ValidationServiceTest.
 *
 * @covers \Hyde\Framework\Services\ValidationService
 * @covers \Hyde\Support\Models\ValidationResult
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\ValidateCommandTest
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

    public function test_check_documentation_site_has_an_index_page_can_pass()
    {
        touch('_docs/index.md');
        $this->test('check_documentation_site_has_an_index_page', 0);
        unlink('_docs/index.md');
    }

    public function test_check_documentation_site_has_an_index_page_can_pass_with_warning_when_only_finding_readme()
    {
        touch('_docs/README.md');
        $this->test('check_documentation_site_has_an_index_page', 2);
        $this->assertStringContainsString('a _docs/readme.md file was found',
            $this->service->run('check_documentation_site_has_an_index_page')->tip());
        unlink('_docs/README.md');
    }

    public function test_check_documentation_site_has_an_index_page_can_fail()
    {
        touch('_docs/foo.md');
        $this->test('check_documentation_site_has_an_index_page', 2);
        unlink('_docs/foo.md');
    }

    public function test_check_documentation_site_has_an_index_page_be_skipped()
    {
        $this->test('check_documentation_site_has_an_index_page', 1);
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
        unlinkIfExists(Hyde::path('_site/media/app.css'));
        $this->test('check_site_has_an_app_css_stylesheet', 2);
        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

    public function test_check_site_has_a_base_url_set_can_pass()
    {
        config(['site.url' => 'https://example.com']);
        $this->test('check_site_has_a_base_url_set', 0);
    }

    public function test_check_site_has_a_base_url_set_can_fail()
    {
        config(['site.url' => null]);
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
        Hyde::touch(('_pages/index.md'));
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 2);
        unlink(Hyde::path('_pages/index.md'));
    }

    // Some unit tests

    public function test_validation_result_message_returns_message()
    {
        $result = new ValidationResult();
        $result->message = 'foo';
        $this->assertEquals('foo', $result->message());
    }

    public function test_validation_result_passed_returns_true_when_passed_is_true()
    {
        $result = new ValidationResult();
        $result->passed = true;
        $this->assertTrue($result->passed());
        $result->passed = false;
        $this->assertFalse($result->passed());
    }

    public function test_validation_result_failed_returns_true_when_passed_is_false()
    {
        $result = new ValidationResult();
        $result->passed = true;
        $this->assertFalse($result->failed());
        $result->passed = false;
        $this->assertTrue($result->failed());
    }

    public function test_validation_result_skipped_returns_true_when_skipped_is_true()
    {
        $result = new ValidationResult();
        $this->assertFalse($result->skipped());
        $result->skipped = true;
        $this->assertTrue($result->skipped());
        $result->skipped = false;
        $this->assertFalse($result->skipped());
    }

    public function test_validation_result_tip_returns_message_when_set()
    {
        $result = new ValidationResult();
        $this->assertFalse($result->tip());
        $result->tip = 'foo';
        $this->assertEquals('foo', $result->tip());
    }
}
