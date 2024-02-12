<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Facades\Filesystem;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ValidationService();
    }

    // Rather meta, but lets us know that the method assertions are correct, and gives us test coverage
    protected function test(string $method, int $expectedStatusCode)
    {
        $result = $this->service->run($method);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertEquals($expectedStatusCode, $result->statusCode());
    }

    public function testChecksReturnsAnArrayOfValidationCheckMethods()
    {
        $checks = ValidationService::checks();
        $this->assertIsArray($checks);

        // Assert each key starts with 'check_' and is a valid class method name
        foreach ($checks as $check) {
            $this->assertStringStartsWith('check_', $check);
            $this->assertTrue(method_exists(ValidationService::class, $check));
        }
    }

    public function testCheckValidatorsCanRun()
    {
        $this->test('check_validators_can_run', 0);
    }

    public function testCheckSiteHasA404PageCanPass()
    {
        $this->test('check_site_has_a_404_page', 0);
    }

    public function testCheckSiteHasA404PageCanFail()
    {
        rename(Hyde::path('_pages/404.blade.php'), Hyde::path('_pages/404.blade.php.bak'));
        $this->test('check_site_has_a_404_page', 2);
        rename(Hyde::path('_pages/404.blade.php.bak'), Hyde::path('_pages/404.blade.php'));
    }

    public function testCheckDocumentationSiteHasAnIndexPageCanPass()
    {
        touch('_docs/index.md');
        $this->test('check_documentation_site_has_an_index_page', 0);
        unlink('_docs/index.md');
    }

    public function testCheckDocumentationSiteHasAnIndexPageCanPassWithWarningWhenOnlyFindingReadme()
    {
        touch('_docs/README.md');
        $this->test('check_documentation_site_has_an_index_page', 2);
        $this->assertStringContainsString('a _docs/readme.md file was found',
            $this->service->run('check_documentation_site_has_an_index_page')->tip());
        unlink('_docs/README.md');
    }

    public function testCheckDocumentationSiteHasAnIndexPageCanFail()
    {
        touch('_docs/foo.md');
        $this->test('check_documentation_site_has_an_index_page', 2);
        unlink('_docs/foo.md');
    }

    public function testCheckDocumentationSiteHasAnIndexPageBeSkipped()
    {
        $this->test('check_documentation_site_has_an_index_page', 1);
    }

    public function testCheckSiteHasAnIndexPageCanPass()
    {
        $this->test('check_site_has_an_index_page', 0);
    }

    public function testCheckSiteHasAnIndexPageCanFail()
    {
        rename(Hyde::path('_pages/index.blade.php'), Hyde::path('_pages/index.blade.php.bak'));
        $this->test('check_site_has_an_index_page', 2);
        rename(Hyde::path('_pages/index.blade.php.bak'), Hyde::path('_pages/index.blade.php'));
    }

    public function testCheckSiteHasAnAppCssStylesheetCanPass()
    {
        $this->test('check_site_has_an_app_css_stylesheet', 0);
    }

    public function testCheckSiteHasAnAppCssStylesheetCanFail()
    {
        rename(Hyde::path('_media/app.css'), Hyde::path('_media/app.css.bak'));
        $this->test('check_site_has_an_app_css_stylesheet', 2);
        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

    public function testCheckSiteHasABaseUrlSetCanPass()
    {
        config(['hyde.url' => 'https://example.com']);
        $this->test('check_site_has_a_base_url_set', 0);
    }

    public function testCheckSiteHasABaseUrlSetCanFail()
    {
        config(['hyde.url' => null]);
        $this->test('check_site_has_a_base_url_set', 2);
    }

    public function testCheckATorchlightApiTokenIsSetCanSkip()
    {
        config(['hyde.features' => []]);
        $this->test('check_a_torchlight_api_token_is_set', 1);
    }

    public function testCheckATorchlightApiTokenIsSetCanPass()
    {
        config(['torchlight.token' => '12345']);
        $this->test('check_a_torchlight_api_token_is_set', 0);
    }

    public function testCheckATorchlightApiTokenIsSetCanFail()
    {
        config(['torchlight.token' => null]);
        $this->test('check_a_torchlight_api_token_is_set', 2);
    }

    public function testCheckForConflictsBetweenBladeAndMarkdownPagesCanPass()
    {
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 0);
    }

    public function testCheckForConflictsBetweenBladeAndMarkdownPagesCanFail()
    {
        Filesystem::touch('_pages/index.md');
        $this->test('check_for_conflicts_between_blade_and_markdown_pages', 2);
        Filesystem::unlink('_pages/index.md');
    }

    // Some unit tests

    public function testValidationResultMessageReturnsMessage()
    {
        $result = new ValidationResult();
        $this->assertEquals('Generic check', $result->message());
    }

    public function testValidationResultPassedReturnsTrueWhenPassedIsTrue()
    {
        $result = new ValidationResult();
        $result->pass();
        $this->assertTrue($result->passed());
        $result->fail();
        $this->assertFalse($result->passed());
    }

    public function testValidationResultFailedReturnsTrueWhenPassedIsFalse()
    {
        $result = new ValidationResult();
        $result->pass();
        $this->assertFalse($result->failed());
        $result->fail();
        $this->assertTrue($result->failed());
    }

    public function testValidationResultSkippedReturnsTrueWhenSkippedIsTrue()
    {
        $result = new ValidationResult();
        $this->assertFalse($result->skipped());
        $result->skip();
        $this->assertTrue($result->skipped());
    }

    public function testValidationResultTipReturnsMessageWhenSet()
    {
        $result = new ValidationResult();
        $this->assertFalse($result->tip());
        $result->withTip('foo');
        $this->assertEquals('foo', $result->tip());
    }
}
