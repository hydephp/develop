<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\MarkdownConverterService;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\MarkdownConverterService
 */
class MarkdownConverterServiceTest extends TestCase
{
    public function test_service_can_parse_markdown_to_html()
    {
        $markdown = '# Hello World!';

        $html = (new MarkdownConverterService($markdown))->parse();

        $this->assertIsString($html);
        $this->assertEquals("<h1>Hello World!</h1>\n", $html);
    }

    public function test_service_can_parse_markdown_to_html_with_permalinks()
    {
        $markdown = '## Hello World!';

        $html = (new MarkdownConverterService($markdown))->withPermalinks()->parse();

        $this->assertIsString($html);
        $this->assertEquals(
            '<h2>Hello World!<a id="hello-world" href="#hello-world" class="heading-permalink" aria-hidden="true" '.
            'title="Permalink">#</a></h2>'."\n",
            $html
        );
    }

    public function test_torchlight_extension_is_not_enabled_by_default()
    {
        $markdown = '# Hello World!';
        $service = new MarkdownConverterService($markdown);
        $service->parse();
        $this->assertNotContains('Torchlight\Commonmark\V2\TorchlightExtension', $service->getExtensions());
    }

    public function test_torchlight_extension_is_enabled_automatically_when_has_torchlight_feature()
    {
        $markdown = '# Hello World!';
        $service = new MarkdownConverterService($markdown);
        $service->addFeature('torchlight')->parse();
        $this->assertContains('Torchlight\Commonmark\V2\TorchlightExtension', $service->getExtensions());
    }

    public function test_torchlight_integration_injects_attribution()
    {
        $markdown = '# Hello World! <!-- Syntax highlighted by torchlight.dev -->';

        // Enable the extension in config

        $service = new MarkdownConverterService($markdown);

        $html = $service->parse();

        $this->assertStringContainsString('Syntax highlighting by <a href="https://torchlight.dev/" '
                .'rel="noopener nofollow">Torchlight.dev</a>', $html);
    }

    public function test_bladedown_is_not_enabled_by_default()
    {
        $service = new MarkdownConverterService('[Blade]: {{ "Hello World!" }}');
        $this->assertEquals("<p>[Blade]: {{ &quot;Hello World!&quot; }}</p>\n", $service->parse());
    }

    public function test_bladedown_can_be_enabled()
    {
        config(['markdown.enable_blade' => true]);
        $service = new MarkdownConverterService('[Blade]: {{ "Hello World!" }}');
        $service->addFeature('bladedown')->parse();
        $this->assertEquals("Hello World!\n", $service->parse());
    }
}
