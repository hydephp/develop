<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\ValidationService;
use Hyde\Hyde;
use Hyde\Support\Models\ValidationResult;
use Hyde\Testing\TestCase;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\ValidateCommandTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Services\ValidationService::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Support\Models\ValidationResult::class)]
class ValidationServiceTest extends TestCase
{
    protected ValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ValidationService();
    }

    protected function testMethod(string $method, int $expectedStatusCode): void
    {
        $result = $this->service->run($method);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertSame($expectedStatusCode, $result->statusCode());
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
        $this->testMethod('check_validators_can_run', 0);
    }

    public function testCheckSiteHasA404PageCanPass()
    {
        $this->testMethod('check_site_has_a_404_page', 0);
    }

    public function testCheckSiteHasA404PageCanFail()
    {
        rename(Hyde::path('_pages/404.blade.php'), Hyde::path('_pages/404.blade.php.bak'));

        $this->testMethod('check_site_has_a_404_page', 2);

        rename(Hyde::path('_pages/404.blade.php.bak'), Hyde::path('_pages/404.blade.php'));
    }

    public function testCheckDocumentationSiteHasAnIndexPageCanPass()
    {
        touch('_docs/index.md');

        $this->testMethod('check_documentation_site_has_an_index_page', 0);

        unlink('_docs/index.md');
    }

    public function testCheckDocumentationSiteHasAnIndexPageCanPassWithWarningWhenOnlyFindingReadme()
    {
        touch('_docs/README.md');

        $this->testMethod('check_documentation_site_has_an_index_page', 2);

        $this->assertStringContainsString('a _docs/readme.md file was found',
            $this->service->run('check_documentation_site_has_an_index_page')->tip()
        );

        unlink('_docs/README.md');
    }

    public function testCheckDocumentationSiteHasAnIndexPageCanFail()
    {
        touch('_docs/foo.md');

        $this->testMethod('check_documentation_site_has_an_index_page', 2);

        unlink('_docs/foo.md');
    }

    public function testCheckDocumentationSiteHasAnIndexPageBeSkipped()
    {
        $this->testMethod('check_documentation_site_has_an_index_page', 1);
    }

    public function testCheckSiteHasAnIndexPageCanPass()
    {
        $this->testMethod('check_site_has_an_index_page', 0);
    }

    public function testCheckSiteHasAnIndexPageCanFail()
    {
        rename(Hyde::path('_pages/index.blade.php'), Hyde::path('_pages/index.blade.php.bak'));

        $this->testMethod('check_site_has_an_index_page', 2);

        rename(Hyde::path('_pages/index.blade.php.bak'), Hyde::path('_pages/index.blade.php'));
    }

    public function testCheckSiteHasAnAppCssStylesheetCanPass()
    {
        $this->testMethod('check_site_has_an_app_css_stylesheet', 0);
    }

    public function testCheckSiteHasAnAppCssStylesheetCanFail()
    {
        rename(Hyde::path('_media/app.css'), Hyde::path('_media/app.css.bak'));

        $this->testMethod('check_site_has_an_app_css_stylesheet', 2);

        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

    public function testCheckSiteHasABaseUrlSetCanPass()
    {
        $this->withSiteUrl();

        $this->testMethod('check_site_has_a_base_url_set', 0);
    }

    public function testCheckSiteHasABaseUrlSetCanFail()
    {
        $this->withoutSiteUrl();

        $this->testMethod('check_site_has_a_base_url_set', 2);
    }

    public function testCheckATorchlightApiTokenIsSetCanSkip()
    {
        config(['hyde.features' => []]);

        $this->testMethod('check_a_torchlight_api_token_is_set', 1);
    }

    public function testCheckATorchlightApiTokenIsSetCanPass()
    {
        config(['torchlight.token' => '12345']);

        $this->testMethod('check_a_torchlight_api_token_is_set', 0);
    }

    public function testCheckATorchlightApiTokenIsSetCanFail()
    {
        config(['torchlight.token' => null]);

        $this->testMethod('check_a_torchlight_api_token_is_set', 2);
    }

    public function testCheckForConflictsBetweenBladeAndMarkdownPagesCanPass()
    {
        $this->testMethod('check_for_conflicts_between_blade_and_markdown_pages', 0);
    }

    public function testCheckForConflictsBetweenBladeAndMarkdownPagesCanFail()
    {
        Filesystem::touch('_pages/index.md');

        $this->testMethod('check_for_conflicts_between_blade_and_markdown_pages', 2);

        Filesystem::unlink('_pages/index.md');
    }

    // Some unit tests

    public function testValidationResultMessageReturnsMessage()
    {
        $result = new ValidationResult();
        $this->assertSame('Generic check', $result->message());
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

        $this->assertSame('foo', $result->tip());
    }
}
