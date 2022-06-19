<?php

namespace Hyde\Testing\Framework\Feature\Services;

use Hyde\Framework\Actions\MarkdownConverter;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Services\HydeSmartDocs;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\HydeSmartDocsTest
 * @covers \Hyde\Framework\Concerns\FacadeHelpers\HydeSmartDocsFacade
 */
class HydeSmartDocsTest extends TestCase
{
    protected DocumentationPage $mock;
    protected string $html;

    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents(Hyde::path('_docs/foo.md'), "# Foo\n\nHello world.");
        $this->mock = DocumentationPage::parse('foo');
        $this->html = MarkdownConverter::parse($this->mock->body);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink(Hyde::path('_docs/foo.md'));
    }

    protected function assertEqualsIgnoringNewlines(string $expected, string $actual): void
    {
        $this->assertEquals(
            str_replace("\n", '', $expected),
            str_replace("\n", '', $actual)
        );
    }

    protected function mockTorchlight(): void
    {
        app()->bind('env', function () {
            return 'production';
        });
        config(['torchlight.token' => '12345']);
    }

    // test facade create helper creates a new instance and processes it
    public function test_create_helper_creates_new_instance_and_processes_it()
    {
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertInstanceOf(HydeSmartDocs::class, $page);

        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
    }

    // Test instance can be constructed directly with same result as facade
    public function test_instance_can_be_constructed_directly_with_same_result_as_facade()
    {
        $class = new HydeSmartDocs($this->mock, $this->html);
        $facade = HydeSmartDocs::create($this->mock, $this->html);

        // Baseline since we manually need to call the process method
        $this->assertNotEquals($class, $facade);

        $class->process();

        // Now they should be the equal
        $this->assertEquals($class, $facade);
    }

    // Test renderHeader returns the extracted header
    public function test_render_header_returns_the_extracted_header()
    {
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $page->renderHeader());
    }

    // Test renderHeader returns the extracted header with varying newlines
    public function test_render_header_returns_the_extracted_header_with_varying_newlines()
    {
        $tests = [
            "# Foo\n\nHello world.",
            "# Foo\r\n\r\nHello world.",
            "\n\n\n# Foo \r\n\r\n\n\n\n Hello world.",
        ];

        foreach ($tests as $test) {
            $page = HydeSmartDocs::create($this->mock, MarkdownConverter::parse($test));
            $this->assertEqualsIgnoringNewlines('<h1>Foo</h1>', $page->renderHeader());
        }
    }

    // Test renderBody returns the extracted body
    public function test_render_body_returns_the_extracted_body()
    {
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
    }

    // Test renderBody returns the extracted body with varying newlines
    public function test_render_body_returns_the_extracted_body_with_varying_newlines()
    {
        $tests = [
            "# Foo\n\nHello world.",
            "# Foo\r\n\r\nHello world.",
            "\n\n\n# Foo \r\n\r\n\n\n\n Hello world.",
        ];

        foreach ($tests as $test) {
            $page = HydeSmartDocs::create($this->mock, MarkdownConverter::parse($test));
            $this->assertEqualsIgnoringNewlines('<p>Hello world.</p>', $page->renderBody());
        }
    }

    // Test renderFooter is empty by default
    public function test_render_footer_is_empty_by_default()
    {
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('', $page->renderFooter());
    }

    // Test addDynamicHeaderContent adds source link when conditions are met
    public function test_add_dynamic_header_content_adds_source_link_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'header']);
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('<h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderHeader());
    }

    // Test edit source link is added to footer when conditions are met
    public function test_edit_source_link_is_added_to_footer_when_conditions_are_met()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'footer']);
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('<p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderFooter());
    }

    // Test edit source link can be added to both header and footer
    public function test_edit_source_link_can_be_added_to_both_header_and_footer()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('<h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderHeader());
        $this->assertEqualsIgnoringNewlines('<p class="edit-page-link"><a href="https://example.com/foo.md">Edit Source</a></p>', $page->renderFooter());
    }

    // Test edit source link text can be customized
    public function test_edit_source_link_text_can_be_customized()
    {
        config(['docs.source_file_location_base' => 'https://example.com/']);
        config(['docs.edit_source_link_position' => 'both']);
        config(['docs.edit_source_link_text' => 'Go to Source']);
        $page = HydeSmartDocs::create($this->mock, $this->html);

        $this->assertEqualsIgnoringNewlines('<h1>Foo</h1><p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>', $page->renderHeader());
        $this->assertEqualsIgnoringNewlines('<p class="edit-page-link"><a href="https://example.com/foo.md">Go to Source</a></p>', $page->renderFooter());
    }

    // Test addDynamicFooterContent adds torchlight attribution when conditions are met
    public function test_add_dynamic_footer_content_adds_torchlight_attribution_when_conditions_are_met()
    {
        $this->mockTorchlight();
        $page = HydeSmartDocs::create($this->mock, 'Syntax highlighted by torchlight.dev');

        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/"', $page->renderFooter());
    }
}
