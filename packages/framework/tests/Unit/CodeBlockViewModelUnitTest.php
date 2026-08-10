<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Markdown\Extensions\CodeBlockViewModel;
use Hyde\Testing\UnitTestCase;
use Hyde\Testing\UsesRealBladeInUnitTests;
use Illuminate\Support\HtmlString;

/**
 * @see \Hyde\Framework\Testing\Feature\CodeBlocksTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Extensions\CodeBlockViewModel::class)]
class CodeBlockViewModelUnitTest extends UnitTestCase
{
    use UsesRealBladeInUnitTests;

    protected static bool $needsKernel = true;

    protected function setUp(): void
    {
        $this->createRealBladeCompilerEnvironment();
    }

    public function testCanConstructWithOnlyContents()
    {
        $model = new CodeBlockViewModel('<pre><code>Hello</code></pre>');

        $this->assertSame('<pre><code>Hello</code></pre>', $model->contents);
        $this->assertNull($model->language);
        $this->assertNull($model->label);
    }

    public function testCanConstructWithAllArguments()
    {
        $model = new CodeBlockViewModel('<pre><code>Hello</code></pre>', 'php', 'foo.php');

        $this->assertSame('php', $model->language);
        $this->assertSame('foo.php', $model->label);
    }

    public function testLabelCanBeAnHtmlString()
    {
        $label = new HtmlString('<a href="#">foo.php</a>');

        $this->assertSame($label, (new CodeBlockViewModel('<pre><code>Hello</code></pre>', label: $label))->label);
    }

    public function testRenderReturnsCodeBlockComponent()
    {
        $html = (new CodeBlockViewModel('<pre><code>Hello</code></pre>'))->render();

        $this->assertStringContainsString('<div class="hyde-code-block ', $html);
        $this->assertStringContainsString('<pre><code>Hello</code></pre>', $html);
    }

    public function testRenderDoesNotEscapeContents()
    {
        $this->assertStringNotContainsString('&lt;pre&gt;',
            (new CodeBlockViewModel('<pre><code>Hello</code></pre>'))->render()
        );
    }

    public function testRenderOmitsTheLabelWhenNoneIsSet()
    {
        $this->assertStringNotContainsString('hyde-code-block-label',
            (new CodeBlockViewModel('<pre><code>Hello</code></pre>'))->render()
        );
    }

    public function testRenderIncludesTheLabelWhenOneIsSet()
    {
        $html = (new CodeBlockViewModel('<pre><code>Hello</code></pre>', label: 'foo.php'))->render();

        $this->assertStringContainsString('hyde-code-block-label', $html);
        $this->assertStringContainsString('<figure class="hyde-code-block ', $html);
        $this->assertStringContainsString('<figcaption class="hyde-code-block-label ', $html);
        $this->assertStringContainsString('>foo.php</figcaption>', $html);
    }

    public function testRenderEscapesAStringLabel()
    {
        $html = (new CodeBlockViewModel('<pre><code>Hello</code></pre>', label: '<script>alert(1)</script>'))->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function testRenderDoesNotEscapeAnHtmlStringLabel()
    {
        $html = (new CodeBlockViewModel('<pre><code>Hello</code></pre>', label: new HtmlString('<a href="#">foo.php</a>')))->render();

        $this->assertStringContainsString('<a href="#">foo.php</a>', $html);
    }
}
