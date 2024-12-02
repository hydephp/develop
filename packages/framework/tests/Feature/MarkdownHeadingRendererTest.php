<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Framework\Services\MarkdownService;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;

/**
 * @covers \Hyde\Markdown\Processing\HeadingRenderer
 *
 * @see \Hyde\Framework\Testing\Unit\HeadingRendererUnitTest
 */
class MarkdownHeadingRendererTest extends TestCase
{
    public function testBasicHeadingRendering()
    {
        $markdown = <<<'MARKDOWN'
        # Heading 1
        ## Heading 2
        ### Heading 3
        #### Heading 4
        ##### Heading 5
        ###### Heading 6
        MARKDOWN;

        $html = (new MarkdownService($markdown))->parse();

        $this->assertStringContainsString('<h1>Heading 1</h1>', $html);
        $this->assertStringContainsString('<h2>Heading 2</h2>', $html);
        $this->assertStringContainsString('<h3>Heading 3</h3>', $html);
        $this->assertStringContainsString('<h4>Heading 4</h4>', $html);
        $this->assertStringContainsString('<h5>Heading 5</h5>', $html);
        $this->assertStringContainsString('<h6>Heading 6</h6>', $html);

        $this->assertSame(<<<'HTML'
        <h1>Heading 1</h1>
        <h2>Heading 2</h2>
        <h3>Heading 3</h3>
        <h4>Heading 4</h4>
        <h5>Heading 5</h5>
        <h6>Heading 6</h6>

        HTML, $html);
    }

    public function testPermalinksInDocumentationPages()
    {
        $markdown = '## Documentation Heading';
        $html = (new MarkdownService($markdown, DocumentationPage::class))->parse();

        $this->assertStringContainsString('heading-permalink', $html);
        $this->assertStringContainsString('id="documentation-heading"', $html);
        $this->assertStringContainsString('href="#documentation-heading"', $html);
        $this->assertStringContainsString('title="Permalink"', $html);
        // $this->assertStringContainsString('aria-label="Permalink to this heading"', $html);

        $this->assertSame(<<<'HTML'
        <h2>Documentation Heading<a id="documentation-heading" href="#documentation-heading" class="heading-permalink" title="Permalink"></a></h2>

        HTML, $html);
    }

    public function testPermalinksAreNotAddedToRegularMarkdownPages()
    {
        $markdown = '## Regular Page Heading';
        $html = (new MarkdownService($markdown, MarkdownPage::class))->parse();

        $this->assertStringNotContainsString('heading-permalink', $html);
    }

    public function testSetextStyleHeadings()
    {
        $markdown = <<<'MARKDOWN'
        Heading 1
        =========
        
        Heading 2
        ---------
        MARKDOWN;

        $html = (new MarkdownService($markdown))->parse();

        $this->assertStringContainsString('<h1>Heading 1</h1>', $html);
        $this->assertStringContainsString('<h2>Heading 2</h2>', $html);
    }

    public function testHeadingsWithCustomAttributes()
    {
        $markdown = <<<'MARKDOWN'
        ## Heading {.custom-class #custom-id}
        ### Another Heading {data-test="value"}
        MARKDOWN;

        $html = (new MarkdownService($markdown, MarkdownPage::class))->parse();

        $this->assertStringContainsString('id="custom-id"', $html);
        $this->assertStringContainsString('class="custom-class"', $html);
        $this->assertStringContainsString('data-test="value"', $html);

        $this->assertSame(<<<'HTML'
        <h2 class="custom-class" id="custom-id">Heading</h2>
        <h3 data-test="value">Another Heading</h3>
        
        HTML, $html);
    }

    public function testHeadingsWithCustomAttributesAndPermalinks()
    {
        $markdown = <<<'MARKDOWN'
        ## Heading {.custom-class #custom-id}
        ### Another Heading {data-test="value"}
        MARKDOWN;

        $html = (new MarkdownService($markdown, DocumentationPage::class))->parse();

        $this->assertStringContainsString('id="custom-id"', $html);
        $this->assertStringContainsString('class="custom-class"', $html);
        $this->assertStringContainsString('data-test="value"', $html);

        $this->assertSame(<<<'HTML'
        <h2 class="custom-class" id="custom-id">Heading<a id="heading" href="#heading" class="heading-permalink" title="Permalink"></a></h2>
        <h3 data-test="value">Another Heading<a id="another-heading" href="#another-heading" class="heading-permalink" title="Permalink"></a></h3>
        
        HTML, $html);
    }

    public function testPermalinkConfigurationLevels()
    {
        config(['markdown.permalinks.min_level' => 2]);
        config(['markdown.permalinks.max_level' => 4]);

        $markdown = <<<'MARKDOWN'
        # H1 No Permalink
        ## H2 Has Permalink
        ### H3 Has Permalink
        #### H4 Has Permalink
        ##### H5 No Permalink
        ###### H6 No Permalink
        MARKDOWN;

        $html = (new MarkdownService($markdown, DocumentationPage::class))->parse();

        $this->assertStringNotContainsString('<h1>H1 No Permalink</h1><a', $html);
        $this->assertStringContainsString('<h2>H2 Has Permalink<a', $html);
        $this->assertStringContainsString('<h3>H3 Has Permalink<a', $html);
        $this->assertStringContainsString('<h4>H4 Has Permalink<a', $html);
        $this->assertStringNotContainsString('<h5>H5 No Permalink</h1><a', $html);
        $this->assertStringNotContainsString('<h6>H6 No Permalink</h1><a', $html);

        $this->assertSame(<<<'HTML'
        <h1>H1 No Permalink</h1>
        <h2>H2 Has Permalink<a id="h2-has-permalink" href="#h2-has-permalink" class="heading-permalink" title="Permalink"></a></h2>
        <h3>H3 Has Permalink<a id="h3-has-permalink" href="#h3-has-permalink" class="heading-permalink" title="Permalink"></a></h3>
        <h4>H4 Has Permalink<a id="h4-has-permalink" href="#h4-has-permalink" class="heading-permalink" title="Permalink"></a></h4>
        <h5>H5 No Permalink</h5>
        <h6>H6 No Permalink</h6>
        
        HTML, $html);
    }

    public function testDisablingPermalinksGlobally()
    {
        config(['markdown.permalinks.enabled' => false]);

        $markdown = '## Heading';
        $html = (new MarkdownService($markdown, DocumentationPage::class))->parse();

        $this->assertStringNotContainsString('heading-permalink', $html);
    }

    public function testHeadingsWithSpecialCharacters()
    {
        $markdown = <<<'MARKDOWN'
        ## Heading with & special < > "characters"
        ### Heading with émojis 🎉
        MARKDOWN;

        $html = (new MarkdownService($markdown, DocumentationPage::class))->parse();

        $this->assertStringContainsString('Heading with &amp; special &lt; &gt; &quot;characters&quot;', $html);
        $this->assertStringContainsString('Heading with émojis 🎉', $html);

        // Todo: Try to normalize to heading-with-special-characters?
        $this->assertSame(<<<'HTML'
        <h2>Heading with &amp; special &lt; &gt; &quot;characters&quot;<a id="heading-with-amp-special-lt-gt-quotcharactersquot" href="#heading-with-amp-special-lt-gt-quotcharactersquot" class="heading-permalink" title="Permalink"></a></h2>
        <h3>Heading with émojis 🎉<a id="heading-with-emojis" href="#heading-with-emojis" class="heading-permalink" title="Permalink"></a></h3>

        HTML, $html);
    }

    public function testHeadingsAllowMarkdownStyling()
    {
        $markdown = <<<'MARKDOWN'
        ## Heading with **Markdown** styling
        MARKDOWN;

        $html = (new MarkdownService($markdown, MarkdownPage::class))->parse();

        $this->assertStringContainsString('Heading with <strong>Markdown</strong> styling', $html);

        $this->assertSame(<<<'HTML'
        <h2>Heading with <strong>Markdown</strong> styling</h2>

        HTML, $html);
    }

    public function testHeadingsAllowBasicHtmlButEscapesDangerousInput()
    {
        $markdown = <<<'MARKDOWN'
        ## Heading with <strong>HTML</strong>
        ### Heading with <script>alert('XSS')</script>
        MARKDOWN;

        $html = (new MarkdownService($markdown, MarkdownPage::class))->parse();

        $this->assertStringContainsString('Heading with <strong>HTML</strong>', $html);
        $this->assertStringContainsString("Heading with &lt;script>alert('XSS')&lt;/script>", $html);

        $this->assertSame(<<<'HTML'
        <h2>Heading with <strong>HTML</strong></h2>
        <h3>Heading with &lt;script>alert('XSS')&lt;/script></h3>

        HTML, $html);
    }

    public function testCustomPageClassConfiguration()
    {
        config(['markdown.permalinks.pages' => [MarkdownPage::class]]);

        $markdown = '## Test Heading';

        // Should now have permalinks
        $html = (new MarkdownService($markdown, MarkdownPage::class))->parse();
        $this->assertStringContainsString('heading-permalink', $html);

        // Should not have permalinks
        $html = (new MarkdownService($markdown, DocumentationPage::class))->parse();
        $this->assertStringNotContainsString('heading-permalink', $html);
    }
}
